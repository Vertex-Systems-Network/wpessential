<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class ListingQueryReaderTest extends TestCase
{
    public function testMapsDeclaredParametersIntoCanonicalQueryRequest(): void
    {
        $query = new ListingQueryTestConsumer();
        $reader = new ListingQueryReader($query);
        $binding = new ListingQueryBinding(
            sourceRef: 'wordpress.posts',
            projection: ['post_id', 'title'],
            filterParameters: ['status' => 'post_status'],
            searchParameter: 'q',
            orderBy: [['field_ref' => 'post_id', 'direction' => 'desc']],
            pageSize: 10,
        );

        $result = $reader->read($binding, ['status' => 'publish', 'q' => 'hello', 'offset' => 10], $this->context());

        self::assertTrue($result->ok);
        self::assertSame(1, $result->returned);
        self::assertSame('publish', $query->lastRequest['filters'][0]['value'] ?? null);
        self::assertSame('hello', $query->lastRequest['search'] ?? null);
        self::assertSame(10, $query->lastRequest['offset'] ?? null);
        self::assertSame(10, $query->lastRequest['page_size'] ?? null);
    }

    public function testRejectsUndeclaredPublicParameterBeforeExecution(): void
    {
        $query = new ListingQueryTestConsumer();
        $reader = new ListingQueryReader($query);
        $binding = new ListingQueryBinding('wordpress.posts', ['post_id']);

        try {
            $reader->read($binding, ['raw_sql' => 'SELECT 1'], $this->context());
            self::fail('Expected undeclared parameter rejection.');
        } catch (InvalidArgumentException) {
            self::assertSame(0, $query->readCalls);
        }
    }

    public function testRejectsProjectionOutsideSourceSchemaBeforeExecution(): void
    {
        $query = new ListingQueryTestConsumer();
        $reader = new ListingQueryReader($query);
        $binding = new ListingQueryBinding('wordpress.posts', ['secret_column']);

        try {
            $reader->read($binding, [], $this->context());
            self::fail('Expected source-schema mismatch rejection.');
        } catch (InvalidArgumentException) {
            self::assertSame(0, $query->readCalls);
        }
    }

    public function testPreservesCanonicalQueryFailureEvidenceAndRowsFailClosed(): void
    {
        $query = new ListingQueryTestConsumer();
        $query->failure = true;
        $reader = new ListingQueryReader($query);
        $binding = new ListingQueryBinding('wordpress.posts', ['post_id']);

        $result = $reader->read($binding, [], $this->context());

        self::assertFalse($result->ok);
        self::assertSame([], $result->rows);
        self::assertSame('wpe_query_forbidden', $result->errorCode);
        self::assertSame('$.policy', $result->errorPath);
    }

    public function testRejectsRawProviderStyleSemanticReference(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListingQueryBinding('WP_Query:post_type=post', ['post_id']);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class ListingQueryTestConsumer implements QueryReadConsumerInterface
{
    /** @var array<string,mixed> */
    public array $lastRequest = [];
    public int $readCalls = 0;
    public bool $failure = false;

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $sourceRef,
            'source_type' => 'wordpress.posts',
            'capability_version' => 1,
            'available' => true,
            'field_schema' => [
                'post_id' => 'int',
                'title' => 'string',
                'post_status' => 'string',
            ],
            'predicates' => ['comparison', 'text'],
            'sort_modes' => ['field'],
            'pagination_modes' => ['offset'],
            'max_page_size' => 100,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        ++$this->readCalls;
        $this->lastRequest = $request;

        if ($this->failure) {
            return [
                'contract_version' => self::CONTRACT_VERSION,
                'ok' => false,
                'source_ref' => 'wordpress.posts',
                'projection' => [],
                'rows' => [],
                'returned' => 0,
                'error' => [
                    'code' => 'wpe_query_forbidden',
                    'path' => '$.policy',
                    'message' => 'Query authorization denied.',
                ],
            ];
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => $request['projection'],
            'rows' => [['post_id' => 42, 'title' => 'Hello']],
            'returned' => 1,
            'error' => null,
        ];
    }
}
