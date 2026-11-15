<?php
/**
 * WordPress navigation with optional published Gutenberg / Elementor mega content.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped Kit render template.

$wpe_name = trim( (string) $a['menu'] );
$wpe_menu = '' !== $wpe_name ? wp_get_nav_menu_object( $wpe_name ) : false;
if ( ! $wpe_menu && '' === $wpe_name ) {
	$wpe_locations = get_nav_menu_locations();
	$wpe_menu      = ! empty( $wpe_locations['primary'] ) ? wp_get_nav_menu_object( (int) $wpe_locations['primary'] ) : false;
}
if ( ! $wpe_menu ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p id="' . esc_attr( $id ) . '" class="wpe-menu__empty">' . esc_html__( 'Select a WordPress menu or assign the primary menu.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_label = trim( (string) $a['label'] );
$wpe_label = '' !== $wpe_label ? $wpe_label : __( 'Main navigation', 'wpessential' );
$wpe_depth = min( 3, max( 1, absint( $a['depth'] ) ) );
$wpe_break = in_array( (string) $a['breakpoint'], array( '1024', '768', '560' ), true ) ? (string) $a['breakpoint'] : '768';

// Never allow a menu assignment to render arbitrary posts or unpublished templates.
// Published synced patterns and Elementor library entries are the only allowed types.
$wpe_allowed_items = array();
foreach ( (array) wp_get_nav_menu_items( (int) $wpe_menu->term_id ) as $wpe_item ) {
	$wpe_allowed_items[ (int) $wpe_item->ID ] = true;
}
$wpe_templates = array();
foreach ( array_slice( (array) $a['templateItems'], 0, 30 ) as $wpe_mapping ) {
	if ( ! is_array( $wpe_mapping ) ) {
		continue;
	}
	$wpe_menu_item = trim( (string) ( $wpe_mapping['menuItemId'] ?? '' ) );
	$wpe_template  = trim( (string) ( $wpe_mapping['templateId'] ?? '' ) );
	$wpe_source    = (string) ( $wpe_mapping['source'] ?? 'wp_block' );
	if ( ! ctype_digit( $wpe_menu_item ) || ! ctype_digit( $wpe_template ) ) {
		continue;
	}
	$wpe_menu_item = absint( $wpe_menu_item );
	$wpe_template  = absint( $wpe_template );
	if ( ! $wpe_template || ! isset( $wpe_allowed_items[ $wpe_menu_item ] ) ) {
		continue;
	}
	if ( ! in_array( $wpe_source, array( 'wp_block', 'elementor_library' ), true ) ) {
		continue;
	}
	$wpe_post = get_post( $wpe_template );
	if ( ! $wpe_post || 'publish' !== $wpe_post->post_status || $wpe_source !== $wpe_post->post_type ) {
		continue;
	}
	$wpe_templates[ $wpe_menu_item ] = array(
		'post'   => $wpe_post,
		'source' => $wpe_source,
	);
}

// Filters are scoped to these two wp_nav_menu() calls and always removed.
$wpe_css_filter  = static function ( $classes, $item, $args, $depth ) use ( $wpe_templates ) {
	if ( 0 === $depth && isset( $wpe_templates[ (int) $item->ID ] ) ) {
		$classes[] = 'wpe-menu__has-template';
	}
	return $classes;
};
$wpe_item_filter = static function ( $output, $item, $depth ) use ( $wpe_templates ) {
	$wpe_item_id = (int) $item->ID;
	if ( 0 !== $depth || ! isset( $wpe_templates[ $wpe_item_id ] ) ) {
		return $output;
	}
	// Skip recursive attempts if a saved pattern embeds this same Kit menu.
	if ( ! empty( $GLOBALS['wpessential_kit_megamenu_rendering'] ) ) {
		return $output;
	}
	$wpe_data = $wpe_templates[ $wpe_item_id ];
	$wpe_post = $wpe_data['post'];
	$wpe_html = '';

	$GLOBALS['wpessential_kit_megamenu_rendering'] = true;
	try {
		if ( 'wp_block' === $wpe_data['source'] ) {
			$wpe_html = do_blocks( $wpe_post->post_content );
		} elseif ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->frontend ) ) {
			$wpe_html = (string) \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $wpe_post->ID, true );
		}
	} finally {
		unset( $GLOBALS['wpessential_kit_megamenu_rendering'] );
	}
	if ( '' === trim( (string) $wpe_html ) ) {
		return $output;
	}
	$wpe_title = trim( wp_strip_all_tags( (string) $item->title, true ) );
	$wpe_title = $wpe_title ? $wpe_title : __( 'More options', 'wpessential' );
	return $output . '<div class="wpe-menu__mega-content" role="group" aria-label="' . esc_attr( $wpe_title ) . '">' . $wpe_html . '</div>';
};

$wpe_args = array(
	'menu'        => (int) $wpe_menu->term_id,
	'echo'        => false,
	'fallback_cb' => false,
	'container'   => false,
	'depth'       => $wpe_depth,
);
if ( $wpe_templates ) {
	add_filter( 'nav_menu_css_class', $wpe_css_filter, 10, 4 );
	add_filter( 'walker_nav_menu_start_el', $wpe_item_filter, 10, 3 );
}
try {
	$wpe_desktop = wp_nav_menu( $wpe_args + array( 'menu_class' => 'wpe-menu__items' ) );
	$wpe_mobile  = wp_nav_menu( $wpe_args + array( 'menu_class' => 'wpe-menu__mobile-items' ) );
} finally {
	if ( $wpe_templates ) {
		remove_filter( 'nav_menu_css_class', $wpe_css_filter, 10 );
		remove_filter( 'walker_nav_menu_start_el', $wpe_item_filter, 10 );
	}
}
if ( ! $wpe_desktop ) {
	return;
}
$wpe_trigger = 'click' === (string) $a['desktopTrigger'] ? 'click' : 'hover';
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-menu is-break-<?php echo esc_attr( $wpe_break ); ?><?php echo ! empty( $a['mega'] ) ? ' is-mega' : ''; ?><?php echo empty( $a['showIndicator'] ) ? ' is-no-indicator' : ''; ?> is-trigger-<?php echo esc_attr( $wpe_trigger ); ?>">
	<nav class="wpe-menu__desktop" aria-label="<?php echo esc_attr( $wpe_label ); ?>">
		<?php echo $wpe_desktop; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native menu and published builder templates rendered by WordPress/Elementor. ?>
	</nav>
	<details class="wpe-menu__mobile">
		<summary class="wpe-menu__toggle"><?php echo esc_html( '' !== trim( (string) $a['mobileLabel'] ) ? $a['mobileLabel'] : __( 'Menu', 'wpessential' ) ); ?><span aria-hidden="true">☰</span></summary>
		<nav aria-label="<?php echo esc_attr( $wpe_label ); ?>">
			<?php echo $wpe_mobile; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Native menu and published builder templates. ?>
		</nav>
	</details>
</div>
