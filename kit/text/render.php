<?php
/**
 * Text render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.
$wpe_tag   = in_array( $a['tag'], array( 'div', 'section', 'article', 'aside', 'blockquote' ), true ) ? $a['tag'] : 'div';
$wpe_body  = wpautop( wp_kses_post( (string) $a['content'] ) );
$wpe_clamp = array_filter( (array) $a['clamp'] );
$wpe_class = array( 'wpe-text', 'is-wrap-' . sanitize_key( $a['wrap'] ) );
if ( $a['dropCap'] ) {
	$wpe_class[] = 'has-drop-cap';
	$wpe_class[] = 'is-drop-cap-' . sanitize_key( $a['dropCapView'] );
}
if ( $a['gradientText'] ) {
	$wpe_class[] = 'is-gradient';
}
if ( $wpe_clamp ) {
	$wpe_class[] = 'is-clamped';
}
?>
<<?php echo esc_html( $wpe_tag ); ?> id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>">
	<div class="wpe-text__body"<?php echo $wpe_clamp ? ' id="' . esc_attr( $id ) . '-body"' : ''; ?>><?php echo $wpe_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post above. ?></div>
	<?php if ( $wpe_clamp && '' !== trim( (string) $a['moreText'] ) ) : ?>
		<button type="button" class="wpe-text__more" aria-expanded="false" aria-controls="<?php echo esc_attr( $id ); ?>-body" onclick="var t=this.closest('.wpe-text');var o=t.classList.toggle('is-open');this.setAttribute('aria-expanded',o)"><?php echo esc_html( $a['moreText'] ); ?></button>
	<?php endif; ?>
</<?php echo esc_html( $wpe_tag ); ?>>
