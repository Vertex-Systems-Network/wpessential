<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Connect\LicenseService;
use WPEssential\Platform\Admin\Connect\Mcp\SettingsAbilities;
use WPEssential\Platform\Admin\Core\ModuleToggleStore;
use WPEssential\Platform\Admin\Elements\ElementRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;

/** Free-side Framework abilities must never reuse a name a module (Free or Pro) registers. */
final class AbilityNameCollisionTest extends TestCase
{
    public function testFrameworkAbilitiesDoNotCollideWithModuleAbilities(): void
    {
        $abilities = new AbilityRegistry(new PolicyEngine(new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        }));
        (new SettingsAbilities(new ModuleToggleStore(), new ElementRegistry(), new LicenseService()))->register($abilities);
        $framework = array_map(static fn ($descriptor): string => $descriptor->name, $abilities->descriptors());
        self::assertNotEmpty($framework);

        $moduleNames = [];
        $root = dirname(__DIR__, 5) . '/frameworks/Modules';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                preg_match_all("#'(wpessential/[a-z0-9-]+/[a-z0-9-]+)'#", (string) file_get_contents($file->getPathname()), $matches);
                $moduleNames = array_merge($moduleNames, $matches[1]);
            }
        }
        self::assertContains('wpessential/settings/get', $moduleNames, 'the scan must see Pro module ability names');
        self::assertSame([], array_values(array_intersect($framework, $moduleNames)));
    }
}
