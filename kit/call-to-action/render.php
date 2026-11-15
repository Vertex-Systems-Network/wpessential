<?php
/**
 * CTA banner renderer.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit::template().

$wpe_tag   = in_array( $a['titleTag'], array( 'h2', 'h3', 'h4' ), true ) ? $a['titleTag'] : 'h2';
$wpe_style = in_array( $a['style'], array( 'boxed', 'bordered', 'minimal' ), true ) ? $a['style'] : 'boxed';
$wpe_pos   = in_array( $a['imagePosition'], array( 'left', 'right', 'top' ), true ) ? $a['imagePosition'] : 'right';
$wpe_image = is_array( $a['image'] ) ? $a['image'] : array();
$wpe_alt   = trim( (string) $a['imageAlt'] );
$wpe_img   = '';
if ( ! empty( $wpe_image['id'] ) && wp_attachment_is_image( (int) $wpe_image['id'] ) ) {
	$wpe_img = wp_get_attachment_image(
		(int) $wpe_image['id'],
		'large',
		false,
		array(
			'class'   => 'wpe-call-to-action__image',
			'alt'     => $wpe_alt,
			'loading' => 'lazy',
		)
	);
} elseif ( ! empty( $wpe_image['url'] ) ) {
	$wpe_url = esc_url( (string) $wpe_image['url'], array( 'http', 'https' ) );
	if ( $wpe_url ) {
		$wpe_img = '<img class="wpe-call-to-action__image" src="' . esc_url( $wpe_url ) . '" alt="' . esc_attr( $wpe_alt ) . '" loading="lazy" decoding="async">';
	}
}
$wpe_primary   = esc_url( trim( (string) $a['primaryUrl'] ), array( 'http', 'https' ) );
$wpe_secondary = esc_url( trim( (string) $a['secondaryUrl'] ), array( 'http', 'https' ) );
?>
<section id="<?php echo esc_attr( $id ); ?>" class="wpe-call-to-action is-<?php echo esc_attr( $wpe_style ); ?> is-image-<?php echo esc_attr( $wpe_pos ); ?>">
	<div class="wpe-call-to-action__content">
		<?php if ( '' !== trim( (string) $a['eyebrow'] ) ) : ?>
			<p class="wpe-call-to-action__eyebrow"><?php echo esc_html( (string) $a['eyebrow'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== trim( (string) $a['title'] ) ) : ?>
			<<?php echo esc_html( $wpe_tag ); ?> class="wpe-call-to-action__title"><?php echo esc_html( (string) $a['title'] ); ?></<?php echo esc_html( $wpe_tag ); ?>>
		<?php endif; ?>
		<?php if ( '' !== trim( (string) $a['description'] ) ) : ?>
			<div class="wpe-call-to-action__description"><?php echo wp_kses_post( (string) $a['description'] ); ?></div>
		<?php endif; ?>
		<?php if ( ( $wpe_primary && trim( (string) $a['primaryText'] ) ) || ( $wpe_secondary && trim( (string) $a['secondaryText'] ) ) ) : ?>
			<div class="wpe-call-to-action__actions">
				<?php if ( $wpe_primary && trim( (string) $a['primaryText'] ) ) : ?>
					<a class="wpe-call-to-action__button is-primary" href="<?php echo esc_url( $wpe_primary ); ?>"<?php echo $a['primaryNewTab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( (string) $a['primaryText'] ); ?></a>
				<?php endif; ?>
				<?php if ( $wpe_secondary && trim( (string) $a['secondaryText'] ) ) : ?>
					<a class="wpe-call-to-action__button is-secondary" href="<?php echo esc_url( $wpe_secondary ); ?>"<?php echo $a['secondaryNewTab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( (string) $a['secondaryText'] ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php if ( $wpe_img ) : ?>
		<div class="wpe-call-to-action__media"><?php echo $wpe_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL or trusted WP attachment markup. ?></div>
	<?php endif; ?>
</section>
