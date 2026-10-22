<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WP_Error;

/**
 * AI Connect state: the MCP on/off switch, the "Allow AI to change settings" switch and the
 * Application Passwords that connected clients use. Both switches are off by default (ARCHITECTURE §16).
 */
final class AiConnect {

	public const OPTION   = 'wpessential_ai_connect';
	public const APP_NAME = 'WPEssential AI';
	public const CLIENTS  = [
		'claude-code'    => 'Claude Code',
		'claude-desktop' => 'Claude Desktop',
		'cursor'         => 'Cursor',
		'vscode'         => 'VS Code',
		'windsurf'       => 'Windsurf',
		'gemini'         => 'Gemini CLI',
		'generic'        => 'Other MCP client',
	];

	/** @var array<string,bool> Used when WordPress options are unavailable (unit tests, early boot). */
	private array $memory = [];

	/** @return array{enabled:bool,allow_writes:bool} */
	public function settings(): array {
		$stored = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : $this->memory;
		$stored = is_array( $stored ) ? $stored : [];

		return [
			'enabled'      => ! empty( $stored['enabled'] ),
			'allow_writes' => ! empty( $stored['allow_writes'] ),
		];
	}

	public function enabled(): bool {
		return $this->settings()['enabled'];
	}

	public function allowWrites(): bool {
		return $this->settings()['allow_writes'];
	}

	public function set( string $key, bool $value ): void {
		$settings         = $this->settings();
		$settings[ $key ] = $value;
		if ( $key === 'enabled' && ! $value ) {
			$settings['allow_writes'] = false;
		}
		if ( function_exists( 'update_option' ) ) {
			update_option( self::OPTION, $settings, false );
		} else {
			$this->memory = $settings;
		}
	}

	public static function endpoint(): string {
		return function_exists( 'rest_url' ) ? (string) rest_url( McpServer::NAMESPACE . McpServer::ROUTE ) : '/wp-json/' . McpServer::NAMESPACE . McpServer::ROUTE;
	}

	public static function secure(): bool {
		return function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available()
			&& ( ( function_exists( 'is_ssl' ) && is_ssl() ) || ( function_exists( 'wp_get_environment_type' ) && wp_get_environment_type() === 'local' ) );
	}

	/** @return list<array{uuid:string,client:string,created:int,last_used:int}> */
	public function sessions( int $userId ): array {
		if ( ! class_exists( 'WP_Application_Passwords' ) ) {
			return [];
		}
		$sessions = [];
		foreach ( \WP_Application_Passwords::get_user_application_passwords( $userId ) as $item ) {
			$name = (string) ( $item['name'] ?? '' );
			if ( ! str_starts_with( $name, self::APP_NAME ) ) {
				continue;
			}
			$sessions[] = [
				'uuid'      => (string) $item['uuid'],
				'client'    => trim( substr( $name, strlen( self::APP_NAME ) ), ' ·' ),
				'created'   => (int) ( $item['created'] ?? 0 ),
				'last_used' => (int) ( $item['last_used'] ?? 0 ),
			];
		}

		return $sessions;
	}

	/** @return array{password:string,uuid:string}|WP_Error The plain password is returned once and never stored. */
	public function connect( int $userId, string $client ): array|WP_Error {
		if ( ! $this->enabled() ) {
			return new WP_Error( 'wpe_ai_off', __( 'Turn AI Connect on first.', 'wpessential' ) );
		}
		if ( ! self::secure() || ! class_exists( 'WP_Application_Passwords' ) ) {
			return new WP_Error( 'wpe_ai_insecure', __( 'AI Connect needs HTTPS and Application Passwords.', 'wpessential' ) );
		}
		$created = \WP_Application_Passwords::create_new_application_password(
			$userId,
			[ 'name' => self::APP_NAME . ' · ' . ( self::CLIENTS[ $client ] ?? self::CLIENTS['generic'] ) ]
		);
		if ( $created instanceof WP_Error ) {
			return $created;
		}

		return [
			'password' => (string) $created[0],
			'uuid'     => (string) $created[1]['uuid'],
		];
	}

	public function revoke( int $userId, string $uuid ): bool {
		foreach ( $this->sessions( $userId ) as $session ) {
			if ( $session['uuid'] === $uuid ) {
				return \WP_Application_Passwords::delete_application_password( $userId, $uuid ) === true;
			}
		}

		return false;
	}

	public function revokeAll( int $userId ): void {
		foreach ( $this->sessions( $userId ) as $session ) {
			\WP_Application_Passwords::delete_application_password( $userId, $session['uuid'] );
		}
	}

	/** Ready-to-paste client configuration. The password is shown once on the screen that created it. */
	public static function snippet( string $client, string $login, string $password ): string {
		$header = 'Basic ' . base64_encode( $login . ':' . str_replace( ' ', '', $password ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- HTTP Basic credentials.
		$url    = self::endpoint();

		if ( $client === 'claude-code' ) {
			return 'claude mcp add --transport http wpessential ' . $url . ' --header "Authorization: ' . $header . '"';
		}

		$server = [
			'type'    => 'http',
			'url'     => $url,
			'headers' => [ 'Authorization' => $header ],
		];
		$key    = $client === 'vscode' ? 'servers' : 'mcpServers';
		if ( $client === 'windsurf' ) {
			$server = [
				'serverUrl' => $url,
				'headers'   => [ 'Authorization' => $header ],
			];
		}

		return (string) wp_json_encode( [ $key => [ 'wpessential' => $server ] ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
	}
}
