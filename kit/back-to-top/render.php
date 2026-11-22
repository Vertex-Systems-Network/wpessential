<?php
/**
 * Back to Top.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-btt is-<?php echo 'left' === $a['position'] ? 'left' : 'right'; ?>">
	<?php if ( ! empty( $a['progress'] ) ) : ?><div class="wpe-btt__bar" aria-hidden="true"></div><?php endif; ?>
	<a class="wpe-btt__btn" href="#" aria-label="<?php echo esc_attr( (string) $a['label'] ); ?>"><span aria-hidden="true">↑</span></a>
</div>
