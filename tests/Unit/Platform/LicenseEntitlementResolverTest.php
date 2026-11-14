<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Entitlements\LicenseEntitlementResolver;
use WPEssential\Platform\Entitlements\ProductEntitlementState;

final class LicenseEntitlementResolverTest extends TestCase
{
    private const NOW = 1_800_000_000;

    /** @return iterable<string,array{array<string,mixed>,ProductEntitlementState,?string,bool,bool}> */
    public static function cases(): iterable
    {
        $active = ['key' => 'ABCD-1234-EFGH', 'status' => 'active', 'pro' => true, 'verified' => self::NOW - 3600];
        $future = gmdate('Y-m-d', self::NOW + 10 * 86400);
        $past = gmdate('Y-m-d', self::NOW - 10 * 86400);

        yield 'no licence' => [[], ProductEntitlementState::VerificationUnavailable, 'license_missing', true, false];
        yield 'active pro' => [$active + ['expires' => $future], ProductEntitlementState::ProActive, null, true, true];
        yield 'lifetime pro' => [$active + ['expires' => ''], ProductEntitlementState::ProActive, null, true, true];
        yield 'trial' => [$active + ['plan' => 'Pro Trial', 'expires' => $future], ProductEntitlementState::TrialActive, null, true, true];
        yield 'grace' => [$active + ['expires' => $past], ProductEntitlementState::Grace, 'license_in_grace', true, true];
        yield 'free plan licence' => [['pro' => false] + $active, ProductEntitlementState::VerificationUnavailable, 'license_not_pro', true, false];
        yield 'stale check' => [['verified' => self::NOW - 31 * 86400] + $active, ProductEntitlementState::VerificationStale, 'license_check_stale', true, false];
        yield 'legacy data uses checked' => [['verified' => 0, 'checked' => self::NOW - 40 * 86400] + $active, ProductEntitlementState::VerificationStale, 'license_check_stale', true, false];
        yield 'server says expired' => [['status' => 'invalid', 'error' => 'wpe_license_expired'] + $active, ProductEntitlementState::Expired, 'license_expired', true, false];
        yield 'cancelled' => [['status' => 'invalid', 'error' => 'wpe_license_cancelled'] + $active, ProductEntitlementState::Expired, 'license_expired', true, false];
        yield 'past expiry while inactive' => [['status' => 'invalid', 'expires' => $past] + $active, ProductEntitlementState::Expired, 'license_expired', true, false];
        yield 'revoked' => [['status' => 'invalid', 'error' => 'wpe_license_revoked'] + $active, ProductEntitlementState::Suspended, 'license_revoked', true, false];
        yield 'site removed' => [['status' => 'invalid', 'error' => 'wpe_license_site_inactive'] + $active, ProductEntitlementState::VerificationUnavailable, 'license_inactive', true, false];
    }

    /** @param array<string,mixed> $license */
    #[DataProvider('cases')]
    public function testMapsLicenceToEntitlement(array $license, ProductEntitlementState $state, ?string $reason, bool $read, bool $write): void
    {
        $snapshot = LicenseEntitlementResolver::resolve($license, self::NOW);

        self::assertSame($state, $snapshot->state);
        self::assertSame($reason, $snapshot->reason);
        self::assertSame($read, $snapshot->permitsPremiumRead(), 'Pro data stays readable in every licence state.');
        self::assertSame($write, $snapshot->permitsPremiumMutation());
    }
}
