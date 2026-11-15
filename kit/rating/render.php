<?php
/**
 * Display-only numeric and icon rating.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped render template.

$wpe_max    = '10' === (string) $a['scale'] ? 10 : 5;
$wpe_value  = min( $wpe_max, max( 0, (float) $a['rating'] ) );
$wpe_symbol = in_array( $a['icon'], array( 'heart', 'circle', 'star' ), true ) ? $a['icon'] : 'star';
$wpe_paths  = array(
	'star'   => 'M12 2l3 6.4 7 .9-5 5 .9 7L12 18l-6.2 3.3.9-7-5-5 7-.9z',
	'heart'  => 'M20.8 5.2a5.5 5.5 0 0 0-7.8 0l-1 1-1-1a5.5 5.5 0 0 0-7.8 7.8L12 22l9.8-9a5.5 5.5 0 0 0-1-7.8z',
	'circle' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z',
);
$wpe_score  = number_format( $wpe_value, (int) ( 0.0 !== fmod( (float) $wpe_value, 1.0 ) ), '.', '' );
$wpe_label  = sprintf(
	/* translators: 1: Score; 2: total possible score. */
	__( 'Rated %1$s out of %2$d', 'wpessential' ),
	$wpe_score,
	$wpe_max
);
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-rating">
	<?php if ( '' !== trim( (string) $a['title'] ) ) : ?>
		<p class="wpe-rating__title"><?php echo esc_html( (string) $a['title'] ); ?></p>
	<?php endif; ?>
	<div class="wpe-rating__summary" role="img" aria-label="<?php echo esc_attr( $wpe_label ); ?>">
		<span class="wpe-rating__icons" aria-hidden="true">
			<?php for ( $wpe_i = 0; $wpe_i < $wpe_max; $wpe_i++ ) : ?>
				<?php $wpe_fill = min( 100, max( 0, ( $wpe_value - $wpe_i ) * 100 ) ); ?>
				<span class="wpe-rating__symbol" style="--wpek-rating-fill:<?php echo esc_attr( (string) $wpe_fill ); ?>%">
					<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?php echo esc_attr( $wpe_paths[ $wpe_symbol ] ); ?>"></path></svg>
					<svg class="wpe-rating__on" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="<?php echo esc_attr( $wpe_paths[ $wpe_symbol ] ); ?>"></path></svg>
				</span>
			<?php endfor; ?>
		</span>
		<?php if ( $a['showNumber'] ) : ?>
			<span class="wpe-rating__score"><?php echo esc_html( $wpe_score ); ?>
				<?php if ( $a['showScale'] ) : ?>
					/ <?php echo esc_html( (string) $wpe_max ); ?>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</div>
</div>
