<?php
/**
 * Semantic divider and decorative spacer.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_mode    = in_array( $a['mode'], array( 'line', 'text', 'symbol', 'space' ), true ) ? $a['mode'] : 'line';
$wpe_pattern = in_array( $a['lineStyle'], array( 'solid', 'dashed', 'dotted', 'double' ), true ) ? $a['lineStyle'] : 'solid';
$wpe_text    = trim( (string) $a['text'] );
$wpe_symbols = array(
	'star'    => '★',
	'diamond' => '◆',
	'dot'     => '●',
);
$wpe_symbol  = $wpe_symbols[ $a['symbol'] ] ?? '★';
?>
<?php if ( 'space' === $wpe_mode ) : ?>
	<div id="<?php echo esc_attr( $id ); ?>" class="wpe-divider is-space" aria-hidden="true"></div>
<?php elseif ( 'line' === $wpe_mode ) : ?>
	<hr id="<?php echo esc_attr( $id ); ?>" class="wpe-divider is-line is-<?php echo esc_attr( $wpe_pattern ); ?>">
<?php else : ?>
	<div id="<?php echo esc_attr( $id ); ?>" class="wpe-divider is-label is-<?php echo esc_attr( $wpe_pattern ); ?>" role="separator"<?php echo 'text' === $wpe_mode && $wpe_text ? ' aria-label="' . esc_attr( $wpe_text ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped accessible label. ?>>
		<span class="wpe-divider__line" aria-hidden="true"></span>
		<?php if ( 'text' === $wpe_mode ) : ?>
			<span class="wpe-divider__label"><?php echo esc_html( '' !== $wpe_text ? $wpe_text : __( 'Section', 'wpessential' ) ); ?></span>
		<?php else : ?>
			<span class="wpe-divider__label" aria-hidden="true"><?php echo esc_html( $wpe_symbol ); ?></span>
		<?php endif; ?>
		<span class="wpe-divider__line" aria-hidden="true"></span>
	</div>
<?php endif; ?>
