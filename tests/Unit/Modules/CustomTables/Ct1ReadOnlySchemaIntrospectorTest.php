<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentityResolver;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class Ct1ReadOnlySchemaIntrospectorTest extends TestCase
{
    private const ID = '74444444-4444-4444-8444-444444444444';

    public function testTrustedCurrentSitePrefixProducesIsolatedCanonicalCt1Identity(): void
    {
        $descriptor = $this->descriptor();
        $siteOne = new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_'));
        $siteSeven = new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wp_7_'));

        $first = $siteOne->resolve($descriptor);
        $seventh = $siteSeven->resolve($descriptor);

        self::assertSame('wp_wpe_orders', $first->physicalName);
        self::assertSame('wp_7_wpe_orders', $seventh->physicalName);
        self::assertNotSame($first->physicalName, $seventh->physicalName);
        self::assertSame('orders', $seventh->tableKey);
    }

    public function testIdentityObjectRejectsForgedPhysicalNameEvenInsideNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Ct1ManagedTableIdentity(
            tableKey: 'orders',
            sitePrefix: 'wp_2_',
            physicalName: 'wp_2_wpe_users',
        );
    }

    public function testResolverFailsClosedWhenCanonicalPhysicalNameExceedsDatabaseLimit(): void
    {
        $tableKey = 'a' . str_repeat('b', 47);
        $descriptor = $this->descriptor($tableKey);
        $resolver = new Ct1ManagedTableIdentityResolver(new FakeCt1Wpdb('wordpress_site_12_'));

        $this->expectException(RuntimeException::class);
        $resolver->resolve($descriptor);
    }

    public function testMissingTableReturnsExplicitMissingObservationAfterOneMetadataRead(): void
    {
        $wpdb = new FakeCt1Wpdb('wp_9_', ['tables' => []]);
        $observed = (new WordPressCt1SchemaIntrospector($wpdb))->observe($this->descriptor());

        self::assertFalse($observed->exists);
        self::assertSame([], $observed->columns);
        self::assertSame([], $observed->primaryKey);
        self::assertSame([], $observed->indexes);
        self::assertSame('unknown', $observed->charsetCollation);
        self::assertCount(1, $wpdb->queries);
        self::assertCount(1, $wpdb->prepared);
        self::assertSame(['wp_9_wpe_orders'], $wpdb->prepared[0]['args']);
        self::assertStringContainsString('TABLE_NAME = %s', $wpdb->prepared[0]['query']);
        self::assertStringNotContainsString('wp_9_wpe_orders', $wpdb->prepared[0]['query']);
    }

    public function testSupportedPhysicalMetadataNormalizesToNoOpDesiredPlan(): void
    {
        $wpdb = new FakeCt1Wpdb('wp_2_', $this->supportedMetadataResponses('wp_2_wpe_orders'));
        $desired = $this->descriptor();
        $observed = (new WordPressCt1SchemaIntrospector($wpdb))->observe($desired);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);

        self::assertTrue($observed->exists);
        self::assertFalse($observed->isBlockedByDrift());
        self::assertSame(['id'], $observed->primaryKey);
        self::assertCount(4, $observed->columns);
        self::assertCount(2, $observed->indexes);
        self::assertSame('inherit', $observed->charsetCollation);
        self::assertTrue($plan->isNoOp());
        self::assertSame(MigrationRisk::R0, $plan->risk);
        self::assertSame($observed->fingerprint, $plan->sourceObservedFingerprint);

        self::assertCount(3, $wpdb->prepared);
        foreach ($wpdb->prepared as $prepared) {
            self::assertSame(['wp_2_wpe_orders'], $prepared['args']);
            self::assertStringContainsString('TABLE_NAME = %s', $prepared['query']);
            self::assertStringNotContainsString('wp_2_wpe_orders', $prepared['query']);
        }
    }

    public function testFractionalDatetimeMetadataBecomesBlockingUnsupportedDrift(): void
    {
        $responses = $this->supportedMetadataResponses('wp_wpe_orders');
        $responses['columns'][1] = [
            'column_name' => 'reference',
            'data_type' => 'datetime',
            'column_type' => 'datetime(6)',
            'is_nullable' => 'NO',
            'column_default' => null,
            'extra' => '',
            'character_maximum_length' => null,
            'numeric_precision' => null,
            'numeric_scale' => null,
        ];
        $wpdb = new FakeCt1Wpdb('wp_', $responses);

        $observed = (new WordPressCt1SchemaIntrospector($wpdb))->observe($this->descriptor());
        $codes = array_map(
            static fn ($finding): string => $finding->code,
            $observed->findings,
        );

        self::assertTrue($observed->isBlockedByDrift());
        self::assertContains('unsupported_column_type', $codes);
    }

    public function testMalformedIndexSequenceFailsClosedInsteadOfGuessingOrder(): void
    {
        $responses = $this->supportedMetadataResponses('wp_wpe_orders');
        $responses['indexes'][4]['seq_in_index'] = '3';
        $wpdb = new FakeCt1Wpdb('wp_', $responses);

        $this->expectException(RuntimeException::class);
        (new WordPressCt1SchemaIntrospector($wpdb))->observe($this->descriptor());
    }

    public function testObservationExecutesMetadataSelectsOnlyAndContainsNoMutationPrimitive(): void
    {
        $wpdb = new FakeCt1Wpdb('wp_', $this->supportedMetadataResponses('wp_wpe_orders'));
        (new WordPressCt1SchemaIntrospector($wpdb))->observe($this->descriptor());

        foreach ($wpdb->queries as $query) {
            self::assertMatchesRegularExpression('/^\s*SELECT\b/i', $query);
            self::assertStringContainsString('INFORMATION_SCHEMA.', $query);
        }

        $reflection = new \ReflectionClass(WordPressCt1SchemaIntrospector::class);
        $path = $reflection->getFileName();
        self::assertIsString($path);
        $source = file_get_contents($path);
        self::assertIsString($source);

        foreach ([
            'CREATE TABLE',
            'ALTER TABLE',
            'DROP TABLE',
            'RENAME TABLE',
            'INSERT INTO',
            'UPDATE ',
            'DELETE FROM',
            'dbDelta(',
            '->query(',
            '->insert(',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    private function descriptor(string $tableKey = 'orders'): TableSchemaDescriptor
    {
        $definition = new Definition(
            id: self::ID,
            slug: $tableKey,
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: [
                'key' => $tableKey,
                'label' => 'Orders',
                'desired_schema_version' => 1,
                'columns' => [
                    [
                        'key' => 'id',
                        'type' => 'bigint',
                        'nullable' => false,
                        'auto_increment' => true,
                    ],
                    [
                        'key' => 'reference',
                        'type' => 'varchar',
                        'length' => 64,
                        'nullable' => false,
                        'default' => '',
                    ],
                    [
                        'key' => 'amount',
                        'type' => 'decimal',
                        'precision' => 12,
                        'scale' => 2,
                        'nullable' => false,
                        'default' => '0.00',
                    ],
                    [
                        'key' => 'active',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => true,
                    ],
                ],
                'primary_key' => ['id'],
                'indexes' => [
                    [
                        'key' => 'reference_unique',
                        'columns' => ['reference'],
                        'unique' => true,
                    ],
                    [
                        'key' => 'amount_active',
                        'columns' => ['amount', 'active'],
                        'unique' => false,
                    ],
                ],
                'data_classification' => 'internal',
            ],
            revision: 1,
        );

        return (new TableDefinitionCompiler())->compile($definition);
    }

    /** @return array<string,list<array<string,mixed>>> */
    private function supportedMetadataResponses(string $physicalName): array
    {
        return [
            'tables' => [[
                'table_name' => $physicalName,
                'table_collation' => 'utf8mb4_unicode_ci',
            ]],
            'columns' => [
                [
                    'column_name' => 'id',
                    'data_type' => 'bigint',
                    'column_type' => 'bigint(20)',
                    'is_nullable' => 'NO',
                    'column_default' => null,
                    'extra' => 'auto_increment',
                    'character_maximum_length' => null,
                    'numeric_precision' => '19',
                    'numeric_scale' => '0',
                ],
                [
                    'column_name' => 'reference',
                    'data_type' => 'varchar',
                    'column_type' => 'varchar(64)',
                    'is_nullable' => 'NO',
                    'column_default' => '',
                    'extra' => '',
                    'character_maximum_length' => '64',
                    'numeric_precision' => null,
                    'numeric_scale' => null,
                ],
                [
                    'column_name' => 'amount',
                    'data_type' => 'decimal',
                    'column_type' => 'decimal(12,2)',
                    'is_nullable' => 'NO',
                    'column_default' => '0.00',
                    'extra' => '',
                    'character_maximum_length' => null,
                    'numeric_precision' => '12',
                    'numeric_scale' => '2',
                ],
                [
                    'column_name' => 'active',
                    'data_type' => 'tinyint',
                    'column_type' => 'tinyint(1)',
                    'is_nullable' => 'NO',
                    'column_default' => '1',
                    'extra' => '',
                    'character_maximum_length' => null,
                    'numeric_precision' => '3',
                    'numeric_scale' => '0',
                ],
            ],
            'indexes' => [
                [
                    'index_name' => 'PRIMARY',
                    'non_unique' => '0',
                    'seq_in_index' => '1',
                    'column_name' => 'id',
                    'sub_part' => null,
                    'index_type' => 'BTREE',
                ],
                [
                    'index_name' => 'amount_active',
                    'non_unique' => '1',
                    'seq_in_index' => '1',
                    'column_name' => 'amount',
                    'sub_part' => null,
                    'index_type' => 'BTREE',
                ],
                [
                    'index_name' => 'amount_active',
                    'non_unique' => '1',
                    'seq_in_index' => '2',
                    'column_name' => 'active',
                    'sub_part' => null,
                    'index_type' => 'BTREE',
                ],
                [
                    'index_name' => 'reference_unique',
                    'non_unique' => '0',
                    'seq_in_index' => '1',
                    'column_name' => 'reference',
                    'sub_part' => null,
                    'index_type' => 'BTREE',
                ],
                [
                    'index_name' => 'amount_active',
                    'non_unique' => '1',
                    'seq_in_index' => '2',
                    'column_name' => 'active',
                    'sub_part' => null,
                    'index_type' => 'BTREE',
                ],
            ],
        ];
    }
}

final class FakeCt1Wpdb
{
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    /** @var list<string> */
    public array $queries = [];

    /** @var list<array{query:string,args:list<mixed>}> */
    public array $prepared = [];

    /** @param array<string,list<array<string,mixed>>> $responses */
    public function __construct(
        public string $prefix,
        private array $responses = [],
    ) {
    }

    public function prepare(string $query, mixed ...$args): string
    {
        $this->prepared[] = ['query' => $query, 'args' => $args];
        if ($args === []) {
            return $query;
        }

        $escaped = "'" . str_replace("'", "''", (string) $args[0]) . "'";
        return preg_replace('/%s/', $escaped, $query, 1) ?? '';
    }

    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        self::assertOutputMode($output);
        $this->queries[] = $query;

        if (str_contains($query, 'INFORMATION_SCHEMA.TABLES')) {
            return $this->responses['tables'] ?? [];
        }
        if (str_contains($query, 'INFORMATION_SCHEMA.COLUMNS')) {
            return $this->responses['columns'] ?? [];
        }
        if (str_contains($query, 'INFORMATION_SCHEMA.STATISTICS')) {
            return $this->responses['indexes'] ?? [];
        }
        if (str_contains($query, 'INFORMATION_SCHEMA.SCHEMATA')) {
            return $this->responses['schemata'] ?? [];
        }

        throw new RuntimeException('Unexpected fake CT1 metadata query.');
    }

    private static function assertOutputMode(string $output): void
    {
        if ($output !== 'ARRAY_A') {
            throw new RuntimeException('Unexpected wpdb output mode in CT1 test fixture.');
        }
    }
}
