<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Presentation catalog of every WPEssential Core module surface.
 *
 * Editions mirror the runtime `ModuleManifest` editions. `runtime` marks surfaces that ship a
 * kernel module; the others are contract-complete surfaces without a runtime module yet.
 */
final class ModuleCatalog {

	/** @return array<string,string> */
	public static function categories(): array {
		return [
			'content'    => 'Content modeling',
			'admin'      => 'Admin experience',
			'users'      => 'Users & access',
			'frontend'   => 'Frontend & builders',
			'automation' => 'Automation & messaging',
			'tools'      => 'Site tools',
		];
	}

	/**
	 * @return array<string,array{id:string,name:string,edition:string,category:string,contract:string,icon:string,runtime:bool,builder:?string,description:string}>
	 */
	public static function all(): array {
		$modules = [];
		foreach ( self::definitions() as [ $id, $name, $edition, $category, $contract, $icon, $runtime, $builder, $description ] ) {
			$modules[ $id ] = [
				'id'          => $id,
				'name'        => $name,
				'edition'     => $edition,
				'category'    => $category,
				'contract'    => $contract,
				'icon'        => $icon,
				'runtime'     => $runtime,
				'builder'     => $builder,
				'description' => $description,
			];
		}

		return $modules;
	}

	/** @return array{id:string,name:string,edition:string,category:string,contract:string,icon:string,runtime:bool,builder:?string,description:string}|null */
	public static function find( string $id ): ?array {
		return self::all()[ $id ] ?? null;
	}

	/** @return list<array{0:string,1:string,2:string,3:string,4:string,5:string,6:bool,7:?string,8:string}> */
	private static function definitions(): array {
		return [
			[ 'custom-post-types', 'Custom Post Types', 'free', 'content', 'cpt', 'dashicons-admin-post', true, 'wpessential-cpt', 'Register and manage custom post types with labels, supports, rewrites and REST.' ],
			[ 'taxonomies', 'Taxonomies', 'free', 'content', 'taxonomy', 'dashicons-tag', true, 'wpessential-taxonomy', 'Hierarchical and flat taxonomies with object types and CPT UI import.' ],
			[ 'custom-fields', 'Custom Fields', 'pro', 'content', 'fields', 'dashicons-forms', true, 'wpessential-fields', 'Field groups, field types, location rules and typed storage.' ],
			[ 'relations', 'Relations', 'pro', 'content', 'relations', 'dashicons-networking', true, 'wpessential-relations', 'Many-to-many relationships between posts, terms and users.' ],
			[ 'custom-tables', 'Custom Tables', 'pro', 'content', 'tables', 'dashicons-database', true, 'wpessential-custom-tables', 'Dedicated database tables with migrations, indexes and admin views.' ],
			[ 'status', 'Status Manager', 'pro', 'content', 'status', 'dashicons-flag', true, null, 'Custom post statuses with transition policies and audit.' ],
			[ 'query', 'Query Builder', 'pro', 'content', 'query', 'dashicons-filter', true, 'wpessential-query', 'Reusable cached queries powering listings, columns and dynamic data.' ],
			[ 'admin-columns', 'Admin Columns', 'pro', 'admin', 'columns', 'dashicons-columns', true, 'wpessential-admin-columns', 'List-table columns with inline edit, filters, sorting and export.' ],
			[ 'admin-menu', 'Admin Menu', 'pro', 'admin', 'admin-menu', 'dashicons-menu', true, null, 'Reorder, rename and hide admin menu items per role.' ],
			[ 'dashboard-widgets', 'Dashboard Widgets', 'pro', 'admin', 'dashboard-widgets', 'dashicons-welcome-widgets-menus', true, null, 'Create and control WordPress dashboard widgets per role.' ],
			[ 'settings', 'Settings Pages', 'pro', 'admin', 'settings', 'dashicons-admin-settings', true, null, 'Options pages with fields, sections and validated storage.' ],
			[ 'roles', 'Roles & Capabilities', 'pro', 'users', 'roles', 'dashicons-groups', true, null, 'Roles, capability maps and safe permission-impact preview.' ],
			[ 'profiles', 'User Profiles', 'pro', 'users', 'profiles', 'dashicons-id', true, null, 'Frontend user profiles with custom fields and privacy controls.' ],
			[ 'membership', 'Membership', 'pro', 'users', 'membership', 'dashicons-awards', true, null, 'Restrict content by plan, role or rule with access audit.' ],
			[ 'dashboard', 'Frontend Dashboard', 'pro', 'frontend', 'dashboard', 'dashicons-dashboard', true, null, 'Account dashboards for members and customers.' ],
			[ 'listings', 'Dynamic Listings', 'pro', 'frontend', 'listings', 'dashicons-grid-view', true, null, 'Grids, lists and maps of dynamic content with filters.' ],
			[ 'builder-widgets', 'Builder Widgets', 'pro', 'frontend', 'builder-widgets', 'dashicons-layout', true, null, 'Blocks and page-builder widgets bound to WPEssential data.' ],
			[ 'forms-workflows', 'Forms & Workflows', 'pro', 'automation', 'forms-workflows', 'dashicons-feedback', true, null, 'Forms with conditional logic and post-submit workflows.' ],
			[ 'notifications', 'Notifications', 'pro', 'automation', 'notifications', 'dashicons-bell', true, null, 'Event-driven notifications across email, admin and webhooks.' ],
			[ 'emails', 'Emails', 'pro', 'automation', 'emails', 'dashicons-email-alt', true, null, 'Email templates, delivery log and transport diagnostics.' ],
			[ 'chat', 'Chat', 'pro', 'automation', 'chat', 'dashicons-format-chat', true, null, 'Live chat and conversation inbox for site visitors.' ],
			[ 'cron', 'Cron Manager', 'pro', 'automation', 'cron', 'dashicons-clock', true, null, 'Inspect, run and audit scheduled WordPress events.' ],
			[ 'import-export', 'Import / Export', 'free', 'tools', 'import-export', 'dashicons-migrate', false, null, 'Portable export and import of definitions and content.' ],
			[ 'reset', 'Reset Tools', 'free', 'tools', 'reset', 'dashicons-image-rotate', false, null, 'Guarded, previewable reset of selected site data.' ],
			[ 'rest-api', 'REST API Control', 'free', 'tools', 'rest-api', 'dashicons-rest-api', false, null, 'REST route exposure, authentication and rate limits.' ],
			[ 'backup', 'Backup', 'pro', 'tools', 'backup', 'dashicons-backup', false, null, 'Scheduled backups with remote destinations and verified restore.' ],
			[ 'media', 'Media Manager', 'pro', 'tools', 'media', 'dashicons-format-gallery', false, null, 'Folders, optimisation, replacement and usage tracking.' ],
			[ 'protector', 'Site Protector', 'pro', 'tools', 'protector', 'dashicons-shield', false, null, 'Login hardening, firewall rules and security monitoring.' ],
			[ 'connections', 'Connections', 'pro', 'tools', 'connections', 'dashicons-admin-links', false, null, 'External services and APIs with secure credentials.' ],
		];
	}
}
