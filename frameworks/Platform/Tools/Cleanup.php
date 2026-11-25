<?php

declare(strict_types=1);

namespace WPEssential\Platform\Tools;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database cleanup tools for Reset: each one counts first, then removes in bounded batches through
 * WordPress functions where WordPress has one, so hooks (caches, attachments, counts) stay correct.
 */
final class Cleanup {

	/** Rows handled per run of a batch tool; run again to continue. */
	public const BATCH = 500;

	/** @return array<string,array{0:string,1:string}> Tool id => [label, what it does]. */
	public static function tools(): array {
		return [
			'expired_transients' => [ __( 'Expired transients', 'wpessential' ), __( 'Cached values that have already expired. Always safe.', 'wpessential' ) ],
			'all_transients'     => [ __( 'All transients', 'wpessential' ), __( 'Every cached value, including ones still in use. Plugins rebuild them; the site may be slower for a moment.', 'wpessential' ) ],
			'revisions'          => [ __( 'Post revisions', 'wpessential' ), __( 'Old saved versions of posts and pages. The current content stays.', 'wpessential' ) ],
			'auto_drafts'        => [ __( 'Auto-drafts', 'wpessential' ), __( 'Empty drafts WordPress creates when you open a new post and leave.', 'wpessential' ) ],
			'trashed_posts'      => [ __( 'Posts in the Trash', 'wpessential' ), __( 'Empties the Trash of every post type.', 'wpessential' ) ],
			'spam_comments'      => [ __( 'Spam and trashed comments', 'wpessential' ), __( 'Comments marked as spam or moved to the Trash.', 'wpessential' ) ],
			'orphaned_postmeta'  => [ __( 'Orphaned post meta', 'wpessential' ), __( 'Custom fields left behind by posts that no longer exist.', 'wpessential' ) ],
			'theme_mods'         => [ __( 'Theme customizer settings', 'wpessential' ), __( 'Resets the active theme\'s Customizer settings (colours, logo, layout) to its defaults.', 'wpessential' ) ],
			'widgets'            => [ __( 'Widgets', 'wpessential' ), __( 'Moves every widget to Inactive Widgets, so nothing is lost and you can drag them back.', 'wpessential' ) ],
		];
	}

	/** @return array<string,int> Items each tool would affect now. */
	public function counts(): array {
		$counts = [];
		foreach ( array_keys( self::tools() ) as $tool ) {
			$counts[ $tool ] = $this->count( $tool );
		}

		return $counts;
	}

	public function count( string $tool ): int {
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Live counts for a maintenance tool.
		$count = match ( $tool ) {
			'expired_transients' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d", $wpdb->esc_like( '_transient_timeout_' ) . '%', time() ) ),
			'all_transients'     => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_site_transient_' ) . '%' ) ),
			'revisions'          => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", 'revision' ) ),
			'auto_drafts'        => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s", 'auto-draft' ) ),
			'trashed_posts'      => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s", 'trash' ) ),
			'spam_comments'      => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved IN (%s, %s)", 'spam', 'trash' ) ),
			'orphaned_postmeta'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" ),
			'theme_mods'         => count( (array) get_theme_mods() ),
			'widgets'            => $this->activeWidgetCount(),
			default              => 0,
		};
		// phpcs:enable

		return $count;
	}

	/** Runs one tool; returns how many items it removed or moved. */
	public function run( string $tool ): int {
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bounded maintenance queries.
		switch ( $tool ) {
			case 'expired_transients':
				$before = $this->count( $tool );
				delete_expired_transients( true );
				return $before;
			case 'all_transients':
				$names = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_' ) . '%', $wpdb->esc_like( '_site_transient_' ) . '%' ) );
				$done  = 0;
				foreach ( $names as $name ) {
					$name = (string) $name;
					if ( str_starts_with( $name, '_site_transient_timeout_' ) || str_starts_with( $name, '_transient_timeout_' ) ) {
						continue;
					}
					$done += str_starts_with( $name, '_site_transient_' ) ? (int) delete_site_transient( substr( $name, 16 ) ) : (int) delete_transient( substr( $name, 11 ) );
				}
				// Timeout rows whose value row was already gone.
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s", $wpdb->esc_like( '_transient_timeout_' ) . '%', $wpdb->esc_like( '_site_transient_timeout_' ) . '%' ) );
				wp_cache_flush();
				return $done;
			case 'revisions':
				return $this->eachPost( 'post_type', 'revision', static fn ( int $id ): bool => (bool) wp_delete_post_revision( $id ) );
			case 'auto_drafts':
				return $this->eachPost( 'post_status', 'auto-draft', static fn ( int $id ): bool => (bool) wp_delete_post( $id, true ) );
			case 'trashed_posts':
				return $this->eachPost( 'post_status', 'trash', static fn ( int $id ): bool => (bool) wp_delete_post( $id, true ) );
			case 'spam_comments':
				$ids  = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_approved IN (%s, %s) LIMIT %d", 'spam', 'trash', self::BATCH ) );
				$done = 0;
				foreach ( $ids as $id ) {
					$done += (int) wp_delete_comment( (int) $id, true );
				}
				return $done;
			case 'orphaned_postmeta':
				return (int) $wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE p.ID IS NULL" );
			case 'theme_mods':
				$count = $this->count( $tool );
				remove_theme_mods();
				return $count;
			case 'widgets':
				return $this->deactivateWidgets();
		}
		// phpcs:enable

		return 0;
	}

	/** @param callable(int): bool $delete */
	private function eachPost( string $column, string $value, callable $delete ): int {
		$column = 'post_type' === $column ? 'post_type' : 'post_status';
		global $wpdb;
		// The WHERE clauses are fixed strings from run(), never user input.
		$ids  = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE {$column} = %s LIMIT %d", $value, self::BATCH ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$done = 0;
		foreach ( $ids as $id ) {
			$done += (int) $delete( (int) $id );
		}

		return $done;
	}

	private function activeWidgetCount(): int {
		$count = 0;
		foreach ( (array) get_option( 'sidebars_widgets', [] ) as $sidebar => $widgets ) {
			if ( $sidebar !== 'wp_inactive_widgets' && $sidebar !== 'array_version' && is_array( $widgets ) ) {
				$count += count( $widgets );
			}
		}

		return $count;
	}

	private function deactivateWidgets(): int {
		$sidebars = (array) get_option( 'sidebars_widgets', [] );
		$inactive = is_array( $sidebars['wp_inactive_widgets'] ?? null ) ? $sidebars['wp_inactive_widgets'] : [];
		$moved    = 0;
		foreach ( $sidebars as $sidebar => $widgets ) {
			if ( $sidebar === 'wp_inactive_widgets' || $sidebar === 'array_version' || ! is_array( $widgets ) ) {
				continue;
			}
			$inactive             = array_merge( $inactive, $widgets );
			$moved               += count( $widgets );
			$sidebars[ $sidebar ] = [];
		}
		$sidebars['wp_inactive_widgets'] = array_values( array_unique( $inactive ) );
		update_option( 'sidebars_widgets', $sidebars );

		return $moved;
	}
}
