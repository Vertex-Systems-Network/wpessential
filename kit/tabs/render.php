<?php
/**
 * Tabs render. $a = values, $id = element id.
 *
 * WAI-ARIA tabs (tablist / tab / tabpanel) with arrow-key navigation from script.js. Without
 * JavaScript every panel shows, one after the other, so no content is ever hidden.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_items = array_values( array_filter( (array) $a['items'], 'is_array' ) );
if ( ! $wpe_items ) {
	return;
}
$wpe_active = max( 0, min( count( $wpe_items ) - 1, (int) $a['active'] - 1 ) );
$wpe_class  = array(
	'wpe-tabs',
	'is-indicator-' . sanitize_key( $a['indicator'] ),
	'is-icon-' . sanitize_key( $a['iconPosition'] ),
	'is-mobile-' . sanitize_key( $a['mobile'] ),
	'is-motion-' . sanitize_key( $a['panelMotion'] ),
);
$wpe_data   = array(
	'data-wpe-tabs'  => '1',
	'data-deep-link' => $a['deepLink'] ? '1' : '0',
	'data-autoplay'  => (string) max( 0, (int) $a['autoplay'] ),
	'data-mobile'    => sanitize_key( $a['mobile'] ),
	'data-mobile-at' => (string) (int) $a['mobileAt'],
);
$wpe_attr   = '';
foreach ( $wpe_data as $wpe_k => $wpe_v ) {
	$wpe_attr .= ' ' . $wpe_k . '="' . esc_attr( $wpe_v ) . '"';
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>"<?php echo $wpe_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>>
	<div class="wpe-tabs__nav" role="tablist" aria-orientation="horizontal">
		<?php
		foreach ( $wpe_items as $wpe_i => $wpe_item ) :
			$wpe_key  = sanitize_title( ( $wpe_item['anchor'] ?? '' ) ? $wpe_item['anchor'] : '' );
			$wpe_tid  = $id . '-tab-' . $wpe_i;
			$wpe_pid  = $id . '-panel-' . $wpe_i;
			$wpe_on   = $wpe_i === $wpe_active;
			$wpe_icon = \WPEssential\Platform\Kit\Kit::icon_html( $wpe_item['icon'] ?? array(), 'wpe-tabs__svg' );
			?>
			<button type="button" class="wpe-tabs__tab" role="tab" id="<?php echo esc_attr( $wpe_tid ); ?>" aria-controls="<?php echo esc_attr( $wpe_pid ); ?>" aria-selected="<?php echo $wpe_on ? 'true' : 'false'; ?>" tabindex="<?php echo $wpe_on ? '0' : '-1'; ?>"<?php echo $wpe_key ? ' data-anchor="' . esc_attr( $wpe_key ) . '"' : ''; ?>>
				<?php if ( $wpe_icon ) : ?>
					<span class="wpe-tabs__icon" aria-hidden="true"><?php echo $wpe_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon markup. ?></span>
				<?php endif; ?>
				<span class="wpe-tabs__label"><?php echo esc_html( (string) ( $wpe_item['title'] ?? '' ) ); ?></span>
				<?php if ( '' !== trim( (string) ( $wpe_item['badge'] ?? '' ) ) ) : ?>
					<span class="wpe-tabs__badge"><?php echo esc_html( $wpe_item['badge'] ); ?></span>
				<?php endif; ?>
			</button>
		<?php endforeach; ?>
	</div>
	<div class="wpe-tabs__panels">
		<?php foreach ( $wpe_items as $wpe_i => $wpe_item ) : ?>
			<div class="wpe-tabs__panel" role="tabpanel" id="<?php echo esc_attr( $id . '-panel-' . $wpe_i ); ?>" aria-labelledby="<?php echo esc_attr( $id . '-tab-' . $wpe_i ); ?>" tabindex="0"<?php echo $wpe_i === $wpe_active ? '' : ' hidden'; ?>>
				<button type="button" class="wpe-tabs__accordion-title" aria-expanded="<?php echo $wpe_i === $wpe_active ? 'true' : 'false'; ?>" tabindex="-1"><?php echo esc_html( (string) ( $wpe_item['title'] ?? '' ) ); ?></button>
				<div class="wpe-tabs__content"><?php echo wp_kses_post( wpautop( (string) ( $wpe_item['content'] ?? '' ) ) ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
