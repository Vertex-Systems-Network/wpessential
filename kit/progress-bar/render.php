<?php
/**
 * Progress Bar renderer. $a contains Kit values; $id is the unique element ID.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Local variables in Kit::template().

$wpe_items = array_values( array_filter( (array) $a['items'], 'is_array' ) );
if ( ! $wpe_items ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-progress__empty">' . esc_html( (string) $a['emptyText'] ) . '</p>';
	}
	return;
}
$wpe_layout = 'circle' === $a['layout'] ? 'circle' : 'horizontal';
$wpe_format = in_array( $a['valueFormat'], array( 'percent', 'value', 'fraction' ), true ) ? $a['valueFormat'] : 'percent';
$wpe_class  = 'wpe-progress is-' . $wpe_layout . ' is-' . ( 'square' === $a['roundness'] ? 'square' : 'rounded' );
$wpe_dur    = min( 5000, max( 200, (int) $a['duration'] ) );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $wpe_class ); ?>" data-wpe-progress="1" data-animate="<?php echo $a['animate'] ? '1' : '0'; ?>" style="--wpek-progress-duration:<?php echo esc_attr( (string) $wpe_dur ); ?>ms">
	<div class="wpe-progress__items" role="list">
		<?php foreach ( $wpe_items as $wpe_i => $wpe_item ) : ?>
			<?php
			$wpe_raw_max   = $wpe_item['max'] ?? 100;
			$wpe_raw_value = $wpe_item['value'] ?? 0;
			$wpe_max       = is_numeric( $wpe_raw_max ) ? (float) $wpe_raw_max : 100.0;
			$wpe_max       = is_finite( $wpe_max ) ? min( 1000000, max( 1, $wpe_max ) ) : 100.0;
			$wpe_value     = is_numeric( $wpe_raw_value ) ? (float) $wpe_raw_value : 0.0;
			$wpe_value     = is_finite( $wpe_value ) ? min( $wpe_max, max( 0, $wpe_value ) ) : 0.0;
			$wpe_percent   = min( 100, max( 0, 100 * $wpe_value / $wpe_max ) );
			$wpe_percent_s = rtrim( rtrim( number_format( $wpe_percent, 2, '.', '' ), '0' ), '.' );
			$wpe_value_s   = rtrim( rtrim( number_format( $wpe_value, 2, '.', '' ), '0' ), '.' );
			$wpe_max_s     = rtrim( rtrim( number_format( $wpe_max, 2, '.', '' ), '0' ), '.' );
			$wpe_label     = trim( (string) ( $wpe_item['label'] ?? '' ) );
			$wpe_desc      = trim( (string) ( $wpe_item['description'] ?? '' ) );
			$wpe_label_id  = $id . '-label-' . $wpe_i;
			$wpe_display   = 'value' === $wpe_format ? $wpe_value_s : ( 'fraction' === $wpe_format ? $wpe_value_s . ' / ' . $wpe_max_s : $wpe_percent_s . '%' );
			?>
			<div class="wpe-progress__item" role="listitem" data-target="<?php echo esc_attr( $wpe_percent_s ); ?>" style="--wpek-target-progress:<?php echo esc_attr( $wpe_percent_s ); ?>%">
				<?php if ( $a['showLabel'] && '' !== $wpe_label ) : ?>
					<div class="wpe-progress__header">
						<span class="wpe-progress__label" id="<?php echo esc_attr( $wpe_label_id ); ?>"><?php echo esc_html( $wpe_label ); ?></span>
						<?php if ( $a['showValue'] && 'horizontal' === $wpe_layout ) : ?>
							<span class="wpe-progress__value" aria-hidden="true"><?php echo esc_html( $wpe_display ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="wpe-progress__visual">
					<div class="wpe-progress__track" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $wpe_max_s ); ?>" aria-valuenow="<?php echo esc_attr( $wpe_value_s ); ?>" aria-valuetext="<?php echo esc_attr( $wpe_percent_s . '% complete' ); ?>"<?php echo $a['showLabel'] && '' !== $wpe_label ? ' aria-labelledby="' . esc_attr( $wpe_label_id ) . '"' : ' aria-label="' . esc_attr( '' !== $wpe_label ? $wpe_label : __( 'Progress', 'wpessential' ) ) . '"'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute names fixed and all values escaped. ?>>
						<?php if ( 'horizontal' === $wpe_layout ) : ?>
							<span class="wpe-progress__fill" aria-hidden="true"></span>
						<?php endif; ?>
					</div>
					<?php if ( $a['showValue'] && 'circle' === $wpe_layout ) : ?>
						<span class="wpe-progress__value wpe-progress__circle-value" aria-hidden="true"><?php echo esc_html( $wpe_display ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $a['showValue'] && 'horizontal' === $wpe_layout && ( ! $a['showLabel'] || '' === $wpe_label ) ) : ?>
					<span class="wpe-progress__value wpe-progress__value-standalone" aria-hidden="true"><?php echo esc_html( $wpe_display ); ?></span>
				<?php endif; ?>
				<?php if ( $a['showDescription'] && '' !== $wpe_desc ) : ?>
					<p class="wpe-progress__description"><?php echo esc_html( $wpe_desc ); ?></p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
