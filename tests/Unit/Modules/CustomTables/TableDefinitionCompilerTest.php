<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class TableDefinitionCompilerTest extends TestCase
{
    private const ID = '71111111-1111-4111-8111-111111111111';

    public function testCompilesBoundedDesiredStateWithoutEnvironmentSpecificPhysicalFacts(): void
    {
        $descriptor = (new TableDefinitionCompiler())->compile($this->definition($this->payload()));

        self::assertSame(self::ID, $descriptor->definitionId);
        self::assertSame(3, $descriptor->revision);
        self::assertSame('orders', $descriptor->tableKey);
        self::assertSame('Orders', $descriptor->label);
        self::assertSame('managed', $descriptor->storageMode);
        self::assertSame('site', $descriptor->scope);
        self::assertSame(4, $descriptor->desiredSchemaVersion);
        self::assertSame(['id'], $descriptor->primaryKey);
        self::assertCount(6, $descriptor->columns);
        self::assertCount(2, $descriptor->indexes);
        self::assertSame('inherit', $descriptor->charsetCollation);
        self::assertSame('confidential', $descriptor->dataClassification);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $descriptor->compatibilityFingerprint);

        $canonical = $descriptor->canonicalSemanticState();
        self::assertArrayNotHasKey('definition_id', $canonical);
        self::assertArrayNotHasKey('revision', $canonical);
        self::assertArrayNotHasKey('table_prefix', $canonical);
        self::assertArrayNotHasKey('sql', $canonical);
    }

    public function testFingerprintIgnoresDefinitionIdentityRevisionAndExplicitDefaultedEnvelopeSettings(): void
    {
        $compiler = new TableDefinitionCompiler();
        $first = $compiler->compile($this->definition($this->payload(), revision: 3));

        $payload = $this->payload();
        $payload['storage_mode'] = 'managed';
        $payload['scope'] = 'site';
        $payload['charset_collation'] = 'inherit';
        $second = $compiler->compile($this->definition(
            $payload,
            revision: 99,
            id: '72222222-2222-4222-8222-222222222222',
        ));

        self::assertSame($first->compatibilityFingerprint, $second->compatibilityFingerprint);
    }

    public function testSemanticSchemaChangeChangesFingerprint(): void
    {
        $compiler = new TableDefinitionCompiler();
        $baseline = $compiler->compile($this->definition($this->payload()));
        $payload = $this->payload();
        $payload['columns'][2]['precision'] = 14;

        $changed = $compiler->compile($this->definition($payload));

        self::assertNotSame($baseline->compatibilityFingerprint, $changed->compatibilityFingerprint);
    }

    /** @dataProvider invalidDefinitionEnvelopeProvider */
    public function testRejectsInvalidDefinitionEnvelope(
        string $type,
        int $surface,
        int $schemaVersion,
        DefinitionStatus $status,
        array $dependencies,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        (new TableDefinitionCompiler())->compile(new Definition(
            id: self::ID,
            slug: 'orders',
            type: $type,
            schemaVersion: $schemaVersion,
            ownerSurfaceId: $surface,
            status: $status,
            payload: $this->payload(),
            dependencies: $dependencies,
            revision: 3,
        ));
    }

    /** @return iterable<string,array{string,int,int,DefinitionStatus,array<int,mixed>}> */
    public static function invalidDefinitionEnvelopeProvider(): iterable
    {
        yield 'wrong type' => ['status', 7, 1, DefinitionStatus::Published, []];
        yield 'wrong surface' => ['table', 5, 1, DefinitionStatus::Published, []];
        yield 'wrong schema' => ['table', 7, 2, DefinitionStatus::Published, []];
        yield 'draft' => ['table', 7, 1, DefinitionStatus::Draft, []];
        yield 'dependency channel' => ['table', 7, 1, DefinitionStatus::Published, [['type' => 'provider', 'id' => 'mysql']]];
    }

    /** @dataProvider invalidPayloadMutationProvider */
    public function testRejectsMalformedOrExecutablePayload(callable $mutate): void
    {
        $payload = $this->payload();
        $mutate($payload);
        $this->expectException(InvalidArgumentException::class);

        (new TableDefinitionCompiler())->compile($this->definition($payload));
    }

    /** @return iterable<string,array{callable(array<string,mixed>&):void}> */
    public static function invalidPayloadMutationProvider(): iterable
    {
        yield 'raw sql payload channel' => [static function (array &$payload): void {
            $payload['sql'] = 'CREATE TABLE anything';
        }];
        yield 'non canonical table key' => [static function (array &$payload): void {
            $payload['key'] = 'Orders-Live';
        }];
        yield 'external storage selector' => [static function (array &$payload): void {
            $payload['storage_mode'] = 'external';
        }];
        yield 'network scope widening' => [static function (array &$payload): void {
            $payload['scope'] = 'network';
        }];
        yield 'explicit charset implementation' => [static function (array &$payload): void {
            $payload['charset_collation'] = 'utf8mb4_unicode_ci';
        }];
        yield 'callback column channel' => [static function (array &$payload): void {
            $payload['columns'][0]['callback'] = 'php_function';
        }];
        yield 'duplicate column key' => [static function (array &$payload): void {
            $payload['columns'][] = $payload['columns'][0];
        }];
        yield 'unknown type' => [static function (array &$payload): void {
            $payload['columns'][1]['type'] = 'geometry';
        }];
        yield 'varchar over compatibility bound' => [static function (array &$payload): void {
            $payload['columns'][1]['length'] = 192;
        }];
        yield 'decimal scale exceeds precision' => [static function (array &$payload): void {
            $payload['columns'][2]['scale'] = 13;
        }];
        yield 'sql expression datetime default' => [static function (array &$payload): void {
            $payload['columns'][4]['default'] = 'CURRENT_TIMESTAMP';
        }];
        yield 'text default deferred' => [static function (array &$payload): void {
            $payload['columns'][5]['type'] = 'text';
            $payload['columns'][5]['default'] = 'payload';
        }];
        yield 'nullable false null default' => [static function (array &$payload): void {
            $payload['columns'][1]['default'] = null;
        }];
        yield 'primary key missing column' => [static function (array &$payload): void {
            $payload['primary_key'] = ['missing'];
        }];
        yield 'auto increment outside primary key' => [static function (array &$payload): void {
            $payload['primary_key'] = ['reference'];
        }];
        yield 'two auto increment columns' => [static function (array &$payload): void {
            $payload['columns'][1]['type'] = 'integer';
            unset($payload['columns'][1]['length']);
            $payload['columns'][1]['auto_increment'] = true;
            $payload['primary_key'] = ['id', 'reference'];
        }];
        yield 'index references missing column' => [static function (array &$payload): void {
            $payload['indexes'][0]['columns'] = ['missing'];
        }];
        yield 'duplicate index key' => [static function (array &$payload): void {
            $payload['indexes'][1]['key'] = $payload['indexes'][0]['key'];
        }];
        yield 'duplicate index semantics' => [static function (array &$payload): void {
            $payload['indexes'][] = [
                'key' => 'reference_copy',
                'columns' => ['reference'],
                'unique' => true,
            ];
        }];
    }

    public function testCompilerSourceContainsNoPhysicalDdlOrDatabaseExecutionDependency(): void
    {
        $reflection = new \ReflectionClass(TableDefinitionCompiler::class);
        $path = $reflection->getFileName();
        self::assertIsString($path);
        $source = file_get_contents($path);
        self::assertIsString($source);

        foreach ([
            'dbDelta(',
            'CREATE TABLE',
            'ALTER TABLE',
            'DROP TABLE',
            'NativeWpdbAdapter',
            'DatabaseAdapterInterface',
            'MigrationCoordinator',
            'MigrationRunner',
        ] as $forbidden) {
            self::assertStringNotContainsString($forbidden, $source);
        }
    }

    /** @param array<string,mixed> $payload */
    private function definition(
        array $payload,
        DefinitionStatus $status = DefinitionStatus::Published,
        int $revision = 3,
        string $id = self::ID,
    ): Definition {
        return new Definition(
            id: $id,
            slug: 'orders',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: $status,
            payload: $payload,
            revision: $revision,
        );
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'key' => 'orders',
            'label' => 'Orders',
            'desired_schema_version' => 4,
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
                [
                    'key' => 'published_at',
                    'type' => 'datetime',
                    'nullable' => true,
                    'default' => null,
                ],
                [
                    'key' => 'metadata',
                    'type' => 'json',
                    'nullable' => true,
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
            'data_classification' => 'confidential',
        ];
    }
}
