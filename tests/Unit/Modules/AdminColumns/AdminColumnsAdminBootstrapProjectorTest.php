<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsAdminBootstrapProjector;
use WPEssential\Platform\DataSources\DataSourceAvailability;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class AdminColumnsAdminBootstrapProjectorTest extends TestCase
{
    public function testProjectsDeterministicReadOnlyTargetsAndNativeSources(): void
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: [
                'post.title' => 'string',
                'post.id' => 'integer',
                'post.date' => 'datetime',
            ],
            predicates: ['eq', 'contains'],
            sortModes: ['field'],
            paginationModes: ['offset'],
        ));

        $projector = new AdminColumnsAdminBootstrapProjector(
            $registry,
            static fn (): array => [
                'post' => (object) [
                    'name' => 'post',
                    'labels' => (object) ['name' => 'Posts'],
                ],
                'page' => (object) [
                    'name' => 'page',
                    'labels' => (object) ['name' => 'Pages'],
                ],
                'bad target' => (object) [
                    'name' => 'bad target',
                    'labels' => (object) ['name' => 'Invalid'],
                ],
            ],
        );

        $bootstrap = $projector->project();

        self::assertSame('columns', $bootstrap['surface']);
        self::assertSame(1, $bootstrap['contractVersion']);
        self::assertSame(['page', 'post'], array_column($bootstrap['targets'], 'key'));
        self::assertSame(['post.date', 'post.id', 'post.title'], array_column($bootstrap['sources'], 'reference'));
        self::assertSame(['date', 'text'], $bootstrap['sources'][0]['formats']);
        self::assertSame(['number', 'text'], $bootstrap['sources'][1]['formats']);
        self::assertSame(['text'], $bootstrap['sources'][2]['formats']);

        foreach ([...$bootstrap['targets'], ...$bootstrap['sources']] as $record) {
            self::assertSame([
                'sort' => false,
                'filter' => false,
                'edit' => false,
                'export' => false,
            ], $record['capabilities']);
        }
    }

    public function testUnavailableCanonicalSourceFailsClosedWithNoAdvertisedSurface(): void
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            availability: DataSourceAvailability::Degraded,
            degradedReason: 'Test source unavailable.',
        ));

        $bootstrap = (new AdminColumnsAdminBootstrapProjector(
            $registry,
            static fn (): array => [
                'post' => (object) [
                    'name' => 'post',
                    'labels' => (object) ['name' => 'Posts'],
                ],
            ],
        ))->project();

        self::assertSame([], $bootstrap['targets']);
        self::assertSame([], $bootstrap['sources']);
    }
}
