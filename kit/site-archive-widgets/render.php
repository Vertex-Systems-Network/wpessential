<?php
/**
 * Context-aware WordPress site/archive output.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped render template.

$wpe_mode        = in_array( $a['mode'], array( 'title', 'description', 'breadcrumbs', 'meta', 'author', 'pagination' ), true ) ? $a['mode'] : 'title';
$wpe_obj         = get_queried_object();
$wpe_post        = $wpe_obj instanceof \WP_Post && 'publish' === $wpe_obj->post_status ? $wpe_obj : null;
$wpe_tag         = in_array( $a['tag'], array( 'h1', 'h2', 'h3', 'p', 'div' ), true ) ? $a['tag'] : 'h1';
$wpe_title       = '';
$wpe_description = '';
if ( is_search() ) {
	$wpe_title = sprintf( /* translators: %s: site search query. */ __( 'Search results for: %s', 'wpessential' ), get_search_query( false ) );
} elseif ( is_home() ) {
	$wpe_title = get_the_title( (int) get_option( 'page_for_posts' ) );
} elseif ( is_archive() ) {
	$wpe_title       = get_the_archive_title();
	$wpe_description = get_the_archive_description();
} elseif ( $wpe_post ) {
	$wpe_title = get_the_title( $wpe_post->ID );
}
if ( '' === trim( (string) $wpe_title ) ) {
	$wpe_title = (string) $a['fallback'];
}
$wpe_title  = (string) $a['prefix'] . $wpe_title;
$wpe_output = '';
if ( 'title' === $wpe_mode && '' !== trim( $wpe_title ) ) {
	$wpe_output = '<' . $wpe_tag . ' class="wpe-site-archive-widgets__title">' . esc_html( wp_strip_all_tags( $wpe_title, true ) ) . '</' . $wpe_tag . '>';
} elseif ( 'description' === $wpe_mode && $wpe_description ) {
	$wpe_output = '<div class="wpe-site-archive-widgets__body">' . wp_kses_post( $wpe_description ) . '</div>';
} elseif ( 'meta' === $wpe_mode && $wpe_post ) {
	$wpe_fragments = array();
	if ( ! empty( $a['showDate'] ) ) {
		$wpe_fragments[] = esc_html( get_the_date( '', $wpe_post->ID ) );
	}
	if ( ! empty( $a['showAuthor'] ) ) {
		$wpe_fragments[] = esc_html( get_the_author_meta( 'display_name', (int) $wpe_post->post_author ) );
	}
	if ( $wpe_fragments ) {
		$wpe_output = '<div class="wpe-site-archive-widgets__meta">' . implode( '<span aria-hidden="true">·</span>', $wpe_fragments ) . '</div>';
	}
} elseif ( 'author' === $wpe_mode && $wpe_post ) {
	$wpe_author = get_userdata( (int) $wpe_post->post_author );
	if ( $wpe_author ) {
		$wpe_output = '<div class="wpe-site-archive-widgets__author">' . get_avatar( $wpe_author->ID, 64, '', $wpe_author->display_name ) . '<div><strong>' . esc_html( $wpe_author->display_name ) . '</strong><div class="wpe-site-archive-widgets__body">' . wp_kses_post( get_the_author_meta( 'description', $wpe_author->ID ) ) . '</div></div></div>';
	}
} elseif ( 'pagination' === $wpe_mode && ! is_singular() ) {
	global $wp_query;
	$wpe_pages = isset( $wp_query->max_num_pages ) ? absint( $wp_query->max_num_pages ) : 0;
	if ( $wpe_pages > 1 ) {
		$wpe_links  = paginate_links(
			array(
				'total'   => $wpe_pages,
				'current' => max( 1, absint( get_query_var( 'paged' ) ) ),
				'type'    => 'list',
			)
		);
		$wpe_output = '<nav aria-label="' . esc_attr__( 'Archive pages', 'wpessential' ) . '" class="wpe-site-archive-widgets__pagination">' . wp_kses_post( $wpe_links ) . '</nav>';
	}
} elseif ( 'breadcrumbs' === $wpe_mode ) {
	$wpe_crumbs = array();
	if ( ! empty( $a['showHome'] ) ) {
		$wpe_crumbs[] = array(
			'title' => __( 'Home', 'wpessential' ),
			'url'   => home_url( '/' ),
		);
	}
	if ( $wpe_post ) {
		foreach ( array_slice( array_reverse( get_post_ancestors( $wpe_post->ID ) ), 0, 10 ) as $wpe_parent ) {
			if ( 'publish' === get_post_status( $wpe_parent ) ) {
				$wpe_crumbs[] = array(
					'title' => get_the_title( $wpe_parent ),
					'url'   => get_permalink( $wpe_parent ),
				);
			}
		}
		$wpe_crumbs[] = array(
			'title' => get_the_title( $wpe_post->ID ),
			'url'   => '',
		);
	} elseif ( '' !== trim( $wpe_title ) ) {
		$wpe_crumbs[] = array(
			'title' => $wpe_title,
			'url'   => '',
		);
	}
	if ( $wpe_crumbs ) {
		$wpe_output = '<nav class="wpe-site-archive-widgets__breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'wpessential' ) . '"><ol>';
		foreach ( $wpe_crumbs as $wpe_index => $wpe_crumb ) {
			$wpe_end     = count( $wpe_crumbs ) - 1 === $wpe_index;
			$wpe_label   = esc_html( wp_strip_all_tags( (string) $wpe_crumb['title'], true ) );
			$wpe_output .= '<li>';
			$wpe_output .= ! $wpe_end && $wpe_crumb['url'] ? '<a href="' . esc_url( $wpe_crumb['url'] ) . '">' . $wpe_label . '</a>' : '<span aria-current="page">' . $wpe_label . '</span>';
			$wpe_output .= '</li>';
		}
		$wpe_output .= '</ol></nav>';
	}
}
if ( ! $wpe_output ) {
	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-site-archive-widgets is-<?php echo esc_attr( $wpe_mode ); ?>">
	<?php echo $wpe_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fragments escaped; WP avatar/pagination and rich archive description sanitized. ?>
</div>
