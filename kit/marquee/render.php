<?php
/**
 * Marquee.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_items = array_values( array_filter( array_map( static fn( $i ) => trim( (string) ( $i['text'] ?? '' ) ), (array) $a['items'] ) ) );
$wpe_sep   = (string) $a['separator'];
$wpe_row   = static function ( $hidden ) use ( $wpe_items, $wpe_sep ) {
	echo '<ul class="wpe-marquee__row"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';
	foreach ( $wpe_items as $wpe_text ) {
		echo '<li>' . esc_html( $wpe_text ) . '</li>' . ( '' !== $wpe_sep ? '<li class="wpe-marquee__sep" aria-hidden="true">' . esc_html( $wpe_sep ) . '</li>' : '' );
	}
	echo '</ul>';
};
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-marquee<?php echo ! empty( $a['reverse'] ) ? ' is-reverse' : ''; ?>">
	<?php $wpe_row( false ); ?>
	<?php $wpe_row( true ); ?>
</div>
