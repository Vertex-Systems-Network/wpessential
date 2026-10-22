<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Demos\DemoCatalog;

final class DemoCatalogTest extends TestCase
{
    public function testManifestEntriesAreNormalizedAndUnsafeValuesDropped(): void
    {
        $demo = DemoCatalog::normalize([
            'id' => 'Base',
            'title' => 'Base',
            'niche' => ['business', 'Bad Niche!'],
            'type' => 'pro',
            'thumbnail' => 'http://insecure.test/a.webp',
            'preview' => ['desktop' => 'https://demo.test/base'],
            'builders' => ['gutenberg' => [], 'elementor' => [], 'divi' => []],
        ]);

        self::assertNotNull($demo);
        self::assertSame('base', $demo['id']);
        self::assertSame(['business'], $demo['niche']);
        self::assertSame('pro', $demo['type']);
        self::assertSame('', $demo['thumbnail']);
        self::assertSame('https://demo.test/base', $demo['preview']);
        self::assertSame(['gutenberg', 'elementor'], $demo['builders']);
    }

    public function testInvalidIdsAreRejected(): void
    {
        self::assertNull(DemoCatalog::normalize(['id' => '../etc']));
        self::assertNull(DemoCatalog::normalize([]));
    }
}
