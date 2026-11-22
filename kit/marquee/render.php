<?php
/**
 * Marquee.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_items = array_values( array_filter( array_map( static fn( $i ) => trim( (string) ( $i['text'] ?? '' ) ), (array) $a['items'] ), static fn( $t ) => '' !== $t ) );
if ( ! $wpe_items ) {
	// Front end shows nothing; the editor shows why the element is empty.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p id="' . esc_attr( $id ) . '" class="wpe-marquee__empty">' . esc_html__( 'Add items to show the marquee.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_sep   = (string) $a['separator'];
$wpe_row   = static function ( $hidden ) use ( $wpe_items, $wpe_sep ) {
	echo '<ul class="wpe-marquee__row"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';
	foreach ( $wpe_items as $wpe_text ) {
		echo '<li>' . esc_html( $wpe_text ) . '</li>' . ( '' !== $wpe_sep ? '<li class="wpe-marquee__sep" aria-hidden="true">' . esc_html( $wpe_sep ) . '</li>' : '' );
	}
	echo '</ul>';
};
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-marquee<?php echo ! empty( $a['reverse'] ) ? ' is-reverse' : ''; ?>" tabindex="0" role="region" aria-label="<?php esc_attr_e( 'Scrolling text, focus to pause', 'wpessential' ); ?>">
	<?php $wpe_row( false ); ?>
	<?php $wpe_row( true ); ?>
</div>
