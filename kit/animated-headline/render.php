<?php
/**
 * Animated Headline.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_tag   = in_array( $a['tag'], array( 'h1', 'h2', 'h3', 'p' ), true ) ? $a['tag'] : 'h2';
$wpe_words = array_values( array_filter( array_map( 'trim', explode( '|', (string) $a['words'] ) ), static fn( $w ) => '' !== $w ) );
$wpe_first = $wpe_words[0] ?? '';
?>
<<?php echo esc_html( $wpe_tag ); ?> id="<?php echo esc_attr( $id ); ?>" class="wpe-anim-head">
	<?php echo esc_html( (string) $a['before'] ); ?>
	<span class="screen-reader-text"><?php echo esc_html( implode( ', ', $wpe_words ) ); ?></span>
	<span class="wpe-anim-head__word" aria-hidden="true" data-words="<?php echo esc_attr( (string) wp_json_encode( $wpe_words ) ); ?>"><?php echo esc_html( $wpe_first ); ?></span>
	<?php echo esc_html( (string) $a['after'] ); ?>
</<?php echo esc_html( $wpe_tag ); ?>>
