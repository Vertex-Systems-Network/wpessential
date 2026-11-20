<?php
/**
 * Container renderer. $a = settings, $id = unique element ID, $content = rendered
 * Gutenberg InnerBlocks markup when provided by the dynamic-block callback.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template-local variables included inside Kit::template().

$wpe_tag     = in_array( $a['tag'], array( 'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav' ), true ) ? $a['tag'] : 'div';
$wpe_label   = trim( (string) $a['ariaLabel'] );
$wpe_content = '' !== trim( (string) $content ) ? $content : wp_kses_post( (string) $a['content'] );
$wpe_classes = array(
	'wpe-container',
	'is-' . ( 'grid' === $a['layout'] ? 'grid' : 'flex' ),
	'is-content-' . ( 'full' === $a['contentWidth'] ? 'full' : 'boxed' ),
	'is-overflow-' . ( in_array( $a['overflow'], array( 'visible', 'hidden', 'auto' ), true ) ? $a['overflow'] : 'visible' ),
	'is-hover-' . sanitize_key( (string) $a['hover'] ),
);
if ( ! empty( $a['equalChildren'] ) ) {
	$wpe_classes[] = 'is-equal';
}
?>
<<?php echo esc_html( $wpe_tag ); ?> id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $wpe_classes ) ); ?>"<?php echo 'div' === $wpe_tag && '' !== $wpe_label ? ' role="group"' : ''; ?><?php echo '' !== $wpe_label ? ' aria-label="' . esc_attr( $wpe_label ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Role literal and accessible label escaped. ?>>
	<div class="wpe-container__inner">
		<?php echo $wpe_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- InnerBlocks rendered through WordPress; Elementor rich text sanitized with wp_kses_post. ?>
	</div>
</<?php echo esc_html( $wpe_tag ); ?>>
