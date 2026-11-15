<?php
/**
 * Button render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_icon  = \WPEssential\Platform\Kit\Kit::icon_html( $a['icon'], 'wpe-button__icon' );
$wpe_rel   = array_filter( array( $a['newTab'] ? 'noopener' : '', $a['nofollow'] ? 'nofollow' : '' ) );
$wpe_label = '<span class="wpe-button__text">' . esc_html( $a['text'] ) . '</span>';
$wpe_inner = $a['iconFirst'] ? $wpe_icon . $wpe_label : $wpe_label . $wpe_icon;
$wpe_class = 'wpe-button__link is-' . sanitize_key( $a['variant'] ) . ' is-' . sanitize_key( $a['size'] ) . ' wpe-hover-' . sanitize_key( $a['hover'] );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-button">
	<a class="<?php echo esc_attr( $wpe_class ); ?>" href="<?php echo esc_url( $a['url'] ? $a['url'] : '#' ); ?>"<?php echo $a['newTab'] ? ' target="_blank"' : ''; ?><?php echo $wpe_rel ? ' rel="' . esc_attr( implode( ' ', $wpe_rel ) ) . '"' : ''; ?><?php echo $a['ariaLabel'] ? ' aria-label="' . esc_attr( $a['ariaLabel'] ) . '"' : ''; ?>><?php echo $wpe_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></a>
</div>
