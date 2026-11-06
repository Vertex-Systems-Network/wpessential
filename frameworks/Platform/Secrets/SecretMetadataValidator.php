<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Platform\Secrets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;

final class SecretMetadataValidator {

	private const FORBIDDEN = [
		'authorization',
		'cookie',
		'password',
		'passwd',
		'secret',
		'token',
		'api_key',
		'apikey',
		'private_key',
		'client_secret',
	];

	/** @param array<string, scalar|null> $metadata */
	public function validate( array $metadata ): void {
		foreach ( $metadata as $key => $value ) {
			$normalized = strtolower( str_replace( '-', '_', (string) $key ) );
			foreach ( self::FORBIDDEN as $needle ) {
				if ( $normalized === $needle || str_ends_with( $normalized, '_' . $needle ) ) {
					throw new InvalidArgumentException( sprintf( 'Secret-bearing metadata key "%s" is not allowed.', $key ) );
				}
			}
			if ( ! is_scalar( $value ) && $value !== null ) {
				throw new InvalidArgumentException( 'Secret metadata values must be scalar or null.' );
			}
		}
	}
}
