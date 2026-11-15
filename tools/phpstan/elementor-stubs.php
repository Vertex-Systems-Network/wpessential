<?php
/**
 * Minimal Elementor API used by the kit (frameworks/Platform/Kit), for PHPStan only. The real
 * classes come from the Elementor plugin; the kit loads them only when Elementor is active.
 *
 * @phpcs:disable
 */

namespace Elementor;

class Controls_Manager {
	const TAB_CONTENT  = 'content';
	const TAB_STYLE    = 'style';
	const TAB_ADVANCED = 'advanced';
	const TEXT         = 'text';
	const TEXTAREA     = 'textarea';
	const WYSIWYG      = 'wysiwyg';
	const CODE         = 'code';
	const SELECT       = 'select';
	const SWITCHER     = 'switcher';
	const SLIDER       = 'slider';
	const COLOR        = 'color';
	const DIMENSIONS   = 'dimensions';
	const MEDIA        = 'media';
	const ICONS        = 'icons';
	const REPEATER     = 'repeater';
}

abstract class Controls_Stack {
	/** @param array<string,mixed> $args */
	public function start_controls_section( string $id, array $args = [] ): void {}
	public function end_controls_section(): void {}
	/** @param array<string,mixed> $args @param array<string,mixed> $options */
	public function add_control( string $id, array $args, array $options = [] ): bool { return true; }
	/** @param array<string,mixed> $args @param array<string,mixed> $options */
	public function add_responsive_control( string $id, array $args, array $options = [] ): void {}
	/** @param array<string,mixed> $args @param array<string,mixed> $options */
	public function add_group_control( string $group_name, array $args = [], array $options = [] ): void {}
	/** @return array<string,mixed> */
	public function get_controls( ?string $control_id = null ) { return []; }
	/** @return array<string,mixed> */
	public function get_settings_for_display( ?string $setting_key = null ) { return []; }
}

abstract class Widget_Base extends Controls_Stack {
	/** @param array<string,mixed> $data @param array<string,mixed>|null $args */
	public function __construct( array $data = [], ?array $args = null ) {}
}

class Repeater extends Controls_Stack {}

class Group_Control_Base {
	public static function get_type(): string { return ''; }
}
class Group_Control_Typography extends Group_Control_Base {}
class Group_Control_Border extends Group_Control_Base {}
class Group_Control_Box_Shadow extends Group_Control_Base {}
class Group_Control_Text_Shadow extends Group_Control_Base {}
class Group_Control_Text_Stroke extends Group_Control_Base {}
class Group_Control_Background extends Group_Control_Base {}
class Group_Control_Css_Filter extends Group_Control_Base {}

class Icons_Manager {
	/** @param array<string,mixed> $icon @param array<string,mixed> $attributes */
	public static function render_icon( array $icon, array $attributes = [], string $tag = 'i' ): bool|string { return true; }
}

class Elements_Manager {
	/** @param array<string,mixed> $properties */
	public function add_category( string $category_name, array $category_properties ): void {}
}

class Widgets_Manager {
	public function register( Widget_Base $widget_instance ): bool { return true; }
}
