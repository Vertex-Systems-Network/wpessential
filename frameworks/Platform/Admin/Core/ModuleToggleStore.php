<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persists the owner's on/off choice per module.
 *
 * Only switched-off module ids are stored, so every module is on by default and new modules
 * appear switched on. Edition and entitlement gates are applied separately.
 */
final class ModuleToggleStore {

	public const OPTION = 'wpessential_module_toggles';

	private const ID_PATTERN = '/^[a-z0-9][a-z0-9-]*$/';

	/** @var array<string,true>|null */
	private ?array $disabled = null;

	public function isEnabled( string $moduleId ): bool {
		return ! isset( $this->disabled()[ $moduleId ] );
	}

	public function set( string $moduleId, bool $enabled ): void {
		if ( preg_match( self::ID_PATTERN, $moduleId ) !== 1 ) {
			return;
		}

		$disabled = $this->disabled();
		if ( $enabled ) {
			unset( $disabled[ $moduleId ] );
		} else {
			$disabled[ $moduleId ] = true;
		}

		$this->disabled = $disabled;
		if ( function_exists( 'update_option' ) ) {
			update_option( self::OPTION, array_keys( $disabled ), false );
		}
	}

	/** @return array<string,true> */
	private function disabled(): array {
		if ( $this->disabled !== null ) {
			return $this->disabled;
		}

		$stored         = function_exists( 'get_option' ) ? get_option( self::OPTION, [] ) : [];
		$this->disabled = [];
		foreach ( is_array( $stored ) ? $stored : [] as $id ) {
			if ( is_string( $id ) && preg_match( self::ID_PATTERN, $id ) === 1 ) {
				$this->disabled[ $id ] = true;
			}
		}

		return $this->disabled;
	}
}
