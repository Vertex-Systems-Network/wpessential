<?php
/**
 * Tables and default plan products.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Installer.
 */
class Install {

	const DB_VERSION = '3';

	/**
	 * Activation.
	 */
	public static function activate() {
		self::tables();
		update_option( 'wpes_server_db', self::DB_VERSION );
		Plugin::secret();
		self::protect_packages();
		set_transient( 'wpes_server_setup_plans', 1, HOUR_IN_SECONDS );
	}

	/**
	 * Run table updates when the version changes.
	 */
	public static function maybe_upgrade() {
		if ( get_option( 'wpes_server_db' ) !== self::DB_VERSION ) {
			self::activate();
		}
		// Plans need WooCommerce loaded, so they are created on init after activation.
		add_action(
			'init',
			static function () {
				if ( get_transient( 'wpes_server_setup_plans' ) ) {
					delete_transient( 'wpes_server_setup_plans' );
					Plans::create_products();
				}
			},
			20
		);
	}

	/**
	 * Create tables.
	 */
	public static function tables() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wpes_licenses (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				license_key varchar(64) NOT NULL,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				order_id bigint(20) unsigned NOT NULL DEFAULT 0,
				product_id bigint(20) unsigned NOT NULL DEFAULT 0,
				plan varchar(32) NOT NULL DEFAULT 'free',
				status varchar(20) NOT NULL DEFAULT 'active',
				sites_limit int(11) NOT NULL DEFAULT 1,
				white_label tinyint(1) NOT NULL DEFAULT 0,
				auto_renew tinyint(1) NOT NULL DEFAULT 0,
				expires_at datetime NULL DEFAULT NULL,
				major smallint(5) unsigned NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				updated_at datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY license_key (license_key),
				KEY user_id (user_id),
				KEY status_expires (status,expires_at)
			) $charset;
			CREATE TABLE {$wpdb->prefix}wpes_activations (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				license_id bigint(20) unsigned NOT NULL,
				domain varchar(191) NOT NULL,
				is_staging tinyint(1) NOT NULL DEFAULT 0,
				created_at datetime NOT NULL,
				last_check datetime NOT NULL,
				PRIMARY KEY  (id),
				UNIQUE KEY license_domain (license_id,domain),
				KEY domain (domain)
			) $charset;
			CREATE TABLE {$wpdb->prefix}wpes_events (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				created_at datetime NOT NULL,
				level varchar(10) NOT NULL DEFAULT 'info',
				event varchar(64) NOT NULL,
				trace_id varchar(32) NOT NULL DEFAULT '',
				source varchar(16) NOT NULL DEFAULT '',
				license_id bigint(20) unsigned NOT NULL DEFAULT 0,
				order_id bigint(20) unsigned NOT NULL DEFAULT 0,
				user_id bigint(20) unsigned NOT NULL DEFAULT 0,
				domain varchar(191) NOT NULL DEFAULT '',
				message text NOT NULL,
				context longtext NULL,
				ip varchar(45) NOT NULL DEFAULT '',
				PRIMARY KEY  (id),
				KEY license_id (license_id),
				KEY order_id (order_id),
				KEY trace_id (trace_id),
				KEY level_created (level,created_at),
				KEY event (event)
			) $charset;"
		);
	}

	/**
	 * Private folder for paid packages.
	 *
	 * @return string
	 */
	public static function protect_packages() {
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wpes-packages';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Deny rule.
			file_put_contents( $dir . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Directory index.
		}
		return $dir;
	}
}
