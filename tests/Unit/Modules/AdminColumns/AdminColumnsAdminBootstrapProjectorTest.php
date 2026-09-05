<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\AdminColumnsSourceCatalogInterface;
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

    public function testComposesValidatedOptionalOwnerSourcesWithoutHardDependency(): void
    {
        $registry = $this->nativeRegistry();
        $ownerSources = new class implements AdminColumnsSourceCatalogInterface {
            public function adminColumnSources(): array
            {
                return [
                    [
                        'owner' => 'fields',
                        'reference' => 'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222',
                        'label' => 'Headline',
                        'formats' => ['text'],
                        'capabilities' => [
                            'sort' => false,
                            'filter' => false,
                            'edit' => false,
                            'export' => false,
                        ],
                        'ownerMetadata' => [
                            'groupRevision' => 3,
                            'fieldUuid' => '22222222-2222-4222-8222-222222222222',
                            'logicalType' => 'string',
                            'storageOwner' => 'native_post_meta',
                            'postTypes' => ['post'],
                        ],
                    ],
                ];
            }
        };

        $bootstrap = (new AdminColumnsAdminBootstrapProjector(
            $registry,
            static fn (): array => [
                'post' => (object) [
                    'name' => 'post',
                    'labels' => (object) ['name' => 'Posts'],
                ],
            ],
            $ownerSources,
        ))->project();

        self::assertSame(['post.id', 'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222'], array_column($bootstrap['sources'], 'reference'));
        self::assertSame('fields', $bootstrap['sources'][1]['owner']);
        self::assertSame([
            'groupRevision' => 3,
            'fieldUuid' => '22222222-2222-4222-8222-222222222222',
            'logicalType' => 'string',
            'storageOwner' => 'native_post_meta',
            'postTypes' => ['post'],
        ], $bootstrap['sources'][1]['ownerMetadata']);
    }

    public function testMalformedOrThrowingOptionalOwnerCatalogFailsClosedToNativeSources(): void
    {
        $malformed = new class implements AdminColumnsSourceCatalogInterface {
            public function adminColumnSources(): array
            {
                return [[
                    'owner' => 'fields',
                    'reference' => 'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222',
                    'label' => 'Headline',
                    'formats' => ['text'],
                    'capabilities' => [
                        'sort' => false,
                        'filter' => false,
                        'edit' => false,
                        'export' => false,
                    ],
                    'ownerMetadata' => [
                        'groupRevision' => 3,
                        'fieldUuid' => '99999999-9999-4999-8999-999999999999',
                        'logicalType' => 'string',
                        'storageOwner' => 'native_post_meta',
                        'postTypes' => ['post'],
                    ],
                ]];
            }
        };
        $bootstrap = (new AdminColumnsAdminBootstrapProjector(
            $this->nativeRegistry(),
            static fn (): array => [
                'post' => (object) [
                    'name' => 'post',
                    'labels' => (object) ['name' => 'Posts'],
                ],
            ],
            $malformed,
        ))->project();
        self::assertSame(['post.id'], array_column($bootstrap['sources'], 'reference'));

        $throwing = new class implements AdminColumnsSourceCatalogInterface {
            public function adminColumnSources(): array
            {
                throw new RuntimeException('Owner discovery unavailable.');
            }
        };
        $bootstrap = (new AdminColumnsAdminBootstrapProjector(
            $this->nativeRegistry(),
            static fn (): array => [
                'post' => (object) [
                    'name' => 'post',
                    'labels' => (object) ['name' => 'Posts'],
                ],
            ],
            $throwing,
        ))->project();

        self::assertSame(['post.id'], array_column($bootstrap['sources'], 'reference'));
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

    private function nativeRegistry(): DataSourceRegistry
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            predicates: ['eq'],
            sortModes: ['field'],
            paginationModes: ['offset'],
        ));
        return $registry;
    }
}
