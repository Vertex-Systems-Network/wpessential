<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchemaNormalizer;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class ProviderDdlPreviewCompilerTest extends TestCase
{
    private const ID = '76666666-6666-4666-8666-666666666666';

    public function testTrustedProviderProfilesAreDeterministicAndConservative(): void
    {
        $mysql = ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0 MySQL Community Server');
        $maria = ProviderCapabilityProfile::fromTrustedServerInfo('10.11.8-MariaDB-ubu2204');

        self::assertSame(ProviderCapabilityProfile::MYSQL, $mysql->provider);
        self::assertSame('8.4.0', $mysql->version);
        self::assertTrue($mysql->instantAddColumnCandidate);
        self::assertTrue($mysql->instantDefaultChangeCandidate);
        self::assertTrue($mysql->inplaceVarcharWideningCandidate);

        self::assertSame(ProviderCapabilityProfile::MARIADB, $maria->provider);
        self::assertSame('10.11.8', $maria->version);
        self::assertTrue($maria->instantAddColumnCandidate);
        self::assertFalse($maria->instantDefaultChangeCandidate);
        self::assertFalse($maria->inplaceVarcharWideningCandidate);

        $this->expectException(InvalidArgumentException::class);
        ProviderCapabilityProfile::fromTrustedServerInfo('5.7.44 MySQL');
    }

    public function testMissingTableCompilesDeterministicExecutionFreeCreatePreview(): void
    {
        $desired = $this->desired();
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', ['exists' => false]);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);
        $identity = Ct1ManagedTableIdentity::derive(self::ID, 'orders', 'wp_2_');
        $profile = ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0');
        $compiler = new ProviderDdlPreviewCompiler();

        $first = $compiler->compile($plan, $desired, $identity, $profile);
        $second = $compiler->compile($plan, $desired, $identity, $profile);

        self::assertSame($first->fingerprint, $second->fingerprint);
        self::assertSame($first->canonical(), $second->canonical());
        self::assertFalse($first->executionAllowed);
        self::assertCount(1, $first->statements);
        self::assertSame('create_table', $first->statements[0]->operationType);
        self::assertFalse($first->statements[0]->executionAllowed);
        self::assertSame('not_applicable', $first->statements[0]->algorithmClassification);
        self::assertStringStartsWith('CREATE TABLE `wp_2_wpe_ct_', $first->statements[0]->sql);
        self::assertStringContainsString('PRIMARY KEY (`id`)', $first->statements[0]->sql);
        self::assertStringContainsString('KEY `reference_lookup` (`reference`)', $first->statements[0]->sql);
        self::assertStringContainsString('ENGINE=InnoDB', $first->statements[0]->sql);
        self::assertStringNotContainsString(';', $first->statements[0]->sql);
    }

    public function testSafeAddColumnUsesTrustedDescriptorAndProviderClassification(): void
    {
        $desired = $this->desired();
        $metadata = $this->matchingObserved();
        array_pop($metadata['columns']);
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);
        self::assertSame(MigrationRisk::R1, $plan->risk);

        $identity = Ct1ManagedTableIdentity::derive(self::ID, 'orders', 'wp_');
        $compiler = new ProviderDdlPreviewCompiler();
        $mysql = $compiler->compile(
            $plan,
            $desired,
            $identity,
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );
        $maria = $compiler->compile(
            $plan,
            $desired,
            $identity,
            ProviderCapabilityProfile::fromTrustedServerInfo('10.11.8-MariaDB'),
        );

        self::assertCount(1, $mysql->statements);
        self::assertSame('add_column', $mysql->statements[0]->operationType);
        self::assertSame('instant_candidate', $mysql->statements[0]->algorithmClassification);
        self::assertSame('instant_candidate', $maria->statements[0]->algorithmClassification);
        self::assertStringContainsString('ADD COLUMN `active` TINYINT(1) NOT NULL DEFAULT 1', $mysql->statements[0]->sql);
    }

    public function testDefaultChangeClassificationDiffersWithoutChangingTrustedSqlShape(): void
    {
        $desired = $this->desired();
        $metadata = $this->matchingObserved();
        $metadata['columns'][2]['default'] = false;
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);
        $identity = Ct1ManagedTableIdentity::derive(self::ID, 'orders', 'wp_');
        $compiler = new ProviderDdlPreviewCompiler();

        $mysql = $compiler->compile(
            $plan,
            $desired,
            $identity,
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );
        $maria = $compiler->compile(
            $plan,
            $desired,
            $identity,
            ProviderCapabilityProfile::fromTrustedServerInfo('10.11.8-MariaDB'),
        );

        self::assertSame('alter_column_default', $mysql->statements[0]->operationType);
        self::assertSame('instant_candidate', $mysql->statements[0]->algorithmClassification);
        self::assertSame('provider_default', $maria->statements[0]->algorithmClassification);
        self::assertSame($mysql->statements[0]->sql, $maria->statements[0]->sql);
        self::assertStringContainsString('ALTER COLUMN `active` SET DEFAULT 1', $mysql->statements[0]->sql);
    }

    public function testSafeSecondaryIndexAdditionCompilesFromTrustedIndexDescriptor(): void
    {
        $desired = $this->desired();
        $metadata = $this->matchingObserved();
        $metadata['indexes'] = [];
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);
        $preview = (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::ID, 'orders', 'wp_'),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );

        self::assertCount(1, $preview->statements);
        self::assertSame('add_index', $preview->statements[0]->operationType);
        self::assertStringContainsString(
            'ADD INDEX `reference_lookup` (`reference`)',
            $preview->statements[0]->sql,
        );
        self::assertSame('provider_default', $preview->statements[0]->algorithmClassification);
    }

    public function testBlockedR4PlanCannotCompileStatements(): void
    {
        $desired = $this->desired();
        $metadata = $this->matchingObserved();
        $metadata['columns'][] = [
            'key' => 'legacy_note',
            'type' => 'varchar',
            'length' => 32,
            'nullable' => true,
        ];
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);

        self::assertTrue($plan->blocked);
        self::assertSame(MigrationRisk::R4, $plan->risk);

        $this->expectException(InvalidArgumentException::class);
        (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::ID, 'orders', 'wp_'),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );
    }

    public function testUncertifiedNullabilityOperationFailsClosedDespiteR2Plan(): void
    {
        $desired = $this->desired();
        $metadata = $this->matchingObserved();
        $metadata['columns'][1]['nullable'] = true;
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);

        self::assertFalse($plan->blocked);
        self::assertSame(MigrationRisk::R2, $plan->risk);

        $this->expectException(InvalidArgumentException::class);
        (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::ID, 'orders', 'wp_'),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );
    }

    public function testMismatchedDefinitionIdentityCannotReuseAnotherPhysicalTable(): void
    {
        $desired = $this->desired();
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', ['exists' => false]);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);

        $this->expectException(InvalidArgumentException::class);
        (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(
                '77777777-7777-4777-8777-777777777777',
                'orders',
                'wp_',
            ),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );
    }

    public function testCompilerSourcesContainNoDatabaseExecutionDependency(): void
    {
        foreach ([
            ProviderCapabilityProfile::class,
            ProviderDdlPreviewCompiler::class,
            \WPEssential\Modules\CustomTables\Migration\ProviderStatementPreview::class,
            \WPEssential\Modules\CustomTables\Migration\ProviderMigrationPreview::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $path = $reflection->getFileName();
            self::assertIsString($path);
            $source = file_get_contents($path);
            self::assertIsString($source);

            foreach ([
                'DatabaseAdapterInterface',
                'NativeWpdbAdapter',
                'MigrationRunner',
                'dbDelta(',
                '->query(',
                '->get_results(',
                '->insert(',
                '->update(',
                '->delete(',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $class . ' must remain execution-free.');
            }
        }
    }

    private function desired(): TableSchemaDescriptor
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
                'desired_schema_version' => 3,
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
                        'key' => 'active',
                        'type' => 'boolean',
                        'nullable' => false,
                        'default' => true,
                    ],
                ],
                'primary_key' => ['id'],
                'indexes' => [[
                    'key' => 'reference_lookup',
                    'columns' => ['reference'],
                    'unique' => false,
                ]],
                'data_classification' => 'internal',
            ],
            revision: 4,
        ));
    }

    /** @return array<string,mixed> */
    private function matchingObserved(): array
    {
        return [
            'exists' => true,
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
                    'key' => 'active',
                    'type' => 'boolean',
                    'nullable' => false,
                    'default' => true,
                ],
            ],
            'primary_key' => ['id'],
            'indexes' => [[
                'key' => 'reference_lookup',
                'columns' => ['reference'],
                'unique' => false,
            ]],
            'charset_collation' => 'inherit',
        ];
    }
}
