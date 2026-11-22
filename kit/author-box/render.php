<?php
/**
 * Author Box.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_post = get_post();
if ( ! $wpe_post instanceof WP_Post ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p id="' . esc_attr( $id ) . '" class="wpe-post-empty">' . esc_html__( 'Shows data of the current post on the site.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_uid = (int) $wpe_post->post_author;
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-author-box">
	<?php echo get_avatar( $wpe_uid, 72, '', '', array( 'class' => 'wpe-author-box__avatar' ) ); ?>
	<div>
		<p class="wpe-author-box__name"><?php echo esc_html( get_the_author_meta( 'display_name', $wpe_uid ) ); ?></p>
		<?php if ( ! empty( $a['bio'] ) && get_the_author_meta( 'description', $wpe_uid ) ) : ?><p class="wpe-author-box__bio"><?php echo esc_html( (string) get_the_author_meta( 'description', $wpe_uid ) ); ?></p><?php endif; ?>
		<?php if ( ! empty( $a['link'] ) ) : ?><a href="<?php echo esc_url( get_author_posts_url( $wpe_uid ) ); ?>"><?php esc_html_e( 'All posts', 'wpessential' ); ?> →</a><?php endif; ?>
	</div>
</div>
