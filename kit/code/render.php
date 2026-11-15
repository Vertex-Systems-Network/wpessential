<?php
/**
 * Safe HTML, literal source snippet or WordPress-supported oEmbed.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Local template scope.

$wpe_mode     = in_array( $a['mode'], array( 'html', 'code', 'embed' ), true ) ? $a['mode'] : 'html';
$wpe_language = in_array( $a['language'], array( 'text', 'html', 'css', 'javascript', 'php', 'json', 'bash', 'python' ), true ) ? $a['language'] : 'text';
$wpe_markup   = '';
$wpe_url      = '';

if ( 'html' === $wpe_mode ) {
	$wpe_markup = wp_kses_post( (string) $a['html'] );
} elseif ( 'embed' === $wpe_mode ) {
	$wpe_url     = esc_url( trim( (string) $a['embedUrl'] ), array( 'https', 'http' ) );
	$wpe_host    = '' !== $wpe_url ? strtolower( (string) wp_parse_url( $wpe_url, PHP_URL_HOST ) ) : '';
	$wpe_host    = preg_replace( '/^www\./', '', $wpe_host );
	$wpe_allowed = array( 'youtube.com', 'm.youtube.com', 'youtu.be', 'vimeo.com', 'player.vimeo.com', 'soundcloud.com', 'spotify.com', 'open.spotify.com', 'mixcloud.com', 'www.mixcloud.com' );
	if ( '' !== $wpe_url && in_array( $wpe_host, $wpe_allowed, true ) ) {
		$wpe_raw = wp_oembed_get( $wpe_url, array( 'discover' => false ) );
		if ( is_string( $wpe_raw ) ) {
			$wpe_markup = wp_kses(
				$wpe_raw,
				array(
					'iframe'     => array(
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'title'           => true,
						'frameborder'     => true,
						'allow'           => true,
						'allowfullscreen' => true,
						'loading'         => true,
						'referrerpolicy'  => true,
					),
					'div'        => array(
						'class' => true,
						'style' => true,
					),
					'p'          => array(),
					'a'          => array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
					),
					'blockquote' => array(
						'class' => true,
						'cite'  => true,
					),
					'br'         => array(),
				)
			);
		}
	}
}
if ( 'code' === $wpe_mode ) {
	$wpe_code = (string) $a['code'];
	if ( '' !== $wpe_code ) {
		$wpe_markup = '<pre class="wpe-code__pre"><code class="wpe-code__source language-' . esc_attr( $wpe_language ) . '">' . esc_html( $wpe_code ) . '</code></pre>';
	}
}

if ( '' === trim( $wpe_markup ) && 'embed' !== $wpe_mode ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-code__empty">' . esc_html__( 'Add content to this element.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_class = array(
	'wpe-code',
	'is-' . $wpe_mode,
	'a-wrap-' . ( $a['wrapCode'] ? 'yes' : 'no' ),
);
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>">
	<?php if ( 'code' === $wpe_mode ) : ?>
		<div class="wpe-code__code-panel">
			<?php if ( $a['showCopy'] || $a['showLanguage'] ) : ?>
				<div class="wpe-code__toolbar">
					<?php if ( $a['showLanguage'] ) : ?>
						<span class="wpe-code__language"><?php echo esc_html( strtoupper( $wpe_language ) ); ?></span>
					<?php endif; ?>
					<?php if ( $a['showCopy'] ) : ?>
						<button class="wpe-code__copy" type="button" aria-label="<?php esc_attr_e( 'Copy code', 'wpessential' ); ?>"><?php echo esc_html( '' !== trim( (string) $a['copyLabel'] ) ? $a['copyLabel'] : __( 'Copy', 'wpessential' ) ); ?></button>
						<span class="wpe-code__copy-status screen-reader-text" role="status" aria-live="polite"></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php echo $wpe_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped code and restricted language constructed above. ?>
		</div>
	<?php elseif ( 'embed' === $wpe_mode ) : ?>
		<div class="wpe-code__embed">
			<?php echo $wpe_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress oEmbed HTML sanitized through explicit KSES allowlist. ?>
			<?php if ( $wpe_url ) : ?>
				<p class="wpe-code__fallback"><a href="<?php echo esc_url( $wpe_url ); ?>" rel="noopener noreferrer" target="_blank"><?php echo esc_html( '' !== trim( (string) $a['embedTitle'] ) ? $a['embedTitle'] : __( 'Open embedded content', 'wpessential' ) ); ?></a></p>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="wpe-code__html"><?php echo $wpe_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by wp_kses_post. ?></div>
	<?php endif; ?>
</div>
