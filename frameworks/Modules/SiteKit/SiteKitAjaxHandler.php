<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;

/** In-place saves for the Site Kit screen (the route requires edit_theme_options). */
final class SiteKitAjaxHandler implements AjaxHandlerInterface {

	public function __construct(
		private readonly string $operation,
		private readonly SiteKitStore $store,
		private readonly SiteKitSnippets $snippets,
	) {}

	/** @param array<string,mixed> $payload */
	public function handle( array $payload ): mixed {
		$group = is_string( $payload['group'] ?? null ) ? $payload['group'] : '';

		return match ( $this->operation ) {
			SiteKitAdmin::AJAX_SAVE     => isset( SiteKitSchema::sections()[ $group ] )
				? [ 'changed' => $this->store->update( SiteKitAdmin::groupValues( $group, is_array( $payload['values'] ?? null ) ? $payload['values'] : [] ) ) ]
				: throw new InvalidArgumentException( 'Unknown Site Kit group.' ),
			SiteKitAdmin::AJAX_RESET    => isset( SiteKitSchema::sections()[ $group ] )
				? [
					'reset'  => $this->store->resetSection( $group ),
					'reload' => true,
				]
				: throw new InvalidArgumentException( 'Unknown Site Kit group.' ),
			SiteKitAdmin::AJAX_SNIPPETS => [
				'snippets' => count( $this->snippets->save( array_values( array_filter( (array) ( $payload['snippets'] ?? [] ), 'is_array' ) ), function_exists( 'current_user_can' ) && current_user_can( 'unfiltered_html' ) ) ),
				'reload'   => true,
			],
			default                     => throw new InvalidArgumentException( 'Unknown Site Kit operation.' ),
		};
	}
}
