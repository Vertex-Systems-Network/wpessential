<?php

declare(strict_types=1);


if ( ! defined( 'ABSPATH' ) && PHP_SAPI === 'cli' ) {
	// Composer loads this file outside WordPress for CLI quality and test tools.
	// Do not register WordPress wrappers during CLI tool bootstrap.
	return;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Bootstrap\Plugin;
use WPEssential\Platform\WordPress\Hooks\HookNames;

if ( ! function_exists( 'wpessential_kernel' ) ) {
	/** Return the active WPEssential kernel, if available. */
	function wpessential_kernel(): ?\WPEssential\Kernel\Kernel {
		return Plugin::kernel();
	}
}

if ( ! function_exists( 'wpessential_service' ) ) {
	/**
	 * Retrieve a registered service by its canonical ID.
	 *
	 * @param string $id Canonical registered service ID.
	 */
	function wpessential_service( string $id ): ?object {
		$kernel = wpessential_kernel();
		if ( null === $kernel || ! $kernel->services()->has( $id ) ) {
			return null;
		}

		return $kernel->services()->get( $id );
	}
}

if ( ! function_exists( 'wpessential_apply_filter' ) ) {
	/**
	 * Apply a namespaced WordPress filter when available.
	 *
	 * @param string $name Filter name.
	 * @param mixed  $value Value to filter.
	 * @param mixed  ...$args Additional filter arguments.
	 */
	function wpessential_apply_filter( string $name, mixed $value, mixed ...$args ): mixed {
		if ( ! function_exists( 'apply_filters' ) ) {
			return $value;
		}

		return apply_filters( HookNames::filter( $name ), $value, ...$args );
	}
}

if ( ! function_exists( 'wpessential_do_action' ) ) {
	/**
	 * Dispatch a namespaced WordPress action when available.
	 *
	 * @param string $name Action name.
	 * @param mixed  ...$args Additional action arguments.
	 */
	function wpessential_do_action( string $name, mixed ...$args ): void {
		if ( function_exists( 'do_action' ) ) {
			do_action( HookNames::action( $name ), ...$args );
		}
	}
}
