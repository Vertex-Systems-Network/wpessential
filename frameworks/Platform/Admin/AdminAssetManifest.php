<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final readonly class AdminAssetManifest
{
	public function __construct(
		private string $pluginRoot,
		private string $pluginUrl,
	) {}

	/** @return array{script:string,styles:list<string>,dependencies:list<string>,version:?string}|null */
	public function entry(string $entry = 'main', ?string $styleEntry = null): ?array
	{
		$script = $this->script( $entry );
		if ( $script === null ) {
			return null;
		}

		$style_name = $styleEntry !== null && $this->validEntryName( $styleEntry )
			? $styleEntry
			: $entry;
		$style = $this->style( $style_name );
		if ( $style === null && $style_name !== $entry ) {
			$style = $this->style( $entry );
		}

		return [
			'script' => $script['script'],
			'styles' => $style !== null ? [$style] : [],
			'dependencies' => $script['dependencies'],
			'version' => $script['version'],
		];
	}

	/** @return array{script:string,dependencies:list<string>,version:?string}|null */
	public function script(string $entry = 'main'): ?array
	{
		if ( ! $this->validEntryName( $entry ) ) {
			return null;
		}

		$asset_root = $this->assetRoot();
		$script_path = $asset_root . '/' . $entry . '.js';
		$metadata_path = $asset_root . '/' . $entry . '.asset.php';

		if ( ! is_readable( $script_path ) || ! is_readable( $metadata_path ) ) {
			return null;
		}

		$metadata = require $metadata_path;
		if ( ! is_array( $metadata ) ) {
			return null;
		}

		$dependencies = [];
		foreach ( ($metadata['dependencies'] ?? []) as $dependency ) {
			if (
				is_string( $dependency )
				&& preg_match( '/^[A-Za-z0-9][A-Za-z0-9._-]{0,190}$/', $dependency ) === 1
			) {
				$dependencies[] = $dependency;
			}
		}

		$version = isset( $metadata['version'] ) && is_string( $metadata['version'] ) && $metadata['version'] !== ''
			? $metadata['version']
			: null;

		return [
			'script' => $this->assetUrl( $entry . '.js' ),
			'dependencies' => $dependencies,
			'version' => $version,
		];
	}

	public function style(string $entry): ?string
	{
		if ( ! $this->validEntryName( $entry ) ) {
			return null;
		}

		$style_path = $this->assetRoot() . '/' . $entry . '.css';
		if ( ! is_readable( $style_path ) ) {
			return null;
		}

		return $this->assetUrl( $entry . '.css' );
	}

	private function assetRoot(): string
	{
		return rtrim( $this->pluginRoot, '/\\' ) . '/assets/admin';
	}

	private function assetUrl(string $file): string
	{
		return rtrim( $this->pluginUrl, '/' ) . '/assets/admin/' . $file;
	}

	private function validEntryName(string $entry): bool
	{
		return preg_match( '/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $entry ) === 1
			&& ! str_contains( $entry, '..' );
	}
}
