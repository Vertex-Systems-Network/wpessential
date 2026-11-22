<?php
/**
 * Table of Contents.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Included in Kit template scope.

$wpe_levels = in_array( $a['levels'], array( 'h2', 'h2,h3', 'h2,h3,h4' ), true ) ? $a['levels'] : 'h2,h3';
?>
<details id="<?php echo esc_attr( $id ); ?>" class="wpe-toc" open data-levels="<?php echo esc_attr( $wpe_levels ); ?>">
	<summary><?php echo esc_html( (string) $a['heading'] ); ?></summary>
	<nav aria-label="<?php echo esc_attr( (string) $a['heading'] ); ?>"><ol class="wpe-toc__list"><?php if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) : ?><li><?php esc_html_e( 'Page headings are listed here on the site.', 'wpessential' ); ?></li><?php endif; ?></ol></nav>
</details>
