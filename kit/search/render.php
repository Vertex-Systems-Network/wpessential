<?php
/**
 * Native WordPress GET search; no JavaScript required.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped Kit renderer.

$wpe_scope = in_array( $a['scope'], array( 'all', 'post', 'page', 'product' ), true ) ? $a['scope'] : 'all';
if ( 'product' === $wpe_scope && ( ! post_type_exists( 'product' ) || ! function_exists( 'wc_get_products' ) ) ) {
	$wpe_scope = 'all';
}
$wpe_label = trim( (string) $a['label'] );
$wpe_label = '' !== $wpe_label ? $wpe_label : __( 'Search', 'wpessential' );
$wpe_mode  = 'expand' === $a['mode'] ? 'expand' : 'inline';
$wpe_style = in_array( $a['style'], array( 'boxed', 'line', 'minimal' ), true ) ? $a['style'] : 'boxed';
$wpe_form  = $id . '-form';
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-search is-<?php echo esc_attr( $wpe_mode ); ?> is-<?php echo esc_attr( $wpe_style ); ?>">
	<?php if ( 'expand' === $wpe_mode ) : ?>
		<details class="wpe-search__details">
			<summary class="wpe-search__toggle"><?php echo esc_html( '' !== trim( (string) $a['expandText'] ) ? $a['expandText'] : $wpe_label ); ?></summary>
	<?php endif; ?>
	<form id="<?php echo esc_attr( $wpe_form ); ?>" class="wpe-search__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="wpe-search__label<?php echo ! $a['showLabel'] ? ' screen-reader-text' : ''; ?>" for="<?php echo esc_attr( $id ); ?>-field"><?php echo esc_html( $wpe_label ); ?></label>
		<div class="wpe-search__row">
			<input id="<?php echo esc_attr( $id ); ?>-field" class="wpe-search__input" name="s" type="search" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( (string) $a['placeholder'] ); ?>">
			<?php if ( 'all' !== $wpe_scope ) : ?>
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $wpe_scope ); ?>">
			<?php endif; ?>
			<button class="wpe-search__submit" type="submit">
				<?php if ( 'icon' === $a['buttonStyle'] ) : ?>
					<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="m16 16 5 5"></path></svg>
					<span class="screen-reader-text"><?php echo esc_html( (string) $a['buttonText'] ); ?></span>
				<?php else : ?>
					<?php echo esc_html( (string) $a['buttonText'] ); ?>
				<?php endif; ?>
			</button>
		</div>
	</form>
	<?php if ( 'expand' === $wpe_mode ) : ?>
		</details>
	<?php endif; ?>
</div>
