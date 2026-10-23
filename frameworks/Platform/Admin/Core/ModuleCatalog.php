<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Presentation catalog of every WPEssential module surface.
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
			'ai'         => 'Developer & AI',
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
			[ 'custom-post-types', 'Custom Post Types', 'free', 'content', 'cpt', 'file', true, 'wpessential-cpt', 'Register and manage custom post types with labels, supports, rewrites and REST.' ],
			[ 'taxonomies', 'Taxonomies', 'free', 'content', 'taxonomy', 'tag', true, 'wpessential-taxonomy', 'Hierarchical and flat taxonomies with object types and CPT UI import.' ],
			[ 'custom-fields', 'Custom Fields', 'pro', 'content', 'fields', 'form', true, 'wpessential-fields', 'Field groups, field types, location rules and typed storage.' ],
			[ 'relations', 'Relations', 'pro', 'content', 'relations', 'link', true, 'wpessential-relations', 'Many-to-many relationships between posts, terms and users.' ],
			[ 'custom-tables', 'Custom Tables', 'pro', 'content', 'tables', 'database', true, 'wpessential-custom-tables', 'Dedicated database tables with migrations, indexes and admin views.' ],
			[ 'status', 'Status Manager', 'pro', 'content', 'status', 'flag', true, null, 'Custom post statuses with transition policies and audit.' ],
			[ 'query', 'Query Builder', 'pro', 'content', 'query', 'filter', true, 'wpessential-query', 'Reusable cached queries powering listings, columns and dynamic data.' ],
			[ 'admin-columns', 'Admin Columns', 'pro', 'admin', 'columns', 'columns', true, 'wpessential-admin-columns', 'List-table columns with inline edit, filters, sorting and export.' ],
			[ 'admin-menu', 'Admin Menu', 'pro', 'admin', 'admin-menu', 'menu', true, null, 'Reorder, rename and hide admin menu items per role.' ],
			[ 'dashboard-widgets', 'Dashboard Widgets', 'pro', 'admin', 'dashboard-widgets', 'monitor', true, null, 'Create and control WordPress dashboard widgets per role.' ],
			[ 'settings', 'Settings Pages', 'pro', 'admin', 'settings', 'cog', true, null, 'Options pages with fields, sections and validated storage.' ],
			[ 'roles', 'Roles & Capabilities', 'pro', 'users', 'roles', 'users', true, null, 'Roles, capability maps and safe permission-impact preview.' ],
			[ 'profiles', 'User Profiles', 'pro', 'users', 'profiles', 'user', true, null, 'Frontend user profiles with custom fields and privacy controls.' ],
			[ 'membership', 'Membership', 'pro', 'users', 'membership', 'star', true, null, 'Restrict content by plan, role or rule with access audit.' ],
			[ 'dashboard', 'Frontend Dashboard', 'pro', 'frontend', 'dashboard', 'home', true, null, 'Account dashboards for members and customers.' ],
			[ 'listings', 'Dynamic Listings', 'pro', 'frontend', 'listings', 'grid', true, null, 'Grids, lists and maps of dynamic content with filters.' ],
			[ 'builder-widgets', 'Builder Widgets', 'pro', 'frontend', 'builder-widgets', 'brush', true, null, 'Blocks and page-builder widgets bound to WPEssential data.' ],
			[ 'forms-workflows', 'Forms & Workflows', 'pro', 'automation', 'forms-workflows', 'form', true, null, 'Forms with conditional logic and post-submit workflows.' ],
			[ 'notifications', 'Notifications', 'pro', 'automation', 'notifications', 'bell', true, null, 'Event-driven notifications across email, admin and webhooks.' ],
			[ 'emails', 'Emails', 'pro', 'automation', 'emails', 'mail', true, null, 'Email templates, delivery log and transport diagnostics.' ],
			[ 'chat', 'Chat', 'pro', 'automation', 'chat', 'chat', true, null, 'Live chat and conversation inbox for site visitors.' ],
			[ 'cron', 'Cron Manager', 'pro', 'automation', 'cron', 'clock', true, null, 'Inspect, run and audit scheduled WordPress events.' ],
			[ 'import-export', 'Import / Export', 'free', 'tools', 'import-export', 'download', false, null, 'Portable export and import of definitions and content.' ],
			[ 'reset', 'Reset Tools', 'free', 'tools', 'reset', 'refresh', false, null, 'Guarded, previewable reset of selected site data.' ],
			[ 'rest-api', 'REST API Control', 'free', 'tools', 'rest-api', 'code', false, null, 'REST route exposure, authentication and rate limits.' ],
			[ 'backup', 'Backup', 'pro', 'tools', 'backup', 'shield', false, null, 'Scheduled backups with remote destinations and verified restore.' ],
			[ 'media', 'Media Manager', 'pro', 'tools', 'media', 'image', false, null, 'Folders, optimisation, replacement and usage tracking.' ],
			[ 'protector', 'Site Protector', 'pro', 'tools', 'protector', 'shield', false, null, 'Login hardening, firewall rules and security monitoring.' ],
			[ 'connections', 'Connections', 'pro', 'tools', 'connections', 'plug', false, null, 'External services and APIs with secure credentials.' ],
			[ 'ai-connect', 'AI Connect', 'pro', 'ai', 'ai', 'spark', false, 'wpessential-ai', 'Let Claude, Cursor and other MCP clients read and, if you allow it, change WPEssential settings.' ],
		];
	}
}
