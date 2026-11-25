<?php
/**
 * Account Dashboard.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

// Current URL with its query string; only same-site redirects are accepted.
$wpe_here = wp_validate_redirect( esc_url_raw( set_url_scheme( '//' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ?? '' ) ) . wp_unslash( $_SERVER['REQUEST_URI'] ?? '/' ) ) ), home_url( '/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- esc_url_raw + wp_validate_redirect.
if ( ! is_user_logged_in() ) {
	?>
	<div id="<?php echo esc_attr( $id ); ?>" class="wpe-ad is-guest">
		<p><?php echo esc_html( (string) $a['guest'] ); ?></p>
		<a class="wpe-ad__login" href="<?php echo esc_url( wp_login_url( $wpe_here ) ); ?>"><?php esc_html_e( 'Sign in', 'wpessential' ); ?></a>
	</div>
	<?php
	return;
}
if ( ! defined( 'DONOTCACHEPAGE' ) ) {
	define( 'DONOTCACHEPAGE', true ); // Per-user output; guest output stays cacheable.
}
$wpe_user  = wp_get_current_user();
$wpe_links = array();
if ( function_exists( 'wc_get_account_menu_items' ) ) {
	foreach ( wc_get_account_menu_items() as $wpe_ep => $wpe_label ) {
		if ( 'customer-logout' !== $wpe_ep ) {
			$wpe_links[ wc_get_account_endpoint_url( $wpe_ep ) ] = $wpe_label;
		}
	}
} else {
	$wpe_links[ get_edit_profile_url() ] = __( 'Profile', 'wpessential' );
	if ( current_user_can( 'edit_posts' ) ) {
		$wpe_links[ admin_url( 'edit.php' ) ] = __( 'My posts', 'wpessential' );
		$wpe_links[ admin_url() ]            = __( 'Dashboard', 'wpessential' );
	}
}
$wpe_greeting = str_replace( '%s', $wpe_user->display_name, (string) $a['greeting'] );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-ad">
	<div class="wpe-ad__head">
		<?php echo get_avatar( $wpe_user->ID, 56, '', '', array( 'class' => 'wpe-ad__avatar' ) ); ?>
		<div>
			<p class="wpe-ad__hello"><?php echo esc_html( $wpe_greeting ); ?></p>
			<a class="wpe-ad__logout" href="<?php echo esc_url( wp_logout_url( $wpe_here ) ); ?>"><?php esc_html_e( 'Sign out', 'wpessential' ); ?></a>
		</div>
	</div>
	<ul class="wpe-ad__links">
		<?php foreach ( $wpe_links as $wpe_href => $wpe_label ) : ?>
			<li><a href="<?php echo esc_url( $wpe_href ); ?>"><?php echo esc_html( $wpe_label ); ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
