<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchemaNormalizer;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/** Regressions found by the real MariaDB R1 execution integration (ADR-0224). */
final class RealDatabaseRegressionTest extends TestCase
{
    private const ID = '78888888-8888-4888-8888-888888888888';

    public function testCreateTableCarriesTrustedWordPressCharsetCollation(): void
    {
        $desired = $this->desired();
        $plan = (new TableSchemaDiffPlanner())->plan($desired, (new ObservedTableSchemaNormalizer())->normalize('notes', ['exists' => false]));

        $preview = (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::ID, 'notes', 'wp_'),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
            'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci',
        );

        self::assertStringEndsWith('ENGINE=InnoDB DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci', $preview->statements[0]->sql);
    }

    public function testUntrustedCharsetClauseIsRejected(): void
    {
        $desired = $this->desired();
        $plan = (new TableSchemaDiffPlanner())->plan($desired, (new ObservedTableSchemaNormalizer())->normalize('notes', ['exists' => false]));

        $this->expectException(InvalidArgumentException::class);
        (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::ID, 'notes', 'wp_'),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
            'DEFAULT CHARSET=utf8mb4, DROP TABLE x',
        );
    }

    public function testNullableColumnWithoutDefaultEqualsObservedDefaultNull(): void
    {
        $desired = $this->desired();
        $observed = (new ObservedTableSchemaNormalizer())->normalize('notes', [
            'exists' => true,
            'columns' => [
                ['key' => 'id', 'type' => 'bigint', 'nullable' => false, 'auto_increment' => true],
                ['key' => 'body', 'type' => 'varchar', 'length' => 64, 'nullable' => true, 'has_default' => true, 'default' => null],
            ],
            'primary_key' => ['id'],
            'indexes' => [],
        ]);

        self::assertSame([], (new TableSchemaDiffPlanner())->plan($desired, $observed)->operations);
    }

    private function desired(): TableSchemaDescriptor
    {
        return (new TableDefinitionCompiler())->compile(new Definition(
            id: self::ID,
            slug: 'notes',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: [
                'key' => 'notes',
                'label' => 'Notes',
                'desired_schema_version' => 1,
                'columns' => [
                    ['key' => 'id', 'type' => 'bigint', 'nullable' => false, 'auto_increment' => true],
                    ['key' => 'body', 'type' => 'varchar', 'length' => 64, 'nullable' => true],
                ],
                'primary_key' => ['id'],
                'indexes' => [],
                'data_classification' => 'internal',
            ],
            revision: 2,
        ));
    }
}
