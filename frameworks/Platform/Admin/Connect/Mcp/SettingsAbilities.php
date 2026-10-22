<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Connect\Mcp;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Closure;
use InvalidArgumentException;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Connect\LicenseService;
use WPEssential\Platform\Admin\Core\ModuleCatalog;
use WPEssential\Platform\Admin\Core\ModuleToggleStore;
use WPEssential\Platform\Admin\Elements\ElementRegistry;
use WPEssential\Platform\Admin\Ui\Shell;
use WPEssential\Platform\Auth\ExecutionChannel;

/**
 * WPEssential's own settings, exposed to AI clients as abilities (ADR-0231 decision 1b).
 * The AI channel only sees these through AbilityRegistry, so capability policy and audit apply.
 */
final class SettingsAbilities {

	public const OWNER_SURFACE_ID = 43;
	private const CAPABILITY      = 'manage_options';

	public function __construct(
		private readonly ModuleToggleStore $toggles,
		private readonly ElementRegistry $elements,
		private readonly LicenseService $license,
	) {}

	public function register( AbilityRegistry $abilities ): void {
		$this->add( $abilities, 'modules/list', false, 'List WPEssential modules with edition and on/off state.', [], fn (): array => $this->modules() );
		$this->add(
			$abilities,
			'modules/toggle',
			true,
			'Switch a WPEssential module on or off. Premium modules need an active Premium license.',
			[
				'module'  => [ 'type' => 'string' ],
				'enabled' => [ 'type' => 'boolean' ],
			],
			fn ( array $input ): array => $this->toggleModule( (string) ( $input['module'] ?? '' ), (bool) ( $input['enabled'] ?? false ) )
		);
		$this->add( $abilities, 'elements/list', false, 'List Gutenberg blocks and Elementor widgets with their on/off state.', [], fn (): array => $this->elementList() );
		$this->add(
			$abilities,
			'elements/toggle',
			true,
			'Switch a Gutenberg block or Elementor widget on or off.',
			[
				'type'    => [
					'type' => 'string',
					'enum' => ElementRegistry::TYPES,
				],
				'element' => [ 'type' => 'string' ],
				'enabled' => [ 'type' => 'boolean' ],
			],
			fn ( array $input ): array => $this->toggleElement( (string) ( $input['type'] ?? '' ), (string) ( $input['element'] ?? '' ), (bool) ( $input['enabled'] ?? false ) )
		);
		$this->add( $abilities, 'license/status', false, 'Read the site license status. The key is always masked.', [], fn (): array => $this->licenseStatus() );
		$this->add( $abilities, 'settings/get', false, 'Read WPEssential settings that AI may change.', [], fn (): array => $this->settings() );
		$this->add(
			$abilities,
			'settings/update',
			true,
			'Change WPEssential settings for the connected user. Only the listed keys are accepted.',
			[
				'ui_mode' => [
					'type' => 'string',
					'enum' => Shell::MODES,
				],
			],
			fn ( array $input ): array => $this->updateSettings( $input )
		);
	}

	/**
	 * @param array<string,array<string,mixed>> $properties
	 * @param Closure(array<string,mixed>):array<string,mixed> $callback
	 */
	private function add( AbilityRegistry $abilities, string $name, bool $mutates, string $description, array $properties, Closure $callback ): void {
		$abilities->register(
			new AbilityDescriptor(
				name: 'wpessential/' . $name,
				ownerSurfaceId: self::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: $mutates,
				channels: [ ExecutionChannel::Internal, ExecutionChannel::Ai ],
				inputSchema: [
					'type'                 => 'object',
					'description'          => $description,
					'properties'           => (object) $properties,
					'additionalProperties' => false,
				],
				outputSchema: [ 'type' => 'object' ],
			),
			new CallbackAbilityHandler( $callback )
		);
	}

	/** @return array{modules:list<array<string,mixed>>} */
	private function modules(): array {
		$list = [];
		foreach ( ModuleCatalog::all() as $module ) {
			$locked = $module['edition'] === 'pro' && ! Shell::premiumAvailable();
			$list[] = [
				'id'       => $module['id'],
				'name'     => $module['name'],
				'edition'  => $module['edition'],
				'category' => $module['category'],
				'enabled'  => ! $locked && $this->toggles->isEnabled( $module['id'] ),
				'locked'   => $locked,
			];
		}

		return [ 'modules' => $list ];
	}

	/** @return array<string,mixed> */
	private function toggleModule( string $id, bool $enabled ): array {
		$module = ModuleCatalog::find( $id );
		if ( $module === null ) {
			throw new InvalidArgumentException( 'Unknown module.' );
		}
		if ( $enabled && $module['edition'] === 'pro' && ! Shell::premiumAvailable() ) {
			throw new InvalidArgumentException( 'This is a Premium module. Activate a Premium license first.' );
		}
		$before = $this->toggles->isEnabled( $module['id'] );
		$this->toggles->set( $module['id'], $enabled );

		return [
			'changed' => [
				'module' => $module['id'],
				'from'   => $before,
				'to'     => $enabled,
			],
		];
	}

	/** @return array<string,list<array<string,mixed>>> */
	private function elementList(): array {
		$out = [];
		foreach ( ElementRegistry::TYPES as $type ) {
			$out[ $type ] = [];
			foreach ( $this->elements->all( $type ) as $element ) {
				$out[ $type ][] = [
					'id'      => $element['id'],
					'name'    => $element['name'],
					'edition' => $element['edition'],
					'enabled' => $this->elements->isEnabled( $type, $element['id'] ),
				];
			}
		}

		return $out;
	}

	/** @return array<string,mixed> */
	private function toggleElement( string $type, string $id, bool $enabled ): array {
		if ( ! in_array( $type, ElementRegistry::TYPES, true ) ) {
			throw new InvalidArgumentException( 'Unknown element type.' );
		}
		$before = $this->elements->isEnabled( $type, $id );
		if ( ! $this->elements->set( $type, $id, $enabled ) ) {
			throw new InvalidArgumentException( 'Unknown element.' );
		}

		return [
			'changed' => [
				'element' => $type . ':' . $id,
				'from'    => $before,
				'to'      => $enabled,
			],
		];
	}

	/** @return array<string,mixed> */
	private function licenseStatus(): array {
		$data = $this->license->publicData();

		return [
			'status'  => $data['status'],
			'plan'    => $data['plan'],
			'expires' => $data['expires'],
			'premium' => $this->license->hasPro(),
			'key'     => $data['key'],
		];
	}

	/** @return array<string,mixed> */
	private function settings(): array {
		return [ 'ui_mode' => Shell::mode() ];
	}

	/**
	 * @param array<string,mixed> $input
	 * @return array<string,mixed>
	 */
	private function updateSettings( array $input ): array {
		$changed = [];
		if ( array_key_exists( 'ui_mode', $input ) ) {
			$mode = (string) $input['ui_mode'];
			if ( ! in_array( $mode, Shell::MODES, true ) ) {
				throw new InvalidArgumentException( 'ui_mode must be system, light or dark.' );
			}
			$changed['ui_mode'] = [
				'from' => Shell::mode(),
				'to'   => $mode,
			];
			update_user_meta( get_current_user_id(), Shell::MODE_META, $mode );
		}
		if ( $changed === [] ) {
			throw new InvalidArgumentException( 'No supported setting was given.' );
		}

		return [ 'changed' => $changed ];
	}
}
