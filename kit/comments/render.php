<?php
/**
 * Public approved WordPress comments, optional native core reply form.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped Kit template.

$wpe_post_id = absint( $a['postId'] );
if ( ! $wpe_post_id ) {
	$wpe_post_id = (int) get_queried_object_id();
}
$wpe_post   = $wpe_post_id ? get_post( $wpe_post_id ) : null;
$wpe_object = $wpe_post ? get_post_type_object( $wpe_post->post_type ) : null;
if ( ! $wpe_post || 'publish' !== $wpe_post->post_status || ! $wpe_object || ! $wpe_object->public ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-comments__empty">' . esc_html__( 'Select a published, public post to show comments.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_comments = get_comments(
	array(
		'post_id' => $wpe_post_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'number'  => min( 40, max( 1, (int) $a['count'] ) ),
		'order'   => 'ASC' === $a['sort'] ? 'ASC' : 'DESC',
		'orderby' => 'comment_date_gmt',
	)
);
$wpe_title    = trim( (string) $a['title'] );
?>
<section id="<?php echo esc_attr( $id ); ?>" class="wpe-comments">
	<?php if ( $wpe_title ) : ?>
		<h2 class="wpe-comments__heading"><?php echo esc_html( $wpe_title ); ?></h2>
	<?php endif; ?>
	<?php if ( $a['showPostTitle'] ) : ?>
		<p class="wpe-comments__post"><a href="<?php echo esc_url( get_permalink( $wpe_post_id ) ); ?>"><?php echo esc_html( get_the_title( $wpe_post_id ) ); ?></a></p>
	<?php endif; ?>
	<?php if ( $a['showCount'] ) : ?>
		<p class="wpe-comments__count"><?php echo esc_html( sprintf( /* translators: %d: Number of approved comments. */ _n( '%d comment', '%d comments', get_comments_number( $wpe_post_id ), 'wpessential' ), get_comments_number( $wpe_post_id ) ) ); ?></p>
	<?php endif; ?>
	<?php if ( $wpe_comments ) : ?>
		<ol class="wpe-comments__list">
			<?php foreach ( $wpe_comments as $wpe_comment ) : ?>
				<li id="<?php echo esc_attr( $id . '-comment-' . $wpe_comment->comment_ID ); ?>" class="wpe-comments__item">
					<?php if ( $a['showAvatar'] ) : ?>
						<div class="wpe-comments__avatar"><?php echo get_avatar( $wpe_comment, 48, '', '', array( 'class' => 'wpe-comments__avatar-image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core API returns trusted escaped image. ?></div>
					<?php endif; ?>
					<div class="wpe-comments__body">
						<p class="wpe-comments__author"><?php echo esc_html( get_comment_author( $wpe_comment ) ); ?></p>
						<?php if ( $a['showDate'] ) : ?>
							<time class="wpe-comments__date" datetime="<?php echo esc_attr( get_comment_date( 'c', $wpe_comment ) ); ?>"><?php echo esc_html( get_comment_date( get_option( 'date_format' ), $wpe_comment ) ); ?></time>
						<?php endif; ?>
						<div class="wpe-comments__text"><?php echo wp_kses_post( get_comment_text( $wpe_comment ) ); ?></div>
						<a class="wpe-comments__permalink" href="<?php echo esc_url( get_comment_link( $wpe_comment ) ); ?>"><?php esc_html_e( 'Comment link', 'wpessential' ); ?></a>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php else : ?>
		<p class="wpe-comments__empty"><?php echo esc_html( (string) $a['emptyMessage'] ); ?></p>
	<?php endif; ?>
	<?php if ( $a['showForm'] && comments_open( $wpe_post_id ) && post_type_supports( $wpe_post->post_type, 'comments' ) ) : ?>
		<div class="wpe-comments__form">
			<?php
			comment_form(
				array(
					'id_form'     => $id . '-reply-form',
					'id_submit'   => $id . '-reply-submit',
					'title_reply' => __( 'Leave a reply', 'wpessential' ),
				),
				$wpe_post_id
			);
			?>
		</div>
	<?php endif; ?>
</section>
