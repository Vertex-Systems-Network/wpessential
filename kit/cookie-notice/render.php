<?php
/**
 * Cookie Notice.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_policy = '' !== (string) $a['policy'] ? (string) $a['policy'] : (string) get_privacy_policy_url();
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-cookie" role="dialog" aria-live="polite" aria-label="<?php esc_attr_e( 'Cookie notice', 'wpessential' ); ?>" data-wpe-key="wpe-consent">
	<p><?php echo esc_html( (string) $a['text'] ); ?><?php if ( '' !== $wpe_policy ) : ?> <a href="<?php echo esc_url( $wpe_policy ); ?>"><?php esc_html_e( 'Privacy policy', 'wpessential' ); ?></a><?php endif; ?></p>
	<div class="wpe-cookie__actions">
		<button type="button" data-wpe-dismiss="declined"><?php echo esc_html( (string) $a['decline'] ); ?></button>
		<button type="button" class="is-primary" data-wpe-dismiss="accepted"><?php echo esc_html( (string) $a['accept'] ); ?></button>
	</div>
</div>
