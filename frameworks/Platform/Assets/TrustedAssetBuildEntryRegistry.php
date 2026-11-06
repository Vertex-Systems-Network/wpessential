<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Platform\Assets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;

final class TrustedAssetBuildEntryRegistry {

	/** @var array<string,TrustedAssetBuildEntry> */
	private array $entries = [];

	public function __construct( private readonly AssetRegistry $assets ) {
	}

	public function register( TrustedAssetBuildEntry $entry ): void {
		$this->assets->get( $entry->assetHandle );

		if ( isset( $this->entries[ $entry->assetHandle ] ) ) {
			throw new RuntimeException(
				sprintf(
					'Trusted asset build mapping "%s" is already registered.',
					$entry->assetHandle,
				)
			);
		}

		$this->entries[ $entry->assetHandle ] = $entry;
	}

	public function get( string $assetHandle ): TrustedAssetBuildEntry {
		return $this->entries[ $assetHandle ]
			?? throw new RuntimeException(
				sprintf(
					'Trusted asset build mapping "%s" is not registered.',
					$assetHandle,
				)
			);
	}
}
