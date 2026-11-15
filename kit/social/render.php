<?php
/**
 * Static platform SVG path data, not externally uploaded SVG markup.
 *
 * @package WPEssential
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- Scoped template.

$wpe_paths  = array(
	'instagram' => 'M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5zm5 5a5 5 0 1 0 0 10 5 5 0 0 0 0-10zm6-2h.01',
	'facebook'  => 'M14 22v-8h3l.5-4H14V8c0-1 .5-2 2-2h2V2a20 20 0 0 0-3-.2C11 1.8 10 4.1 10 7v3H7v4h3v8',
	'linkedin'  => 'M3 9v12M3 4v1M9 21V9h4v2c1-2 8-3 8 4v6M9 13v-4',
	'youtube'   => 'M3 7q0-2 3-2h12q3 0 3 2v10q0 2-3 2H6q-3 0-3-2zm7 2 6 3-6 3z',
	'x'         => 'M3 3 21 21M21 3 3 21M6 3h4l8 18h-4z',
	'tiktok'    => 'M14 3v12a4 4 0 1 1-4-4M14 3c1 4 3 5 7 5',
	'pinterest' => 'M8 21l3-12M9 14a6 6 0 1 1 4 4',
	'github'    => 'M12 2a10 10 0 0 0-3 19v-3q-4 1-5-3m13 6v-3a8 8 0 0 0 3-7q1-3-1-5l-2 1a12 12 0 0 0-10 0L5 6q-2 2-1 5a8 8 0 0 0 3 7v3',
	'whatsapp'  => 'M4 20 5 16a9 9 0 1 1 4 3zM9 7q-2 2 2 6t6 2l-2-2',
	'telegram'  => 'm2 12 20-9-5 18-5-7-4 4-1-7z',
	'website'   => 'M2 12h20M12 2a10 10 0 0 0 0 20M12 2a10 10 0 0 1 0 20M4 5h16M4 19h16',
);
$wpe_labels = array(
	'instagram' => 'Instagram',
	'facebook'  => 'Facebook',
	'linkedin'  => 'LinkedIn',
	'youtube'   => 'YouTube',
	'x'         => 'X',
	'tiktok'    => 'TikTok',
	'pinterest' => 'Pinterest',
	'github'    => 'GitHub',
	'whatsapp'  => 'WhatsApp',
	'telegram'  => 'Telegram',
	'website'   => 'Website',
);
$wpe_links  = array();
$wpe_items  = is_array( $a['items'] ) ? array_slice( $a['items'], 0, 20 ) : array();
foreach ( $wpe_items as $wpe_item ) {
	if ( ! is_array( $wpe_item ) ) {
		continue;
	}
	$wpe_platform = sanitize_key( (string) ( $wpe_item['platform'] ?? '' ) );
	$wpe_url      = esc_url( trim( (string) ( $wpe_item['url'] ?? '' ) ), array( 'https' ) );
	if ( ! isset( $wpe_paths[ $wpe_platform ] ) || 'https' !== wp_parse_url( $wpe_url, PHP_URL_SCHEME ) || ! wp_parse_url( $wpe_url, PHP_URL_HOST ) ) {
		continue;
	}
	$wpe_label   = trim( (string) ( $wpe_item['label'] ?? '' ) );
	$wpe_links[] = array(
		'platform' => $wpe_platform,
		'url'      => $wpe_url,
		'label'    => '' !== $wpe_label ? $wpe_label : $wpe_labels[ $wpe_platform ],
	);
}
if ( ! $wpe_links ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<p class="wpe-social__empty">' . esc_html__( 'Add HTTPS social profile URLs.', 'wpessential' ) . '</p>';
	}
	return;
}
$wpe_shape = in_array( $a['shape'], array( 'circle', 'square', 'pill' ), true ) ? $a['shape'] : 'circle';
?>
<nav id="<?php echo esc_attr( $id ); ?>" class="wpe-social is-<?php echo esc_attr( $wpe_shape ); ?>" aria-label="<?php echo esc_attr( '' !== trim( (string) $a['heading'] ) ? $a['heading'] : __( 'Social links', 'wpessential' ) ); ?>">
	<ul class="wpe-social__list">
		<?php foreach ( $wpe_links as $wpe_item ) : ?>
			<li>
				<a class="wpe-social__link" href="<?php echo esc_url( $wpe_item['url'] ); ?>" aria-label="<?php echo esc_attr( $wpe_item['label'] ); ?>"<?php echo $a['newTab'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
					<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo esc_attr( $wpe_paths[ $wpe_item['platform'] ] ); ?>"></path></svg>
					<?php if ( $a['showLabels'] ) : ?>
						<span class="wpe-social__label"><?php echo esc_html( $wpe_item['label'] ); ?></span>
					<?php endif; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
