<?php
/**
 * Accessible team member card.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_image = is_array( $a['photo'] ) ? $a['photo'] : array();
$wpe_alt   = trim( (string) $a['name'] );
$wpe_size  = in_array( $a['imageSize'], array( 'medium', 'medium_large', 'large', 'full' ), true ) ? $a['imageSize'] : 'medium_large';
$wpe_photo = '';
if ( ! empty( $wpe_image['id'] ) && wp_attachment_is_image( (int) $wpe_image['id'] ) ) {
	$wpe_photo = wp_get_attachment_image(
		(int) $wpe_image['id'],
		$wpe_size,
		false,
		array(
			'class'   => 'wpe-team-member__photo',
			'alt'     => $wpe_alt,
			'loading' => 'lazy',
		)
	);
} elseif ( ! empty( $wpe_image['url'] ) ) {
	$wpe_url = esc_url( (string) $wpe_image['url'], array( 'http', 'https' ) );
	if ( '' !== $wpe_url ) {
		$wpe_photo = '<img class="wpe-team-member__photo" src="' . esc_url( $wpe_url ) . '" alt="' . esc_attr( $wpe_alt ) . '" loading="lazy" decoding="async">';
	}
}
$wpe_profile = esc_url( trim( (string) $a['profileUrl'] ), array( 'https', 'http' ) );
$wpe_email   = sanitize_email( (string) $a['email'] );
$wpe_class   = array(
	'wpe-team-member',
	'is-' . ( 'side' === $a['layout'] ? 'side' : 'stack' ),
	'is-photo-' . ( 'circle' === $a['shape'] ? 'circle' : 'square' ),
	'is-' . ( in_array( $a['appearance'], array( 'minimal', 'boxed', 'accent' ), true ) ? $a['appearance'] : 'minimal' ),
);
$wpe_socials = is_array( $a['socials'] ) ? array_slice( $a['socials'], 0, 12 ) : array();
?>
<article id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>">
	<?php if ( '' !== $wpe_photo ) : ?>
		<div class="wpe-team-member__media"><?php echo $wpe_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped or WordPress attachment image. ?></div>
	<?php endif; ?>
	<div class="wpe-team-member__content">
		<?php if ( '' !== trim( (string) $a['name'] ) ) : ?>
			<h3 class="wpe-team-member__name"><?php echo esc_html( (string) $a['name'] ); ?></h3>
		<?php endif; ?>
		<?php if ( '' !== trim( (string) $a['role'] ) ) : ?>
			<p class="wpe-team-member__role"><?php echo esc_html( (string) $a['role'] ); ?></p>
		<?php endif; ?>
		<?php if ( $a['showBio'] && '' !== trim( (string) $a['bio'] ) ) : ?>
			<p class="wpe-team-member__bio"><?php echo esc_html( (string) $a['bio'] ); ?></p>
		<?php endif; ?>
		<?php if ( $wpe_profile ) : ?>
			<a class="wpe-team-member__profile" href="<?php echo esc_url( $wpe_profile ); ?>"<?php echo $a['newTab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( (string) $a['profileLabel'] ); ?></a>
		<?php endif; ?>
		<?php if ( $wpe_email ) : ?>
			<a class="wpe-team-member__email" href="mailto:<?php echo esc_attr( $wpe_email ); ?>"><?php echo esc_html( $wpe_email ); ?></a>
		<?php endif; ?>
		<?php if ( $wpe_socials ) : ?>
			<ul class="wpe-team-member__socials">
				<?php foreach ( $wpe_socials as $wpe_social ) : ?>
					<?php
					$wpe_href  = is_array( $wpe_social ) ? esc_url( trim( (string) ( $wpe_social['url'] ?? '' ) ), array( 'https', 'http' ) ) : '';
					$wpe_label = is_array( $wpe_social ) ? trim( (string) ( $wpe_social['label'] ?? '' ) ) : '';
					if ( '' === $wpe_href || '' === $wpe_label ) {
						continue;
					}
					?>
					<li><a href="<?php echo esc_url( $wpe_href ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $wpe_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</article>
