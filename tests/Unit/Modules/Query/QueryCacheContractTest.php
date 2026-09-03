<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Query;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Query\QueryCacheContract;
use WPEssential\Modules\Query\QueryDefinition;
use WPEssential\Modules\Query\QueryDiagnosticSnapshot;
use WPEssential\Modules\Query\QueryOrderClause;
use WPEssential\Modules\Query\QueryPagination;
use WPEssential\Modules\Query\QueryPredicate;
use WPEssential\Modules\Query\QueryPredicateType;
use WPEssential\Modules\Query\QuerySourceReference;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final class QueryCacheContractTest extends TestCase
{
    public function testNonCacheableSourceRemainsDisabledWithoutProducingKeyMaterial(): void
    {
        $decision = (new QueryCacheContract())->decide(
            $this->definition(),
            $this->descriptor(cacheable: false),
            $this->context(),
            60,
        );

        self::assertFalse($decision->eligible);
        self::assertSame('source_not_cacheable', $decision->reason);
        self::assertNull($decision->key);
        self::assertFalse($decision->policy->enabled);
        self::assertSame([], $decision->dependencies->generationKeys);
    }

    public function testCacheableSourceRequiresGenerationKeysAndExplicitScopeInputs(): void
    {
        $contract = new QueryCacheContract();

        $missingDependencies = $contract->decide(
            $this->definition(),
            $this->descriptor(cacheable: true),
            $this->context(),
            60,
        );
        self::assertSame('missing_generation_keys', $missingDependencies->reason);

        $anonymous = $contract->decide(
            $this->definition(),
            $this->descriptor(cacheable: true, generationKeys: ['posts.generation']),
            new ExecutionContext(new Principal(null), 7, networkId: 3),
            60,
        );
        self::assertSame('principal_unavailable', $anonymous->reason);

        $missingNetwork = $contract->decide(
            $this->definition(),
            $this->descriptor(cacheable: true, generationKeys: ['posts.generation']),
            new ExecutionContext(new Principal(11), 7),
            60,
        );
        self::assertSame('network_scope_unavailable', $missingNetwork->reason);

        $invalidTtl = $contract->decide(
            $this->definition(),
            $this->descriptor(cacheable: true, generationKeys: ['posts.generation']),
            $this->context(),
            0,
        );
        self::assertSame('invalid_ttl', $invalidTtl->reason);
    }

    public function testEligibleDecisionUsesSharedCacheValuesWithDeterministicScopeAndDependencies(): void
    {
        $descriptor = $this->descriptor(
            cacheable: true,
            generationKeys: ['posts.status', 'posts.generation'],
        );
        $decision = (new QueryCacheContract())->decide(
            $this->definition(),
            $descriptor,
            $this->context(),
            90,
        );

        self::assertTrue($decision->eligible);
        self::assertSame('eligible', $decision->reason);
        self::assertNotNull($decision->key);
        self::assertSame('query.result', $decision->key->namespace);
        self::assertSame(3, $decision->key->networkId);
        self::assertSame(7, $decision->key->siteId);
        self::assertSame(11, $decision->key->principalId);
        self::assertSame('q4.c1', $decision->key->revision);
        self::assertTrue($decision->policy->enabled);
        self::assertSame(90, $decision->policy->ttlSeconds);
        self::assertSame(['posts.generation', 'posts.status'], $decision->dependencies->generationKeys);
    }

    public function testCacheFingerprintChangesAcrossQueryOrPrincipalScope(): void
    {
        $contract = new QueryCacheContract();
        $descriptor = $this->descriptor(cacheable: true, generationKeys: ['posts.generation']);

        $first = $contract->decide($this->definition(offset: 0), $descriptor, $this->context(userId: 11), 60);
        $offsetChanged = $contract->decide($this->definition(offset: 20), $descriptor, $this->context(userId: 11), 60);
        $principalChanged = $contract->decide($this->definition(offset: 0), $descriptor, $this->context(userId: 12), 60);

        self::assertNotNull($first->key);
        self::assertNotNull($offsetChanged->key);
        self::assertNotNull($principalChanged->key);
        self::assertNotSame($first->key->fingerprint(), $offsetChanged->key->fingerprint());
        self::assertNotSame($first->key->fingerprint(), $principalChanged->key->fingerprint());
    }

    public function testMismatchedSourceFailsClosedBeforeCacheKeyCreation(): void
    {
        $descriptor = new DataSourceDescriptor(
            id: 'provider.other',
            sourceType: 'provider.other',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            cacheable: true,
            cacheGenerationKeys: ['provider.generation'],
        );

        $decision = (new QueryCacheContract())->decide(
            $this->definition(),
            $descriptor,
            $this->context(),
            60,
        );

        self::assertSame('source_mismatch', $decision->reason);
        self::assertNull($decision->key);
    }

    public function testDiagnosticSnapshotExposesOnlyFiniteSafeContractFields(): void
    {
        $definition = $this->definition(metadata: [
            'provider_exception' => 'SELECT secret FROM wp_posts',
            'opaque_value' => 'must-not-escape',
        ]);
        $decision = (new QueryCacheContract())->decide(
            $definition,
            $this->descriptor(cacheable: false),
            $this->context(),
            60,
        );

        $diagnostics = QueryDiagnosticSnapshot::fromDecision($definition, $decision)->toArray();
        $encoded = json_encode($diagnostics, JSON_THROW_ON_ERROR);

        self::assertSame([
            'query_key',
            'query_revision',
            'source_ref',
            'source_capability_version',
            'page_size',
            'offset',
            'principal_scoped',
            'cache_eligible',
            'cache_reason',
        ], array_keys($diagnostics));
        self::assertSame('source_not_cacheable', $diagnostics['cache_reason']);
        self::assertStringNotContainsString('SELECT', $encoded);
        self::assertStringNotContainsString('secret', $encoded);
        self::assertStringNotContainsString('must-not-escape', $encoded);
    }

    private function context(int $userId = 11): ExecutionContext
    {
        return new ExecutionContext(new Principal($userId), 7, networkId: 3);
    }

    /** @param list<string> $generationKeys */
    private function descriptor(bool $cacheable, array $generationKeys = []): DataSourceDescriptor
    {
        return new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: [
                'post.id' => 'integer',
                'post.title' => 'string',
            ],
            predicates: ['eq'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            scopes: ['site'],
            cacheable: $cacheable,
            cacheGenerationKeys: $generationKeys,
            diagnosticsAvailable: false,
        );
    }

    /** @param array<string,mixed>|null $metadata */
    private function definition(int $offset = 0, ?array $metadata = null): QueryDefinition
    {
        return new QueryDefinition(
            identity: [
                'uuid' => '01990f6e-1f30-7000-8000-000000000151',
                'key' => 'posts.cache-contract',
                'name' => 'Cache contract',
                'revision' => 4,
                'lifecycle' => 'published',
            ],
            astVersion: 1,
            source: new QuerySourceReference('wordpress.posts', 'wordpress.posts', 1),
            operation: 'select',
            projection: ['post.id', 'post.title'],
            parameters: [],
            filter: new QueryPredicate(
                QueryPredicateType::Comparison,
                ['field_ref' => 'post.id', 'operator' => 'eq', 'value' => 42],
            ),
            orderBy: [new QueryOrderClause('post.id', 'asc')],
            pagination: new QueryPagination('offset', 20, $offset),
            distinct: false,
            executionPolicy: [],
            cachePolicy: [],
            metadata: $metadata,
        );
    }
}
