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
}
