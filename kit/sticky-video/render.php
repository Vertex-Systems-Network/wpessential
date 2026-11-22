<?php
/**
 * Sticky Video.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_url = esc_url_raw( (string) $a['url'] );
if ( '' === $wpe_url ) {
	return;
}
$wpe_corner = 'bottom-left' === $a['corner'] ? 'bottom-left' : 'bottom-right';
$wpe_player = preg_match( '/\.(mp4|webm|ogv)(\?|$)/i', $wpe_url )
	? '<video src="' . esc_url( $wpe_url ) . '" controls playsinline preload="metadata"></video>'
	: wp_oembed_get( $wpe_url );
if ( ! $wpe_player ) {
	return;
}
?>
<figure id="<?php echo esc_attr( $id ); ?>" class="wpe-sv is-<?php echo esc_attr( $wpe_corner ); ?>">
	<div class="wpe-sv__frame">
		<div class="wpe-sv__player"><?php echo $wpe_player; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped video tag or core oEmbed HTML. ?></div>
		<button type="button" class="wpe-sv__close" aria-label="<?php esc_attr_e( 'Close floating video', 'wpessential' ); ?>">×</button>
	</div>
	<?php if ( '' !== trim( (string) $a['caption'] ) ) : ?>
		<figcaption><?php echo esc_html( (string) $a['caption'] ); ?></figcaption>
	<?php endif; ?>
</figure>
