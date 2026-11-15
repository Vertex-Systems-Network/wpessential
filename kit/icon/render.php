<?php
/**
 * Single theme or Elementor icon, optionally a labeled link.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped render template.
$wpe_glyph = \WPEssential\Platform\Kit\Kit::icon_html( $a['icon'], 'wpe-icon-single__svg' );
$wpe_url   = esc_url( trim( (string) $a['url'] ), array( 'http', 'https', 'mailto', 'tel' ) );
$wpe_label = trim( (string) $a['label'] );
$wpe_view  = in_array( $a['view'], array( 'default', 'stacked', 'framed' ), true ) ? $a['view'] : 'default';
$wpe_shape = in_array( $a['shape'], array( 'circle', 'rounded', 'square' ), true ) ? $a['shape'] : 'circle';
if ( '' === $wpe_glyph ) {
	return;
}
$wpe_link = $wpe_url && empty( $a['decorative'] );
$wpe_tag  = $wpe_link ? 'a' : 'span';
if ( $wpe_link && ! $wpe_label ) {
	$wpe_label = __( 'Open link', 'wpessential' );
}
$wpe_aria = ! empty( $a['decorative'] ) ? ' aria-hidden="true"' : ( $wpe_label ? ' aria-label="' . esc_attr( $wpe_label ) . '"' : '' );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-icon-single is-<?php echo esc_attr( $wpe_view ); ?> is-shape-<?php echo esc_attr( $wpe_shape ); ?>">
	<<?php echo esc_html( $wpe_tag ); ?> class="wpe-icon-single__face"<?php echo $wpe_link ? ' href="' . esc_url( $wpe_url ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL. ?><?php echo $wpe_link && ! empty( $a['newTab'] ) ? ' target="_blank" rel="noopener noreferrer"' : ''; ?><?php echo $wpe_aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed aria-hidden or escaped aria-label. ?><?php echo ! $wpe_link && empty( $a['decorative'] ) && $wpe_label ? ' role="img"' : ''; ?>>
		<?php echo $wpe_glyph; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped shared Kit icon renderer. ?>
	</<?php echo esc_html( $wpe_tag ); ?>>
</div>
