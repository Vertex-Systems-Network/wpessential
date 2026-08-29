<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Platform\Modules\ModuleManifest;
use WPEssential\Platform\Modules\ModuleState;

final class SmokeModule implements ModuleInterface
{
    public function __construct(private readonly ModuleManifest $manifest) {}

    public function manifest(): ModuleManifest
    {
        return $this->manifest;
    }

    public function register(ServiceRegistryInterface $services): void {}

    public function boot(ServiceRegistryInterface $services): void {}
}

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$kernel = new Kernel();
$kernel->registerModule(new SmokeModule(new ModuleManifest('base', 'Base', '1.0.0')));
$kernel->registerModule(new SmokeModule(new ModuleManifest('child', 'Child', '1.0.0', dependencies: ['base'])));
$order = array_map(static fn (ModuleInterface $module): string => $module->manifest()->id, $kernel->modules()->bootOrder());
expect($order === ['base', 'child'], 'dependency order must be deterministic');
$kernel->boot();
expect($kernel->isBooted(), 'kernel should boot');
expect($kernel->modules()->state('base') === ModuleState::Booted, 'base should boot');
expect($kernel->modules()->state('child') === ModuleState::Booted, 'child should boot');

$degraded = new Kernel();
$degraded->registerModule(new SmokeModule(new ModuleManifest('needs-missing', 'Needs Missing', '1.0.0', dependencies: ['missing'])));
$degraded->registerModule(new SmokeModule(new ModuleManifest('depends-on-degraded', 'Depends On Degraded', '1.0.0', dependencies: ['needs-missing'])));
$degraded->boot();
expect($degraded->modules()->state('needs-missing') === ModuleState::Degraded, 'missing dependency should degrade rather than fatal');
expect($degraded->modules()->state('depends-on-degraded') === ModuleState::Degraded, 'degraded dependency must propagate');

$late = new Kernel();
$late->registerModule(new SmokeModule(new ModuleManifest('late-child', 'Late Child', '1.0.0', dependencies: ['late-base'])));
$late->modules()->bootOrder();
expect($late->modules()->state('late-child') === ModuleState::Degraded, 'missing dependency should degrade during inspection');
$late->registerModule(new SmokeModule(new ModuleManifest('late-base', 'Late Base', '1.0.0')));
$lateOrder = array_map(static fn (ModuleInterface $module): string => $module->manifest()->id, $late->modules()->bootOrder());
expect($lateOrder === ['late-base', 'late-child'], 'late dependency registration should recover degraded state before boot');

$cycle = new Kernel();
$cycle->registerModule(new SmokeModule(new ModuleManifest('a', 'A', '1.0.0', dependencies: ['b'])));
$cycle->registerModule(new SmokeModule(new ModuleManifest('b', 'B', '1.0.0', dependencies: ['a'])));
try {
    $cycle->modules()->bootOrder();
    expect(false, 'cycle must throw');
} catch (RuntimeException) {
    // expected
}

fwrite(STDOUT, "WPEssential kernel smoke PASS\n");
