<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionDeletableRepositoryInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class CustomPostTypeAbilityHandler implements AbilityHandlerInterface {

	public const LIST   = 'list';
	public const GET    = 'get';
	public const SAVE   = 'save';
	public const STATUS = 'status';
	public const DELETE = 'delete';

	public function __construct(
		private DefinitionRepositoryInterface $definitions,
		private CustomPostTypeDefinitionProjector $projector,
		private CustomPostTypeValidationService $validation,
		private string $action,
	) {
		if ( ! in_array( $this->action, [ self::LIST, self::GET, self::SAVE, self::STATUS, self::DELETE ], true ) ) {
			throw new InvalidArgumentException( 'Unsupported CPT ability action.' );
		}
	}

	public function handle( array $input, ExecutionContext $context ): mixed {
		return match ( $this->action ) {
			self::LIST => $this->list(),
			self::GET => $this->get( $input ),
			self::SAVE => $this->save( $input, $context ),
			self::STATUS => $this->changeStatus( $input ),
			self::DELETE => $this->delete( $input ),
			default => throw new RuntimeException( 'Unsupported CPT ability action.' ),
		};
	}

	/** @return array{definitions:list<array<string,mixed>>} */
	private function list(): array {
		$definitions = array_values(
			array_filter(
				$this->definitions->byType( CustomPostTypeDefinitionProjector::DEFINITION_TYPE ),
				static fn ( Definition $definition ): bool => $definition->ownerSurfaceId === CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
			)
		);
		usort( $definitions, static fn ( Definition $left, Definition $right ): int => [ $left->slug, $left->id ] <=> [ $right->slug, $right->id ] );

		return [ 'definitions' => array_map( $this->serialize( ... ), $definitions ) ];
	}

	/** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
	private function get( array $input ): array {
		return [ 'definition' => $this->serialize( $this->owned( $this->requiredUuid( $input, 'id' ) ) ) ];
	}

	/** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
	private function save( array $input, ExecutionContext $context ): array {
		$payload = $input['payload'] ?? null;
		if ( ! is_array( $payload ) || array_is_list( $payload ) ) {
			throw new InvalidArgumentException( 'CPT payload must be an object/map.' );
		}

		$id       = $input['id'] ?? null;
		$existing = null;
		if ( $id !== null ) {
			if ( ! is_string( $id ) || ! $this->isUuid( $id ) ) {
				throw new InvalidArgumentException( 'CPT id must be a lowercase RFC 4122 UUID.' );
			}
			$existing = $this->owned( $id );
			$this->assertExpectedRevision( $input, $existing );
		}

		$validationInput = [ 'payload' => $payload ];
		if ( $existing instanceof Definition ) {
			$validationInput['id'] = $existing->id;
		}
		$this->assertValidationAllowsMutation( $this->validation->validate( $validationInput ) );

		$key = $payload['post_type_key'] ?? null;
		if ( ! is_string( $key ) || trim( $key ) === '' ) {
			throw new InvalidArgumentException( 'CPT payload requires post_type_key.' );
		}
		$key = trim( $key );

		// AI may draft and edit, never publish, disable or archive: new definitions start as
		// drafts and an existing definition keeps its status (status changes are UI-only).
		if ( $context->channel === ExecutionChannel::Ai ) {
			// The entry lock is a human safeguard: AI can neither set nor clear it.
			unset( $payload['locked'] );
			if ( ( $existing?->payload['locked'] ?? null ) === true ) {
				$payload['locked'] = true;
			}
		}
		$current   = $existing?->status ?? DefinitionStatus::Draft;
		$status    = $context->channel === ExecutionChannel::Ai ? $current : $this->statusFromInput( $input, $current );
		$candidate = new Definition(
			id: $existing?->id ?? $this->uuid(),
			slug: $existing?->slug ?? ( 'cpt-' . str_replace( '_', '-', $key ) ),
			type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
			schemaVersion: $existing?->schemaVersion ?? 1,
			ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
			status: $status,
			payload: $payload,
			revision: ( $existing?->revision ?? 0 ) + 1,
			dependencies: $existing?->dependencies ?? [],
		);
		$this->validatePayload( $candidate );
		$candidate = $this->withChecksum( $candidate );
		$this->definitions->save( $candidate );

		return [ 'definition' => $this->serialize( $candidate ) ];
	}

	/**
	 * Deletes a definition permanently, like WordPress' "Delete Permanently". Refused while the
	 * entry is locked, while it is published (disable or archive it first, so the runtime
	 * registration is withdrawn), or while other definitions depend on it.
	 *
	 * @param array<string,mixed> $input
	 * @return array{deleted:string}
	 */
	private function delete( array $input ): array {
		$existing = $this->owned( $this->requiredUuid( $input, 'id' ) );
		$this->assertExpectedRevision( $input, $existing );
		if ( ( $existing->payload['locked'] ?? false ) === true ) {
			throw new InvalidArgumentException( 'This custom post type is locked. Turn off the entry lock before deleting it.' );
		}
		if ( $existing->status === DefinitionStatus::Published ) {
			throw new InvalidArgumentException( 'Disable or archive this custom post type before deleting it.' );
		}
		if ( $this->definitions->dependentsOf( $existing->id ) !== [] ) {
			throw new InvalidArgumentException( 'Other definitions depend on this custom post type; remove those links first.' );
		}
		if ( ! $this->definitions instanceof DefinitionDeletableRepositoryInterface ) {
			throw new RuntimeException( 'This site cannot delete custom post type definitions.' );
		}
		if ( ! $this->definitions->deleteIfCurrentRevision( $existing->id, $existing->revision ) ) {
			throw new RuntimeException( 'CPT write conflict: the definition changed before it could be deleted.' );
		}

		return [ 'deleted' => $existing->id ];
	}

	/** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
	private function changeStatus( array $input ): array {
		$existing = $this->owned( $this->requiredUuid( $input, 'id' ) );
		$this->assertExpectedRevision( $input, $existing );
		$status = $this->statusFromInput( $input, $existing->status, required: true );

		if ( $status === DefinitionStatus::Published ) {
			$this->assertValidationAllowsMutation(
				$this->validation->validate(
					[
						'id'      => $existing->id,
						'payload' => $existing->payload,
					]
				)
			);
		}

		$candidate = new Definition(
			id: $existing->id,
			slug: $existing->slug,
			type: $existing->type,
			schemaVersion: $existing->schemaVersion,
			ownerSurfaceId: $existing->ownerSurfaceId,
			status: $status,
			payload: $existing->payload,
			revision: $existing->revision + 1,
			dependencies: $existing->dependencies,
		);
		$this->validatePayload( $candidate );
		$candidate = $this->withChecksum( $candidate );
		$this->definitions->save( $candidate );

		return [ 'definition' => $this->serialize( $candidate ) ];
	}

	/** @param array<string,mixed> $input */
	private function assertExpectedRevision( array $input, Definition $existing ): void {
		$expected = $input['expected_revision'] ?? null;
		if ( ! is_int( $expected ) || $expected < 1 ) {
			throw new InvalidArgumentException( 'Updating a CPT requires a positive expected_revision.' );
		}
		if ( $expected !== $existing->revision ) {
			throw new RuntimeException(
				sprintf(
					'CPT write conflict: expected revision %d, current revision is %d.',
					$expected,
					$existing->revision,
				)
			);
		}
	}

	/**
	 * @param array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{post_type_key:?string}} $report
	 */
	private function assertValidationAllowsMutation( array $report ): void {
		if ( $report['valid'] ) {
			return;
		}

		foreach ( $report['issues'] as $issue ) {
			if ( $issue['severity'] === 'blocked' ) {
				throw new InvalidArgumentException( $issue['message'] );
			}
		}

		throw new InvalidArgumentException( 'CPT validation blocked the requested mutation.' );
	}

	/** @param array<string,mixed> $input */
	private function statusFromInput(
		array $input,
		DefinitionStatus $default,
		bool $required = false,
	): DefinitionStatus {
		$value = $input['status'] ?? null;
		if ( $value === null && ! $required ) {
			return $default;
		}
		if ( ! is_string( $value ) ) {
			throw new InvalidArgumentException( 'CPT status must be a string.' );
		}
		$status = DefinitionStatus::tryFrom( $value );
		if ( ! $status instanceof DefinitionStatus ) {
			throw new InvalidArgumentException( 'CPT status must be draft, published, disabled, or archived.' );
		}
		return $status;
	}

	private function owned( string $id ): Definition {
		$definition = $this->definitions->get( $id );
		if ( ! $definition instanceof Definition
			|| $definition->type !== CustomPostTypeDefinitionProjector::DEFINITION_TYPE
			|| $definition->ownerSurfaceId !== CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID
		) {
			throw new RuntimeException( 'CPT definition was not found in the canonical Surface 1 owner.' );
		}
		return $definition;
	}

	private function validatePayload( Definition $definition ): void {
		$this->projector->project(
			new Definition(
				id: $definition->id,
				slug: $definition->slug,
				type: $definition->type,
				schemaVersion: $definition->schemaVersion,
				ownerSurfaceId: $definition->ownerSurfaceId,
				status: DefinitionStatus::Published,
				payload: $definition->payload,
				revision: $definition->revision,
				dependencies: $definition->dependencies,
			)
		);
	}

	private function withChecksum( Definition $definition ): Definition {
		return new Definition(
			id: $definition->id,
			slug: $definition->slug,
			type: $definition->type,
			schemaVersion: $definition->schemaVersion,
			ownerSurfaceId: $definition->ownerSurfaceId,
			status: $definition->status,
			payload: $definition->payload,
			revision: $definition->revision,
			dependencies: $definition->dependencies,
			checksum: $definition->computedChecksum(),
		);
	}

	/** @return array<string,mixed> */
	private function serialize( Definition $definition ): array {
		return [
			'id'               => $definition->id,
			'slug'             => $definition->slug,
			'type'             => $definition->type,
			'schema_version'   => $definition->schemaVersion,
			'owner_surface_id' => $definition->ownerSurfaceId,
			'status'           => $definition->status->value,
			'payload'          => $definition->payload,
			'revision'         => $definition->revision,
			'dependencies'     => $definition->dependencies,
			'checksum'         => $definition->checksum,
		];
	}

	/** @param array<string,mixed> $input */
	private function requiredUuid( array $input, string $field ): string {
		$value = $input[ $field ] ?? null;
		if ( ! is_string( $value ) || ! $this->isUuid( $value ) ) {
			throw new InvalidArgumentException( sprintf( '%s must be a lowercase RFC 4122 UUID.', $field ) );
		}
		return $value;
	}

	private function isUuid( string $value ): bool {
		return preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value ) === 1;
	}

	private function uuid(): string {
		if ( function_exists( 'wp_generate_uuid4' ) ) {
			$uuid = strtolower( (string) wp_generate_uuid4() );
			if ( $this->isUuid( $uuid ) ) {
				return $uuid;
			}
		}

		$bytes    = random_bytes( 16 );
		$bytes[6] = chr( ( ord( $bytes[6] ) & 0x0f ) | 0x40 );
		$bytes[8] = chr( ( ord( $bytes[8] ) & 0x3f ) | 0x80 );
		$hex      = bin2hex( $bytes );

		return sprintf(
			'%s-%s-%s-%s-%s',
			substr( $hex, 0, 8 ),
			substr( $hex, 8, 4 ),
			substr( $hex, 12, 4 ),
			substr( $hex, 16, 4 ),
			substr( $hex, 20, 12 ),
		);
	}
}
