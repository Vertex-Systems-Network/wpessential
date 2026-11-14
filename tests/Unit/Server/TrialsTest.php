<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Server;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPEssential\Server\Trials;

final class TrialsTest extends TestCase
{
    private const DAY = 86400;

    public static function setUpBeforeClass(): void
    {
        if (!defined('DAY_IN_SECONDS')) {
            define('DAY_IN_SECONDS', self::DAY);
        }
        require_once dirname(__DIR__, 3) . '/server/wpessential-server/src/Trials.php';
        require_once dirname(__DIR__, 3) . '/server/wpessential-server/src/Licenses.php';
    }

    /** @return iterable<string,array{string,bool,bool,bool,bool,bool,?string}> */
    public static function eligibility(): iterable
    {
        yield 'new live site, new account' => ['shop.example.com', true, false, false, false, false, null];
        yield 'no site address' => ['', true, false, false, false, false, 'domain'];
        yield 'request not proved to come from the site' => ['shop.example.com', false, false, false, false, false, 'trial_site_unverified'];
        yield 'staging site' => ['staging.example.com', true, true, false, false, false, 'trial_staging'];
        yield 'account already has Pro' => ['shop.example.com', true, false, false, false, true, 'trial_has_pro'];
        yield 'site had a trial (other account)' => ['shop.example.com', true, false, true, false, false, 'trial_used_site'];
        yield 'account had a trial (other site)' => ['other.example.com', true, false, false, true, false, 'trial_used_account'];
    }

    #[DataProvider('eligibility')]
    public function testOneTrialPerSiteAndPerAccount(string $domain, bool $verified, bool $staging, bool $siteUsed, bool $accountUsed, bool $hasPro, ?string $expected): void
    {
        self::assertSame($expected, Trials::refusal($domain, $verified, $staging, $siteUsed, $accountUsed, $hasPro));
    }

    public function testTrialLastsThirtyDays(): void
    {
        self::assertSame('2026-12-31 00:00:00', Trials::ends_at(strtotime('2026-12-01 00:00:00 UTC')));
    }

    public function testRemindersAtSevenAndTwoDaysLeftOnly(): void
    {
        $end = 1_800_000_000;

        self::assertSame(0, Trials::reminder_stage($end, $end - 10 * self::DAY), 'Too early.');
        self::assertSame(7, Trials::reminder_stage($end, $end - 7 * self::DAY));
        self::assertSame(7, Trials::reminder_stage($end, $end - 3 * self::DAY));
        self::assertSame(2, Trials::reminder_stage($end, $end - 2 * self::DAY));
        self::assertSame(2, Trials::reminder_stage($end, $end - 3600));
        self::assertSame(0, Trials::reminder_stage($end, $end + 1), 'Ended: the expiry email covers it.');
    }

    public function testOnlyTheTrialPlanCountsAsTrial(): void
    {
        self::assertTrue(Trials::is_trial(['plan' => 'pro_trial']));
        self::assertFalse(Trials::is_trial(['plan' => 'pro_yearly']));
        self::assertFalse(Trials::is_trial(null));
    }

    public function testOneTrialRunsOnOneLiveSiteAndThreeCopies(): void
    {
        $live = ['is_staging' => 0];
        $copy = ['is_staging' => 1];

        self::assertNull(Trials::activation_refusal(false, []), 'First live site.');
        self::assertNull(Trials::activation_refusal(true, [$live]), 'A copy next to the live site.');
        self::assertSame('trial_live_used', Trials::activation_refusal(false, [$live]), 'A second live site.');
        self::assertNull(Trials::activation_refusal(true, [$live, $copy, $copy]));
        self::assertSame('trial_copies_used', Trials::activation_refusal(true, [$live, $copy, $copy, $copy]));
        self::assertNull(Trials::activation_refusal(false, [$copy, $copy]), 'Started on a local copy, then the live site.');
    }

    public function testLocalSitesAreRecognisedButPublicStagingHostsAreNot(): void
    {
        foreach (['localhost', 'mysite.local', 'shop.test', 'app.localhost', '127.0.0.1', '192.168.1.20', '10.0.0.5', '172.16.0.5', '172.31.255.1'] as $domain) {
            self::assertTrue(\WPEssential\Server\Licenses::looks_local($domain), $domain);
        }
        foreach (['staging.example.com', 'example.com', 'site.wpengine.com', 'mylocal.com', 'test.example.com', '172.32.0.1', '172.15.0.1'] as $domain) {
            self::assertFalse(\WPEssential\Server\Licenses::looks_local($domain), $domain);
        }
    }
}
