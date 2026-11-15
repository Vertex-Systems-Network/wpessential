<?php
/**
 * Elementor rich-content Container widget.
 *
 * For nested Elementor widgets, prefer Elementor's native Container.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Kit\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Container layout with WYSIWYG content in Elementor.
 */
class Container extends \WPEssential\Platform\Kit\Widget {
	const KIT = 'container';
}
