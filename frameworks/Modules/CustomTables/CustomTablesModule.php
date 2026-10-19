<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use LogicException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\CustomTablesRuntimeCompositionFactory;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Modules\ModuleManifest;

/**
 * ADR-0227: registers the R1 migration abilities on the internal channel only.
 *
 * No WordPress Abilities API exposure, AJAX route or REST channel; those are a later ADR.
 */
final class CustomTablesModule implements ModuleInterface {

	public const CAPABILITY = 'manage_options';

	private const UUID_PATTERN = '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$';

	public function __construct(
		private readonly WordPressCt1SchemaIntrospector $schema,
		private readonly WordPressProviderCapabilityProfileResolver $profiles,
		private readonly string $sitePrefix,
	) {
	}

	public function manifest(): ModuleManifest {
		return new ModuleManifest(
			id: 'custom-tables',
			name: 'Custom Tables',
			version: '0.1.0',
			edition: 'pro',
		);
	}

	public function register( ServiceRegistryInterface $services ): void {
		$abilities   = $services->get( 'platform.abilities' );
		$definitions = $services->get( 'platform.definitions' );
		$factory     = $services->get( 'custom-tables.runtime-composition.factory' );
		$issuer      = $services->get( 'custom-tables.execution-confirmation.issuer' );
		if ( ! $abilities instanceof AbilityRegistry ) {
			throw new LogicException( 'Custom Tables requires the shared ability registry.' );
		}
		if ( ! $definitions instanceof DefinitionRepositoryInterface ) {
			throw new LogicException( 'Custom Tables requires the shared Definition Repository.' );
		}
		if ( ! $factory instanceof CustomTablesRuntimeCompositionFactory ) {
			throw new LogicException( 'Custom Tables requires the trusted runtime composition factory.' );
		}
		if ( ! $issuer instanceof WpdbMigrationExecutionConfirmationIssuer ) {
			throw new LogicException( 'Custom Tables requires the execution confirmation issuer.' );
		}

		foreach ( CustomTablesMigrationAbilityHandler::ACTIONS as $action ) {
			$abilities->register(
				new AbilityDescriptor(
					name: self::abilityName( $action ),
					ownerSurfaceId: TableDefinitionCompiler::OWNER_SURFACE_ID,
					capability: self::CAPABILITY,
					mutates: true,
					channels: [ ExecutionChannel::Internal ],
					inputSchema: self::inputSchema( $action ),
					outputSchema: [ 'type' => 'object' ],
				),
				new CustomTablesMigrationAbilityHandler( $action, $factory, $this->schema, $this->profiles, $definitions, $issuer, $this->sitePrefix ),
			);
		}
	}

	public function boot( ServiceRegistryInterface $services ): void {
	}

	public static function abilityName( string $action ): string {
		return 'wpessential/custom-tables/' . $action . '-migration';
	}

	/** @return array<string,mixed> */
	private static function inputSchema( string $action ): array {
		$properties = match ( $action ) {
			'prepare' => [
				'definition_id' => [
					'type'    => 'string',
					'pattern' => self::UUID_PATTERN,
				],
			],
			'execute' => [
				'run_id'    => [
					'type'    => 'string',
					'pattern' => self::UUID_PATTERN,
				],
				'backup_id' => [
					'type'      => 'string',
					'minLength' => 3,
				],
			],
			default   => [
				'run_id'                  => [
					'type'    => 'string',
					'pattern' => self::UUID_PATTERN,
				],
				'expected_state_revision' => [
					'type'    => 'integer',
					'minimum' => 1,
				],
			],
		};

		return [
			'type'                 => 'object',
			'required'             => array_keys( $properties ),
			'properties'           => $properties,
			'additionalProperties' => false,
		];
	}
}
