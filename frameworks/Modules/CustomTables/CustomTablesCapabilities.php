<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ADR-0229: the dedicated capability for applying Custom Tables schema migrations (spec §25).
 *
 * Granted at runtime to users who hold `manage_options` through `user_has_cap`. Nothing is written to roles or
 * user meta. An explicit deny in the user's caps wins, and the grant filter can revoke the default.
 */
final class CustomTablesCapabilities {

	public const APPLY_MIGRATIONS = 'wpessential_apply_table_migrations';

	public const DEFAULT_GRANT_SOURCE = 'manage_options';

	public const GRANT_FILTER = 'wpessential_custom_tables_grant_migration_capability';

	public static function register(): void {
		if ( ! function_exists( 'add_filter' ) ) {
			return;
		}

		add_filter( 'user_has_cap', [ self::class, 'filterUserCaps' ], 10, 4 );
	}

	/**
	 * @param mixed $allcaps
	 * @param mixed $caps
	 * @param mixed $args
	 * @param mixed $user
	 * @return mixed
	 */
	public static function filterUserCaps( $allcaps, $caps = [], $args = [], $user = null ) {
		if ( ! is_array( $allcaps ) || ! is_array( $caps ) || ! in_array( self::APPLY_MIGRATIONS, $caps, true ) ) {
			return $allcaps;
		}
		if ( array_key_exists( self::APPLY_MIGRATIONS, $allcaps ) ) {
			return $allcaps;
		}
		if ( empty( $allcaps[ self::DEFAULT_GRANT_SOURCE ] ) ) {
			return $allcaps;
		}

		$userId = is_object( $user ) && isset( $user->ID ) && is_numeric( $user->ID ) ? (int) $user->ID : 0;
		$grant  = function_exists( 'apply_filters' )
			? apply_filters( self::GRANT_FILTER, true, $userId )
			: true;

		if ( $grant === true ) {
			$allcaps[ self::APPLY_MIGRATIONS ] = true;
		}

		return $allcaps;
	}
}
