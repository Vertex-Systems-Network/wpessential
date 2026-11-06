<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomPostTypes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Registrations\PermalinkRuntime;

final readonly class CustomPostTypeValidationService {

	private const PREVIEW_ID = '00000000-0000-4000-8000-000000000001';

	public function __construct(
		private DefinitionRepositoryInterface $definitions,
		private CustomPostTypeDefinitionProjector $projector,
	) {}

	/**
	 * @param array<string,mixed> $input
	 * @return array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{post_type_key:?string}}
	 */
	public function validate( array $input ): array {
		$issues  = [];
		$payload = $input['payload'] ?? null;
		if ( ! is_array( $payload ) || array_is_list( $payload ) ) {
			$issues[] = $this->issue( 'payload_invalid', 'blocked', 'payload', 'CPT payload must be an object/map.' );
			return $this->report( null, $issues );
		}

		$current  = $this->currentDefinition( $input, $issues );
		$keyValue = $payload['post_type_key'] ?? null;
		$key      = is_string( $keyValue ) ? trim( $keyValue ) : null;

		$candidate = new Definition(
			id: $current?->id ?? self::PREVIEW_ID,
			slug: $current?->slug ?? ( 'cpt-preview-' . ( $key !== null && $key !== '' ? str_replace( '_', '-', $key ) : 'candidate' ) ),
			type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
			schemaVersion: $current?->schemaVersion ?? 1,
			ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
			status: DefinitionStatus::Published,
			payload: $payload,
			revision: ( $current?->revision ?? 0 ) + 1,
			dependencies: $current?->dependencies ?? [],
		);

		$effectiveArgs = null;
		try {
			$effectiveArgs = $this->projector->project( $candidate )->payload;
			// Internal WPEssential setting, not a register_post_type() argument.
			unset( $effectiveArgs[ PermalinkRuntime::ARG ] );
		} catch ( InvalidArgumentException $exception ) {
			$issues[] = $this->issue( 'registration_schema_invalid', 'blocked', 'payload', $exception->getMessage() );
		}

		if ( $key !== null && $key !== '' ) {
			$this->validateCanonicalOwnership( $key, $current?->id, $issues );
			$this->validateRuntimeOwnership( $key, $current, $issues );
		}
		$this->validateTaxonomyDependencies( $payload, $issues );
		$this->validateBlockTemplate( $payload, $issues );

		return $this->report( $key, $issues, $effectiveArgs );
	}

	/**
	 * A lock needs a template, and every template block must be registered, or new posts
	 * start empty or with a missing-block placeholder that a lock stops editors from replacing.
	 *
	 * @param array<string,mixed> $payload
	 * @param list<array{id:string,severity:string,field:string,message:string}> $issues
	 */
	private function validateBlockTemplate( array $payload, array &$issues ): void {
		$template = is_array( $payload['template'] ?? null ) ? $payload['template'] : [];
		if ( ( $payload['template_lock'] ?? null ) !== null && $template === [] ) {
			$issues[] = $this->issue( 'template_lock_without_template', 'blocked', 'template_lock', 'Template lock needs a block template; add the blocks new posts start with.' );
		}
		if ( $template === [] || ! class_exists( 'WP_Block_Type_Registry' ) ) {
			return;
		}
		$registry = \WP_Block_Type_Registry::get_instance();
		foreach ( $template as $block ) {
			if ( is_string( $block ) && ! $registry->is_registered( $block ) ) {
				$issues[] = $this->issue( 'template_block_unknown', 'blocked', 'template', sprintf( 'Block "%s" is not registered on this site.', $block ) );
			}
		}
	}

	/**
	 * @param array<string,mixed> $input
	 * @param list<array{id:string,severity:string,field:string,message:string}> $issues
	 */
	private function currentDefinition( array $input, array &$issues ): ?Definition {
		$id = $input['id'] ?? null;
		if ( $id === null || $id === '' ) {
			return null;
		}
		if ( ! is_string( $id ) ) {
			$issues[] = $this->issue( 'definition_id_invalid', 'blocked', 'id', 'CPT id must be a string.' );
			return null;
		}

		$definition = $this->definitions->get( $id );
		if ( ! $definition instanceof Definition
			|| $definition->type !== CustomPostTypeDefinitionProjector::DEFINITION_TYPE
			|| $definition->ownerSurfaceId !== CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID
		) {
			$issues[] = $this->issue( 'definition_not_found', 'blocked', 'id', 'CPT definition was not found in the canonical Surface 1 owner.' );
			return null;
		}

		$existingKey  = $definition->payload['post_type_key'] ?? null;
		$requestedKey = $input['payload']['post_type_key'] ?? null;
		if ( is_string( $existingKey ) && is_string( $requestedKey ) && trim( $existingKey ) !== trim( $requestedKey ) ) {
			$issues[] = $this->issue(
				'runtime_key_immutable',
				'blocked',
				'post_type_key',
				'Existing CPT post_type_key is immutable; create a new definition for a different runtime key.',
			);
		}

		return $definition;
	}

	/** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
	private function validateCanonicalOwnership( string $key, ?string $currentDefinitionId, array &$issues ): void {
		foreach ( $this->definitions->byType( CustomPostTypeDefinitionProjector::DEFINITION_TYPE ) as $definition ) {
			if ( $definition->ownerSurfaceId !== CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID
				|| $definition->id === $currentDefinitionId
			) {
				continue;
			}

			$existingKey = $definition->payload['post_type_key'] ?? null;
			if ( is_string( $existingKey ) && trim( $existingKey ) === $key ) {
				$issues[] = $this->issue(
					'duplicate_definition',
					'blocked',
					'post_type_key',
					sprintf( 'Post type key "%s" is already owned by another canonical CPT definition.', $key ),
				);
				return;
			}
		}
	}

	/** @param list<array{id:string,severity:string,field:string,message:string}> $issues */
	private function validateRuntimeOwnership( string $key, ?Definition $current, array &$issues ): void {
		if ( ! function_exists( 'post_type_exists' ) || ! post_type_exists( $key ) ) {
			return;
		}

		$currentKey                     = $current?->payload['post_type_key'] ?? null;
		$isCurrentPublishedRegistration = $current instanceof Definition
			&& $current->status === DefinitionStatus::Published
			&& is_string( $currentKey )
			&& trim( $currentKey ) === $key;
		if ( $isCurrentPublishedRegistration ) {
			$issues[] = $this->issue(
				'runtime_registration_present',
				'info',
				'post_type_key',
				sprintf( 'Post type key "%s" is active at runtime. WordPress does not reliably identify the registering owner, so ownership is not inferred from runtime presence alone.', $key ),
			);
			return;
		}

		$issues[] = $this->issue(
			'runtime_registration_collision',
			'blocked',
			'post_type_key',
			sprintf( 'Post type key "%s" is already registered by WordPress or another runtime owner.', $key ),
		);
	}

	/**
	 * @param array<string,mixed> $payload
	 * @param list<array{id:string,severity:string,field:string,message:string}> $issues
	 */
	private function validateTaxonomyDependencies( array $payload, array &$issues ): void {
		$taxonomies = $payload['taxonomies'] ?? null;
		if ( ! is_array( $taxonomies ) || ! array_is_list( $taxonomies ) || ! function_exists( 'taxonomy_exists' ) ) {
			return;
		}

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! is_string( $taxonomy ) || trim( $taxonomy ) === '' ) {
				continue;
			}
			$taxonomy = trim( $taxonomy );
			if ( ! taxonomy_exists( $taxonomy ) ) {
				$issues[] = $this->issue(
					'missing_taxonomy',
					'compatibility_warning',
					'taxonomies',
					sprintf( 'Taxonomy "%s" is not currently registered; the CPT can remain defined but this relationship is degraded.', $taxonomy ),
				);
			}
		}
	}

	/**
	 * @param list<array{id:string,severity:string,field:string,message:string}> $issues
	 * @return array{valid:bool,issues:list<array{id:string,severity:string,field:string,message:string}>,candidate:array{post_type_key:?string}}
	 */
	/** @param array<string,mixed>|null $effectiveArgs the compiled register_post_type() args, when the payload projects. */
	private function report( ?string $key, array $issues, ?array $effectiveArgs = null ): array {
		$valid = true;
		foreach ( $issues as $issue ) {
			if ( $issue['severity'] === 'blocked' ) {
				$valid = false;
				break;
			}
		}

		return [
			'valid'     => $valid,
			'issues'    => $issues,
			'candidate'      => [ 'post_type_key' => $key ],
			'effective_args' => $effectiveArgs,
		];
	}

	/** @return array{id:string,severity:string,field:string,message:string} */
	private function issue( string $id, string $severity, string $field, string $message ): array {
		return [
			'id'       => $id,
			'severity' => $severity,
			'field'    => $field,
			'message'  => $message,
		];
	}
}
