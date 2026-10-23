<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use Throwable;
use WP_REST_Request;
use WP_REST_Response;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Connect\Mcp\SettingsAbilities;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

/**
 * WPEssential MCP server (JSON-RPC 2.0 over HTTP, ARCHITECTURE §16).
 * Tools are the abilities that allow the AI channel. Mutating tools are listed only when
 * "Allow AI to change settings" is on. Only AI Connect Application Passwords are accepted.
 */
final class McpServer {

	public const NAMESPACE = 'wpessential/v1';
	public const ROUTE     = '/mcp';
	public const PROTOCOL  = '2025-06-18';

	/** @var Closure():int */
	private readonly Closure $currentUser;

	/** @param (Closure():int)|null $currentUser */
	public function __construct(
		private readonly AbilityRegistry $abilities,
		private readonly AiConnect $connect,
		private readonly ?AuditLoggerInterface $audit = null,
		?Closure $currentUser = null,
	) {
		$this->currentUser = $currentUser ?? static fn (): int => function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
	}

	public function register(): void {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'rest_api_init', [ $this, 'routes' ] );
		}
	}

	public function routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			[
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'handle' ],
					'permission_callback' => [ $this, 'permission' ],
				],
				[
					'methods'             => 'GET',
					'callback'            => static fn (): WP_REST_Response => new WP_REST_Response( null, 405 ),
					'permission_callback' => '__return_true',
				],
			]
		);
	}

	public function permission(): bool {
		if ( ! $this->connect->enabled() || ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$uuid = function_exists( 'rest_get_authenticated_app_password' ) ? (string) rest_get_authenticated_app_password() : '';
		foreach ( $this->connect->sessions( get_current_user_id() ) as $session ) {
			if ( $uuid !== '' && $session['uuid'] === $uuid ) {
				return true;
			}
		}

		return false;
	}

	public function handle( WP_REST_Request $request ): WP_REST_Response {
		$message = json_decode( (string) $request->get_body(), true );
		if ( ! is_array( $message ) || ( $message['jsonrpc'] ?? '' ) !== '2.0' || ! is_string( $message['method'] ?? null ) ) {
			return new WP_REST_Response( self::error( null, -32600, 'Invalid Request' ), 400 );
		}
		if ( ! array_key_exists( 'id', $message ) ) {
			return new WP_REST_Response( null, 202 ); // Notification.
		}
		$params = is_array( $message['params'] ?? null ) ? $message['params'] : [];

		return new WP_REST_Response( $this->dispatch( $message['id'], $message['method'], $params ), 200 );
	}

	/**
	 * @param array<string,mixed> $params
	 * @return array<string,mixed>
	 */
	public function dispatch( mixed $id, string $method, array $params ): array {
		return match ( $method ) {
			'initialize' => self::result(
				$id,
				[
					'protocolVersion' => self::PROTOCOL,
					'capabilities'    => [ 'tools' => [ 'listChanged' => false ] ],
					'serverInfo'      => [
						'name'    => 'wpessential',
						'version' => defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : '0.0.0',
					],
					'instructions'    => 'Manage WPEssential on this WordPress site. Confirm changes with the user before calling a tool that changes settings.',
				]
			),
			'ping'       => self::result( $id, [] ),
			'tools/list' => self::result( $id, [ 'tools' => $this->tools() ] ),
			'tools/call' => $this->call( $id, (string) ( $params['name'] ?? '' ), is_array( $params['arguments'] ?? null ) ? $params['arguments'] : [] ),
			default      => self::error( $id, -32601, 'Method not found' ),
		};
	}

	/** @return list<array<string,mixed>> */
	public function tools(): array {
		$tools = [
			[
				'name'        => 'site_info',
				'description' => 'Site name, URL, WordPress and WPEssential versions.',
				'inputSchema' => [
					'type'       => 'object',
					'properties' => (object) [],
				],
			],
		];
		foreach ( $this->exposed() as $tool => $descriptor ) {
			$schema  = $descriptor->inputSchema !== [] ? $descriptor->inputSchema : [
				'type'       => 'object',
				'properties' => (object) [],
			];
			$tools[] = [
				'name'        => $tool,
				'description' => (string) ( $schema['description'] ?? $descriptor->name ),
				'inputSchema' => $schema,
				'annotations' => [
					'readOnlyHint'    => ! $descriptor->mutates,
					'destructiveHint' => false,
				],
			];
		}

		return $tools;
	}

	/** @return array<string,AbilityDescriptor> MCP tool name => descriptor. */
	private function exposed(): array {
		$writes = $this->connect->allowWrites();
		$out    = [];
		foreach ( $this->abilities->descriptors() as $descriptor ) {
			if ( $descriptor->allows( ExecutionChannel::Ai ) && ( $writes || ! $descriptor->mutates ) ) {
				$out[ str_replace( [ '/', '-' ], '_', $descriptor->name ) ] = $descriptor;
			}
		}

		return $out;
	}

	/**
	 * @param array<string,mixed> $arguments
	 * @return array<string,mixed>
	 */
	private function call( mixed $id, string $tool, array $arguments ): array {
		if ( $tool === 'site_info' ) {
			return self::result( $id, self::content( $this->siteInfo() ) );
		}
		$descriptor = $this->exposed()[ $tool ] ?? null;
		if ( $descriptor === null ) {
			return self::error( $id, -32602, 'Unknown tool' );
		}

		$context  = new ExecutionContext(
			principal: new Principal( ( $this->currentUser )() > 0 ? ( $this->currentUser )() : null ),
			siteId: function_exists( 'get_current_blog_id' ) ? max( 1, (int) get_current_blog_id() ) : 1,
			channel: ExecutionChannel::Ai,
		);
		$decision = $this->abilities->authorize( $descriptor->name, $context, $arguments );
		if ( ! $decision->allowed ) {
			$this->record( $context, $descriptor, AuditOutcome::Denied, $decision->reason );

			return self::result( $id, self::content( [ 'error' => 'Not allowed: ' . $decision->reason ], true ) );
		}

		try {
			$output = $this->abilities->execute( $descriptor->name, $arguments, $context );
		} catch ( Throwable $error ) {
			$this->record( $context, $descriptor, AuditOutcome::Failed, $error->getMessage() );

			return self::result( $id, self::content( [ 'error' => $error->getMessage() ], true ) );
		}
		$this->record( $context, $descriptor, AuditOutcome::Success, null );

		return self::result( $id, self::content( $output ) );
	}

	/** @return array<string,string> */
	private function siteInfo(): array {
		global $wp_version;

		return [
			'name'        => function_exists( 'get_bloginfo' ) ? (string) get_bloginfo( 'name' ) : '',
			'url'         => function_exists( 'home_url' ) ? (string) home_url() : '',
			'wordpress'   => is_string( $wp_version ?? null ) ? $wp_version : '',
			'wpessential' => defined( 'WPE_VERSION' ) ? (string) WPE_VERSION : '',
			'ai_writes'   => $this->connect->allowWrites() ? 'allowed' : 'read-only',
		];
	}

	private function record( ExecutionContext $context, AbilityDescriptor $descriptor, AuditOutcome $outcome, ?string $reason ): void {
		if ( $this->audit === null || ! function_exists( 'wp_generate_uuid4' ) ) {
			return;
		}
		try {
			$this->audit->record(
				new AuditRecord(
					id: wp_generate_uuid4(),
					context: $context,
					ownerSurfaceId: SettingsAbilities::OWNER_SURFACE_ID,
					action: 'mcp.tools.call',
					outcome: $outcome,
					resourceType: 'ability',
					resourceId: $descriptor->name,
					reason: $reason,
				)
			);
		} catch ( Throwable ) {
			// Audit storage failures must not break the AI client; the logger reports its own health.
			return;
		}
	}

	/** @return array<string,mixed> */
	private static function content( mixed $data, bool $isError = false ): array {
		return [
			'content'           => [
				[
					'type' => 'text',
					'text' => (string) json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ), // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode -- runs outside WordPress in tests.
				],
			],
			'structuredContent' => is_array( $data ) ? $data : [ 'value' => $data ],
			'isError'           => $isError,
		];
	}

	/**
	 * @param array<string,mixed> $result
	 * @return array<string,mixed>
	 */
	private static function result( mixed $id, array $result ): array {
		return [
			'jsonrpc' => '2.0',
			'id'      => $id,
			'result'  => $result === [] ? (object) [] : $result,
		];
	}

	/** @return array<string,mixed> */
	private static function error( mixed $id, int $code, string $message ): array {
		return [
			'jsonrpc' => '2.0',
			'id'      => $id,
			'error'   => [
				'code'    => $code,
				'message' => $message,
			],
		];
	}
}
