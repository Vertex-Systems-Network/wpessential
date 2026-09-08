<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\WordPress;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;

final class WordPressAuthorizationServiceWiringTest extends TestCase
{
    public function testStablePlatformOwnedServiceIds(): void
    {
        self::assertSame('platform.wordpress.capabilities', WordPressAuthorizationServices::CAPABILITY_CHECKER);
        self::assertSame('platform.wordpress.post-resources', WordPressAuthorizationServices::POST_RESOURCES);
    }

    public function testBootstrapReusesOneCapabilityCheckerForPolicyAndServicePublication(): void
    {
        $pluginPath = dirname(__DIR__, 4) . '/frameworks/Bootstrap/Plugin.php';
        $source = file_get_contents($pluginPath);

        self::assertIsString($source);
        self::assertSame(1, substr_count($source, 'new WordPressCapabilityChecker($abilityEnvironment)'));
        self::assertStringContainsString('$abilityPolicy = new PolicyEngine($capabilityChecker);', $source);
        self::assertStringContainsString(
            '$services->set(WordPressAuthorizationServices::CAPABILITY_CHECKER, $capabilityChecker);',
            $source,
        );
    }

    public function testBootstrapPublishesOneNeutralPostResourceAuthorizer(): void
    {
        $pluginPath = dirname(__DIR__, 4) . '/frameworks/Bootstrap/Plugin.php';
        $source = file_get_contents($pluginPath);

        self::assertIsString($source);
        self::assertSame(1, substr_count($source, 'new WordPressPostResourceAuthorizer()'));
        self::assertStringContainsString(
            '$services->set(WordPressAuthorizationServices::POST_RESOURCES, $postResourceAuthorizer);',
            $source,
        );
        self::assertStringNotContainsString('Modules\\Status', $source);
    }
}
