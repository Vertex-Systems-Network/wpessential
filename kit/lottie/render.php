<?php
/**
 * Lottie Animation.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_url = esc_url_raw( (string) $a['url'] );
if ( '' === $wpe_url ) {
	return;
}
$wpe_trigger = in_array( $a['trigger'], array( 'view', 'load', 'hover' ), true ) ? $a['trigger'] : 'view';
$wpe_label   = trim( (string) $a['label'] );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-lt" data-src="<?php echo esc_url( $wpe_url ); ?>" data-trigger="<?php echo esc_attr( $wpe_trigger ); ?>" data-loop="<?php echo empty( $a['loop'] ) ? '0' : '1'; ?>"
	<?php echo '' !== $wpe_label ? 'role="img" aria-label="' . esc_attr( $wpe_label ) . '"' : 'aria-hidden="true"'; ?>></div>
