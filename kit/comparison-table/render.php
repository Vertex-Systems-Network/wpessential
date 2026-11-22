<?php
/**
 * Comparison Table.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_cols = array_values( array_filter( array_map( 'trim', explode( '|', (string) $a['columns'] ) ), static fn( $c ) => '' !== $c ) );
$wpe_hi   = (int) $a['highlight'];
$wpe_cell = static function ( $value ) {
	$v = strtolower( trim( (string) $value ) );
	if ( in_array( $v, array( 'yes', 'y', '✓', 'true' ), true ) ) {
		return '<span class="wpe-compare__yes" aria-hidden="true">✓</span><span class="screen-reader-text">' . esc_html__( 'Yes', 'wpessential' ) . '</span>';
	}
	if ( in_array( $v, array( 'no', 'n', '-', '—', 'false', '' ), true ) ) {
		return '<span class="wpe-compare__no" aria-hidden="true">—</span><span class="screen-reader-text">' . esc_html__( 'No', 'wpessential' ) . '</span>';
	}
	return esc_html( trim( (string) $value ) );
};
if ( ! $wpe_cols ) {
	return;
}
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-compare">
	<table>
		<caption class="screen-reader-text"><?php echo esc_html( (string) $a['caption'] ); ?></caption>
		<thead><tr><td></td><?php foreach ( $wpe_cols as $wpe_i => $wpe_col ) : ?><th scope="col"<?php echo $wpe_i + 1 === $wpe_hi ? ' class="is-hi"' : ''; ?>><?php echo esc_html( $wpe_col ); ?></th><?php endforeach; ?></tr></thead>
		<tbody>
			<?php foreach ( (array) $a['rows'] as $wpe_row ) : ?>
				<?php $wpe_vals = explode( '|', (string) ( $wpe_row['values'] ?? '' ) ); ?>
				<tr>
					<th scope="row"><?php echo esc_html( (string) ( $wpe_row['feature'] ?? '' ) ); ?></th>
					<?php foreach ( $wpe_cols as $wpe_i => $wpe_col ) : ?>
						<td<?php echo $wpe_i + 1 === $wpe_hi ? ' class="is-hi"' : ''; ?>><?php echo $wpe_cell( $wpe_vals[ $wpe_i ] ?? '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in $wpe_cell. ?></td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
