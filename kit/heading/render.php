<?php
/**
 * Heading render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span' );
$wpe_tag  = in_array( $a['tag'], $wpe_tags, true ) ? $a['tag'] : 'h2';
$wpe_text = wp_kses_post( $a['text'] );
if ( '' !== trim( (string) $a['accent'] ) ) {
	$wpe_text .= ( $a['accentNewLine'] ? '<br>' : ' ' ) . '<span class="wpe-heading__accent is-' . esc_attr( sanitize_key( $a['accentStyle'] ) ) . '">' . wp_kses_post( $a['accent'] ) . '</span>';
}
if ( $a['link'] ) {
	$wpe_text = '<a href="' . esc_url( $a['link'] ) . '">' . $wpe_text . '</a>';
}
$wpe_classes = array( 'wpe-heading', 'wpe-hover-' . sanitize_key( $a['hover'] ) );
if ( 'none' !== $a['motion'] ) {
	$wpe_classes[] = 'wpe-motion-' . sanitize_key( $a['motion'] );
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_classes ) ); ?>">
	<?php if ( '' !== trim( (string) $a['eyebrow'] ) ) : ?>
		<p class="wpe-heading__eyebrow"><?php echo esc_html( $a['eyebrow'] ); ?></p>
	<?php endif; ?>
	<<?php echo esc_html( $wpe_tag ); ?> class="wpe-heading__title"><?php echo $wpe_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_kses_post / esc_url above. ?></<?php echo esc_html( $wpe_tag ); ?>>
</div>
