<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Review;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;

/** ADR-0226: the provider profile comes only from the connected server, never from a caller. */
final class WordPressProviderCapabilityProfileResolver {

	public function __construct( private readonly object $wpdb ) {
		if ( ! method_exists( $this->wpdb, 'db_server_info' ) ) {
			throw new InvalidArgumentException( 'Custom Tables provider profile resolver requires wpdb::db_server_info().' );
		}
	}

	public function resolve(): ProviderCapabilityProfile {
		$serverInfo = $this->wpdb->db_server_info();
		if ( ! is_string( $serverInfo ) ) {
			throw new RuntimeException( 'Custom Tables database server information is unavailable.' );
		}

		return ProviderCapabilityProfile::fromTrustedServerInfo( $serverInfo );
	}
}
