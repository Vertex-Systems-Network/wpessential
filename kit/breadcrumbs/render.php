<?php
/**
 * Breadcrumbs.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_trail = array( array( (string) $a['homeLabel'], home_url( '/' ) ) );
if ( is_singular() ) {
	$wpe_post = get_queried_object();
	if ( $wpe_post instanceof WP_Post ) {
		$wpe_type = get_post_type_object( $wpe_post->post_type );
		if ( 'page' !== $wpe_post->post_type && $wpe_type && $wpe_type->has_archive ) {
			$wpe_trail[] = array( $wpe_type->labels->name, (string) get_post_type_archive_link( $wpe_post->post_type ) );
		} elseif ( 'post' === $wpe_post->post_type && get_option( 'page_for_posts' ) ) {
			$wpe_trail[] = array( get_the_title( (int) get_option( 'page_for_posts' ) ), (string) get_permalink( (int) get_option( 'page_for_posts' ) ) );
		}
		foreach ( array_reverse( get_post_ancestors( $wpe_post ) ) as $wpe_parent ) {
			$wpe_trail[] = array( get_the_title( $wpe_parent ), (string) get_permalink( $wpe_parent ) );
		}
		$wpe_trail[] = array( get_the_title( $wpe_post ), '' );
	}
} elseif ( is_search() ) {
	/* translators: %s: search terms. */
	$wpe_trail[] = array( sprintf( __( 'Search: %s', 'wpessential' ), get_search_query( false ) ), '' );
} elseif ( is_404() ) {
	$wpe_trail[] = array( __( 'Page not found', 'wpessential' ), '' );
} elseif ( is_archive() ) {
	$wpe_trail[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
} elseif ( is_home() && ! is_front_page() ) {
	$wpe_trail[] = array( get_option( 'page_for_posts' ) ? get_the_title( (int) get_option( 'page_for_posts' ) ) : __( 'Blog', 'wpessential' ), '' );
} elseif ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
	$wpe_trail[] = array( __( 'Current page', 'wpessential' ), '' );
}
if ( empty( $a['showCurrent'] ) && count( $wpe_trail ) > 1 ) {
	array_pop( $wpe_trail );
}
?>
<nav id="<?php echo esc_attr( $id ); ?>" class="wpe-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'wpessential' ); ?>">
	<ol itemscope itemtype="https://schema.org/BreadcrumbList">
		<?php foreach ( $wpe_trail as $wpe_i => $wpe_crumb ) : ?>
			<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<?php if ( $wpe_i ) : ?><span class="wpe-breadcrumbs__sep" aria-hidden="true"><?php echo esc_html( (string) $a['separator'] ); ?></span><?php endif; ?>
				<?php if ( '' !== $wpe_crumb[1] ) : ?>
					<a itemprop="item" href="<?php echo esc_url( $wpe_crumb[1] ); ?>"><span itemprop="name"><?php echo esc_html( $wpe_crumb[0] ); ?></span></a>
				<?php else : ?>
					<span itemprop="name" aria-current="page"><?php echo esc_html( $wpe_crumb[0] ); ?></span>
				<?php endif; ?>
				<meta itemprop="position" content="<?php echo esc_attr( (string) ( $wpe_i + 1 ) ); ?>">
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
