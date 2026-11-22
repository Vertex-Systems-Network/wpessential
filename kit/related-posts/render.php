<?php
/**
 * Related Posts.
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
$wpe_cats = wp_get_post_categories( $wpe_post->ID );
if ( ! $wpe_cats ) {
	return;
}
$wpe_q = new WP_Query(
	array(
		'post_type'           => $wpe_post->post_type,
		'posts_per_page'      => max( 1, min( 12, (int) $a['count'] ) ),
		'post__not_in'        => array( $wpe_post->ID ),
		'category__in'        => $wpe_cats,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);
if ( ! $wpe_q->have_posts() ) {
	return;
}
?>
<section id="<?php echo esc_attr( $id ); ?>" class="wpe-related">
	<?php if ( '' !== trim( (string) $a['heading'] ) ) : ?><h2 class="wpe-related__title"><?php echo esc_html( (string) $a['heading'] ); ?></h2><?php endif; ?>
	<ul class="wpe-related__list">
		<?php foreach ( $wpe_q->posts as $wpe_rel ) : ?>
			<li><a href="<?php echo esc_url( (string) get_permalink( $wpe_rel ) ); ?>"><?php echo get_the_post_thumbnail( $wpe_rel, 'medium_large', array( 'loading' => 'lazy' ) ); ?><span><?php echo esc_html( get_the_title( $wpe_rel ) ); ?></span></a></li>
		<?php endforeach; ?>
	</ul>
</section>
