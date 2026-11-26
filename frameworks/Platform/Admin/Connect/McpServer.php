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
use WPEssential\Platform\Admin\Connect\Mcp\BuilderTools;
use WPEssential\Platform\Admin\Connect\Mcp\SettingsAbilities;
use WPEssential\Platform\Admin\Connect\Mcp\SiteTools;
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
		if ( function_exists( 'add_filter' ) ) {
			// Connector links (Claude custom connectors, ChatGPT) sign in before REST runs.
			add_filter( 'determine_current_user', [ $this->connect, 'authenticateLink' ], 30 );
			BuilderTools::hooks();
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

	public function permission(): bool|\WP_Error {
		if ( ! $this->connect->enabled() ) {
			return new \WP_Error( 'wpe_mcp_off', 'The WPEssential MCP server is switched off. Turn it on under WPEssential → AI Connect.', [ 'status' => 403 ] );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$link = AiConnect::linkSession();
		if ( $link !== null ) {
			foreach ( $this->connect->sessions( get_current_user_id() ) as $session ) {
				if ( $session['uuid'] === $link ) {
					return true;
				}
			}

			return false; // A revoked connector link stays refused.
		}

		// Any WordPress application password of an administrator works, like the WordPress MCP Adapter
		// (Elementor, Claude Code "Basic" headers); WordPress itself verified it before REST ran.
		return function_exists( 'rest_get_authenticated_app_password' ) && (string) rest_get_authenticated_app_password() !== '';
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
					'instructions'    => ( function_exists( 'get_bloginfo' ) ? SiteTools::instructions() . ' ' : '' ) . 'Confirm changes with the user before calling a tool that changes the site.',
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
		foreach ( $this->siteTools() as $tool ) {
			$tools[] = [
				'name'        => $tool['name'],
				'description' => (string) $tool['description'],
				'inputSchema' => $tool['inputSchema'],
				'annotations' => [ 'readOnlyHint' => ! in_array( $tool['name'], SiteTools::WRITES, true ) && $tool['name'] !== 'wp_rest' ],
			];
		}
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

	/**
	 * Site and builder tools (ported from the Hi WPEssential theme); write tools only while writes are allowed.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function siteTools(): array {
		if ( ! function_exists( 'apply_filters' ) ) {
			return [];
		}
		$writes = $this->connect->allowWrites();
		$tools  = [];
		foreach ( SiteTools::tools() as $name => $tool ) {
			if ( $name === 'site_info' || ( ! $writes && in_array( $name, SiteTools::WRITES, true ) ) ) {
				continue;
			}
			if ( ! $writes && $name === 'block_templates' ) {
				$tool['description'] = 'Gutenberg site editor: list templates and template parts. Saving needs "Allow AI to change settings".';
			}
			$tools[ (string) $name ] = $tool;
		}

		return $tools;
	}

	/**
	 * Runs a site or builder tool. Read-only mode refuses REST writes and template saves.
	 *
	 * @param array<string,mixed> $tool
	 * @param array<string,mixed> $arguments
	 * @return array<string,mixed>
	 */
	private function runSiteTool( mixed $id, array $tool, array $arguments ): array {
		$writes = $this->connect->allowWrites();
		$method = strtoupper( (string) ( $arguments['method'] ?? 'GET' ) );
		if ( ! $writes && ( ( $tool['name'] === 'wp_rest' && $method !== 'GET' ) || ( $tool['name'] === 'block_templates' && ! empty( $arguments['content'] ) )
			|| ( in_array( $tool['name'], [ 'elementor_kit', 'global_styles' ], true ) && ( ! empty( $arguments['settings'] ) || ! empty( $arguments['styles'] ) ) ) ) ) {
			return self::result( $id, self::content( [ 'error' => 'Read-only: turn on "Allow AI to change settings" under WPEssential → AI Connect.' ], true ) );
		}
		try {
			$data = call_user_func( $tool['run'], $arguments );
		} catch ( Throwable $error ) {
			return self::result( $id, self::content( [ 'error' => $error->getMessage() ], true ) );
		}
		if ( $data instanceof \WP_Error ) {
			return self::result( $id, self::content( [ 'error' => $data->get_error_message() ], true ) );
		}

		return self::result( $id, self::content( $data ) );
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
			return self::result( $id, self::content( function_exists( 'get_bloginfo' ) && function_exists( 'wp_get_theme' ) ? SiteTools::tool_site_info() + $this->siteInfo() : $this->siteInfo() ) );
		}
		$site       = $this->siteTools()[ $tool ] ?? null;
		$descriptor = $site === null ? ( $this->exposed()[ $tool ] ?? null ) : null;
		if ( $site === null && $descriptor === null ) {
			return self::error( $id, -32602, 'Unknown tool' );
		}

		$context  = new ExecutionContext(
			principal: new Principal( ( $this->currentUser )() > 0 ? ( $this->currentUser )() : null ),
			siteId: function_exists( 'get_current_blog_id' ) ? max( 1, (int) get_current_blog_id() ) : 1,
			channel: ExecutionChannel::Ai,
		);
		$started  = microtime( true );
		$meta     = [
			'client'    => $this->connect->clientFor( ( $this->currentUser )(), AiConnect::linkSession() ?? ( function_exists( 'rest_get_authenticated_app_password' ) ? rest_get_authenticated_app_password() : null ) ),
			'arguments' => $arguments,
		];
		if ( $site !== null ) {
			// Site tools (REST, templates, builder data) are not abilities, but every AI call is still audited.
			$response = $this->runSiteTool( $id, $site, $arguments );
			$failed   = ! empty( $response['result']['isError'] );
			$this->record( $context, 'site/' . $tool, $failed ? AuditOutcome::Failed : AuditOutcome::Success, $failed ? (string) ( $response['result']['structuredContent']['error'] ?? '' ) : null, $meta + [ 'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ) ] );

			return $response;
		}
		if ( $descriptor === null ) {
			return self::error( $id, -32602, 'Unknown tool' );
		}
		$decision = $this->abilities->authorize( $descriptor->name, $context, $arguments );
		if ( ! $decision->allowed ) {
			$this->record( $context, $descriptor->name, AuditOutcome::Denied, $decision->reason, $meta );

			return self::result( $id, self::content( [ 'error' => 'Not allowed: ' . $decision->reason ], true ) );
		}

		try {
			$output = $this->abilities->execute( $descriptor->name, $arguments, $context );
		} catch ( Throwable $error ) {
			$this->record( $context, $descriptor->name, AuditOutcome::Failed, $error->getMessage(), $meta + [ 'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ) ] );

			return self::result( $id, self::content( [ 'error' => $error->getMessage() ], true ) );
		}
		$this->record( $context, $descriptor->name, AuditOutcome::Success, null, $meta + [ 'duration_ms' => (int) round( ( microtime( true ) - $started ) * 1000 ) ] );

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

	/** @param array<string,mixed> $metadata */
	private function record( ExecutionContext $context, string $resourceId, AuditOutcome $outcome, ?string $reason, array $metadata = [] ): void {
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
					resourceId: $resourceId,
					reason: $reason,
					metadata: $metadata,
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
			// MCP requires an object here; lists and scalars are wrapped.
			'structuredContent' => is_array( $data ) && $data !== [] && ! array_is_list( $data ) ? $data : [ 'value' => $data ],
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
