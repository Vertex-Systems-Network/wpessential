<?php
/**
 * Accessible notice with a progressive dismiss button.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Template scope.

$wpe_type  = in_array( $a['type'], array( 'info', 'success', 'warning', 'error' ), true ) ? $a['type'] : 'info';
$wpe_style = in_array( $a['style'], array( 'boxed', 'accent', 'minimal' ), true ) ? $a['style'] : 'boxed';
$wpe_role  = 'error' === $wpe_type || 'warning' === $wpe_type ? 'alert' : 'status';
$wpe_key   = md5( (string) $a['title'] . '|' . (string) $a['content'] . '|' . $wpe_type );
$wpe_icons = array(
	'info'    => 'i',
	'success' => '✓',
	'warning' => '!',
	'error'   => '×',
);
?>
<aside id="<?php echo esc_attr( $id ); ?>" class="wpe-alert is-<?php echo esc_attr( $wpe_type ); ?> is-<?php echo esc_attr( $wpe_style ); ?>" role="<?php echo esc_attr( $wpe_role ); ?>" data-wpe-alert="1" data-remember="<?php echo $a['remember'] ? '1' : '0'; ?>" data-alert-key="<?php echo esc_attr( $wpe_key ); ?>">
	<?php if ( $a['icon'] ) : ?>
		<span class="wpe-alert__icon" aria-hidden="true"><?php echo esc_html( $wpe_icons[ $wpe_type ] ); ?></span>
	<?php endif; ?>
	<div class="wpe-alert__body">
		<?php if ( '' !== trim( (string) $a['title'] ) ) : ?>
			<p class="wpe-alert__title"><?php echo esc_html( (string) $a['title'] ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== trim( (string) $a['content'] ) ) : ?>
			<div class="wpe-alert__content"><?php echo wp_kses_post( (string) $a['content'] ); ?></div>
		<?php endif; ?>
	</div>
	<?php if ( $a['dismissible'] ) : ?>
		<button type="button" class="wpe-alert__dismiss" aria-label="<?php echo esc_attr( (string) $a['dismissLabel'] ); ?>" hidden>&times;</button>
	<?php endif; ?>
</aside>
