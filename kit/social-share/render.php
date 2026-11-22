<?php
/**
 * Social Share.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_url   = is_singular() ? (string) get_permalink() : home_url( '/' . ltrim( (string) ( $GLOBALS['wp']->request ?? '' ), '/' ) );
$wpe_title = is_singular() ? (string) get_the_title() : (string) wp_get_document_title();
$wpe_u     = rawurlencode( $wpe_url );
$wpe_t     = rawurlencode( wp_strip_all_tags( $wpe_title ) );
$wpe_links = array(
	'x'        => array( 'X', 'X', 'https://twitter.com/intent/tweet?url=' . $wpe_u . '&text=' . $wpe_t ),
	'facebook' => array( 'Facebook', 'f', 'https://www.facebook.com/sharer/sharer.php?u=' . $wpe_u ),
	'linkedin' => array( 'LinkedIn', 'in', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $wpe_u ),
	'whatsapp' => array( 'WhatsApp', 'WA', 'https://wa.me/?text=' . $wpe_t . '%20' . $wpe_u ),
	'email'    => array( __( 'Email', 'wpessential' ), '@', 'mailto:?subject=' . $wpe_t . '&body=' . $wpe_u ),
);
?>
<div id="<?php echo esc_attr( $id ); ?>" class="wpe-share<?php echo ! empty( $a['showNames'] ) ? ' has-names' : ''; ?>">
	<?php if ( '' !== trim( (string) $a['label'] ) ) : ?><span class="wpe-share__label"><?php echo esc_html( (string) $a['label'] ); ?></span><?php endif; ?>
	<ul class="wpe-share__list">
		<?php foreach ( $wpe_links as $wpe_key => $wpe_link ) : ?>
			<?php if ( ! empty( $a[ 'net_' . $wpe_key ] ) ) : ?>
				<li><a class="wpe-share__btn is-<?php echo esc_attr( $wpe_key ); ?>" href="<?php echo esc_url( $wpe_link[2], array( 'https', 'mailto' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php /* translators: %s: network. */ echo esc_attr( sprintf( __( 'Share on %s', 'wpessential' ), $wpe_link[0] ) ); ?>"><span class="wpe-share__short" aria-hidden="true"><?php echo esc_html( $wpe_link[1] ); ?></span><span class="wpe-share__name"><?php echo esc_html( $wpe_link[0] ); ?></span></a></li>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( ! empty( $a['net_copy'] ) ) : ?>
			<li><button type="button" class="wpe-share__btn is-copy" data-url="<?php echo esc_url( $wpe_url ); ?>" data-done="<?php esc_attr_e( 'Link copied', 'wpessential' ); ?>" data-fail="<?php esc_attr_e( 'Copy failed — copy the address bar link', 'wpessential' ); ?>"><span class="wpe-share__short" aria-hidden="true">⧉</span><span class="wpe-share__name"><?php esc_html_e( 'Copy link', 'wpessential' ); ?></span></button><span class="wpe-share__status screen-reader-text" role="status"></span></li>
		<?php endif; ?>
	</ul>
</div>
