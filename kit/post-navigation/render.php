<?php
/**
 * Post Navigation.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_post = get_post();
if ( ! $wpe_post instanceof WP_Post ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p id="' . esc_attr( $id ) . '" class="wpe-post-empty">' . esc_html__( 'Shows data of the current post on the site.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_prev = get_previous_post( ! empty( $a['sameTerm'] ) );
$wpe_next = get_next_post( ! empty( $a['sameTerm'] ) );
if ( ! $wpe_prev && ! $wpe_next ) {
	return;
}
?>
<nav id="<?php echo esc_attr( $id ); ?>" class="wpe-post-nav" aria-label="<?php esc_attr_e( 'Posts', 'wpessential' ); ?>">
	<?php if ( $wpe_prev instanceof WP_Post ) : ?><a class="is-prev" href="<?php echo esc_url( (string) get_permalink( $wpe_prev ) ); ?>" rel="prev"><small><?php esc_html_e( 'Previous', 'wpessential' ); ?></small><span><?php echo esc_html( get_the_title( $wpe_prev ) ); ?></span></a><?php endif; ?>
	<?php if ( $wpe_next instanceof WP_Post ) : ?><a class="is-next" href="<?php echo esc_url( (string) get_permalink( $wpe_next ) ); ?>" rel="next"><small><?php esc_html_e( 'Next', 'wpessential' ); ?></small><span><?php echo esc_html( get_the_title( $wpe_next ) ); ?></span></a><?php endif; ?>
</nav>
