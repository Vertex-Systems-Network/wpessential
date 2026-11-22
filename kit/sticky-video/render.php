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
$wpe_path   = (string) wp_parse_url( $wpe_url, PHP_URL_PATH );
if ( preg_match( '/\.(mp4|webm|ogv)$/i', $wpe_path ) ) {
	$wpe_player = '<video src="' . esc_url( $wpe_url ) . '" controls playsinline preload="metadata"></video>';
} else {
	// Core's trusted provider list only (no discovery): no requests to arbitrary hosts, no untrusted markup.
	$wpe_key    = 'wpe_sv_' . md5( $wpe_url );
	$wpe_player = get_transient( $wpe_key );
	if ( false === $wpe_player ) {
		$wpe_player = (string) wp_oembed_get( $wpe_url, array( 'discover' => false ) );
		set_transient( $wpe_key, $wpe_player, '' === $wpe_player ? HOUR_IN_SECONDS : DAY_IN_SECONDS );
	}
	if ( '' === $wpe_player ) {
		echo '<p class="wpe-sv__fallback"><a href="' . esc_url( $wpe_url ) . '" rel="noopener">' . esc_html__( 'Watch the video', 'wpessential' ) . '</a></p>';
		return;
	}
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
