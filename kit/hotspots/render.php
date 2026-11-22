<?php
/**
 * Hotspots.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_img = is_array( $a['image'] ) ? $a['image'] : array();
$wpe_src = ! empty( $wpe_img['id'] ) ? (string) wp_get_attachment_image_url( (int) $wpe_img['id'], 'large' ) : (string) ( $wpe_img['url'] ?? '' );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-hotspots">
	<?php if ( '' !== $wpe_src ) : ?><img src="<?php echo esc_url( $wpe_src ); ?>" alt="<?php echo esc_attr( (string) $a['alt'] ); ?>" loading="lazy"><?php endif; ?>
	<?php foreach ( (array) $a['spots'] as $wpe_n => $wpe_s ) : ?>
		<details class="wpe-hotspots__spot" style="left:<?php echo esc_attr( (string) min( 100, max( 0, (int) ( $wpe_s['x'] ?? 50 ) ) ) ); ?>%;top:<?php echo esc_attr( (string) min( 100, max( 0, (int) ( $wpe_s['y'] ?? 50 ) ) ) ); ?>%">
			<summary aria-label="<?php echo esc_attr( (string) ( $wpe_s['title'] ?? '' ) ); ?>"><?php echo esc_html( (string) ( $wpe_n + 1 ) ); ?></summary>
			<div class="wpe-hotspots__note"><strong><?php echo esc_html( (string) ( $wpe_s['title'] ?? '' ) ); ?></strong><?php if ( ! empty( $wpe_s['text'] ) ) : ?><p><?php echo esc_html( (string) $wpe_s['text'] ); ?></p><?php endif; ?></div>
		</details>
	<?php endforeach; ?>
</div>
