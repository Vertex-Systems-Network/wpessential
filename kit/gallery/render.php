<?php
/**
 * Gallery render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_items = array_values( array_filter( (array) $a['items'], 'is_array' ) );
$wpe_valid = array();
$wpe_size  = in_array( $a['imageSize'], array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ? $a['imageSize'] : 'medium_large';

foreach ( $wpe_items as $wpe_item ) {
	$wpe_image      = isset( $wpe_item['image'] ) && is_array( $wpe_item['image'] ) ? $wpe_item['image'] : array();
	$wpe_attachment = ! empty( $wpe_image['id'] ) && wp_attachment_is_image( (int) $wpe_image['id'] ) ? (int) $wpe_image['id'] : 0;
	$wpe_url        = $wpe_attachment ? (string) wp_get_attachment_image_url( $wpe_attachment, 'full' ) : esc_url( (string) ( $wpe_image['url'] ?? '' ) );
	if ( '' === $wpe_url ) {
		continue;
	}
	$wpe_alt = trim( (string) ( $wpe_item['alt'] ?? '' ) );
	if ( '' === $wpe_alt && $wpe_attachment ) {
		$wpe_alt = (string) get_post_meta( $wpe_attachment, '_wp_attachment_image_alt', true );
	}
	$wpe_caption = trim( (string) ( $wpe_item['caption'] ?? '' ) );
	$wpe_img     = $wpe_attachment ? wp_get_attachment_image(
		$wpe_attachment,
		$wpe_size,
		false,
		array(
			'class'   => 'wpe-gallery__img',
			'alt'     => $wpe_alt,
			'loading' => 'lazy',
		)
	) : '<img class="wpe-gallery__img" src="' . esc_url( $wpe_url ) . '" alt="' . esc_attr( $wpe_alt ) . '" loading="lazy" decoding="async">';
	if ( ! $wpe_img ) {
		continue;
	}
	$wpe_link    = esc_url( trim( (string) ( $wpe_item['link'] ?? '' ) ) );
	$wpe_valid[] = array(
		'img'     => $wpe_img,
		'url'     => $wpe_url,
		'alt'     => $wpe_alt,
		'caption' => $wpe_caption,
		'link'    => $wpe_link,
	);
}
if ( ! $wpe_valid ) {
	// The editor gets a hint. On the front end there is no empty gallery wrapper.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-gallery__empty">' . esc_html( (string) $a['emptyText'] ) . '</p>';
	}
	return;
}

$wpe_class        = array(
	'wpe-gallery',
	'is-' . ( 'masonry' === $a['layout'] ? 'masonry' : 'grid' ),
	'is-caption-' . ( 'overlay' === $a['captionPosition'] ? 'overlay' : 'below' ),
	'is-hover-' . sanitize_key( $a['hover'] ),
);
$wpe_has_lightbox = ! empty( $a['lightbox'] );
$wpe_has_opener   = $wpe_has_lightbox && (bool) array_filter(
	$wpe_valid,
	static function ( $wpe_item ) {
		return '' === $wpe_item['link'];
	}
);
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>" data-wpe-gallery="1">
	<div class="wpe-gallery__items">
		<?php foreach ( $wpe_valid as $wpe_i => $wpe_item ) : ?>
			<figure class="wpe-gallery__item">
				<?php
				$wpe_open = $wpe_has_lightbox && '' === $wpe_item['link'];
				$wpe_href = '' !== $wpe_item['link'] ? $wpe_item['link'] : ( $wpe_open ? $wpe_item['url'] : '' );
				?>
				<?php if ( $wpe_href ) : ?>
					<a class="wpe-gallery__media" href="<?php echo esc_url( $wpe_href ); ?>"<?php echo $wpe_open ? ' data-wpe-gallery-open="1" data-full="' . esc_url( $wpe_item['url'] ) . '" data-alt="' . esc_attr( $wpe_item['alt'] ) . '" data-caption="' . esc_attr( $wpe_item['caption'] ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Every attribute value escaped above. ?> aria-label="<?php echo esc_attr( $wpe_open ? sprintf( /* translators: %d: Image position. */ __( 'Open gallery image %d', 'wpessential' ), $wpe_i + 1 ) : sprintf( /* translators: %d: Image position. */ __( 'View image link %d', 'wpessential' ), $wpe_i + 1 ) ); ?>">
						<?php echo $wpe_item['img']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generated image or escaped img attributes. ?>
					</a>
				<?php else : ?>
					<div class="wpe-gallery__media"><?php echo $wpe_item['img']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress image or escaped img attributes. ?></div>
				<?php endif; ?>
				<?php if ( $a['showCaptions'] && '' !== $wpe_item['caption'] ) : ?>
					<figcaption class="wpe-gallery__caption"><?php echo esc_html( $wpe_item['caption'] ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
	<?php if ( $wpe_has_opener ) : ?>
		<dialog class="wpe-gallery__dialog" aria-label="<?php esc_attr_e( 'Gallery image viewer', 'wpessential' ); ?>" data-show-captions="<?php echo $a['lightboxCaptions'] ? '1' : '0'; ?>">
			<div class="wpe-gallery__viewer">
				<button class="wpe-gallery__close" type="button" data-action="close" aria-label="<?php esc_attr_e( 'Close gallery', 'wpessential' ); ?>">&times;</button>
				<button class="wpe-gallery__prev" type="button" data-action="prev" aria-label="<?php esc_attr_e( 'Previous image', 'wpessential' ); ?>">&#8249;</button>
				<img class="wpe-gallery__full" alt="">
				<button class="wpe-gallery__next" type="button" data-action="next" aria-label="<?php esc_attr_e( 'Next image', 'wpessential' ); ?>">&#8250;</button>
				<p class="wpe-gallery__viewer-caption"></p>
			</div>
		</dialog>
	<?php endif; ?>
</div>
