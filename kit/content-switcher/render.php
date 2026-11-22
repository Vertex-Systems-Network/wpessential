<?php
/**
 * Content Switcher.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_b = 'b' === $a['start'];
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-switcher">
	<div class="wpe-switcher__tabs" role="tablist">
		<button type="button" role="tab" id="<?php echo esc_attr( $id ); ?>-ta" aria-controls="<?php echo esc_attr( $id ); ?>-a" aria-selected="<?php echo $wpe_b ? 'false' : 'true'; ?>"<?php echo $wpe_b ? ' tabindex="-1"' : ''; ?>><?php echo esc_html( (string) $a['labelA'] ); ?></button>
		<button type="button" role="tab" id="<?php echo esc_attr( $id ); ?>-tb" aria-controls="<?php echo esc_attr( $id ); ?>-b" aria-selected="<?php echo $wpe_b ? 'true' : 'false'; ?>"<?php echo $wpe_b ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( (string) $a['labelB'] ); ?></button>
	</div>
	<div class="wpe-switcher__panel" role="tabpanel" id="<?php echo esc_attr( $id ); ?>-a" aria-labelledby="<?php echo esc_attr( $id ); ?>-ta"<?php echo $wpe_b ? ' hidden' : ''; ?>><?php echo wp_kses_post( (string) $a['contentA'] ); ?></div>
	<div class="wpe-switcher__panel" role="tabpanel" id="<?php echo esc_attr( $id ); ?>-b" aria-labelledby="<?php echo esc_attr( $id ); ?>-tb"<?php echo $wpe_b ? '' : ' hidden'; ?>><?php echo wp_kses_post( (string) $a['contentB'] ); ?></div>
</div>
