<?php
/**
 * Step-based demo importer.
 *
 * The browser calls run() again and again; each call works for a few seconds and
 * returns progress plus new log lines. State lives in an option, so a closed tab,
 * a timeout or a PHP fatal never loses work: the next call resumes at the same item.
 * An item that crashes the server twice is skipped and logged as an error.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Platform\Demos\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Importer.
 */
class Importer {

	const JOB      = 'wpessential_core_job';
	const IMPORTED = 'wpessential_core_imported';
	const META     = '_wpessential_demo';

	/**
	 * Current job.
	 *
	 * @var array
	 */
	private $job = array();

	/**
	 * Parsed content.json.
	 *
	 * @var array|null
	 */
	private $content = null;

	/**
	 * Labels for each step.
	 *
	 * @return array
	 */
	public static function step_labels() {
		return array(
			'preflight' => __( 'Checking your server', 'wpessential' ),
			'cleanup'   => __( 'Removing the previous demo', 'wpessential' ),
			'package'   => __( 'Preparing the demo package', 'wpessential' ),
			'plugins'   => __( 'Installing plugins', 'wpessential' ),
			'media'       => __( 'Importing images', 'wpessential' ),
			'definitions' => __( 'Creating post types and fields', 'wpessential' ),
			'terms'       => __( 'Creating categories', 'wpessential' ),
			'forms'     => __( 'Creating forms', 'wpessential' ),
			'content'   => __( 'Building pages and posts', 'wpessential' ),
			'products'  => __( 'Adding products', 'wpessential' ),
			'menus'     => __( 'Creating menus', 'wpessential' ),
			'settings'  => __( 'Applying site settings', 'wpessential' ),
			'finalize'  => __( 'Finishing touches', 'wpessential' ),
		);
	}

	/**
	 * Saved record of the last import (used for removal).
	 *
	 * @return array
	 */
	public static function imported() {
		return (array) get_option( self::IMPORTED, array() );
	}

	/**
	 * Current job, if any.
	 *
	 * @return array
	 */
	public static function current() {
		return (array) get_option( self::JOB, array() );
	}

	/**
	 * Start a new job.
	 *
	 * @param string $demo_id Demo.
	 * @param string $builder Builder.
	 * @param array  $options Toggles: plugins, media, content, products, settings, reset.
	 * @return array|\WP_Error
	 */
	public static function start( $demo_id, $builder, array $options ) {
		$running = self::current();
		if ( ! empty( $running['status'] ) && 'running' === $running['status'] && ( time() - (int) $running['touched'] ) < 120 ) {
			return new \WP_Error( 'wpe_busy', __( 'Another import is running in a different tab. Wait for it to finish, or wait two minutes and try again.', 'wpessential' ) );
		}
		$demo = Demos::get( $demo_id );
		if ( ! $demo ) {
			return new \WP_Error( 'wpe_demo', __( 'This demo is no longer in the library. Refresh the page.', 'wpessential' ) );
		}
		if ( empty( $demo['builders'][ $builder ] ) ) {
			return new \WP_Error( 'wpe_builder', __( 'This demo is not available for the selected builder.', 'wpessential' ) );
		}
		if ( ! empty( $demo['locked'] ) ) {
			return new \WP_Error( 'wpe_license', __( 'This is a Pro demo. Activate your license on the License tab to import it.', 'wpessential' ) );
		}
		if ( 'bundled' !== ( $demo['source'] ?? '' ) && 'licensed' === ( $demo['builders'][ $builder ]['package'] ?? '' ) ) {
			$url = Demos::packageUrl( $demo['id'], $builder );
			if ( is_wp_error( $url ) ) {
				return new \WP_Error( 'wpe_license', __( 'Pro demos download from wpessential.org with an activated licence key. Activate your key on the License screen, then import again. Nothing on your site was changed.', 'wpessential' ) );
			}
		}

		delete_option( Rest::AUTOSTART );
		$skip = array_map( 'sanitize_key', (array) ( $options['skip'] ?? array() ) );
		unset( $options['skip'] );
		$options = wp_parse_args(
			array_map( 'rest_sanitize_boolean', $options ),
			array(
				'plugins'  => true,
				'media'    => true,
				'content'  => true,
				'products' => true,
				'settings' => true,
				'reset'    => false,
			)
		);

		$options['skip'] = $skip;

		// Package first: a licence or download failure must stop the import before the previous demo is removed.
		$keys = array( 'preflight', 'package' );
		if ( $options['reset'] && self::imported() ) {
			$keys[] = 'cleanup';
		}
		if ( $options['plugins'] ) {
			$keys[] = 'plugins';
		}
		if ( $options['content'] ) {
			if ( $options['media'] ) {
				$keys[] = 'media';
			}
			array_push( $keys, 'definitions', 'terms', 'forms', 'content' );
			if ( $options['products'] ) {
				$keys[] = 'products';
			}
			$keys[] = 'menus';
		}
		if ( $options['settings'] ) {
			$keys[] = 'settings';
		}
		$keys[] = 'finalize';

		$labels = self::step_labels();
		$job    = array(
			'id'       => wp_generate_password( 12, false ),
			'demo'     => $demo_id,
			'title'    => $demo['title'],
			'builder'  => $builder,
			'options'  => $options,
			'dir'      => '',
			'steps'    => array_map(
				static function ( $key ) use ( $labels ) {
					return array(
						'key'    => $key,
						'label'  => $labels[ $key ],
						'status' => 'pending',
						'total'  => 0,
						'done'   => 0,
					);
				},
				$keys
			),
			'index'    => 0,
			'cursor'   => 0,
			'inflight' => array(
				'step'     => '',
				'cursor'   => -1,
				'attempts' => 0,
			),
			'status'   => 'running',
			'log'      => array(),
			'seq'      => 0,
			'map'      => array(
				'media' => array(),
				'terms' => array(),
				'posts' => array(),
			),
			'counts'   => array(
				'info'    => 0,
				'success' => 0,
				'warning' => 0,
				'error'   => 0,
			),
			'started'  => time(),
			'touched'  => time(),
			'finished' => 0,
		);
		update_option( self::JOB, $job, false );

		$importer      = new self();
		$importer->job = $job;
		/* translators: 1: demo, 2: builder. */
		$importer->log( 'info', sprintf( __( 'Starting import of %1$s for %2$s.', 'wpessential' ), $demo['title'], ucfirst( $builder ) ) );
		$importer->save();
		return $importer->snapshot( 0 );
	}

	/**
	 * Work for a few seconds.
	 *
	 * @param int $since Return log entries after this sequence number.
	 * @return array|\WP_Error
	 */
	public static function run( $since = 0 ) {
		$importer      = new self();
		$importer->job = self::current();
		if ( empty( $importer->job['id'] ) ) {
			return new \WP_Error( 'wpe_no_job', __( 'There is no import to continue.', 'wpessential' ) );
		}
		if ( 'running' === $importer->job['status'] ) {
			$importer->work();
		}
		return $importer->snapshot( $since );
	}

	/**
	 * Cancel the running job (content imported so far stays and can be removed).
	 *
	 * @return array
	 */
	public static function cancel() {
		$importer      = new self();
		$importer->job = self::current();
		if ( ! empty( $importer->job['id'] ) && 'running' === $importer->job['status'] ) {
			$importer->job['status'] = 'cancelled';
			$importer->log( 'warning', __( 'Import cancelled. Everything imported so far can be removed from the Demo Library.', 'wpessential' ) );
			$importer->save();
		}
		return $importer->snapshot( 0 );
	}

	/**
	 * State for the browser.
	 *
	 * @param int $since Log sequence.
	 * @return array
	 */
	private function snapshot( $since ) {
		$job   = $this->job;
		$total = 0;
		$done  = 0;
		foreach ( $job['steps'] as $step ) {
			++$total;
			if ( in_array( $step['status'], array( 'done', 'skipped' ), true ) ) {
				++$done;
			} elseif ( 'running' === $step['status'] && $step['total'] ) {
				$done += $step['done'] / $step['total'];
			}
		}
		return array(
			'id'       => $job['id'],
			'demo'     => $job['demo'],
			'title'    => $job['title'],
			'builder'  => $job['builder'],
			'status'   => $job['status'],
			'steps'    => $job['steps'],
			'index'    => $job['index'],
			'progress' => $total ? (int) floor( $done / $total * 100 ) : 0,
			'counts'   => $job['counts'],
			'seq'      => $job['seq'],
			'log'      => array_values(
				array_filter(
					$job['log'],
					static function ( $line ) use ( $since ) {
						return $line['seq'] > $since;
					}
				)
			),
			'elapsed'  => ( $job['finished'] ? $job['finished'] : time() ) - $job['started'],
			'links'    => 'done' === $job['status'] ? array(
				'site'      => home_url( '/' ),
				'customize' => admin_url( wp_is_block_theme() ? 'site-editor.php' : 'customize.php' ),
			) : null,
		);
	}

	/**
	 * Persist the job.
	 */
	private function save() {
		$this->job['touched'] = time();
		update_option( self::JOB, $this->job, false );
	}

	/**
	 * Add a log line.
	 *
	 * @param string $level   info|success|warning|error.
	 * @param string $message Message.
	 * @param string $hint    Optional fix suggestion.
	 */
	private function log( $level, $message, $hint = '' ) {
		++$this->job['seq'];
		++$this->job['counts'][ $level ];
		$step               = $this->job['steps'][ $this->job['index'] ]['key'] ?? '';
		$this->job['log'][] = array(
			'seq'   => $this->job['seq'],
			'time'  => time(),
			'level' => $level,
			'step'  => $step,
			'msg'   => wp_strip_all_tags( $message ),
			'hint'  => $hint,
		);
		$this->job['log']   = array_slice( $this->job['log'], -400 );
	}

	/**
	 * Main loop: process items until the time budget is used.
	 */
	private function work() {
		$budget = (float) apply_filters( 'wpessential_core_import_budget', 6.0 );
		$start  = microtime( true );
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 120 ); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Imports need more than the default.
		}
		wp_raise_memory_limit( 'admin' );
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound -- Core constant that tells plugins an import is running.
		}

		while ( 'running' === $this->job['status'] && ( microtime( true ) - $start ) < $budget ) {
			if ( ! isset( $this->job['steps'][ $this->job['index'] ] ) ) {
				$this->complete();
				break;
			}
			$step = &$this->job['steps'][ $this->job['index'] ];
			if ( 'pending' === $step['status'] ) {
				$step['status']      = 'running';
				$this->job['cursor'] = 0;
				$step['total']       = $this->count_items( $step['key'] );
				if ( 0 === $step['total'] ) {
					$step['status'] = 'skipped';
					$this->next_step();
					unset( $step );
					continue;
				}
			}

			$cursor   = $this->job['cursor'];
			$inflight = $this->job['inflight'];
			if ( $inflight['step'] === $step['key'] && $inflight['cursor'] === $cursor && $inflight['attempts'] >= 2 ) {
				$this->log( 'error', __( 'One item stopped the server twice and was skipped.', 'wpessential' ), __( 'Raise memory_limit / max_execution_time, then re-import to fill the gap.', 'wpessential' ) );
				$this->advance( $step );
				unset( $step );
				continue;
			}
			$this->job['inflight'] = array(
				'step'     => $step['key'],
				'cursor'   => $cursor,
				'attempts' => ( $inflight['step'] === $step['key'] && $inflight['cursor'] === $cursor ) ? $inflight['attempts'] + 1 : 1,
			);
			$this->save();

			try {
				$fatal = $this->{'step_' . $step['key']}( $cursor );
			} catch ( \Throwable $e ) {
				$fatal = null;
				/* translators: %s: error. */
				$this->log( 'error', sprintf( __( 'Unexpected error: %s', 'wpessential' ), $e->getMessage() ) );
			}

			if ( is_wp_error( $fatal ) ) {
				$step['status']      = 'failed';
				$this->job['status'] = 'failed';
				$this->log( 'error', $fatal->get_error_message(), (string) $fatal->get_error_data() );
				$this->job['finished'] = time();
				unset( $step );
				break;
			}
			$key = $step['key'];
			$this->advance( $step );
			unset( $step );
			// Plugins activated in this request are not fully booted; continue in a fresh request.
			if ( in_array( $key, array( 'cleanup', 'plugins' ), true ) && $this->job['steps'][ $this->job['index'] - 1 ]['key'] === $key && 0 === $this->job['cursor'] ) {
				break;
			}
		}
		$this->save();
	}

	/**
	 * Move to the next item or step.
	 *
	 * @param array $step Step (by reference).
	 */
	private function advance( &$step ) {
		++$this->job['cursor'];
		$step['done'] = min( $step['total'], $this->job['cursor'] );
		if ( $this->job['cursor'] >= $step['total'] ) {
			$step['status'] = 'done';
			$this->next_step();
		}
	}

	/**
	 * Go to the next step.
	 */
	private function next_step() {
		++$this->job['index'];
		$this->job['cursor'] = 0;
	}

	/**
	 * All steps finished.
	 */
	private function complete() {
		$this->job['status']   = 'done';
		$this->job['finished'] = time();
		$counts                = $this->job['counts'];
		if ( $counts['error'] ) {
			/* translators: %d: errors. */
			$this->log( 'warning', sprintf( _n( 'Import finished with %d error. The site works; review the log for what was skipped.', 'Import finished with %d errors. The site works; review the log for what was skipped.', $counts['error'], 'wpessential' ), $counts['error'] ) );
		} else {
			$this->log( 'success', __( 'Your site is ready.', 'wpessential' ) );
		}
		/**
		 * Fires after a demo import.
		 *
		 * @param string $demo    Demo id.
		 * @param string $builder Builder.
		 * @param array  $map     Old key => new ID maps.
		 */
		update_option( 'wpessential_active_demo', sanitize_key( $this->job['demo'] ) );
		do_action( 'wpessential_demo_imported', $this->job['demo'], $this->job['builder'], $this->job['map'] );
	}

	/**
	 * How many items a step has.
	 *
	 * @param string $key Step.
	 * @return int
	 */
	private function count_items( $key ) {
		switch ( $key ) {
			case 'preflight':
			case 'cleanup':
			case 'package':
			case 'settings':
			case 'finalize':
				return 1;
			case 'plugins':
				return count( $this->plugins() );
			case 'products':
				if ( ! class_exists( 'WooCommerce' ) ) {
					$items = $this->content()['products'] ?? array();
					if ( $items ) {
						$this->log( 'warning', __( 'WooCommerce is not active, so the shop products were skipped.', 'wpessential' ) );
					}
					return 0;
				}
				return count( $this->content()['products'] ?? array() );
			default:
				return count( $this->content()[ $key ] ?? array() );
		}
	}

	/**
	 * Demo package plugins for the chosen builder.
	 *
	 * @return array
	 */
	private function plugins() {
		$demo = Demos::get( $this->job['demo'] );
		$skip = (array) ( $this->job['options']['skip'] ?? array() );
		return array_values(
			array_filter(
				(array) ( $demo['builders'][ $this->job['builder'] ]['plugins'] ?? array() ),
				static function ( $plugin ) use ( $skip ) {
					return 'recommended' !== ( $plugin['state'] ?? 'required' ) || ! in_array( $plugin['slug'], $skip, true );
				}
			)
		);
	}

	/**
	 * Package content.json.
	 *
	 * @return array
	 */
	private function content() {
		if ( null === $this->content ) {
			$file          = $this->job['dir'] ? $this->job['dir'] . '/content.json' : '';
			$data          = $file && is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file.
			$this->content = is_array( $data ) ? $data : array();
		}
		return $this->content;
	}

	/**
	 * Record an object for removal.
	 *
	 * @param string $type  posts|terms.
	 * @param mixed  $value ID or [id, taxonomy].
	 */
	private function track( $type, $value ) {
		$record = $this->record();
		if ( 'option' === $type ) {
			if ( ! array_key_exists( $value, $record['options'] ) ) {
				$record['options'][ $value ] = get_option( $value, null );
			}
		} elseif ( in_array( $type, array( 'styles', 'kit' ), true ) || 0 === strpos( $type, 'part_' ) ) {
			// Keep the first snapshot: it is the state before this demo.
			if ( ! isset( $record[ $type ] ) ) {
				$record[ $type ] = $value;
			}
		} else {
			$record[ $type ][] = $value;
		}
		update_option( self::IMPORTED, $record, false );
	}

	/**
	 * The removal record for this job.
	 *
	 * @return array
	 */
	private function record() {
		$record = self::imported();
		if ( empty( $record['demo'] ) || $record['job'] !== $this->job['id'] ) {
			$record = array(
				'job'     => $this->job['id'],
				'demo'    => $this->job['demo'],
				'title'   => $this->job['title'],
				'builder' => $this->job['builder'],
				'time'    => time(),
				'posts'   => array(),
				'terms'   => array(),
				'options' => array(),
			);
		}
		return $record;
	}

	/**
	 * Replace {{tokens}} in a string.
	 *
	 * @param string $text Text.
	 * @param bool   $late Also resolve links to posts (after all posts exist).
	 * @return string
	 */
	private function tokens( $text, $late = false ) {
		$map = $this->job['map'];
		return preg_replace_callback(
			'/\{\{(pattern|media|media_id|theme|home|page|post_id|term_link|woo|site|form):?([a-z0-9\/_.-]*)\}\}/',
			static function ( $m ) use ( $map, $late ) {
				switch ( $m[1] ) {
					case 'pattern':
						$pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( $m[2] );
						return $pattern ? $pattern['content'] : '';
					case 'media':
						return isset( $map['media'][ $m[2] ] ) ? (string) wp_get_attachment_url( $map['media'][ $m[2] ] ) : get_theme_file_uri( 'assets/images/' . $m[2] . '.webp' );
					case 'media_id':
						return (string) ( $map['media'][ $m[2] ] ?? 0 );
					case 'theme':
						return get_theme_file_uri( $m[2] );
					case 'home':
						return home_url( '/' . $m[2] );
					case 'page':
						if ( ! $late ) {
							return $m[0];
						}
						return isset( $map['posts'][ $m[2] ] ) ? (string) get_permalink( $map['posts'][ $m[2] ] ) : home_url( '/' );
					case 'post_id':
						return $late ? (string) ( $map['posts'][ $m[2] ] ?? 0 ) : $m[0];
					case 'woo':
						// WooCommerce pages exist only after the settings step.
						if ( ! $late ) {
							return $m[0];
						}
						return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( $m[2] ) : home_url( '/' );
					case 'form':
						// WPForms form created in the forms step; empty when WPForms could not be installed.
						return (string) ( $map['forms'][ $m[2] ] ?? 0 );
					case 'site':
						// The importing site's own name and admin email, e.g. in the legal pages.
						if ( ! $late ) {
							return $m[0];
						}
						return esc_html( 'email' === $m[2] ? (string) get_option( 'admin_email' ) : (string) get_option( 'blogname' ) );
					case 'term_link':
						if ( ! $late || empty( $map['terms'][ $m[2] ] ) ) {
							return $late ? home_url( '/' ) : $m[0];
						}
						$link = get_term_link( (int) $map['terms'][ $m[2] ][0], $map['terms'][ $m[2] ][1] );
						return is_wp_error( $link ) ? home_url( '/' ) : $link;
					default:
						return $m[0];
				}
			},
			(string) $text
		);
	}

	// Steps. Each processes item $i and returns a WP_Error only for blockers.

	/**
	 * Server checks.
	 *
	 * @return \WP_Error|null
	 */
	private function step_preflight() {
		$blockers = 0;
		foreach ( Status::checks() as $check ) {
			if ( 'ok' === $check['state'] ) {
				continue;
			}
			$level = 'fail' === $check['state'] && ! empty( $check['blocking'] ) ? 'error' : 'warning';
			if ( 'error' === $level ) {
				++$blockers;
			}
			$this->log( $level, $check['label'] . ': ' . $check['value'], $check['hint'] );
		}
		if ( $blockers ) {
			return new \WP_Error( 'wpe_preflight', __( 'Your server needs a change before the import can run safely.', 'wpessential' ), __( 'Fix the items above (your host can help), then press Retry.', 'wpessential' ) );
		}
		$previous = self::imported();
		if ( ! empty( $previous['demo'] ) && ! $this->job['options']['reset'] ) {
			/* translators: %s: demo. */
			$this->log( 'warning', sprintf( __( '%s was imported before. Its pages stay; the new import adds fresh copies.', 'wpessential' ), $previous['title'] ), __( 'Tick "Remove previous demo" next time for a clean site.', 'wpessential' ) );
		}
		$this->log( 'success', __( 'Server checks passed.', 'wpessential' ) );
		return null;
	}

	/**
	 * Remove the previous import.
	 *
	 * @return null
	 */
	private function step_cleanup() {
		$removed = self::remove();
		/* translators: %d: items. */
		$this->log( 'success', sprintf( __( 'Removed %d items from the previous demo and restored its settings.', 'wpessential' ), $removed ) );
		return null;
	}

	/**
	 * Locate or download the package.
	 *
	 * @return \WP_Error|null
	 */
	private function step_package() {
		$demo = Demos::get( $this->job['demo'] );
		$dir  = Demos::package_dir( $demo, $this->job['builder'] );
		if ( is_wp_error( $dir ) ) {
			$hint = 'wpe_license' === $dir->get_error_code() ? __( 'Activate your licence key on the License screen, then press Resume.', 'wpessential' ) : ( 'bundled' === $demo['source'] ? __( 'Upload a fresh copy of the theme, then press Resume.', 'wpessential' ) : __( 'Check your internet connection and press Resume.', 'wpessential' ) );
			return new \WP_Error( $dir->get_error_code(), $dir->get_error_message(), $hint );
		}
		$this->job['dir'] = $dir;
		$this->content    = null;
		if ( ! $this->content() ) {
			return new \WP_Error( 'wpe_package_json', __( 'The demo package could not be read (content.json is damaged).', 'wpessential' ), __( 'Clear the cache on the System Status tab and retry.', 'wpessential' ) );
		}
		$content = $this->content();
		/* translators: 1: pages, 2: images, 3: products. */
		$this->log( 'info', sprintf( __( 'Package ready: %1$d pages and posts, %2$d images, %3$d products.', 'wpessential' ), count( $content['content'] ?? array() ), count( $content['media'] ?? array() ), count( $content['products'] ?? array() ) ) );
		return null;
	}

	/**
	 * One plugin.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_plugins( $i ) {
		$plugin = $this->plugins()[ $i ];
		$name   = $plugin['name'] ?? $plugin['slug'];
		$result = Plugins::apply( $plugin );
		$state  = $plugin['state'] ?? 'required';
		if ( is_wp_error( $result ) ) {
			$level = 'required' === $state ? 'error' : 'warning';
			/* translators: 1: plugin, 2: error. */
			$this->log( $level, sprintf( __( '%1$s could not be set up: %2$s', 'wpessential' ), $name, $result->get_error_message() ), __( 'Install it from Plugins → Add New, then use the Plugins tab here to activate it.', 'wpessential' ) );
			return null;
		}
		$messages = array(
			/* translators: %s: plugin. */
			'installed'   => __( '%s installed and activated.', 'wpessential' ),
			/* translators: %s: plugin. */
			'activated'   => __( '%s activated.', 'wpessential' ),
			/* translators: %s: plugin. */
			'deactivated' => __( '%s deactivated because it conflicts with this demo.', 'wpessential' ),
			/* translators: %s: plugin. */
			'ok'          => __( '%s is already active.', 'wpessential' ),
		);
		$this->log( 'ok' === $result ? 'info' : 'success', sprintf( $messages[ $result ], $name ) );
		return null;
	}

	/**
	 * One image.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_media( $i ) {
		$item = $this->content()['media'][ $i ];
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$source = $item['source'] ?? 'package';
		if ( 'url' === $source ) {
			$tmp = download_url( $item['file'], 60 );
		} else {
			$path = 'theme' === $source ? get_theme_file_path( 'assets/images/' . $item['file'] ) : $this->job['dir'] . '/media/' . $item['file'];
			// Both builder packages of a demo share one media folder next to them.
			if ( 'package' === $source && ! is_readable( $path ) ) {
				$path = dirname( $this->job['dir'] ) . '/media/' . $item['file'];
			}
			$tmp = wp_tempnam( $item['file'] );
			if ( ! is_readable( $path ) || ! copy( $path, $tmp ) ) {
				$tmp = new \WP_Error( 'wpe_media_missing', __( 'file not found in the package', 'wpessential' ) );
			}
		}
		if ( is_wp_error( $tmp ) ) {
			/* translators: 1: file, 2: error. */
			$this->log( 'warning', sprintf( __( 'Image %1$s skipped (%2$s). Pages fall back to the theme copy.', 'wpessential' ), $item['file'], $tmp->get_error_message() ) );
			return null;
		}
		$id = media_handle_sideload(
			array(
				'name'     => wp_basename( $item['file'] ),
				'tmp_name' => $tmp,
			),
			0,
			$item['title'] ?? null
		);
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			/* translators: 1: file, 2: error. */
			$this->log( 'warning', sprintf( __( 'Image %1$s skipped (%2$s).', 'wpessential' ), $item['file'], $id->get_error_message() ), __( 'Check that the uploads folder is writable.', 'wpessential' ) );
			return null;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $item['alt'] ?? '' ) );
		update_post_meta( $id, self::META, $this->job['demo'] );
		$this->job['map']['media'][ $item['key'] ] = $id;
		$this->track( 'posts', $id );
		/* translators: %s: file. */
		$this->log( 'info', sprintf( __( 'Imported image %s.', 'wpessential' ), $item['file'] ) );
		return null;
	}

	/**
	 * One term.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_terms( $i ) {
		$item     = $this->content()['terms'][ $i ];
		$taxonomy = $item['taxonomy'];
		if ( ! taxonomy_exists( $taxonomy ) ) {
			/* translators: 1: term, 2: taxonomy. */
			$this->log( 'info', sprintf( __( 'Skipped "%1$s": %2$s is not available on this site.', 'wpessential' ), $item['name'], $taxonomy ) );
			return null;
		}
		$existing = term_exists( $item['slug'], $taxonomy );
		if ( $existing ) {
			$id = (int) $existing['term_id'];
			/* translators: %s: term. */
			$this->log( 'info', sprintf( __( 'Category "%s" already exists and was reused.', 'wpessential' ), $item['name'] ) );
		} else {
			$created = wp_insert_term(
				$item['name'],
				$taxonomy,
				array(
					'slug'        => $item['slug'],
					'description' => $item['description'] ?? '',
				)
			);
			if ( is_wp_error( $created ) ) {
				/* translators: 1: term, 2: error. */
				$this->log( 'warning', sprintf( __( 'Category "%1$s" was not created: %2$s', 'wpessential' ), $item['name'], $created->get_error_message() ) );
				return null;
			}
			$id = (int) $created['term_id'];
			update_term_meta( $id, self::META, $this->job['demo'] );
			$this->track( 'terms', array( $id, $taxonomy ) );
			/* translators: %s: term. */
			$this->log( 'info', sprintf( __( 'Created category "%s".', 'wpessential' ), $item['name'] ) );
		}
		foreach ( (array) ( $item['meta'] ?? array() ) as $key => $value ) {
			update_term_meta( $id, $key, $this->tokens( $value ) );
		}
		$this->job['map']['terms'][ $item['key'] ] = array( $id, $taxonomy );
		return null;
	}

	/**
	 * One WPForms form from the package (installs WPForms Lite when missing).
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_forms( $i ) {
		$item = $this->content()['forms'][ $i ];
		$id   = Forms::create( (string) $item['title'], (array) ( $item['fields'] ?? array() ), (string) ( $item['submit'] ?? '' ), (string) ( $item['message'] ?? '' ), (array) ( $item['settings'] ?? array() ) );
		if ( is_wp_error( $id ) ) {
			/* translators: 1: form, 2: error. */
			$this->log( 'warning', sprintf( __( 'Form "%1$s" was not created: %2$s', 'wpessential' ), $item['title'], $id->get_error_message() ) );
			return null;
		}
		$this->job['map']['forms'][ $item['key'] ] = $id;
		$this->track( 'posts', $id );
		/* translators: %s: form. */
		$this->log( 'success', sprintf( __( 'Created form "%s".', 'wpessential' ), $item['title'] ) );
		return null;
	}

	/**
	 * One page or post.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_content( $i ) {
		$item = $this->content()['content'][ $i ];
		$type = $item['type'] ?? 'page';
		if ( ! post_type_exists( $type ) ) {
			/* translators: 1: title, 2: post type. */
			$this->log( 'info', sprintf( __( 'Skipped "%1$s": %2$s is not available on this site.', 'wpessential' ), $item['title'], $type ) );
			return null;
		}
		$args = array(
			'post_type'     => $type,
			'post_status'   => 'publish',
			'post_title'    => $item['title'],
			'post_name'     => $item['slug'] ?? '',
			'post_excerpt'  => $item['excerpt'] ?? '',
			'post_content'  => $this->tokens( $item['content'] ?? '' ),
			'post_date'     => $item['date'] ?? current_time( 'mysql' ),
			'menu_order'    => (int) ( $item['order'] ?? 0 ),
			'page_template' => $item['template'] ?? '',
			'post_author'   => get_current_user_id(),
		);
		$id   = $this->insert_post( $type, $args, $item );
		if ( is_wp_error( $id ) ) {
			/* translators: 1: title, 2: error. */
			$this->log( 'error', sprintf( __( '"%1$s" could not be created: %2$s', 'wpessential' ), $item['title'], $id->get_error_message() ) );
			return null;
		}
		update_post_meta( $id, self::META, $this->job['demo'] );
		$this->track( 'posts', $id );
		$this->job['map']['posts'][ $item['key'] ] = $id;

		if ( ! empty( $item['featured'] ) && ! empty( $this->job['map']['media'][ $item['featured'] ] ) ) {
			set_post_thumbnail( $id, $this->job['map']['media'][ $item['featured'] ] );
		}
		foreach ( (array) ( $item['terms'] ?? array() ) as $taxonomy => $keys ) {
			$ids = array();
			foreach ( (array) $keys as $key ) {
				if ( isset( $this->job['map']['terms'][ $key ] ) ) {
					$ids[] = (int) $this->job['map']['terms'][ $key ][0];
				}
			}
			if ( $ids ) {
				wp_set_object_terms( $id, $ids, $taxonomy );
			}
		}
		foreach ( (array) ( $item['meta'] ?? array() ) as $key => $value ) {
			update_post_meta( $id, $key, is_string( $value ) ? wp_slash( $this->tokens( $value, true ) ) : $value );
		}
		if ( ! empty( $item['elementor'] ) ) {
			$this->elementor_meta( $id, $item );
		}
		if ( ! empty( $item['sticky'] ) && 'post' === get_post_type( $id ) ) {
			$this->track( 'option', 'sticky_posts' );
			stick_post( $id );
		}
		$labels = array(
			'post'            => __( 'post', 'wpessential' ),
			'page'            => __( 'page', 'wpessential' ),
			'tribe_events'    => __( 'event', 'wpessential' ),
			'tribe_venue'     => __( 'venue', 'wpessential' ),
			'tribe_organizer' => __( 'organizer', 'wpessential' ),
		);
		/* translators: 1: type, 2: title. */
		$this->log( 'info', sprintf( __( 'Created %1$s "%2$s".', 'wpessential' ), $labels[ $type ] ?? $type, $item['title'] ) );
		return null;
	}

	/**
	 * Insert one item. Events, venues and organizers go through The Events Calendar's API so
	 * its own tables and caches are filled; everything else is a plain post.
	 *
	 * @param string $type Post type.
	 * @param array  $args wp_insert_post() arguments.
	 * @param array  $item Package item.
	 * @return int|\WP_Error
	 */
	private function insert_post( $type, array $args, array $item ) {
		$map = $this->job['map']['posts'];
		if ( 'tribe_venue' === $type && function_exists( 'tribe_create_venue' ) ) {
			$id = tribe_create_venue( $args + array( 'Venue' => $item['title'] ) + (array) ( $item['venue'] ?? array() ) );
			return $id ? (int) $id : new \WP_Error( 'wpe_tec', __( 'The Events Calendar rejected the venue.', 'wpessential' ) );
		}
		if ( 'tribe_organizer' === $type && function_exists( 'tribe_create_organizer' ) ) {
			$id = tribe_create_organizer( $args + array( 'Organizer' => $item['title'] ) + (array) ( $item['organizer'] ?? array() ) );
			return $id ? (int) $id : new \WP_Error( 'wpe_tec', __( 'The Events Calendar rejected the organizer.', 'wpessential' ) );
		}
		if ( 'tribe_events' === $type && function_exists( 'tribe_create_event' ) ) {
			$event = (array) ( $item['event'] ?? array() );
			// A fixed date, or a date relative to the import day so a demo never shows past events.
			$day   = ! empty( $event['date'] ) ? gmdate( 'Y-m-d', strtotime( $event['date'] ) ) : gmdate( 'Y-m-d', strtotime( '+' . (int) ( $event['in_days'] ?? 30 ) . ' days', current_time( 'timestamp' ) ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested -- Local day is wanted.
			$end   = gmdate( 'Y-m-d', strtotime( $day . ' +' . (int) ( $event['days'] ?? 0 ) . ' days' ) );
			$args += array(
				'EventStartDate'   => $day,
				'EventEndDate'     => $end,
				'EventAllDay'      => ! empty( $event['all_day'] ),
				'EventStartHour'   => substr( $event['start'] ?? '09:00', 0, 2 ),
				'EventStartMinute' => substr( $event['start'] ?? '09:00', 3, 2 ),
				'EventEndHour'     => substr( $event['end'] ?? '18:00', 0, 2 ),
				'EventEndMinute'   => substr( $event['end'] ?? '18:00', 3, 2 ),
				'EventCost'        => $event['cost'] ?? '',
				'EventURL'         => $this->tokens( $event['url'] ?? '', true ),
				'EventShowMap'     => true,
				'EventShowMapLink' => true,
			);
			if ( ! empty( $event['venue'] ) && isset( $map[ $event['venue'] ] ) ) {
				$args['Venue'] = array( 'VenueID' => $map[ $event['venue'] ] );
			}
			$organizers = array_values( array_intersect_key( $map, array_flip( (array) ( $event['organizers'] ?? array() ) ) ) );
			if ( $organizers ) {
				$args['Organizer'] = array( 'OrganizerID' => $organizers );
			}
			$id = tribe_create_event( $args );
			return $id ? (int) $id : new \WP_Error( 'wpe_tec', __( 'The Events Calendar rejected the event.', 'wpessential' ) );
		}
		return wp_insert_post( wp_slash( $args ), true );
	}

	/**
	 * Store an Elementor layout on a page or post: native widgets only.
	 *
	 * @param int   $id   Post ID.
	 * @param array $item Package item with an "elementor" element tree.
	 */
	private function elementor_meta( $id, array $item ) {
		$json = $this->tokens( (string) wp_json_encode( $item['elementor'] ) );
		update_post_meta( $id, '_elementor_data', wp_slash( $json ) );
		update_post_meta( $id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $id, '_elementor_template_type', 'wp-' . get_post_type( $id ) );
		update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		if ( ! empty( $item['elementor_settings'] ) ) {
			update_post_meta( $id, '_elementor_page_settings', $item['elementor_settings'] );
		}
		// Block theme: the "Full Width (builder canvas)" template keeps the theme header and footer.
		if ( empty( $item['template'] ) && 'page' === get_post_type( $id ) ) {
			update_post_meta( $id, '_wp_page_template', 'page-full-width' );
		}
	}

	/**
	 * One WooCommerce product.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_products( $i ) {
		$item  = $this->content()['products'][ $i ];
		$map   = $this->job['map'];
		$media = static function ( $key ) use ( $map ) {
			return (int) ( $map['media'][ $key ] ?? 0 );
		};
		$kind  = $item['kind'] ?? 'simple';
		$class = array(
			'simple'   => 'WC_Product_Simple',
			'variable' => 'WC_Product_Variable',
			'grouped'  => 'WC_Product_Grouped',
		)[ $kind ] ?? 'WC_Product_Simple';

		$product = new $class();
		$product->set_name( $item['name'] );
		$product->set_slug( $item['slug'] ?? '' );
		$product->set_short_description( $item['short'] ?? '' );
		$product->set_description( $item['description'] ?? '' );
		$product->set_featured( ! empty( $item['featured'] ) );
		// Passes, tickets and services ship nothing, so checkout skips the address and shipping.
		$product->set_virtual( ! empty( $item['virtual'] ) );
		if ( ! empty( $item['sold_individually'] ) ) {
			$product->set_sold_individually( true );
		}
		if ( ! empty( $item['menu_order'] ) ) {
			$product->set_menu_order( (int) $item['menu_order'] );
		}
		if ( 'simple' === $kind ) {
			$product->set_regular_price( (string) $item['price'] );
			if ( ! empty( $item['sale'] ) ) {
				$product->set_sale_price( (string) $item['sale'] );
			}
		}
		if ( isset( $item['stock'] ) ) {
			if ( 'out' === $item['stock'] ) {
				$product->set_stock_status( 'outofstock' );
			} else {
				$product->set_manage_stock( true );
				$product->set_stock_quantity( (int) $item['stock'] );
			}
		}
		$cats = array();
		foreach ( (array) ( $item['categories'] ?? array() ) as $key ) {
			if ( isset( $map['terms'][ $key ] ) ) {
				$cats[] = (int) $map['terms'][ $key ][0];
			}
		}
		$product->set_category_ids( $cats );
		$product->set_image_id( $media( $item['image'] ?? '' ) );
		$product->set_gallery_image_ids( array_filter( array_map( $media, (array) ( $item['gallery'] ?? array() ) ) ) );
		// Product meta, e.g. the WPEssential Server plan fields (_wpes_plan, _wpes_months …) on plan products.
		foreach ( (array) ( $item['meta'] ?? array() ) as $key => $value ) {
			$product->update_meta_data( sanitize_key( $key ), $value );
		}

		$attributes = array();
		foreach ( (array) ( $item['attributes'] ?? array() ) as $attr ) {
			$a   = new \WC_Product_Attribute();
			$tax = empty( $attr['global'] ) ? '' : $this->global_attribute( $attr );
			if ( $tax ) {
				// Global (taxonomy) attribute, so shop filters such as filter_size work.
				$a->set_id( wc_attribute_taxonomy_id_by_name( $tax ) );
				$a->set_name( $tax );
				$a->set_options( array_map( static fn( $o ) => (int) get_term_by( 'name', $o, $tax )->term_id, $attr['options'] ) );
			} else {
				$a->set_name( $attr['name'] );
				$a->set_options( $attr['options'] );
			}
			$a->set_visible( true );
			$a->set_variation( 'variable' === $kind );
			$attributes[] = $a;
		}
		if ( $attributes ) {
			$product->set_attributes( $attributes );
		}
		if ( 'grouped' === $kind ) {
			$product->set_children(
				array_values(
					array_filter(
						array_map(
							static function ( $key ) use ( $map ) {
								return (int) ( $map['posts'][ $key ] ?? 0 );
							},
							(array) ( $item['children'] ?? array() )
						)
					)
				)
			);
		}
		$id = $product->save();
		// Terms in other product taxonomies (e.g. a niche taxonomy from the demo's definitions).
		foreach ( (array) ( $item['terms'] ?? array() ) as $taxonomy => $keys ) {
			$ids = array();
			foreach ( (array) $keys as $key ) {
				if ( isset( $map['terms'][ $key ] ) ) {
					$ids[] = (int) $map['terms'][ $key ][0];
				}
			}
			if ( $ids && $id ) {
				wp_set_object_terms( $id, $ids, sanitize_key( $taxonomy ) );
			}
		}
		if ( ! $id ) {
			/* translators: %s: product. */
			$this->log( 'error', sprintf( __( 'Product "%s" could not be saved.', 'wpessential' ), $item['name'] ) );
			return null;
		}

		if ( 'variable' === $kind ) {
			$this->variations( $id, $item );
		}
		update_post_meta( $id, self::META, $this->job['demo'] );
		$this->track( 'posts', $id );
		$this->job['map']['posts'][ $item['key'] ] = $id;
		/* translators: %s: product. */
		$this->log( 'info', sprintf( __( 'Added product "%s".', 'wpessential' ), $item['name'] ) );
		return null;
	}

	/**
	 * Create (once) a global product attribute and its terms; returns the taxonomy name.
	 *
	 * @param array $attr Attribute: name, options.
	 * @return string Empty on failure.
	 */
	private function global_attribute( $attr ) {
		$tax = wc_attribute_taxonomy_name( $attr['name'] );
		if ( ! wc_attribute_taxonomy_id_by_name( $tax ) ) {
			$made = wc_create_attribute(
				array(
					'name'     => $attr['name'],
					'slug'     => sanitize_title( $attr['name'] ),
					'order_by' => 'menu_order',
				)
			);
			if ( is_wp_error( $made ) ) {
				return '';
			}
		}
		if ( ! taxonomy_exists( $tax ) ) {
			register_taxonomy(
				$tax,
				array( 'product' ),
				array(
					'hierarchical' => false,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				)
			);
		}
		foreach ( array_values( (array) $attr['options'] ) as $order => $option ) {
			$term = term_exists( $option, $tax );
			if ( ! $term ) {
				$term = wp_insert_term( $option, $tax );
			}
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( (int) $term['term_id'], 'order', $order ); // Keep XS, S, M, L in the given order.
			}
		}
		return $tax;
	}

	/**
	 * Every combination of a variable product's attributes.
	 *
	 * @param int   $id   Parent ID.
	 * @param array $item Product data.
	 */
	private function variations( $id, $item ) {
		$combos = array( array() );
		foreach ( (array) $item['attributes'] as $attr ) {
			$next = array();
			foreach ( $combos as $combo ) {
				foreach ( $attr['options'] as $option ) {
					$next[] = ! empty( $attr['global'] )
						? $combo + array( wc_attribute_taxonomy_name( $attr['name'] ) => sanitize_title( $option ) )
						: $combo + array( sanitize_title( $attr['name'] ) => $option );
				}
			}
			$combos = $next;
		}
		foreach ( $combos as $combo ) {
			$variation = new \WC_Product_Variation();
			$variation->set_parent_id( $id );
			$variation->set_attributes( $combo );
			$variation->set_regular_price( (string) $item['price'] );
			if ( ! empty( $item['sale'] ) ) {
				$variation->set_sale_price( (string) $item['sale'] );
			}
			$variation->save();
		}
		\WC_Product_Variable::sync( $id );
	}

	/**
	 * One post type, taxonomy or field group, saved and published through the plugin's own
	 * Abilities (wpessential/cpt, wpessential/taxonomy, wpessential/fields), so it shows and stays
	 * editable in the plugin's screens. Field groups need WPEssential Pro and are skipped without it.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_definitions( $i ) {
		$item  = (array) $this->content()['definitions'][ $i ];
		$kind  = (string) ( $item['kind'] ?? '' );
		$map   = array(
			'cpt'      => array( 'wpessential/cpt/save', 'wpessential/cpt/status', 'post_type_key' ),
			'taxonomy' => array( 'wpessential/taxonomy/save', 'wpessential/taxonomy/status', 'taxonomy_key' ),
			'fields'   => array( 'wpessential/fields/save-group', 'wpessential/fields/status-group', 'group_key' ),
		);
		$kernel = function_exists( 'wpessential_kernel' ) ? wpessential_kernel() : null;
		$name   = (string) ( $item['payload'][ $map[ $kind ][2] ?? '' ] ?? $kind );
		if ( ! isset( $map[ $kind ] ) || ! $kernel || ! $kernel->services()->has( 'platform.abilities' ) ) {
			return null;
		}
		$abilities = $kernel->services()->get( 'platform.abilities' );
		$context   = $kernel->services()->get( 'platform.abilities.contexts' )->current();
		if ( null === $abilities->descriptor( $map[ $kind ][0] ) ) {
			/* translators: %s: definition key. */
			$this->log( 'warning', sprintf( __( '"%s" needs WPEssential Pro and was skipped.', 'wpessential' ), $name ) );
			return null;
		}
		try {
			$saved = $abilities->execute( $map[ $kind ][0], array( 'payload' => (array) $item['payload'] ), $context )['definition'];
			$saved = $abilities->execute(
				$map[ $kind ][1],
				array(
					'id'                => $saved['id'],
					'expected_revision' => $saved['revision'],
					'status'            => 'published',
				),
				$context
			)['definition'];
			$this->track( 'definitions', array( $kind, (string) $saved['id'] ) );
			// The module registers it on the next request; terms and posts are written in this one.
			$payload = (array) $item['payload'];
			if ( 'cpt' === $kind && ! post_type_exists( $name ) ) {
				register_post_type(
					$name,
					array(
						'public' => true,
						'label'  => (string) ( $payload['name'] ?? $name ),
					)
				);
			} elseif ( 'taxonomy' === $kind && ! taxonomy_exists( $name ) ) {
				register_taxonomy(
					$name,
					(array) ( $payload['object_types'] ?? array( 'post' ) ),
					array(
						'public' => true,
						'label'  => (string) ( $payload['name'] ?? $name ),
					)
				);
			}
			/* translators: %s: definition key. */
			$this->log( 'info', sprintf( __( 'Created "%s".', 'wpessential' ), $name ) );
		} catch ( \Throwable $e ) {
			/* translators: 1: definition key, 2: error. */
			$this->log( 'warning', sprintf( __( '"%1$s" was not created: %2$s', 'wpessential' ), $name, $e->getMessage() ) );
		}
		return null;
	}

	/**
	 * Trash, then delete, the post types, taxonomies and field groups a demo created.
	 *
	 * @param array $items [kind, id] pairs.
	 */
	private static function remove_definitions( array $items ) {
		$kernel = function_exists( 'wpessential_kernel' ) ? wpessential_kernel() : null;
		if ( ! $items || ! $kernel || ! $kernel->services()->has( 'platform.abilities' ) ) {
			return;
		}
		$abilities = $kernel->services()->get( 'platform.abilities' );
		$context   = $kernel->services()->get( 'platform.abilities.contexts' )->current();
		$prefix    = array(
			'cpt'      => array( 'wpessential/cpt/get', 'wpessential/cpt/trash', 'wpessential/cpt/delete' ),
			'taxonomy' => array( 'wpessential/taxonomy/get', 'wpessential/taxonomy/trash', 'wpessential/taxonomy/delete' ),
			'fields'   => array( 'wpessential/fields/get-group', 'wpessential/fields/lifecycle-group', 'wpessential/fields/lifecycle-group' ),
		);
		foreach ( array_reverse( $items ) as $item ) {
			$names = $prefix[ $item[0] ] ?? null;
			if ( ! $names ) {
				continue;
			}
			try {
				$fields = 'fields' === $item[0];
				$def    = $abilities->execute( $names[0], array( 'id' => $item[1] ), $context )['definition'];
				$def    = $abilities->execute(
					$names[1],
					array(
						'id'                => $item[1],
						'expected_revision' => $def['revision'],
					) + ( $fields ? array( 'action' => 'trash' ) : array() ),
					$context
				)['definition'];
				$abilities->execute(
					$names[2],
					array(
						'id'                => $item[1],
						'expected_revision' => $def['revision'],
					) + ( $fields ? array( 'action' => 'delete' ) : array() ),
					$context
				);
			} catch ( \Throwable $e ) {
				continue; // Already removed or edited by the owner: keep it.
			}
		}
	}

	/**
	 * One menu.
	 *
	 * @param int $i Index.
	 * @return null
	 */
	private function step_menus( $i ) {
		$menu  = $this->content()['menus'][ $i ];
		$items = array();
		foreach ( (array) $menu['items'] as $item ) {
			$target             = $this->menu_target( $item );
			$target['children'] = array_map( array( $this, 'menu_target' ), (array) ( $item['children'] ?? array() ) );
			$items[]            = $target;
		}

		// Block themes: a wp_navigation post (the header navigation falls back to the newest one).
		$blocks = '';
		foreach ( $items as $item ) {
			$inner = '';
			foreach ( $item['children'] as $child ) {
				$inner .= '<!-- wp:navigation-link ' . wp_json_encode( self::nav_attrs( $child ) ) . ' /-->';
			}
			$blocks .= '' === $inner
				? '<!-- wp:navigation-link ' . wp_json_encode( self::nav_attrs( $item ) ) . ' /-->'
				: '<!-- wp:navigation-submenu ' . wp_json_encode( self::nav_attrs( $item ) ) . ' -->' . $inner . '<!-- /wp:navigation-submenu -->';
		}
		$nav = wp_insert_post(
			wp_slash(
				array(
					'post_type'    => 'wp_navigation',
					'post_status'  => 'publish',
					'post_title'   => $menu['name'],
					'post_content' => $blocks,
				)
			),
			true
		);
		if ( ! is_wp_error( $nav ) ) {
			update_post_meta( $nav, self::META, $this->job['demo'] );
			$this->track( 'posts', $nav );
		}

		// Classic menu for Elementor headers and widgets.
		$menu_id = wp_create_nav_menu( $menu['name'] . ' (' . $this->job['title'] . ')' );
		if ( ! is_wp_error( $menu_id ) ) {
			$this->track( 'terms', array( $menu_id, 'nav_menu' ) );
			foreach ( $items as $item ) {
				$parent = wp_update_nav_menu_item( $menu_id, 0, self::nav_item( $item ) );
				foreach ( $item['children'] as $child ) {
					wp_update_nav_menu_item( $menu_id, 0, self::nav_item( $child ) + array( 'menu-item-parent-id' => is_wp_error( $parent ) ? 0 : (int) $parent ) );
				}
			}
			if ( ! empty( $menu['location'] ) && array_key_exists( $menu['location'], get_registered_nav_menus() ) ) {
				$this->track( 'option', 'theme_mods_' . get_option( 'stylesheet' ) );
				$locations                      = get_nav_menu_locations();
				$locations[ $menu['location'] ] = $menu_id;
				set_theme_mod( 'nav_menu_locations', $locations );
			}
		}
		/* translators: 1: menu, 2: count. */
		$this->log( 'info', sprintf( __( 'Created menu "%1$s" with %2$d links.', 'wpessential' ), $menu['name'], count( $items ) ) );
		return null;
	}

	/**
	 * Navigation block attributes for a resolved menu entry.
	 *
	 * @param array $item Resolved entry.
	 * @return array
	 */
	private static function nav_attrs( array $item ): array {
		$attrs = array(
			'label' => $item['title'],
			'url'   => $item['url'],
		);
		return $attrs + ( $item['id'] ? array(
			'type' => $item['object'],
			'id'   => $item['id'],
			'kind' => 'post-type',
		) : array( 'kind' => 'custom' ) );
	}

	/**
	 * Classic menu item fields for a resolved menu entry.
	 *
	 * @param array $item Resolved entry.
	 * @return array
	 */
	private static function nav_item( array $item ): array {
		return array(
			'menu-item-title'     => $item['title'],
			'menu-item-url'       => $item['url'],
			'menu-item-object'    => $item['id'] ? $item['object'] : 'custom',
			'menu-item-object-id' => $item['id'],
			'menu-item-type'      => $item['id'] ? 'post_type' : 'custom',
			'menu-item-status'    => 'publish',
		);
	}

	/**
	 * Resolve a menu entry.
	 *
	 * @param array $item Entry: title + page key or url.
	 * @return array
	 */
	private function menu_target( $item ) {
		$id     = 0;
		$object = 'page';
		if ( ! empty( $item['page'] ) && isset( $this->job['map']['posts'][ $item['page'] ] ) ) {
			$id     = (int) $this->job['map']['posts'][ $item['page'] ];
			$object = get_post_type( $id );
		} elseif ( ! empty( $item['woo'] ) && function_exists( 'wc_get_page_id' ) && wc_get_page_id( $item['woo'] ) > 0 ) {
			$id = wc_get_page_id( $item['woo'] );
		}
		return array(
			'title'  => $item['title'],
			'id'     => $id,
			'object' => $object,
			'url'    => $id ? get_permalink( $id ) : $this->tokens( $item['url'] ?? '{{home}}', true ),
		);
	}

	/**
	 * Front page, blog page, style variation, options.
	 *
	 * @return null
	 */
	private function step_settings() {
		self::ensure_elementor_kit();
		$settings = (array) ( $this->content()['settings'] ?? array() );
		$posts    = $this->job['map']['posts'];

		// WordPress's untouched "Hello world!" post would lead the demo blog: keep it as a draft.
		if ( ! empty( $settings['hide_sample_post'] ) ) {
			$sample = get_page_by_path( 'hello-world', OBJECT, 'post' );
			if ( $sample && 'publish' === $sample->post_status && $sample->post_modified === $sample->post_date ) {
				$this->track( 'post_status', array( $sample->ID, 'publish' ) );
				wp_update_post(
					array(
						'ID'          => $sample->ID,
						'post_status' => 'draft',
					)
				);
				$this->log( 'info', __( 'The sample "Hello world!" post was moved to drafts.', 'wpessential' ) );
			}
		}
		if ( ! empty( $settings['front_page'] ) && isset( $posts[ $settings['front_page'] ] ) ) {
			foreach ( array( 'show_on_front', 'page_on_front', 'page_for_posts' ) as $option ) {
				$this->track( 'option', $option );
			}
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $posts[ $settings['front_page'] ] );
			if ( ! empty( $settings['blog_page'] ) && isset( $posts[ $settings['blog_page'] ] ) ) {
				update_option( 'page_for_posts', $posts[ $settings['blog_page'] ] );
			}
			$this->log( 'success', __( 'Home and blog pages assigned.', 'wpessential' ) );
		}

		// A previous demo's shop title must not carry over.
		delete_option( 'wpessential_shop_title' );
		delete_option( 'wpessential_shop_lead' );
		delete_option( 'wpessential_cart_title' );
		delete_option( 'wpessential_pdp_gallery' );
		delete_option( 'wpessential_pdp_perks' );
		delete_option( 'wpessential_shop_banner' );
		foreach ( (array) ( $settings['options'] ?? array() ) as $option => $value ) {
			if ( ! in_array( $option, (array) apply_filters( 'wpessential_core_import_options', array( 'posts_per_page', 'date_format', 'blogname', 'blogdescription', 'thumbnail_size_w', 'thumbnail_size_h', 'wpessential_shop_title', 'wpessential_shop_lead', 'wpessential_cart_title', 'wpessential_pdp_gallery', 'wpessential_pdp_perks', 'wpessential_shop_banner' ) ), true ) ) {
				/* translators: %s: option. */
				$this->log( 'warning', sprintf( __( 'Setting "%s" is not on the allow-list and was ignored.', 'wpessential' ), $option ) );
				continue;
			}
			$this->track( 'option', $option );
			update_option( $option, $value );
		}

		if ( ! empty( $settings['style_data'] ) && is_array( $settings['style_data'] ) ) {
			// A generated demo (Hi WPE) carries its variation inline instead of a theme file.
			$this->style_variation( 'inline', $settings['style_data'] );
		} elseif ( ! empty( $settings['style'] ) ) {
			$this->style_variation( sanitize_key( $settings['style'] ) );
		}
		$parts = (array) ( $settings['parts'] ?? array() );
		foreach ( $parts as $area => $spec ) {
			$this->template_part( sanitize_key( $area ), $spec );
		}
		// Parts this demo does not set go back to the theme's own, so a previous demo's header
		// or footer does not stay behind.
		foreach ( array( 'header', 'header-shop', 'header-transparent', 'footer' ) as $part ) {
			if ( ! isset( $parts[ $part ] ) ) {
				$this->template_part( $part, array( 'theme' => true ) );
			}
		}

		if ( ! empty( $settings['motion'] ) ) {
			$this->track( 'option', 'wpessential_motion' );
			update_option( 'wpessential_motion', array( 'preset' => sanitize_key( $settings['motion'] ) ) );
			/* translators: %s: preset. */
			$this->log( 'info', sprintf( __( 'Animation style set to "%s".', 'wpessential' ), $settings['motion'] ) );
		}
		if ( class_exists( 'WooCommerce' ) && ! empty( $settings['woocommerce'] ) ) {
			$this->woocommerce( (array) $settings['woocommerce'] );
		}
		if ( 'elementor' === $this->job['builder'] && did_action( 'elementor/loaded' ) ) {
			foreach ( array( 'elementor_disable_color_schemes', 'elementor_disable_typography_schemes' ) as $option ) {
				$this->track( 'option', $option );
				update_option( $option, 'yes' );
			}
			if ( ! empty( $settings['elementor_kit'] ) ) {
				$this->elementor_kit( (array) $settings['elementor_kit'] );
			}
			$this->log( 'info', __( 'Elementor set to use the theme colours and fonts.', 'wpessential' ) );
		}
		return null;
	}

	/**
	 * Apply a theme style variation (styles/<name>.json) as the site's global styles,
	 * exactly like choosing it under Appearance → Editor → Styles.
	 *
	 * @param string $name Variation file name without extension.
	 * @param array  $inline Optional variation data (theme.json shape) used instead of a file.
	 */
	private function style_variation( $name, $inline = null ) {
		// "default" resets to the theme's own styles, so a previous demo's look never lingers.
		$file = get_theme_file_path( 'styles/' . $name . '.json' );
		$data = is_array( $inline ) ? $inline : ( 'default' === $name ? array( 'title' => __( 'Default', 'wpessential' ) ) : ( is_readable( $file ) ? json_decode( (string) file_get_contents( $file ), true ) : null ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
		if ( ! is_array( $data ) || ! class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			/* translators: %s: variation. */
			$this->log( 'warning', sprintf( __( 'Style "%s" was not found in the theme, so the default colours stay.', 'wpessential' ), $name ), __( 'Update the Hi WPEssential theme, then import again.', 'wpessential' ) );
			return;
		}
		$post_id = \WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
		if ( ! $post_id ) {
			return;
		}
		$this->track( 'styles', array( $post_id, get_post_field( 'post_content', $post_id ) ) );
		$styles = array(
			'version'                     => $data['version'] ?? 3,
			'isGlobalStylesUserThemeJSON' => true,
			'title'                       => $data['title'] ?? $name,
			'settings'                    => $data['settings'] ?? new \stdClass(),
			'styles'                      => $data['styles'] ?? new \stdClass(),
		);
		wp_update_post(
			wp_slash(
				array(
					'ID'           => $post_id,
					'post_content' => wp_json_encode( $styles ),
				)
			)
		);
		\WP_Theme_JSON_Resolver::clean_cached_data();
		if ( function_exists( 'wp_clean_theme_json_cache' ) ) {
			wp_clean_theme_json_cache();
		}
		/* translators: %s: variation. */
		$this->log( 'success', sprintf( __( 'Applied the %s colours and fonts.', 'wpessential' ), $styles['title'] ) );
		$this->sync_site_kit( $data );
	}

	/**
	 * Copy the variation's palette and fonts into Site Tools Kit, the single source of defaults (ADR-0239).
	 *
	 * @param array $data Variation data (theme.json shape).
	 */
	private function sync_site_kit( $data ) {
		if ( ! class_exists( \WPEssential\Modules\SiteKit\SiteKitStore::class ) ) {
			return;
		}
		$changes = array();
		foreach ( (array) ( $data['settings']['color']['palette'] ?? array() ) as $colour ) {
			if ( isset( $colour['slug'], $colour['color'] ) ) {
				$changes[ 'color_' . sanitize_key( $colour['slug'] ) ] = (string) $colour['color'];
			}
		}
		foreach ( (array) ( $data['settings']['typography']['fontFamilies'] ?? array() ) as $font ) {
			if ( isset( $font['slug'], $font['name'] ) ) {
				$changes[ 'font_' . sanitize_key( $font['slug'] ) ] = (string) $font['name'];
			}
		}
		if ( array() === $changes ) {
			return;
		}
		$this->track( 'option', \WPEssential\Modules\SiteKit\SiteKitStore::OPTION );
		( new \WPEssential\Modules\SiteKit\SiteKitStore() )->update( $changes );
	}

	/**
	 * Use one of the theme's header/footer designs site-wide, e.g. header => header-centered,
	 * or block markup from the package (a demo's own top bar above a theme header pattern).
	 * Saved as a user copy of the part, the same as editing it in the Site Editor.
	 *
	 * @param string       $part Template part slug, e.g. header or header-shop.
	 * @param string|array $spec Theme part file name (parts/<slug>.html) or array( 'content' => markup ).
	 */
	private function template_part( $part, $spec ) {
		// Any header*/footer* part the templates use, e.g. "header-shop" on WooCommerce pages.
		$area = 0 === strpos( $part, 'header' ) ? 'header' : ( 0 === strpos( $part, 'footer' ) ? 'footer' : '' );
		if ( ! $area || ! is_readable( get_theme_file_path( 'parts/' . $part . '.html' ) ) ) {
			return;
		}
		if ( is_array( $spec ) && ! empty( $spec['theme'] ) ) {
			$slug    = $part;
			$content = (string) file_get_contents( get_theme_file_path( 'parts/' . $part . '.html' ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Theme file.
		} elseif ( is_array( $spec ) && ! empty( $spec['content'] ) ) {
			$slug    = __( 'demo', 'wpessential' );
			$content = $this->tokens( (string) $spec['content'], true );
		} elseif ( is_string( $spec ) && is_readable( get_theme_file_path( 'parts/' . sanitize_key( $spec ) . '.html' ) ) ) {
			$slug    = sanitize_key( $spec );
			$content = '<!-- wp:pattern {"slug":"wpessential/' . $slug . '"} /-->';
		} else {
			return;
		}
		$existing = get_posts(
			array(
				'post_type'      => 'wp_template_part',
				'name'           => $part,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- One lookup during import.
					array(
						'taxonomy' => 'wp_theme',
						'field'    => 'name',
						'terms'    => get_stylesheet(),
					),
				),
			)
		);
		if ( ! $existing && is_array( $spec ) && ! empty( $spec['theme'] ) ) {
			return; // Already the theme's part.
		}
		if ( $existing ) {
			$this->track( 'part_' . $part, array( $existing[0]->ID, $existing[0]->post_content ) );
			wp_update_post(
				wp_slash(
					array(
						'ID'           => $existing[0]->ID,
						'post_content' => $content,
					)
				)
			);
		} else {
			$id = wp_insert_post(
				array(
					'post_type'    => 'wp_template_part',
					'post_status'  => 'publish',
					'post_name'    => $part,
					'post_title'   => ucwords( str_replace( '-', ' ', $part ) ),
					'post_content' => wp_slash( $content ),
				),
				true
			);
			if ( is_wp_error( $id ) ) {
				return;
			}
			wp_set_post_terms( $id, get_stylesheet(), 'wp_theme' );
			wp_set_post_terms( $id, $area, 'wp_template_part_area' );
			$this->track( 'posts', $id );
		}
		/* translators: 1: template part, 2: design. */
		$this->log( 'info', sprintf( __( 'Template part "%1$s" set to the "%2$s" design.', 'wpessential' ), $part, $slug ) );
	}

	/**
	 * Layout settings for the active Elementor Kit (container width, buttons, form fields).
	 * Colours and fonts are synced from the theme by wpessential_sync_elementor_kit().
	 *
	 * @param array $values Kit settings.
	 */
	private function elementor_kit( array $values ) {
		$kit_id = self::ensure_elementor_kit();
		if ( ! $kit_id ) {
			return;
		}
		$this->track( 'kit', array( $kit_id, get_post_meta( $kit_id, '_elementor_page_settings', true ) ) );
		$settings = (array) get_post_meta( $kit_id, '_elementor_page_settings', true );
		$values  += array(
			'site_name'        => get_option( 'blogname' ),
			'site_description' => get_option( 'blogdescription' ),
		);
		update_post_meta( $kit_id, '_elementor_page_settings', array_merge( $settings, $values ) );
		$this->log( 'info', __( 'Elementor Kit layout, buttons and form styles set.', 'wpessential' ) );
	}

	/**
	 * The demo's cart / checkout / account page takes the plain slug ("cart", not "cart-2") from the
	 * store page it replaces; that page is kept, renamed "<slug>-previous".
	 *
	 * @param int $old Current store page.
	 * @param int $new Demo page.
	 */
	private function take_slug( int $old, int $new ): void {
		$old_post = $old && $old !== $new ? get_post( $old ) : null;
		$new_post = get_post( $new );
		if ( ! $old_post || ! $new_post || $old_post->post_name !== preg_replace( '/-\d+$/', '', $new_post->post_name ) ) {
			return;
		}
		$slug = $old_post->post_name;
		wp_update_post(
			array(
				'ID'        => $old,
				'post_name' => $slug . '-previous',
			)
		);
		wp_update_post(
			array(
				'ID'        => $new,
				'post_name' => $slug,
			)
		);
	}

	/**
	 * Store pages and shipping.
	 *
	 * @param array $woo Woo settings.
	 */
	private function woocommerce( array $woo ) {
		// Demo pages built from kit elements become the shop's cart, checkout, my account… pages (set before create_pages so Woo skips them).
		foreach ( (array) ( $woo['pages'] ?? array() ) as $name => $key ) {
			if ( isset( $this->job['map']['posts'][ $key ] ) ) {
				$option = 'woocommerce_' . sanitize_key( $name ) . '_page_id';
				$this->track( 'option', $option );
				$this->take_slug( (int) get_option( $option ), (int) $this->job['map']['posts'][ $key ] );
				update_option( $option, (int) $this->job['map']['posts'][ $key ] );
			}
		}
		if ( class_exists( 'WC_Install' ) ) {
			\WC_Install::create_pages();
		}
		foreach ( (array) ( $woo['options'] ?? array() ) as $option => $value ) {
			if ( 0 === strpos( $option, 'woocommerce_' ) ) {
				$this->track( 'option', $option );
				update_option( $option, $value );
			}
		}
		if ( ! empty( $woo['free_shipping'] ) && class_exists( 'WC_Shipping_Zone' ) && ! \WC_Shipping_Zones::get_zones() ) {
			$zone = new \WC_Shipping_Zone();
			$zone->set_zone_name( __( 'Everywhere', 'wpessential' ) );
			$zone->save();
			$instance = $zone->add_shipping_method( 'free_shipping' );
			$method   = \WC_Shipping_Zones::get_shipping_method( $instance );
			if ( $method ) {
				update_option(
					$method->get_instance_option_key(),
					array(
						'title'      => __( 'Free shipping', 'wpessential' ),
						'requires'   => 'min_amount',
						'min_amount' => (string) $woo['free_shipping'],
					)
				);
			}
			$zone->add_shipping_method( 'flat_rate' );
			/* translators: %s: amount. */
			$this->log( 'success', sprintf( __( 'Shipping set up with free delivery over %s.', 'wpessential' ), $woo['free_shipping'] ) );
		}
	}

	/**
	 * Resolve late links, flush permalinks.
	 *
	 * @return null
	 */
	private function step_finalize() {
		$fixed = 0;
		foreach ( $this->job['map']['posts'] as $id ) {
			$post = get_post( $id );
			if ( $post && false !== strpos( $post->post_content, '{{' ) ) {
				wp_update_post(
					wp_slash(
						array(
							'ID'           => $id,
							'post_content' => $this->tokens( $post->post_content, true ),
						)
					)
				);
				++$fixed;
			}
			$elementor = (string) get_post_meta( $id, '_elementor_data', true );
			if ( false !== strpos( $elementor, '{{' ) ) {
				update_post_meta( $id, '_elementor_data', wp_slash( $this->tokens( $elementor, true ) ) );
				++$fixed;
			}
		}
		if ( did_action( 'elementor/loaded' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		if ( $fixed ) {
			/* translators: %d: pages. */
			$this->log( 'info', sprintf( _n( 'Linked %d page to its new address.', 'Linked %d pages to their new addresses.', $fixed, 'wpessential' ), $fixed ) );
		}
		flush_rewrite_rules( false );
		delete_transient( 'wc_term_counts' );
		wp_cache_flush();
		$this->log( 'info', __( 'Permalinks and caches refreshed.', 'wpessential' ) );
		return null;
	}

	/**
	 * Make sure Elementor has an active Kit. Elementor creates it on activation, but a plugin
	 * switched on another way may have none, and Elementor then throws whenever a site option
	 * (blogname, …) changes.
	 *
	 * @return int Kit ID or 0.
	 */
	public static function ensure_elementor_kit() {
		if ( ! class_exists( '\\Elementor\\Core\\Kits\\Manager' ) ) {
			return 0;
		}
		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id || ! get_post( $kit_id ) ) {
			delete_option( 'elementor_active_kit' );
			\Elementor\Core\Kits\Manager::create_default_kit();
			$kit_id = (int) get_option( 'elementor_active_kit' );
		}
		return get_post( $kit_id ) ? $kit_id : 0;
	}

	/**
	 * Remove everything the last import created and restore options.
	 *
	 * @return int Items removed.
	 */
	public static function remove() {
		self::ensure_elementor_kit();
		$record = self::imported();
		$count  = 0;
		foreach ( array_reverse( (array) ( $record['posts'] ?? array() ) ) as $id ) {
			$post = get_post( $id );
			if ( ! $post ) {
				continue;
			}
			if ( 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product( $id );
				if ( $product && $product->is_type( 'variable' ) ) {
					foreach ( $product->get_children() as $child ) {
						wp_delete_post( $child, true );
					}
				}
			}
			if ( 'attachment' === $post->post_type ) {
				wp_delete_attachment( $id, true );
			} else {
				wp_delete_post( $id, true );
			}
			++$count;
		}
		foreach ( (array) ( $record['terms'] ?? array() ) as $term ) {
			if ( 'nav_menu' === $term[1] ) {
				wp_delete_nav_menu( (int) $term[0] );
			} else {
				wp_delete_term( (int) $term[0], $term[1] );
			}
			++$count;
		}
		self::remove_definitions( (array) ( $record['definitions'] ?? array() ) );
		foreach ( (array) ( $record['post_status'] ?? array() ) as $state ) {
			if ( get_post( (int) $state[0] ) ) {
				wp_update_post(
					array(
						'ID'          => (int) $state[0],
						'post_status' => (string) $state[1],
					)
				);
			}
		}
		foreach ( (array) ( $record['options'] ?? array() ) as $option => $value ) {
			if ( null === $value ) {
				delete_option( $option );
			} else {
				update_option( $option, $value );
			}
		}
		if ( ! empty( $record['styles'][0] ) && get_post( $record['styles'][0] ) ) {
			wp_update_post(
				wp_slash(
					array(
						'ID'           => (int) $record['styles'][0],
						'post_content' => (string) $record['styles'][1],
					)
				)
			);
			if ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
				\WP_Theme_JSON_Resolver::clean_cached_data();
			}
		}
		foreach ( array_filter(
			array_keys( $record ),
			static function ( $key ) {
				return 0 === strpos( $key, 'part_' );
			}
		) as $part ) {
			if ( ! empty( $record[ $part ][0] ) && get_post( $record[ $part ][0] ) ) {
				wp_update_post(
					array(
						'ID'           => (int) $record[ $part ][0],
						'post_content' => (string) $record[ $part ][1],
					)
				);
			}
		}
		if ( ! empty( $record['kit'][0] ) && get_post( $record['kit'][0] ) ) {
			update_post_meta( (int) $record['kit'][0], '_elementor_page_settings', $record['kit'][1] ? $record['kit'][1] : array() );
		}
		delete_option( self::IMPORTED );
		$job = self::current();
		if ( ! empty( $job['id'] ) && 'running' !== $job['status'] ) {
			delete_option( self::JOB );
		}
		flush_rewrite_rules( false );
		return $count;
	}
}
