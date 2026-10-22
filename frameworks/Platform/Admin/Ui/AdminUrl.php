<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Ui;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AdminUrl {

	/** @param array<string,string> $args */
	public static function page( string $slug, array $args = [] ): string {
		$query = http_build_query( array_merge( [ 'page' => $slug ], $args ), '', '&', PHP_QUERY_RFC3986 );
		$base  = function_exists( 'admin_url' ) ? (string) admin_url( 'admin.php' ) : 'admin.php';

		return $base . '?' . $query;
	}
}
