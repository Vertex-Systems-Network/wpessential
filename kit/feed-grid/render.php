<?php
/**
 * Feed Grid.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_url = esc_url_raw( (string) $a['url'] );
if ( '' === $wpe_url ) {
	return;
}
include_once ABSPATH . WPINC . '/feed.php';
$wpe_feed = fetch_feed( $wpe_url ); // Core caches feeds for 12 hours.
if ( is_wp_error( $wpe_feed ) ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="wpe-fg__error">' . esc_html( $wpe_feed->get_error_message() ) . '</p>';
	}
	return;
}
$wpe_items = $wpe_feed->get_items( 0, max( 1, min( 24, (int) $a['count'] ) ) );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-fg">
	<?php
	foreach ( $wpe_items as $wpe_item ) :
		$wpe_enc = $wpe_item->get_enclosure();
		$wpe_img = $wpe_enc ? ( $wpe_enc->get_thumbnail() ?: ( str_starts_with( (string) $wpe_enc->get_type(), 'image/' ) ? $wpe_enc->get_link() : '' ) ) : '';
		if ( ! $wpe_img && preg_match( '/<img[^>]+src=["\']([^"\']+)/i', (string) $wpe_item->get_content(), $wpe_m ) ) {
			$wpe_img = $wpe_m[1]; // First image in the item body.
		}
		?>
		<article class="wpe-fg__item">
			<?php if ( $wpe_img ) : ?>
				<img class="wpe-fg__img" src="<?php echo esc_url( $wpe_img ); ?>" alt="" loading="lazy">
			<?php endif; ?>
			<div class="wpe-fg__body">
				<time class="wpe-fg__date" datetime="<?php echo esc_attr( (string) $wpe_item->get_date( 'c' ) ); ?>"><?php echo esc_html( (string) $wpe_item->get_date( get_option( 'date_format' ) ) ); ?></time>
				<h3 class="wpe-fg__title"><a href="<?php echo esc_url( (string) $wpe_item->get_permalink() ); ?>" rel="noopener"><?php echo esc_html( wp_strip_all_tags( (string) $wpe_item->get_title() ) ); ?></a></h3>
				<?php if ( ! empty( $a['excerpt'] ) ) : ?>
					<p class="wpe-fg__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( (string) $wpe_item->get_description() ), 24 ) ); ?></p>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</div>
