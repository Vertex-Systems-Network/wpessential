<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Compatibility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Compatibility\LocalCompatibilityPreflight;

final class LocalCompatibilityPreflightTest extends TestCase
{
    public function testCompatibleExactBoundariesAllowPremiumBoot(): void
    {
        $result = LocalCompatibilityPreflight::evaluate($this->free(), $this->pro());

        self::assertSame(LocalCompatibilityPreflight::COMPATIBLE, $result['state']);
        self::assertSame('pair', $result['dimension']);
        self::assertSame('compatible_local_pair', $result['reason']);
        self::assertSame('none', $result['remediation']);
        self::assertTrue($result['premium_boot_allowed']);
        self::assertTrue($result['premium_migrations_allowed']);
    }

    /**
     * @param array<string,mixed> $freeChanges
     * @param array<string,mixed> $proChanges
     */
    #[DataProvider('incompatibleCases')]
    public function testIncompatibleMetadataFailsClosed(
        array $freeChanges,
        array $proChanges,
        string $expectedState,
    ): void {
        $result = LocalCompatibilityPreflight::evaluate(
            array_replace($this->free(), $freeChanges),
            array_replace($this->pro(), $proChanges),
        );

        self::assertSame($expectedState, $result['state']);
        self::assertFalse($result['premium_boot_allowed']);
        self::assertFalse($result['premium_migrations_allowed']);
        self::assertNotSame('none', $result['remediation']);
    }

    /** @return iterable<string,array{array<string,mixed>,array<string,mixed>,string}> */
    public static function incompatibleCases(): iterable
    {
        yield 'free missing' => [
            ['present' => false],
            [],
            LocalCompatibilityPreflight::FREE_MISSING,
        ];
        yield 'free bootstrap incomplete' => [
            ['bootstrap_complete' => false],
            [],
            LocalCompatibilityPreflight::FREE_BOOTSTRAP_INCOMPLETE,
        ];
        yield 'pro package incomplete' => [
            [],
            ['package_complete' => false],
            LocalCompatibilityPreflight::PRO_PACKAGE_INCOMPLETE,
        ];
        yield 'contradictory pro free range' => [
            [],
            ['min_free_version' => '0.2.0', 'max_free_version' => '0.1.0'],
            LocalCompatibilityPreflight::PRO_METADATA_INVALID,
        ];
        yield 'contradictory pro api range' => [
            [],
            ['min_platform_api' => '0.2.0', 'max_platform_api' => '0.1.0'],
            LocalCompatibilityPreflight::PRO_METADATA_INVALID,
        ];
        yield 'invalid pro schema range' => [
            [],
            ['min_platform_schema' => 2, 'max_platform_schema' => 1],
            LocalCompatibilityPreflight::PRO_METADATA_INVALID,
        ];
        yield 'free version missing' => [
            ['version' => null],
            [],
            LocalCompatibilityPreflight::FREE_METADATA_MISSING,
        ];
        yield 'free version malformed' => [
            ['version' => 'not-a-version'],
            [],
            LocalCompatibilityPreflight::FREE_VERSION_INVALID,
        ];
        yield 'free version too old' => [
            ['version' => '0.0.9'],
            [],
            LocalCompatibilityPreflight::FREE_VERSION_TOO_OLD,
        ];
        yield 'free version too new' => [
            ['version' => '0.2.0'],
            [],
            LocalCompatibilityPreflight::FREE_VERSION_TOO_NEW,
        ];
        yield 'platform api missing' => [
            ['platform_api' => null],
            [],
            LocalCompatibilityPreflight::PLATFORM_API_MISSING,
        ];
        yield 'platform api malformed' => [
            ['platform_api' => '0.1.0-dev'],
            [],
            LocalCompatibilityPreflight::PLATFORM_API_INVALID,
        ];
        yield 'platform api too old' => [
            ['platform_api' => '0.0.9'],
            [],
            LocalCompatibilityPreflight::PLATFORM_API_TOO_OLD,
        ];
        yield 'platform api too new' => [
            ['platform_api' => '0.2.0'],
            [],
            LocalCompatibilityPreflight::PLATFORM_API_TOO_NEW,
        ];
        yield 'platform schema missing' => [
            ['platform_schema' => null],
            [],
            LocalCompatibilityPreflight::PLATFORM_SCHEMA_MISSING,
        ];
        yield 'platform schema malformed' => [
            ['platform_schema' => '1'],
            [],
            LocalCompatibilityPreflight::PLATFORM_SCHEMA_INVALID,
        ];
        yield 'platform schema too old' => [
            ['platform_schema' => 0],
            [],
            LocalCompatibilityPreflight::PLATFORM_SCHEMA_TOO_OLD,
        ];
        yield 'platform schema too new' => [
            ['platform_schema' => 2],
            [],
            LocalCompatibilityPreflight::PLATFORM_SCHEMA_TOO_NEW,
        ];
    }

    /** @return array<string,mixed> */
    private function free(): array
    {
        return [
            'present' => true,
            'bootstrap_complete' => true,
            'version' => '0.1.0-dev',
            'platform_api' => '0.1.0',
            'platform_schema' => 1,
        ];
    }

    /** @return array<string,mixed> */
    private function pro(): array
    {
        return [
            'package_complete' => true,
            'version' => '0.1.0-dev',
            'min_free_version' => '0.1.0-dev',
            'max_free_version' => '0.1.0-dev',
            'min_platform_api' => '0.1.0',
            'max_platform_api' => '0.1.0',
            'min_platform_schema' => 1,
            'max_platform_schema' => 1,
            'schema' => 1,
        ];
    }
}
