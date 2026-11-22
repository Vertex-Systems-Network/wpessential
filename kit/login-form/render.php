<?php
/**
 * Login / Register.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

// Only same-site redirects; anything else falls back to the current page.
$wpe_here     = home_url( '/' . ltrim( (string) ( $GLOBALS['wp']->request ?? '' ), '/' ) );
$wpe_redirect = wp_validate_redirect( (string) $a['redirect'], $wpe_here );

if ( is_user_logged_in() && ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) :
	$wpe_user = wp_get_current_user();
	?>
	<div id="<?php echo esc_attr( $id ); ?>" class="wpe-login is-in">
		<?php /* translators: %s: display name. */ ?>
		<p><?php echo esc_html( sprintf( __( 'Signed in as %s.', 'wpessential' ), $wpe_user->display_name ) ); ?> <a href="<?php echo esc_url( wp_logout_url( $wpe_here ) ); ?>"><?php esc_html_e( 'Log out', 'wpessential' ); ?></a></p>
	</div>
	<?php
	return;
endif;

$wpe_register = ! empty( $a['showRegister'] ) && get_option( 'users_can_register' );
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-login">
	<?php if ( $wpe_register ) : ?>
		<div class="wpe-switcher__tabs" role="tablist">
			<button type="button" role="tab" id="<?php echo esc_attr( $id ); ?>-ta" aria-controls="<?php echo esc_attr( $id ); ?>-a" aria-selected="true"><?php echo esc_html( (string) $a['loginLabel'] ); ?></button>
			<button type="button" role="tab" id="<?php echo esc_attr( $id ); ?>-tb" aria-controls="<?php echo esc_attr( $id ); ?>-b" aria-selected="false" tabindex="-1"><?php echo esc_html( (string) $a['registerLabel'] ); ?></button>
		</div>
	<?php endif; ?>
	<div class="wpe-switcher__panel" role="<?php echo $wpe_register ? 'tabpanel' : 'region'; ?>" id="<?php echo esc_attr( $id ); ?>-a" aria-label="<?php echo esc_attr( (string) $a['loginLabel'] ); ?>">
		<?php
		wp_login_form(
			array(
				'redirect'       => $wpe_redirect,
				'form_id'        => $id . '-form',
				'id_username'    => $id . '-user',
				'id_password'    => $id . '-pass',
				'id_remember'    => $id . '-remember',
				'id_submit'      => $id . '-submit',
				'label_log_in'   => (string) $a['loginLabel'],
				'remember'       => true,
			)
		);
		?>
		<p class="wpe-login__lost"><a href="<?php echo esc_url( wp_lostpassword_url( $wpe_here ) ); ?>"><?php esc_html_e( 'Forgot your password?', 'wpessential' ); ?></a></p>
	</div>
	<?php if ( $wpe_register ) : ?>
		<div class="wpe-switcher__panel" role="tabpanel" id="<?php echo esc_attr( $id ); ?>-b" aria-labelledby="<?php echo esc_attr( $id ); ?>-tb">
			<form class="wpe-login__register" action="<?php echo esc_url( site_url( 'wp-login.php?action=register', 'login_post' ) ); ?>" method="post">
				<p><label for="<?php echo esc_attr( $id ); ?>-rl"><?php esc_html_e( 'Username', 'wpessential' ); ?></label><input type="text" name="user_login" id="<?php echo esc_attr( $id ); ?>-rl" autocomplete="username" required></p>
				<p><label for="<?php echo esc_attr( $id ); ?>-re"><?php esc_html_e( 'Email', 'wpessential' ); ?></label><input type="email" name="user_email" id="<?php echo esc_attr( $id ); ?>-re" autocomplete="email" required></p>
				<p class="wpe-login__note"><?php esc_html_e( 'A link to set your password will be emailed to you.', 'wpessential' ); ?></p>
				<input type="hidden" name="redirect_to" value="<?php echo esc_url( $wpe_redirect ); ?>">
				<p><button type="submit" class="wpe-login__submit"><?php echo esc_html( (string) $a['registerLabel'] ); ?></button></p>
			</form>
		</div>
	<?php endif; ?>
</div>
