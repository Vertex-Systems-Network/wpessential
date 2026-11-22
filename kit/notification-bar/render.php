<?php
/**
 * Notification Bar.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-notice-bar" role="region" aria-label="<?php esc_attr_e( 'Announcement', 'wpessential' ); ?>" data-wpe-key="wpe-bar-<?php echo esc_attr( md5( (string) $a['text'] . (string) $a['key'] ) ); ?>">
	<p><?php echo esc_html( (string) $a['text'] ); ?><?php if ( '' !== (string) $a['link'] ) : ?> <a href="<?php echo esc_url( (string) $a['link'] ); ?>"><?php echo esc_html( (string) $a['linkText'] ); ?></a><?php endif; ?></p>
	<button type="button" class="wpe-notice-bar__close" data-wpe-dismiss aria-label="<?php esc_attr_e( 'Dismiss announcement', 'wpessential' ); ?>">×</button>
</div>
