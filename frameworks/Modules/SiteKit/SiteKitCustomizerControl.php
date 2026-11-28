<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The native Customizer owns the setting; the shared control owns the field UI. */
final class SiteKitCustomizerControl extends \WP_Customize_Control {

	public $type = 'wpessential-site-kit';

	public string $fieldKey = '';

	/** @var array<string,mixed> */
	public array $field = [];

	/** @param array<string,mixed> $field */
	public function __construct( \WP_Customize_Manager $manager, string $id, string $section, string $key, array $field ) {
		$this->fieldKey = $key;
		$this->field    = $field;
		parent::__construct(
			$manager,
			$id,
			[
				'section'  => $section,
				'settings' => [ 'default' => $id ],
			]
		);
	}

	protected function render_content(): void {
		echo '<div class="wpe-ui wpe-sitekit-customizer" data-wpe-setting="' . esc_attr( $this->id ) . '">';
		echo SiteKitAdmin::field( $this->fieldKey, $this->field, $this->value() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- shared escaped control renderer.
		echo '</div>';
	}
}
