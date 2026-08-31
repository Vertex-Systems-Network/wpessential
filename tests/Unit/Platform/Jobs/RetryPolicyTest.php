<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Jobs;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Jobs\RetryPolicy;

final class RetryPolicyTest extends TestCase
{
    public static function delayCases(): iterable
    {
        yield 'first attempt' => [1, 30];
        yield 'second attempt' => [2, 60];
        yield 'third attempt' => [3, 120];
        yield 'maximum delay cap' => [20, 3600];
    }

    #[DataProvider('delayCases')]
    public function testDelayAfterAttemptUsesExponentialBackoffWithCap(int $attempt, int $expected): void
    {
        $policy = new RetryPolicy();

        self::assertSame($expected, $policy->delayAfterAttempt($attempt));
    }

    public function testRejectsNonPositiveAttempt(): void
    {
        $policy = new RetryPolicy();

        $this->expectException(InvalidArgumentException::class);
        $policy->delayAfterAttempt(0);
    }

    public function testRejectsInvalidPolicyConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new RetryPolicy(maxAttempts: 0);
    }
}
