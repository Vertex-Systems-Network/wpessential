<?php
/**
 * Video and audio renderer. $a contains the resolved Kit settings.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit::template() scope.

$wpe_source  = in_array( $a['source'], array( 'file', 'youtube', 'vimeo' ), true ) ? $a['source'] : 'file';
$wpe_type    = 'audio' === $a['mediaType'] ? 'audio' : 'video';
$wpe_url     = esc_url( trim( (string) $a['mediaUrl'] ), array( 'https', 'http' ) );
$wpe_title   = trim( (string) $a['title'] );
$wpe_title   = '' === $wpe_title ? ( 'audio' === $wpe_type ? __( 'Audio', 'wpessential' ) : __( 'Video', 'wpessential' ) ) : $wpe_title;
$wpe_embed   = '';
$wpe_poster  = '';
$wpe_caption = trim( (string) $a['description'] );
$wpe_ratio   = in_array( $a['ratio'], array( '16/9', '4/3', '1/1', '9/16' ), true ) ? $a['ratio'] : '16/9';
$wpe_fit     = 'cover' === $a['fit'] ? 'cover' : 'contain';
$wpe_preload = 'none' === $a['preload'] ? 'none' : 'metadata';
$wpe_kind    = 'file' === $wpe_source ? $wpe_type : 'embed';
$wpe_class   = 'wpe-video is-' . sanitize_key( $wpe_kind );

if ( '' !== $wpe_url && 'file' !== $wpe_source ) {
	$wpe_host = strtolower( (string) wp_parse_url( $wpe_url, PHP_URL_HOST ) );
	$wpe_path = trim( (string) wp_parse_url( $wpe_url, PHP_URL_PATH ), '/' );
	$wpe_id   = '';
	if ( 'youtube' === $wpe_source && in_array( $wpe_host, array( 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be' ), true ) ) {
		if ( in_array( $wpe_host, array( 'youtu.be', 'www.youtu.be' ), true ) ) {
			$wpe_id = explode( '/', $wpe_path )[0];
		} elseif ( 'watch' === $wpe_path ) {
			$wpe_query = array();
			parse_str( (string) wp_parse_url( $wpe_url, PHP_URL_QUERY ), $wpe_query );
			$wpe_id = is_string( $wpe_query['v'] ?? null ) ? $wpe_query['v'] : '';
		} elseif ( preg_match( '~^(?:embed|shorts|live)/([A-Za-z0-9_-]{11})$~', $wpe_path, $wpe_match ) ) {
			$wpe_id = $wpe_match[1];
		}
		if ( preg_match( '/^[A-Za-z0-9_-]{11}$/', $wpe_id ) ) {
			$wpe_embed = 'https://www.youtube-nocookie.com/embed/' . $wpe_id . '?autoplay=1';
		}
	} elseif ( 'vimeo' === $wpe_source && in_array( $wpe_host, array( 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com' ), true ) ) {
		if ( preg_match( '~^(?:video/)?([0-9]{1,16})$~', $wpe_path, $wpe_match ) ) {
			$wpe_embed = 'https://player.vimeo.com/video/' . $wpe_match[1] . '?dnt=1&autoplay=1';
		}
	}
}
if ( is_array( $a['poster'] ) ) {
	if ( ! empty( $a['poster']['id'] ) && wp_attachment_is_image( (int) $a['poster']['id'] ) ) {
		$wpe_poster = (string) wp_get_attachment_image_url( (int) $a['poster']['id'], 'large' );
	} elseif ( ! empty( $a['poster']['url'] ) ) {
		$wpe_poster = esc_url( (string) $a['poster']['url'], array( 'https', 'http' ) );
	}
}
$wpe_valid = 'file' === $wpe_source ? '' !== $wpe_url : '' !== $wpe_embed;
if ( ! $wpe_valid ) {
	// Invalid / empty embeds must never result in untrusted iframe URLs.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-video__empty">' . esc_html( (string) $a['emptyText'] ) . '</p>';
	}
	return;
}
?>
<figure id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $wpe_class ); ?>" style="--wpek-ratio:<?php echo esc_attr( $wpe_ratio ); ?>;--wpek-fit:<?php echo esc_attr( $wpe_fit ); ?>">
	<div class="wpe-video__frame">
		<?php if ( 'embed' === $wpe_kind ) : ?>
			<div class="wpe-video__embed" data-wpe-embed="<?php echo esc_attr( $wpe_embed ); ?>" data-title="<?php echo esc_attr( $wpe_title ); ?>">
				<button class="wpe-video__play" type="button" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: video title. */ __( 'Play %s', 'wpessential' ), $wpe_title ) ); ?>">
					<?php if ( $wpe_poster ) : ?>
						<img class="wpe-video__poster" src="<?php echo esc_url( $wpe_poster ); ?>" alt="" loading="lazy">
					<?php endif; ?>
					<span class="wpe-video__play-icon" aria-hidden="true">&#9654;</span>
					<span><?php esc_html_e( 'Play video', 'wpessential' ); ?></span>
				</button>
			</div>
		<?php elseif ( 'audio' === $wpe_type ) : ?>
			<audio class="wpe-video__native-audio" controls preload="<?php echo esc_attr( $wpe_preload ); ?>"<?php echo $a['loop'] ? ' loop' : ''; ?>>
				<source src="<?php echo esc_url( $wpe_url ); ?>">
				<?php esc_html_e( 'Your browser does not support audio playback.', 'wpessential' ); ?>
			</audio>
		<?php else : ?>
			<video class="wpe-video__native-video" controls playsinline preload="<?php echo esc_attr( $wpe_preload ); ?>"<?php echo $a['loop'] ? ' loop' : ''; ?><?php echo $a['muted'] ? ' muted' : ''; ?><?php echo $wpe_poster ? ' poster="' . esc_url( $wpe_poster ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Safe literal attributes and escaped poster URL. ?>>
				<source src="<?php echo esc_url( $wpe_url ); ?>">
				<?php
				$wpe_track = esc_url( trim( (string) $a['captionsUrl'] ), array( 'https', 'http' ) );
				$wpe_lang  = trim( (string) $a['captionsLanguage'] );
				$wpe_lang  = preg_match( '/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/i', $wpe_lang ) ? $wpe_lang : 'en';
				if ( '' !== $wpe_track ) :
					?>
					<track kind="captions" src="<?php echo esc_url( $wpe_track ); ?>" srclang="<?php echo esc_attr( $wpe_lang ); ?>" label="<?php echo esc_attr( (string) $a['captionsLabel'] ); ?>">
				<?php endif; ?>
				<?php esc_html_e( 'Your browser does not support video playback.', 'wpessential' ); ?>
			</video>
		<?php endif; ?>
	</div>
	<?php if ( 'embed' === $wpe_kind ) : ?>
		<a class="wpe-video__external" href="<?php echo esc_url( $wpe_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Watch on the original site', 'wpessential' ); ?></a>
	<?php endif; ?>
	<?php if ( '' !== $wpe_caption ) : ?>
		<figcaption class="wpe-video__description"><?php echo esc_html( $wpe_caption ); ?></figcaption>
	<?php endif; ?>
</figure>
