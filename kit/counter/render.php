<?php
/**
 * Counter render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_start = is_numeric( $a['start'] ) ? (float) $a['start'] : 0.0;
$wpe_end   = is_numeric( $a['end'] ) ? (float) $a['end'] : 0.0;
// Clamp user input to finite, predictable values for PHP formatting and animation.
$wpe_start     = is_finite( $wpe_start ) ? min( 1000000000, max( -1000000000, $wpe_start ) ) : 0.0;
$wpe_end       = is_finite( $wpe_end ) ? min( 1000000000, max( -1000000000, $wpe_end ) ) : 0.0;
$wpe_digits    = min( 4, max( 0, (int) $a['decimals'] ) );
$wpe_thousands = array(
	'comma' => ',',
	'dot'   => '.',
	'space' => ' ',
	'none'  => '',
);
$wpe_sep       = $wpe_thousands[ $a['thousands'] ] ?? ',';
$wpe_point     = 'comma' === $a['decimalMark'] ? ',' : '.';
// The decimal and thousands separators must never be the same.
if ( $wpe_sep === $wpe_point ) {
	$wpe_sep = '';
}
$wpe_final   = number_format( $wpe_end, $wpe_digits, $wpe_point, $wpe_sep );
$wpe_prefix  = (string) $a['prefix'];
$wpe_suffix  = (string) $a['suffix'];
$wpe_title   = trim( (string) $a['label'] );
$wpe_desc    = trim( (string) $a['description'] );
$wpe_tag     = in_array( $a['titleTag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div' ), true ) ? $a['titleTag'] : 'h3';
$wpe_speed   = min( 10000, max( 200, (int) $a['duration'] ) );
$wpe_easing  = in_array( $a['easing'], array( 'linear', 'ease-out', 'ease-in-out' ), true ) ? $a['easing'] : 'ease-out';
$wpe_icon    = \WPEssential\Platform\Kit\Kit::icon_html( $a['icon'], 'wpe-counter__svg' );
$wpe_classes = array(
	'wpe-counter',
	'is-' . sanitize_key( $a['appearance'] ),
	'is-hover-' . sanitize_key( $a['hover'] ),
	'is-icon-' . sanitize_key( $a['iconPosition'] ),
);
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_classes ) ); ?>" data-wpe-counter="1" data-wpe-start="<?php echo esc_attr( (string) $wpe_start ); ?>" data-wpe-end="<?php echo esc_attr( (string) $wpe_end ); ?>" data-wpe-decimals="<?php echo esc_attr( (string) $wpe_digits ); ?>" data-wpe-thousands="<?php echo esc_attr( $wpe_sep ); ?>" data-wpe-point="<?php echo esc_attr( $wpe_point ); ?>" data-wpe-duration="<?php echo esc_attr( (string) $wpe_speed ); ?>" data-wpe-easing="<?php echo esc_attr( $wpe_easing ); ?>" data-wpe-animate="<?php echo $a['animate'] ? '1' : '0'; ?>">
	<?php if ( $wpe_icon ) : ?>
		<span class="wpe-counter__icon" aria-hidden="true"><?php echo $wpe_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Kit::icon_html returns safe icon markup. ?></span>
	<?php endif; ?>
	<div class="wpe-counter__content">
		<div class="wpe-counter__value">
			<?php if ( '' !== $wpe_prefix ) : ?>
				<span class="wpe-counter__prefix" aria-hidden="true"><?php echo esc_html( $wpe_prefix ); ?></span>
			<?php endif; ?>
			<span class="wpe-counter__digits" aria-hidden="true"><?php echo esc_html( $wpe_final ); ?></span>
			<?php if ( '' !== $wpe_suffix ) : ?>
				<span class="wpe-counter__suffix" aria-hidden="true"><?php echo esc_html( $wpe_suffix ); ?></span>
			<?php endif; ?>
			<span class="screen-reader-text"><?php echo esc_html( $wpe_prefix . $wpe_final . $wpe_suffix ); ?></span>
		</div>
		<?php if ( '' !== $wpe_title ) : ?>
			<<?php echo esc_html( $wpe_tag ); ?> class="wpe-counter__title"><?php echo esc_html( $wpe_title ); ?></<?php echo esc_html( $wpe_tag ); ?>>
		<?php endif; ?>
		<?php if ( '' !== $wpe_desc ) : ?>
			<p class="wpe-counter__description"><?php echo esc_html( $wpe_desc ); ?></p>
		<?php endif; ?>
	</div>
</div>
