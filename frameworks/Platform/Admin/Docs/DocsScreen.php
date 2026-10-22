<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Docs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPEssential\Platform\Admin\Core\AdminScreen;
use WPEssential\Platform\Admin\Ui\Button;
use WPEssential\Platform\Admin\Ui\Icon;
use WPEssential\Platform\Admin\Ui\PageHeader;
use WPEssential\Platform\Admin\Ui\Screen;
use WPEssential\Platform\Admin\Ui\Shell;

/**
 * Docs screen: topic cards that link into the documentation on wpessential.org.
 * Nothing is bundled or fetched; every link opens the live docs in a new tab.
 */
final class DocsScreen implements AdminScreen {

	public const BASE_URL    = 'https://wpessential.org/docs/';
	public const SUPPORT_URL = 'https://wpessential.org/support/';

	public function slug(): string {
		return Shell::DOCS_SLUG;
	}

	public function title(): string {
		return __( 'Docs', 'wpessential' );
	}

	public function register(): void {}

	/** @return list<array{0:string,1:string,2:string,3:array<string,string>}> */
	private function topics(): array {
		return [
			[
				'rocket',
				__( 'Getting started', 'wpessential' ),
				__( 'Install, activate and tour the dashboard.', 'wpessential' ),
				[
					'getting-started/installation'    => __( 'Installation', 'wpessential' ),
					'getting-started/first-steps'     => __( 'First steps', 'wpessential' ),
					'getting-started/free-vs-premium' => __( 'Free vs Premium', 'wpessential' ),
				],
			],
			[
				'layers',
				__( 'Modules', 'wpessential' ),
				__( 'Turn modules on or off and configure each one.', 'wpessential' ),
				[
					'modules/manager'      => __( 'Module manager', 'wpessential' ),
					'modules/dependencies' => __( 'Dependencies', 'wpessential' ),
					'modules/option-tiers' => __( 'Option tiers', 'wpessential' ),
				],
			],
			[
				'grid',
				__( 'Demo Library', 'wpessential' ),
				__( 'Import complete websites and roll them back.', 'wpessential' ),
				[
					'demos/import'  => __( 'Importing a demo', 'wpessential' ),
					'demos/plugins' => __( 'Required plugins', 'wpessential' ),
					'demos/remove'  => __( 'Removing a demo', 'wpessential' ),
				],
			],
			[
				'brush',
				__( 'Elements', 'wpessential' ),
				__( 'Gutenberg blocks and Elementor widgets.', 'wpessential' ),
				[
					'elements/enable'   => __( 'Enable elements', 'wpessential' ),
					'elements/builders' => __( 'Builders', 'wpessential' ),
				],
			],
			[
				'spark',
				__( 'AI Connect', 'wpessential' ),
				__( 'Connect Claude, Cursor or VS Code through MCP.', 'wpessential' ),
				[
					'ai-connect/connect'   => __( 'Connect a client', 'wpessential' ),
					'ai-connect/abilities' => __( 'Approved abilities', 'wpessential' ),
					'ai-connect/revoke'    => __( 'Revoking access', 'wpessential' ),
				],
			],
			[
				'key',
				__( 'Licence & account', 'wpessential' ),
				__( 'Activate Premium and manage your plan.', 'wpessential' ),
				[
					'license/activate' => __( 'Activate a key', 'wpessential' ),
					'license/account'  => __( 'Account & billing', 'wpessential' ),
				],
			],
			[
				'code',
				__( 'Developers', 'wpessential' ),
				__( 'Hooks, filters, abilities and REST.', 'wpessential' ),
				[
					'developers/abilities' => __( 'Abilities API', 'wpessential' ),
					'developers/hooks'     => __( 'Hooks reference', 'wpessential' ),
					'developers/rest'      => __( 'REST routes', 'wpessential' ),
				],
			],
			[
				'help',
				__( 'Troubleshooting', 'wpessential' ),
				__( 'System status and common fixes.', 'wpessential' ),
				[
					'troubleshooting/system-status' => __( 'System status', 'wpessential' ),
					'troubleshooting/imports'       => __( 'Import problems', 'wpessential' ),
				],
			],
		];
	}

	public function render(): void {
		$base = (string) ( function_exists( 'apply_filters' ) ? apply_filters( 'wpessential_docs_url', self::BASE_URL ) : self::BASE_URL );

		$html  = PageHeader::render( __( 'Docs', 'wpessential' ), __( 'Learn WPEssential', 'wpessential' ), __( 'Guides, references and how-tos from wpessential.org. Links open in a new tab.', 'wpessential' ), Button::link( __( 'Open full documentation', 'wpessential' ), $base, 'ghost', false, true ) );
		$html .= '<form class="wpe-ui-toolbar wpe-ui-docs-search" role="search" method="get" action="' . esc_url( $base ) . '" target="_blank">';
		$html .= Icon::render( 'search', 20 ) . '<label class="screen-reader-text" for="wpe-docs-search">' . esc_html__( 'Search the documentation', 'wpessential' ) . '</label>';
		$html .= '<input type="search" id="wpe-docs-search" name="s" class="wpe-ui-input" placeholder="' . esc_attr__( 'Search the documentation…', 'wpessential' ) . '">';
		$html .= '<button type="submit" class="' . esc_attr( Button::classes( 'secondary' ) ) . '">' . esc_html__( 'Search', 'wpessential' ) . '</button></form>';

		$html .= '<div class="wpe-ui-card-grid">';
		foreach ( $this->topics() as [ $icon, $title, $text, $links ] ) {
			$html .= '<section class="wpe-ui-panel wpe-ui-doc"><span class="wpe-ui-icon">' . Icon::render( $icon, 20 ) . '</span><h2>' . esc_html( $title ) . '</h2><p class="wpe-ui-muted">' . esc_html( $text ) . '</p><ul class="wpe-ui-doc__links">';
			foreach ( $links as $path => $label ) {
				$html .= '<li><a href="' . esc_url( $base . $path . '/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $label ) . Icon::render( 'arrow', 14 ) . '</a></li>';
			}
			$html .= '</ul></section>';
		}
		$html .= '</div>';

		$html .= '<section class="wpe-ui-upsell wpe-ui-upsell--row"><div><h2>' . esc_html__( 'Still stuck?', 'wpessential' ) . '</h2><p>' . esc_html__( 'Our support team answers Premium tickets within one business day.', 'wpessential' ) . '</p></div>'
			. Button::link( __( 'Contact support', 'wpessential' ), self::SUPPORT_URL, 'primary', false, true ) . '</section>';

		Screen::render( Shell::DOCS_SLUG, $html );
	}
}
