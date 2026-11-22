<?php
/**
 * Off-Canvas.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_body  = '' !== trim( (string) $content ) ? $content : wp_kses_post( (string) $a['content'] );
$wpe_side  = 'left' === $a['side'] ? 'left' : 'right';
$wpe_title = '' !== trim( (string) $a['title'] ) ? (string) $a['title'] : (string) $a['label'];
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-oc is-<?php echo esc_attr( $wpe_side ); ?>">
	<button type="button" class="wpe-oc__open" aria-haspopup="dialog" aria-controls="<?php echo esc_attr( $id ); ?>-panel"><?php echo esc_html( (string) $a['label'] ); ?></button>
	<dialog class="wpe-oc__panel" id="<?php echo esc_attr( $id ); ?>-panel" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
		<div class="wpe-oc__head">
			<p class="wpe-oc__title" id="<?php echo esc_attr( $id ); ?>-title"><?php echo esc_html( $wpe_title ); ?></p>
			<button type="button" class="wpe-oc__close" aria-label="<?php esc_attr_e( 'Close', 'wpessential' ); ?>">×</button>
		</div>
		<div class="wpe-oc__body"><?php echo $wpe_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Rendered blocks or kses-filtered HTML. ?></div>
	</dialog>
</div>
