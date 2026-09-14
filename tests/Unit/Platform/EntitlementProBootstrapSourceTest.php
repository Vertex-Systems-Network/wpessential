<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;

final class EntitlementProBootstrapSourceTest extends TestCase
{
    public function testCompatibilityPreflightRunsBeforeFreeRuntimeAndPremiumContribution(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/wpessential-pro.php');
        self::assertIsString($source);

        $preflight = strpos($source, '::evaluateRuntime');
        $entitlement = strpos($source, 'ProductEntitlementState::VerificationUnavailable');
        $policy = strpos($source, 'Plugin::setModuleActivationPolicy');
        $contribution = strpos($source, 'Plugin::registerModule');

        self::assertIsInt($preflight);
        self::assertIsInt($entitlement);
        self::assertIsInt($policy);
        self::assertIsInt($contribution);
        self::assertLessThan($entitlement, $preflight);
        self::assertLessThan($policy, $preflight);
        self::assertLessThan($contribution, $policy);
    }

    public function testProAutoloadIsFailClosedUntilCanonicalRequestLocalCompatibilityPasses(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/wpessential-pro.php');
        self::assertIsString($source);

        self::assertStringContainsString("WPEssential\\\\Modules\\\\Compatibility\\\\", $source);
        self::assertStringContainsString('$GLOBALS[\'wpe_pro_compatibility_result\']', $source);
        self::assertStringContainsString("(\$compatibility['state'] ?? '') !== 'compatible'", $source);
        self::assertStringContainsString('$GLOBALS[\'wpe_pro_compatibility_result\'] = $result', $source);
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
