<?php
/**
 * Accordion render. $a = values, $id = element id.
 *
 * Native <details>/<summary>: works without JavaScript, with the keyboard and screen readers.
 * "One at a time" uses the details name attribute.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_items = array_values( array_filter( (array) $a['items'], 'is_array' ) );
if ( ! $wpe_items ) {
	return;
}
$wpe_tag    = in_array( $a['tag'], array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span' ), true ) ? $a['tag'] : 'h3';
$wpe_name   = 'one' === $a['mode'] ? $id : '';
$wpe_icon   = \WPEssential\Platform\Kit\Kit::icon_html( $a['icon'], 'wpe-accordion__svg is-closed' );
$wpe_active = \WPEssential\Platform\Kit\Kit::icon_html( $a['activeIcon'], 'wpe-accordion__svg is-open' );
$wpe_class  = array(
	'wpe-accordion',
	'is-' . sanitize_key( $a['preset'] ),
	'is-icon-' . sanitize_key( $a['iconPosition'] ),
	'is-hover-' . sanitize_key( $a['titleHover'] ),
);
if ( $wpe_active ) {
	$wpe_class[] = 'has-active-icon';
}
$wpe_faq = array();
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_class ) ); ?>">
	<?php
	foreach ( $wpe_items as $wpe_i => $wpe_item ) :
		$wpe_title = (string) ( $wpe_item['title'] ?? '' );
		$wpe_body  = (string) ( $wpe_item['content'] ?? '' );
		$wpe_open  = array(
			'items'  => ! empty( $wpe_item['open'] ),
			'first'  => 0 === $wpe_i,
			'closed' => false,
			'all'    => true,
		)[ $a['start'] ] ?? false;
		// Only one item may start open in "one at a time" mode.
		if ( $wpe_open && $wpe_name ) {
			$wpe_open      = empty( $wpe_seen_open );
			$wpe_seen_open = true;
		}
		$wpe_own  = ! empty( $wpe_item['icon']['value'] ) ? \WPEssential\Platform\Kit\Kit::icon_html( $wpe_item['icon'], 'wpe-accordion__svg is-closed' ) : $wpe_icon;
		$wpe_mark = $wpe_own || $wpe_active ? '<span class="wpe-accordion__icon" aria-hidden="true">' . $wpe_own . $wpe_active . '</span>' : '';
		if ( $a['schema'] && '' !== trim( $wpe_title ) ) {
			$wpe_faq[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $wpe_title ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_kses_post( $wpe_body ),
				),
			);
		}
		?>
		<details class="wpe-accordion__item"<?php echo $wpe_name ? ' name="' . esc_attr( $wpe_name ) . '"' : ''; ?><?php echo $wpe_open ? ' open' : ''; ?>>
			<summary class="wpe-accordion__title">
				<?php if ( $a['numbers'] ) : ?>
					<span class="wpe-accordion__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $wpe_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
				<?php endif; ?>
				<<?php echo esc_html( $wpe_tag ); ?> class="wpe-accordion__label"><?php echo wp_kses_post( $wpe_title ); ?></<?php echo esc_html( $wpe_tag ); ?>>
				<?php echo $wpe_mark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Icon markup. ?>
			</summary>
			<div class="wpe-accordion__body"><?php echo wp_kses_post( wpautop( $wpe_body ) ); ?></div>
		</details>
	<?php endforeach; ?>
	<?php if ( $wpe_faq ) : ?>
		<script type="application/ld+json">
		<?php
		echo wp_json_encode(
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $wpe_faq,
			),
			JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
		);
		?>
											</script>
	<?php endif; ?>
</div>
