<?php
/**
 * Safe SVG illustration: only bundled path data, never arbitrary SVG markup.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_paths    = array(
	'check'   => 'M5 12l5 5L20 7',
	'arrow'   => 'M4 12h16M13 5l7 7-7 7',
	'heart'   => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8L12 22l9.8-9.6a5.5 5.5 0 0 0-1-7.8z',
	'star'    => 'M12 2l3.1 6.3L22 9.3l-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z',
	'sparkle' => 'M12 2l2.5 7.5L22 12l-7.5 2.5L12 22l-2.5-7.5L2 12l7.5-2.5z',
);
$wpe_shape    = isset( $wpe_paths[ $a['shape'] ] ) ? $a['shape'] : 'check';
$wpe_motion   = in_array( $a['animation'], array( 'none', 'draw', 'pulse', 'spin' ), true ) ? $a['animation'] : 'none';
$wpe_duration = min( 6000, max( 200, (int) $a['duration'] ) );
$wpe_title    = trim( (string) $a['title'] );
$wpe_label    = '' !== $wpe_title ? $wpe_title : __( 'Animated icon', 'wpessential' );
?>
<figure id="<?php echo esc_attr( $id ); ?>" class="wpe-svg is-<?php echo esc_attr( $wpe_motion ); ?>" data-wpe-svg="1" style="--wpek-svg-duration:<?php echo esc_attr( (string) $wpe_duration ); ?>ms;--wpek-svg-repeat:<?php echo $a['repeat'] ? 'infinite' : '1'; ?>;--wpek-svg-stroke:<?php echo esc_attr( (string) min( 8, max( 1, (float) $a['strokeWidth'] ) ) ); ?>">
	<svg class="wpe-svg__drawing" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="var(--wpek-svg-stroke)"<?php echo $a['decorative'] ? ' aria-hidden="true"' : ' role="img" aria-label="' . esc_attr( $wpe_label ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static ARIA attribute and escaped label. ?>>
		<path class="wpe-svg__path" pathLength="100" d="<?php echo esc_attr( $wpe_paths[ $wpe_shape ] ); ?>"></path>
	</svg>
	<?php if ( '' !== trim( (string) $a['caption'] ) ) : ?>
		<figcaption class="wpe-svg__caption"><?php echo esc_html( (string) $a['caption'] ); ?></figcaption>
	<?php endif; ?>
</figure>
