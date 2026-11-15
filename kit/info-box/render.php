<?php
/**
 * Info box render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_tag   = in_array( $a['tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ), true ) ? $a['tag'] : 'h3';
$wpe_link  = (string) $a['link'];
$wpe_area  = in_array( $a['linkArea'], array( 'box', 'title', 'button' ), true ) ? $a['linkArea'] : 'box';
$wpe_tab   = $a['newTab'] ? ' target="_blank" rel="noopener"' : '';
$wpe_media = '';
switch ( $a['mediaType'] ) {
	case 'icon':
		$wpe_svg   = \WPEssential\Platform\Kit\Kit::icon_html( $a['icon'], 'wpe-info-box__svg' );
		$wpe_media = $wpe_svg ? '<span class="wpe-info-box__icon" aria-hidden="true">' . $wpe_svg . '</span>' : '';
		break;
	case 'image':
		$wpe_img = is_array( $a['image'] ) ? $a['image'] : array();
		if ( ! empty( $wpe_img['id'] ) && wp_attachment_is_image( (int) $wpe_img['id'] ) ) {
			$wpe_size  = in_array( $a['imageSize'], array( 'thumbnail', 'medium', 'medium_large', 'large', 'full' ), true ) ? $a['imageSize'] : 'medium_large';
			$wpe_media = '<span class="wpe-info-box__image">' . wp_get_attachment_image( (int) $wpe_img['id'], $wpe_size, false, array( 'loading' => 'lazy' ) ) . '</span>';
		} elseif ( ! empty( $wpe_img['url'] ) ) {
			$wpe_media = '<span class="wpe-info-box__image"><img src="' . esc_url( $wpe_img['url'] ) . '" alt="" loading="lazy"></span>';
		}
		break;
	case 'number':
		$wpe_media = '' !== trim( (string) $a['number'] ) ? '<span class="wpe-info-box__number" aria-hidden="true">' . esc_html( $a['number'] ) . '</span>' : '';
		break;
}
$wpe_title = trim( (string) $a['title'] );
if ( '' !== $wpe_title && $wpe_link && 'title' === $wpe_area ) {
	$wpe_title = '<a href="' . esc_url( $wpe_link ) . '"' . $wpe_tab . '>' . wp_kses_post( $wpe_title ) . '</a>';
} else {
	$wpe_title = wp_kses_post( $wpe_title );
}
$wpe_button = '';
if ( '' !== trim( (string) $a['buttonText'] ) ) {
	$wpe_btn_inner = '<span>' . esc_html( $a['buttonText'] ) . '</span>' . \WPEssential\Platform\Kit\Kit::icon_html( $a['buttonIcon'], 'wpe-info-box__button-icon' );
	$wpe_button    = $wpe_link && 'box' !== $wpe_area ? '<a class="wpe-info-box__button" href="' . esc_url( $wpe_link ) . '"' . $wpe_tab . '>' . $wpe_btn_inner . '</a>' : '<span class="wpe-info-box__button">' . $wpe_btn_inner . '</span>';
}
$wpe_classes = array(
	'wpe-info-box',
	'is-view-' . sanitize_key( $a['iconView'] ),
	'is-shape-' . sanitize_key( $a['iconShape'] ),
	'is-hover-' . sanitize_key( $a['hover'] ),
	'is-media-hover-' . sanitize_key( $a['mediaHover'] ),
	'has-' . sanitize_key( $a['mediaType'] ),
);
if ( $a['equalHeight'] ) {
	$wpe_classes[] = 'is-equal';
}
$wpe_box_tag = $wpe_link && 'box' === $wpe_area ? 'a' : 'div';
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-info-box-wrap<?php echo $a['equalHeight'] ? ' is-equal' : ''; ?>">
	<<?php echo esc_html( $wpe_box_tag ); ?> class="<?php echo esc_attr( implode( ' ', $wpe_classes ) ); ?>"<?php echo 'a' === $wpe_box_tag ? ' href="' . esc_url( $wpe_link ) . '"' . $wpe_tab : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped. ?>>
		<?php if ( '' !== trim( (string) $a['badge'] ) ) : ?>
			<span class="wpe-info-box__badge"><?php echo esc_html( $a['badge'] ); ?></span>
		<?php endif; ?>
		<?php echo $wpe_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>
		<div class="wpe-info-box__content">
			<?php if ( '' !== $wpe_title ) : ?>
				<<?php echo esc_html( $wpe_tag ); ?> class="wpe-info-box__title"><?php echo $wpe_title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post above. ?></<?php echo esc_html( $wpe_tag ); ?>>
			<?php endif; ?>
			<?php if ( '' !== trim( wp_strip_all_tags( (string) $a['description'] ) ) ) : ?>
				<div class="wpe-info-box__desc"><?php echo wp_kses_post( wpautop( (string) $a['description'] ) ); ?></div>
			<?php endif; ?>
			<?php echo $wpe_button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>
		</div>
	</<?php echo esc_html( $wpe_box_tag ); ?>>
</div>
