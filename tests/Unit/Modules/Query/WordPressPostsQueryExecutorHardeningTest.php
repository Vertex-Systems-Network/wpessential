<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Query\QueryExecutionError;
use WPEssential\Modules\Query\QueryProviderPlan;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Modules\Query\WordPressPostsQueryExecutor;

final class WordPressPostsQueryExecutorHardeningTest extends TestCase
{
    public function testPresentNullOptionalArgumentCannotWidenForgedPlan(): void
    {
        $calls = 0;
        $executor = new WordPressPostsQueryExecutor(
            static function (array $arguments) use (&$calls): object {
                ++$calls;
                return (object) ['posts' => [], 'found_posts' => 0];
            },
        );
        $plan = new QueryProviderPlan(
            provider: WordPressPostsQueryCompiler::PROVIDER,
            sourceRef: WordPressPostsQueryCompiler::SOURCE_REF,
            arguments: [
                'ignore_sticky_posts' => true,
                'suppress_filters' => true,
                'posts_per_page' => 20,
                'offset' => 0,
                'fields' => 'ids',
                'p' => null,
            ],
            projection: ['post.id'],
        );

        $result = $executor->execute($plan);

        self::assertInstanceOf(QueryExecutionError::class, $result);
        self::assertSame('wpe_query_provider_failed', $result->errorCode);
        self::assertSame('$.execution.arguments.p', $result->path);
        self::assertSame(0, $calls);
    }
}
