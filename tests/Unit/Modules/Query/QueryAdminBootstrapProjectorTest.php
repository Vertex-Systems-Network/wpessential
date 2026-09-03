<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Query\QueryAdminBootstrapProjector;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryAdminBootstrapProjectorTest extends TestCase
{
    public function testProjectsOnlyCanonicalWordPressPostsSourceWithoutSensitiveRuntimeMetadata(): void
    {
        $registry = new DataSourceRegistry();
        $registry->register(new DataSourceDescriptor(
            id: WordPressPostsQueryCompiler::SOURCE_REF,
            sourceType: WordPressPostsQueryCompiler::SOURCE_REF,
            capabilityVersion: 1,
            fieldSchema: ['post.title' => 'string', 'post.id' => 'integer'],
            predicates: ['eq', 'neq', 'contains'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            supportsRelations: true,
            maxPageSize: 100,
            maxBatchSize: 100,
        ));
        $registry->register(new DataSourceDescriptor(
            id: 'example.external',
            sourceType: 'example.external',
            capabilityVersion: 1,
            fieldSchema: ['example.id' => 'integer'],
        ));

        $bootstrap = (new QueryAdminBootstrapProjector($registry))->project();

        self::assertSame('query', $bootstrap['surface']);
        self::assertSame('draft', $bootstrap['identity']['lifecycle']);
        self::assertCount(1, $bootstrap['sources']);
        $source = $bootstrap['sources'][0];
        self::assertSame(WordPressPostsQueryCompiler::SOURCE_REF, $source['sourceRef']);
        self::assertSame(['post.id', 'post.title'], array_column($source['fields'], 'ref'));
        self::assertSame(['eq', 'neq', 'contains'], $source['predicates']);
        self::assertTrue($source['supportsRelations']);
        self::assertArrayNotHasKey('authorization', $source);
        self::assertArrayNotHasKey('cacheGenerationKeys', $source);
    }

    public function testMissingCanonicalSourceProducesEmptyFailClosedSourceList(): void
    {
        $bootstrap = (new QueryAdminBootstrapProjector(new DataSourceRegistry()))->project();
        self::assertSame([], $bootstrap['sources']);
    }
}
