<?php
/**
 * Post Meta.
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
$wpe_items = array();
if ( ! empty( $a['author'] ) ) {
	$wpe_items[] = '<a href="' . esc_url( get_author_posts_url( (int) $wpe_post->post_author ) ) . '">' . esc_html( get_the_author_meta( 'display_name', (int) $wpe_post->post_author ) ) . '</a>';
}
if ( ! empty( $a['date'] ) ) {
	$wpe_items[] = '<time datetime="' . esc_attr( get_the_date( 'c', $wpe_post ) ) . '">' . esc_html( get_the_date( '', $wpe_post ) ) . '</time>';
}
if ( ! empty( $a['reading'] ) ) {
	$wpe_min     = max( 1, (int) ceil( str_word_count( wp_strip_all_tags( (string) $wpe_post->post_content ) ) / 200 ) );
	/* translators: %d: minutes. */
	$wpe_items[] = esc_html( sprintf( _n( '%d min read', '%d min read', $wpe_min, 'wpessential' ), $wpe_min ) );
}
if ( ! empty( $a['terms'] ) ) {
	$wpe_terms = get_the_category_list( ', ', '', $wpe_post->ID );
	if ( $wpe_terms ) {
		$wpe_items[] = wp_kses_post( $wpe_terms );
	}
}
if ( ! empty( $a['comments'] ) ) {
	/* translators: %d: comments. */
	$wpe_items[] = esc_html( sprintf( _n( '%d comment', '%d comments', (int) get_comments_number( $wpe_post ), 'wpessential' ), (int) get_comments_number( $wpe_post ) ) );
}
?>
<ul id="<?php echo esc_attr( $id ); ?>" class="wpe-meta-list">
	<?php foreach ( $wpe_items as $wpe_item ) : ?>
		<li><?php echo $wpe_item; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></li>
	<?php endforeach; ?>
</ul>
