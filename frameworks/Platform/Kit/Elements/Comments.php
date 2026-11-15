<?php
/**
 * Elementor Comments widget.
 *
 * @package WPEssential
 */

namespace WPEssential\Platform\Kit\Elements;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Native WordPress comment rendering and submission. */
class Comments extends \WPEssential\Platform\Kit\Widget {
	const KIT = 'comments';
}
