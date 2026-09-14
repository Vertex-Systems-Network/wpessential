<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;

final class EntitlementProBootstrapSourceTest extends TestCase
{
    public function testPolicyIsInstalledBeforePremiumModuleContribution(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/wpessential-pro.php');
        self::assertIsString($source);

        $policy = strpos($source, 'Plugin::setModuleActivationPolicy');
        $contribution = strpos($source, 'Plugin::registerModule');

        self::assertIsInt($policy);
        self::assertIsInt($contribution);
        self::assertLessThan($contribution, $policy);
    }

    public function testBootstrapContainsNoRemoteLicenseOrBillingTransport(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/wpessential-pro.php');
        self::assertIsString($source);

        foreach (['wp_remote_get', 'wp_remote_post', 'curl_', 'license_key', 'billing_token'] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }
}
