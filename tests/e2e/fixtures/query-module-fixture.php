<?php
/**
 * Plugin Name: WPE Query E2E Fixture
 */

declare(strict_types=1);

add_action('plugins_loaded', static function (): void {
    if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) {
        return;
    }

    \WPEssential\Bootstrap\Plugin::setModuleActivationPolicy(
        new class implements \WPEssential\Contracts\ModuleActivationPolicyInterface {
            public function allows(\WPEssential\Platform\Modules\ModuleManifest $manifest): bool
            {
                return $manifest->edition === 'free' || $manifest->id === 'query';
            }
        },
    );
    \WPEssential\Bootstrap\Plugin::registerModule(new \WPEssential\Modules\Query\QueryModule());
}, -200);
