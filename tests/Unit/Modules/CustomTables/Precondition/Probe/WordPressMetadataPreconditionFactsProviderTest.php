<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Precondition\Probe;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;

final class WordPressMetadataPreconditionFactsProviderTest extends TestCase
{
    public function testFactsComeFromReadOnlyCt1MetadataAndTrustedCapabilities(): void
    {
        $descriptor = self::descriptor();
        $physicalName = Ct1ManagedTableIdentity::derive(
            $descriptor->definitionId,
            $descriptor->tableKey,
            'wp_',
        )->physicalName;
        $wpdb = new MetadataFactsWpdbFake($physicalName);
        $provider = new WordPressMetadataPreconditionFactsProvider(
            new WordPressCt1SchemaIntrospector($wpdb),
        );
        $capabilities = new ProviderCapabilityProfile(
            provider: 'mysql',
            version: '8.0.36',
            instantAddColumnCandidate: true,
            instantDefaultChangeCandidate: false,
            inplaceVarcharWideningCandidate: true,
        );

        $facts = $provider->facts($descriptor, $capabilities);
        $expectedColumnFingerprint = hash(
            'sha256',
            json_encode(
                $descriptor->columns[0]->canonical(),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ),
        );

        self::assertTrue($facts->tableExists);
        self::assertSame($expectedColumnFingerprint, $facts->columnFingerprints['column.orders.id'] ?? null);
        self::assertSame([
            'instant_add_column' => true,
            'instant_default_change' => false,
            'inplace_varchar_widening' => true,
        ], $facts->features);
        self::assertCount(3, $wpdb->queries);
        foreach ($wpdb->queries as $query) {
            self::assertMatchesRegularExpression('/^\s*SELECT\b/i', $query);
            self::assertStringContainsString('INFORMATION_SCHEMA.', $query);
            self::assertStringNotContainsString('INSERT ', $query);
            self::assertStringNotContainsString('UPDATE ', $query);
            self::assertStringNotContainsString('DELETE ', $query);
            self::assertStringNotContainsString('ALTER ', $query);
        }
    }

    public function testMissingTableProducesMissingMetadataWithoutRowReads(): void
    {
        $descriptor = self::descriptor();
        $wpdb = new MetadataFactsWpdbFake('', false);
        $facts = (new WordPressMetadataPreconditionFactsProvider(
            new WordPressCt1SchemaIntrospector($wpdb),
        ))->facts(
            $descriptor,
            new ProviderCapabilityProfile('mysql', '8.0.36', true, true, true),
        );

        self::assertFalse($facts->tableExists);
        self::assertSame([], $facts->columnFingerprints);
        self::assertCount(1, $wpdb->queries);
        self::assertStringContainsString('INFORMATION_SCHEMA.TABLES', $wpdb->queries[0]);
    }

    private static function descriptor(): TableSchemaDescriptor
    {
        return new TableSchemaDescriptor(
            definitionId: '22222222-2222-4222-8222-222222222222',
            revision: 3,
            tableKey: 'orders',
            label: 'Orders',
            storageMode: 'managed',
            scope: 'site',
            desiredSchemaVersion: 2,
            columns: [new TableColumnDescriptor(
                key: 'id',
                type: 'bigint',
                nullable: false,
                hasDefault: false,
                defaultValue: null,
                autoIncrement: true,
            )],
            primaryKey: ['id'],
            indexes: [],
            charsetCollation: 'inherit',
            dataClassification: 'internal',
            compatibilityFingerprint: str_repeat('c', 64),
        );
    }
}

final class MetadataFactsWpdbFake
{
    public string $prefix = 'wp_';
    public string $collate = 'utf8mb4_unicode_ci';
    public string $last_error = '';

    /** @var list<string> */
    public array $queries = [];

    public function __construct(
        private string $physicalName,
        private bool $exists = true,
    ) {
    }

    public function prepare(string $query, mixed ...$args): string
    {
        if ($args === []) {
            return $query;
        }

        return preg_replace('/%s/', "'" . str_replace("'", "''", (string) $args[0]) . "'", $query, 1) ?? '';
    }

    /** @return list<array<string,mixed>> */
    public function get_results(string $query, string $output): array
    {
        if ($output !== 'ARRAY_A') {
            throw new RuntimeException('Unexpected metadata facts output mode.');
        }
        $this->queries[] = $query;

        if (str_contains($query, 'INFORMATION_SCHEMA.TABLES')) {
            return $this->exists ? [[
                'table_name' => $this->physicalName,
                'table_collation' => 'utf8mb4_unicode_ci',
            ]] : [];
        }
        if (str_contains($query, 'INFORMATION_SCHEMA.COLUMNS')) {
            return [[
                'column_name' => 'id',
                'data_type' => 'bigint',
                'column_type' => 'bigint(20)',
                'is_nullable' => 'NO',
                'column_default' => null,
                'extra' => 'auto_increment',
                'character_maximum_length' => null,
                'numeric_precision' => '19',
                'numeric_scale' => '0',
            ]];
        }
        if (str_contains($query, 'INFORMATION_SCHEMA.STATISTICS')) {
            return [[
                'index_name' => 'PRIMARY',
                'non_unique' => '0',
                'seq_in_index' => '1',
                'column_name' => 'id',
                'sub_part' => null,
                'index_type' => 'BTREE',
            ]];
        }

        throw new RuntimeException('Unexpected metadata facts query.');
    }

    public function db_server_info(): string
    {
        return '8.0.36';
    }
}
