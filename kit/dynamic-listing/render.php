<?php
/**
 * Dynamic Listing.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_type = sanitize_key( (string) $a['post_type'] );
$wpe_pto  = get_post_type_object( $wpe_type );
if ( ! $wpe_pto || ! $wpe_pto->public ) {
	return; // Only public post types may be listed.
}
$wpe_args = array(
	'post_type'           => $wpe_type,
	'post_status'         => 'publish',
	'posts_per_page'      => max( 1, min( 24, (int) $a['count'] ) ),
	'orderby'             => in_array( $a['orderby'], array( 'date', 'title', 'menu_order', 'rand' ), true ) ? $a['orderby'] : 'date',
	'order'               => 'title' === $a['orderby'] || 'menu_order' === $a['orderby'] ? 'ASC' : 'DESC',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);
$wpe_tax  = sanitize_key( (string) $a['taxonomy'] );
$wpe_term = sanitize_title( (string) $a['term'] );
if ( $wpe_tax && $wpe_term && is_object_in_taxonomy( $wpe_type, $wpe_tax ) ) {
	$wpe_args['tax_query'] = array( array( 'taxonomy' => $wpe_tax, 'field' => 'slug', 'terms' => $wpe_term ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Optional single-term filter.
}
$wpe_query = new WP_Query( $wpe_args );
if ( ! $wpe_query->have_posts() ) {
	return;
}
// Protected (underscore) keys are never shown.
$wpe_keys = array_filter( array_map( 'trim', explode( ',', (string) $a['fields'] ) ), static fn( $k ) => '' !== $k && ! is_protected_meta( $k, 'post' ) );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-dl">
	<?php
	while ( $wpe_query->have_posts() ) :
		$wpe_query->the_post();
		?>
		<article class="wpe-dl__item">
			<?php if ( has_post_thumbnail() ) : ?>
				<a class="wpe-dl__img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?></a>
			<?php endif; ?>
			<div class="wpe-dl__body">
				<h3 class="wpe-dl__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<?php
				$wpe_rows = array();
				foreach ( $wpe_keys as $wpe_key ) {
					$wpe_val = get_post_meta( get_the_ID(), $wpe_key, true );
					if ( is_scalar( $wpe_val ) && '' !== (string) $wpe_val ) {
						$wpe_rows[ $wpe_key ] = (string) $wpe_val;
					}
				}
				if ( $wpe_rows ) :
					?>
					<dl class="wpe-dl__fields">
						<?php foreach ( $wpe_rows as $wpe_key => $wpe_val ) : ?>
							<div><dt><?php echo esc_html( ucwords( str_replace( array( '_', '-' ), ' ', $wpe_key ) ) ); ?></dt><dd><?php echo esc_html( $wpe_val ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
</div>
<?php
wp_reset_postdata();
