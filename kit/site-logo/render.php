<?php
/**
 * Dynamic WordPress site identity.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_name    = '' !== trim( (string) $a['siteTitle'] ) ? trim( (string) $a['siteTitle'] ) : get_bloginfo( 'name' );
$wpe_tagline = '' !== trim( (string) $a['tagline'] ) ? trim( (string) $a['tagline'] ) : get_bloginfo( 'description' );
$wpe_display = in_array( $a['display'], array( 'logo-title', 'logo', 'title' ), true ) ? $a['display'] : 'logo-title';
$wpe_image   = is_array( $a['override'] ) ? $a['override'] : array();
$wpe_attach  = ! empty( $wpe_image['id'] ) && wp_attachment_is_image( (int) $wpe_image['id'] ) ? (int) $wpe_image['id'] : ( ! empty( $wpe_image['url'] ) ? 0 : (int) get_theme_mod( 'custom_logo' ) );
$wpe_photo   = '';
if ( wp_attachment_is_image( $wpe_attach ) ) {
	$wpe_photo = wp_get_attachment_image(
		$wpe_attach,
		'full',
		false,
		array(
			'class'   => 'wpe-site-logo__image',
			'alt'     => $wpe_name,
			'loading' => 'lazy' === $a['loading'] ? 'lazy' : 'eager',
		)
	);
} elseif ( ! empty( $wpe_image['url'] ) ) {
	$wpe_url = esc_url( (string) $wpe_image['url'], array( 'http', 'https' ) );
	if ( $wpe_url ) {
		$wpe_photo = '<img class="wpe-site-logo__image" src="' . esc_url( $wpe_url ) . '" alt="' . esc_attr( $wpe_name ) . '" loading="' . ( 'lazy' === $a['loading'] ? 'lazy' : 'eager' ) . '">';
	}
}
$wpe_show_logo  = 'title' !== $wpe_display && '' !== $wpe_photo;
$wpe_show_title = 'logo-title' === $wpe_display || 'title' === $wpe_display || ! $wpe_show_logo;
$wpe_href       = esc_url( trim( (string) $a['url'] ), array( 'http', 'https' ) );
if ( '' === $wpe_href ) {
	$wpe_href = home_url( '/' );
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-site-logo is-<?php echo esc_attr( 'stack' === $a['layout'] ? 'stack' : 'inline' ); ?>">
	<a class="wpe-site-logo__link" href="<?php echo esc_url( $wpe_href ); ?>"<?php echo $a['newTab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
		<?php if ( $wpe_show_logo ) : ?>
			<span class="wpe-site-logo__mark"><?php echo $wpe_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL or WordPress image tag. ?></span>
		<?php endif; ?>
		<?php if ( $wpe_show_title ) : ?>
			<span class="wpe-site-logo__title"><?php echo esc_html( $wpe_name ); ?></span>
		<?php endif; ?>
	</a>
	<?php if ( $a['showTagline'] && '' !== trim( $wpe_tagline ) ) : ?>
		<p class="wpe-site-logo__tagline"><?php echo esc_html( $wpe_tagline ); ?></p>
	<?php endif; ?>
</div>
