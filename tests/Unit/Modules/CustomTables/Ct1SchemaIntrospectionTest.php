<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentityResolver;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class Ct1SchemaIntrospectionTest extends TestCase
{
    private const ID = '74444444-4444-4444-8444-444444444444';

    public function testTrustedCurrentSitePrefixProducesStableIsolatedPhysicalIdentity(): void
    {
        $descriptor = $this->desired();
        $siteOne = new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_7_'));
        $siteTwo = new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_12_'));

        $first = $siteOne->resolve($descriptor);
        $again = $siteOne->resolve($descriptor);
        $otherSite = $siteTwo->resolve($descriptor);

        self::assertSame($first->physicalName, $again->physicalName);
        self::assertNotSame($first->physicalName, $otherSite->physicalName);
        self::assertStringStartsWith('wp_7_wpe_ct_', $first->physicalName);
        self::assertLessThanOrEqual(64, strlen($first->physicalName));
        self::assertSame(self::ID, $first->definitionId);
        self::assertSame('orders', $first->tableKey);
    }

    public function testMissingPhysicalTableNormalizesToExplicitMissingStateWithOneRead(): void
    {
        $wpdb = new FakeCt1Wpdb('wp_3_', [[]]);
        $observed = (new WordPressCt1SchemaIntrospector($wpdb))->observe($this->desired());

        self::assertFalse($observed->exists);
        self::assertSame([], $observed->columns);
        self::assertSame([], $observed->primaryKey);
        self::assertSame([], $observed->indexes);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $observed->fingerprint);
        self::assertCount(1, $wpdb->queries);
        self::assertStringStartsWith('SELECT ', $wpdb->queries[0]);
    }

    public function testSupportedMysqlMetadataNormalizesDeterministicallyWithoutMutation(): void
    {
        $descriptor = $this->desired();
        $physical = (new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_9_')))->resolve($descriptor)->physicalName;
        $responses = [
            [[
                'table_name' => $physical,
                'table_collation' => 'utf8mb4_unicode_520_ci',
            ]],
            [
                $this->column('id', 'bigint', 'bigint(20)', 'NO', null, 'auto_increment'),
                $this->column('reference', 'varchar', 'varchar(64)', 'NO', '', '', 64),
                $this->column('amount', 'decimal', 'decimal(12,2)', 'NO', '0.00', '', null, 12, 2),
                $this->column('active', 'tinyint', 'tinyint(1)', 'NO', '1'),
            ],
            [
                $this->index('PRIMARY', 0, 1, 'id'),
                $this->index('amount_active', 1, 1, 'amount'),
                $this->index('amount_active', 1, 2, 'active'),
                $this->index('reference_unique', 0, 1, 'reference'),
            ],
        ];
        $wpdb = new FakeCt1Wpdb('wp_9_', $responses, '8.4.1', 'utf8mb4_unicode_520_ci');
        $introspector = new WordPressCt1SchemaIntrospector($wpdb);

        $first = $introspector->observe($descriptor);

        $wpdb2 = new FakeCt1Wpdb('wp_9_', $responses, '8.4.1', 'utf8mb4_unicode_520_ci');
        $second = (new WordPressCt1SchemaIntrospector($wpdb2))->observe($descriptor);

        self::assertTrue($first->exists);
        self::assertFalse($first->isBlockedByDrift());
        self::assertSame($first->fingerprint, $second->fingerprint);
        self::assertSame(['id'], $first->primaryKey);
        self::assertCount(4, $first->columns);
        self::assertCount(2, $first->indexes);
        self::assertSame('inherit', $first->charsetCollation);
        self::assertSame(true, $first->columns[0]->autoIncrement);
        self::assertSame(true, $first->columns[1]->hasDefault);
        self::assertSame('0.00', $first->columns[1]->defaultValue);
        self::assertSame(false, $first->columns[2]->defaultValue);
        self::assertSame('', $first->columns[3]->defaultValue);

        foreach ($wpdb->queries as $query) {
            self::assertMatchesRegularExpression('/^SELECT\b/i', $query);
            foreach (['INSERT ', 'UPDATE ', 'DELETE ', 'CREATE ', 'ALTER ', 'DROP ', 'RENAME ', 'TRUNCATE '] as $forbidden) {
                self::assertStringNotContainsString($forbidden, strtoupper($query));
            }
        }
    }

    public function testUnsupportedPhysicalTypeBecomesExplicitBlockingFinding(): void
    {
        $descriptor = $this->desired();
        $physical = (new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_')))->resolve($descriptor)->physicalName;
        $wpdb = new FakeCt1Wpdb('wp_', [
            [['table_name' => $physical, 'table_collation' => 'utf8mb4_unicode_ci']],
            [$this->column('id', 'geometry', 'geometry', 'NO', null)],
            [],
        ], '8.0.40', 'utf8mb4_unicode_ci');

        $observed = (new WordPressCt1SchemaIntrospector($wpdb))->observe($descriptor);

        self::assertTrue($observed->isBlockedByDrift());
        self::assertSame('unsupported_column_type', $observed->findings[0]->code);
        self::assertSame('columns.id.type', $observed->findings[0]->path);
    }

    public function testUnsupportedIndexShapeFailsClosedBeforeNormalization(): void
    {
        $descriptor = $this->desired();
        $physical = (new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_')))->resolve($descriptor)->physicalName;
        $wpdb = new FakeCt1Wpdb('wp_', [
            [['table_name' => $physical, 'table_collation' => 'utf8mb4_unicode_ci']],
            [$this->column('id', 'bigint', 'bigint(20)', 'NO', null, 'auto_increment')],
            [$this->index('PRIMARY', 0, 1, 'id', 4)],
        ], '8.0.40', 'utf8mb4_unicode_ci');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported prefix index');
        (new WordPressCt1SchemaIntrospector($wpdb))->observe($descriptor);
    }

    public function testIntrospectionSourceContainsNoDdlOrDataMutationPrimitive(): void
    {
        $reflection = new \ReflectionClass(WordPressCt1SchemaIntrospector::class);
        $path = $reflection->getFileName();
        self::assertIsString($path);
        $source = file_get_contents($path);
        self::assertIsString($source);

        foreach ([
            'dbDelta(',
            'CREATE TABLE',
            'ALTER TABLE',
            'DROP TABLE',
            'RENAME TABLE',
            'INSERT INTO',
            'UPDATE ',
            'DELETE FROM',
            '->query(',
            '->insert(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function desired(): \WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor
    {
        return (new TableDefinitionCompiler())->compile(new Definition(
            id: self::ID,
            slug: 'orders',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: [
                'key' => 'orders',
                'label' => 'Orders',
                'desired_schema_version' => 1,
                'columns' => [
                    ['key' => 'id', 'type' => 'bigint', 'nullable' => false, 'auto_increment' => true],
                    ['key' => 'reference', 'type' => 'varchar', 'length' => 64, 'nullable' => false, 'default' => ''],
                    ['key' => 'amount', 'type' => 'decimal', 'precision' => 12, 'scale' => 2, 'nullable' => false, 'default' => '0.00'],
                    ['key' => 'active', 'type' => 'boolean', 'nullable' => false, 'default' => true],
                ],
                'primary_key' => ['id'],
                'indexes' => [
                    ['key' => 'reference_unique', 'columns' => ['reference'], 'unique' => true],
                    ['key' => 'amount_active', 'columns' => ['amount', 'active'], 'unique' => false],
                ],
            ],
            revision: 1,
        ));
    }

    /** @return array<string,mixed> */
    private function column(
        string $name,
        string $dataType,
        string $columnType,
        string $nullable,
        mixed $default,
        string $extra = '',
        ?int $length = null,
        ?int $precision = null,
        ?int $scale = null,
    ): array {
        return [
            'column_name' => $name,
            'data_type' => $dataType,
            'column_type' => $columnType,
            'is_nullable' => $nullable,
            'column_default' => $default,
            'extra' => $extra,
            'character_maximum_length' => $length,
            'numeric_precision' => $precision,
            'numeric_scale' => $scale,
        ];
    }

    /** @return array<string,mixed> */
    private function index(
        string $name,
        int $nonUnique,
        int $sequence,
        string $column,
        ?int $subPart = null,
    ): array {
        return [
            'index_name' => $name,
            'non_unique' => $nonUnique,
            'seq_in_index' => $sequence,
            'column_name' => $column,
            'sub_part' => $subPart,
            'index_type' => 'BTREE',
        ];
    }
}

final class FakeCt1Wpdb
{
    public string $last_error = '';

    /** @var list<string> */
    public array $queries = [];

    /** @param list<list<array<string,mixed>>> $responses */
    public function __construct(
        public string $prefix,
        private array $responses = [],
        private string $serverInfo = '8.0.40',
        public string $collate = 'utf8mb4_unicode_ci',
    ) {
    }

    public function prepare(string $query, mixed ...$args): string
    {
        foreach ($args as $arg) {
            $escaped = str_replace("'", "''", (string) $arg);
            $query = preg_replace('/%s/', "'" . $escaped . "'", $query, 1) ?? $query;
        }
        return $query;
    }

    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        self::assertArrayOutput($output);
        $this->queries[] = $query;
        return array_shift($this->responses) ?? [];
    }

    public function db_server_info(): string
    {
        return $this->serverInfo;
    }

    private static function assertArrayOutput(string $output): void
    {
        if ($output !== 'ARRAY_A') {
            throw new RuntimeException('Unexpected fake wpdb output mode.');
        }
    }
}
