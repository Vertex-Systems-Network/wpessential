<?php
/**
 * Icon list render. $a = values, $id = element id.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included inside Kit::template(), not in global scope.

$wpe_items = (array) $a['items'];
if ( ! $wpe_items ) {
	return;
}
$wpe_tag     = in_array( $a['tag'], array( 'ul', 'ol', 'div' ), true ) ? $a['tag'] : 'ul';
$wpe_item    = 'div' === $wpe_tag ? 'div' : 'li';
$wpe_marker  = in_array( $a['marker'], array( 'icon', 'number', 'none' ), true ) ? $a['marker'] : 'icon';
$wpe_classes = array(
	'wpe-icon-list',
	'is-' . sanitize_key( $a['layout'] ),
	'is-shape-' . sanitize_key( $a['iconShape'] ),
	'is-hover-' . sanitize_key( $a['hover'] ),
);
if ( $a['divider'] ) {
	$wpe_classes[] = 'has-divider';
}
?>
<<?php echo esc_html( $wpe_tag ); ?> id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_classes ) ); ?>">
	<?php
	foreach ( $wpe_items as $wpe_i => $wpe_row ) :
		$wpe_row  = (array) $wpe_row;
		$wpe_text = (string) ( $wpe_row['text'] ?? '' );
		$wpe_desc = trim( (string) ( $wpe_row['description'] ?? '' ) );
		$wpe_link = (string) ( $wpe_row['link'] ?? '' );
		$wpe_tab  = ! empty( $wpe_row['newTab'] ) ? ' target="_blank" rel="noopener"' : '';
		if ( 'number' === $wpe_marker ) {
			$wpe_icon = '<span class="wpe-icon-list__icon is-number" aria-hidden="true">' . ( (int) $wpe_i + 1 ) . '</span>';
		} elseif ( 'icon' === $wpe_marker ) {
			$wpe_svg  = \WPEssential\Platform\Kit\Kit::icon_html( ! empty( $wpe_row['icon']['value'] ) ? $wpe_row['icon'] : $a['icon'], 'wpe-icon-list__svg' );
			$wpe_icon = $wpe_svg ? '<span class="wpe-icon-list__icon" aria-hidden="true">' . $wpe_svg . '</span>' : '';
		} else {
			$wpe_icon = '';
		}
		$wpe_label = '<span class="wpe-icon-list__text">' . wp_kses_post( $wpe_text ) . '</span>';
		if ( $wpe_link && 'text' === $a['linkArea'] ) {
			$wpe_label = '<a class="wpe-icon-list__text" href="' . esc_url( $wpe_link ) . '"' . $wpe_tab . '>' . wp_kses_post( $wpe_text ) . '</a>';
		}
		$wpe_body  = '<span class="wpe-icon-list__body">' . $wpe_label . ( '' !== $wpe_desc ? '<span class="wpe-icon-list__desc">' . wp_kses_post( $wpe_desc ) . '</span>' : '' ) . '</span>';
		$wpe_inner = $wpe_icon . $wpe_body;
		if ( $wpe_link && 'text' !== $a['linkArea'] ) {
			$wpe_inner = '<a class="wpe-icon-list__link" href="' . esc_url( $wpe_link ) . '"' . $wpe_tab . '>' . $wpe_inner . '</a>';
		}
		?>
		<<?php echo esc_html( $wpe_item ); ?> class="wpe-icon-list__item"><?php echo $wpe_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?></<?php echo esc_html( $wpe_item ); ?>>
	<?php endforeach; ?>
</<?php echo esc_html( $wpe_tag ); ?>>
