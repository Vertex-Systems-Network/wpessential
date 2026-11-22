<?php
/**
 * Steps.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_tag = in_array( $a['titleTag'], array( 'h2', 'h3', 'h4', 'p' ), true ) ? $a['titleTag'] : 'h3';
?>
<ol id="<?php echo esc_attr( $id ); ?>" class="wpe-steps">
	<?php foreach ( (array) $a['items'] as $wpe_n => $wpe_item ) : ?>
		<li class="wpe-steps__item">
			<span class="wpe-steps__num" aria-hidden="true"><?php echo esc_html( (string) ( $wpe_n + 1 ) ); ?></span>
			<div class="wpe-steps__body">
				<<?php echo esc_html( $wpe_tag ); ?> class="wpe-steps__title"><?php echo esc_html( (string) ( $wpe_item['title'] ?? '' ) ); ?></<?php echo esc_html( $wpe_tag ); ?>>
				<?php if ( ! empty( $wpe_item['text'] ) ) : ?><p class="wpe-steps__text"><?php echo wp_kses_post( (string) $wpe_item['text'] ); ?></p><?php endif; ?>
			</div>
		</li>
	<?php endforeach; ?>
</ol>
