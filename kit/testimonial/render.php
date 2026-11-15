<?php
/**
 * Testimonial render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_quote   = trim( (string) $a['quote'] );
$wpe_name    = trim( (string) $a['name'] );
$wpe_role    = trim( (string) $a['role'] );
$wpe_company = trim( (string) $a['company'] );
$wpe_url     = trim( (string) $a['profileUrl'] );
$wpe_rating  = min( 5, max( 1, (int) $a['rating'] ) );
$wpe_image   = is_array( $a['avatar'] ) ? $a['avatar'] : array();
$wpe_photo   = '';

if ( ! empty( $wpe_image['id'] ) && wp_attachment_is_image( (int) $wpe_image['id'] ) ) {
	$wpe_photo = (string) wp_get_attachment_image(
		(int) $wpe_image['id'],
		'thumbnail',
		false,
		array(
			'class'   => 'wpe-testimonial__photo',
			'alt'     => '',
			'loading' => 'lazy',
		)
	);
} elseif ( ! empty( $wpe_image['url'] ) && esc_url( $wpe_image['url'] ) ) {
	$wpe_photo = '<img class="wpe-testimonial__photo" src="' . esc_url( $wpe_image['url'] ) . '" alt="" loading="lazy">';
}
$wpe_class      = array(
	'wpe-testimonial',
	'is-' . sanitize_key( $a['appearance'] ),
	'is-avatar-' . sanitize_key( $a['avatarShape'] ),
	'is-author-' . sanitize_key( $a['authorPosition'] ),
	'is-avatar-side-' . sanitize_key( $a['avatarSide'] ),
	'is-hover-' . sanitize_key( $a['hover'] ),
);
$wpe_has_author = '' !== $wpe_name || '' !== $wpe_role || '' !== $wpe_company || '' !== $wpe_photo;
?>
<figure id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>">
	<?php if ( $a['showRating'] ) : ?>
		<div class="wpe-testimonial__rating">
			<span class="wpe-testimonial__stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', $wpe_rating ) . str_repeat( '☆', 5 - $wpe_rating ) ); ?></span>
			<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: 1: Rating score, 2: Maximum rating. */ __( 'Rating: %1$d out of %2$d', 'wpessential' ), $wpe_rating, 5 ) ); ?></span>
		</div>
	<?php endif; ?>
	<?php if ( '' !== $wpe_quote ) : ?>
		<blockquote class="wpe-testimonial__quote">
			<?php if ( $a['quoteMark'] ) : ?>
				<span class="wpe-testimonial__mark" aria-hidden="true">“</span>
			<?php endif; ?>
			<div class="wpe-testimonial__text"><?php echo wp_kses_post( wpautop( $wpe_quote ) ); ?></div>
		</blockquote>
	<?php endif; ?>
	<?php if ( $wpe_has_author ) : ?>
		<figcaption class="wpe-testimonial__author">
			<?php if ( '' !== $wpe_photo ) : ?>
				<span class="wpe-testimonial__avatar"><?php echo $wpe_photo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped URL or core-generated attachment image. ?></span>
			<?php endif; ?>
			<?php if ( '' !== $wpe_name || '' !== $wpe_role || '' !== $wpe_company ) : ?>
				<span class="wpe-testimonial__identity">
					<?php if ( '' !== $wpe_name ) : ?>
						<?php if ( '' !== $wpe_url ) : ?>
							<a class="wpe-testimonial__name" href="<?php echo esc_url( $wpe_url ); ?>"<?php echo $a['newTab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?php echo esc_html( $wpe_name ); ?></a>
						<?php else : ?>
							<span class="wpe-testimonial__name"><?php echo esc_html( $wpe_name ); ?></span>
						<?php endif; ?>
					<?php endif; ?>
					<?php if ( '' !== $wpe_role || '' !== $wpe_company ) : ?>
						<span class="wpe-testimonial__role"><?php echo esc_html( implode( ', ', array_filter( array( $wpe_role, $wpe_company ), static fn( $value ) => '' !== $value ) ) ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</figcaption>
	<?php endif; ?>
</figure>
