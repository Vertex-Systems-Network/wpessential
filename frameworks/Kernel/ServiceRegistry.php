<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Kernel;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\ServiceRegistryInterface;

final class ServiceRegistry implements ServiceRegistryInterface {

	/** @var array<string, object> */
	private array $services = [];

	public function has( string $id ): bool {
		return isset( $this->services[ $id ] );
	}

	public function set( string $id, object $service ): void {
		$id = trim( $id );
		if ( $id === '' ) {
			throw new InvalidArgumentException( 'Service id cannot be empty.' );
		}

		if ( $this->has( $id ) ) {
			throw new RuntimeException( sprintf( 'Service "%s" is already registered.', $id ) );
		}

		$this->services[ $id ] = $service;
	}

	public function get( string $id ): object {
		if ( ! $this->has( $id ) ) {
			throw new RuntimeException( sprintf( 'Service "%s" is not registered.', $id ) );
		}

		return $this->services[ $id ];
	}
}
