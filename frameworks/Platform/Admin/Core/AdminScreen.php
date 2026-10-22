<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A top-level WPEssential screen registered under the WPEssential menu by CoreAdminController.
 */
interface AdminScreen {

	public function slug(): string;

	public function title(): string;

	/** Hooks the screen's own admin-post handlers and assets; called once at boot. */
	public function register(): void;

	public function render(): void;
}
