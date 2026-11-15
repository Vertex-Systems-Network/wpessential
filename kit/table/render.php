<?php
/**
 * Accessible CSV / TSV table, shared by Gutenberg and Elementor.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template-local scope.

$wpe_source     = substr( (string) $a['data'], 0, 60000 );
$wpe_separators = array(
	'comma'     => ',',
	'tab'       => "\t",
	'semicolon' => ';',
	'pipe'      => '|',
);
$wpe_delimiter  = $wpe_separators[ $a['delimiter'] ] ?? ',';
$wpe_rows       = array();
$wpe_input      = new \SplTempFileObject();
$wpe_input->fwrite( $wpe_source );
$wpe_input->rewind();
$wpe_row_count = 0;

// CSV quoted delimiters and embedded line breaks are parsed by PHP, not a simple split.
while ( ! $wpe_input->eof() && $wpe_row_count < 201 ) {
	$wpe_row = $wpe_input->fgetcsv( $wpe_delimiter );
	if ( ! is_array( $wpe_row ) || array( null ) === $wpe_row ) {
		continue;
	}
	$wpe_cells = array_map(
		static function ( $wpe_cell ) {
			return trim( wp_strip_all_tags( substr( (string) $wpe_cell, 0, 2000 ), true ) );
		},
		array_slice( $wpe_row, 0, 20 )
	);
	if ( array_filter( $wpe_cells, static fn( $cell ) => '' !== $cell ) ) {
		$wpe_rows[] = $wpe_cells;
		++$wpe_row_count;
	}
}
if ( ! $wpe_rows ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-table__empty">' . esc_html__( 'Paste CSV or TSV data to create a table.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_has_header = ! empty( $a['headerRow'] );
$wpe_width      = $wpe_has_header ? min( 20, count( $wpe_rows[0] ) ) : min( 20, max( array_map( 'count', $wpe_rows ) ) );
$wpe_headers    = $wpe_has_header ? array_shift( $wpe_rows ) : array();
for ( $wpe_i = 0; $wpe_i < $wpe_width; $wpe_i++ ) {
	if ( empty( $wpe_headers[ $wpe_i ] ) ) {
		$wpe_headers[ $wpe_i ] = sprintf( /* translators: %d: Table column number. */ __( 'Column %d', 'wpessential' ), $wpe_i + 1 );
	}
}
$wpe_headers   = array_slice( $wpe_headers, 0, $wpe_width );
$wpe_caption   = trim( (string) $a['caption'] );
$wpe_sortable  = ! empty( $a['sortable'] ) && $wpe_has_header;
$wpe_search    = ! empty( $a['searchable'] );
$wpe_classes   = array(
	'wpe-table',
	'is-' . ( 'stack' === $a['layout'] ? 'stack' : 'scroll' ),
	$a['striped'] ? 'is-striped' : '',
	$a['hover'] ? 'is-hover' : '',
	$a['stickyHeader'] ? 'is-sticky' : '',
);
$wpe_search_id = $id . '-search';
// translators: %d: Number of visible table rows after filtering.
$wpe_row_singular = esc_attr__( '%d row shown', 'wpessential' );
// translators: %d: Number of visible table rows after filtering.
$wpe_row_plural = esc_attr__( '%d rows shown', 'wpessential' );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', array_filter( $wpe_classes ) ) ); ?>" data-wpe-table="1" data-row-singular="<?php echo esc_attr( $wpe_row_singular ); ?>" data-row-plural="<?php echo esc_attr( $wpe_row_plural ); ?>">
	<?php if ( $wpe_search ) : ?>
		<div class="wpe-table__tools">
			<label class="screen-reader-text" for="<?php echo esc_attr( $wpe_search_id ); ?>"><?php esc_html_e( 'Search table rows', 'wpessential' ); ?></label>
			<input class="wpe-table__search" id="<?php echo esc_attr( $wpe_search_id ); ?>" type="search" placeholder="<?php echo esc_attr( (string) $a['searchPlaceholder'] ); ?>" autocomplete="off">
		</div>
	<?php endif; ?>
	<div class="wpe-table__viewport" role="region" aria-label="<?php echo esc_attr( '' !== $wpe_caption ? $wpe_caption : __( 'Data table', 'wpessential' ) ); ?>" tabindex="0">
		<table class="wpe-table__table">
			<?php if ( '' !== $wpe_caption ) : ?>
				<caption class="wpe-table__caption <?php echo 'bottom' === $a['captionPosition'] ? 'is-bottom' : 'is-top'; ?>"><?php echo esc_html( $wpe_caption ); ?></caption>
			<?php endif; ?>
			<thead class="wpe-table__head <?php echo $wpe_has_header ? '' : 'screen-reader-text'; ?>">
				<tr>
					<?php foreach ( $wpe_headers as $wpe_j => $wpe_heading ) : ?>
						<th scope="col" data-wpe-col="<?php echo esc_attr( (string) $wpe_j ); ?>">
							<?php if ( $wpe_sortable ) : ?>
								<button class="wpe-table__sort" type="button" data-wpe-sort="<?php echo esc_attr( (string) $wpe_j ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: Table column title. */ __( 'Sort by %s', 'wpessential' ), $wpe_heading ) ); ?>"><?php echo esc_html( $wpe_heading ); ?><span class="wpe-table__sort-indicator" aria-hidden="true">↕</span></button>
							<?php else : ?>
								<?php echo esc_html( $wpe_heading ); ?>
							<?php endif; ?>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $wpe_rows as $wpe_row ) : ?>
					<tr data-wpe-row="1">
						<?php foreach ( $wpe_headers as $wpe_j => $wpe_heading ) : ?>
							<td data-label="<?php echo esc_attr( $wpe_heading ); ?>"><?php echo esc_html( (string) ( $wpe_row[ $wpe_j ] ?? '' ) ); ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php if ( $wpe_search ) : ?>
		<p class="wpe-table__no-results" hidden><?php echo esc_html( '' !== trim( (string) $a['emptyMessage'] ) ? $a['emptyMessage'] : __( 'No matching rows.', 'wpessential' ) ); ?></p>
		<p class="wpe-table__status screen-reader-text" role="status" aria-live="polite"></p>
	<?php endif; ?>
</div>
