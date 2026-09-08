<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchemaNormalizer;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class ObservedSchemaMigrationPlanTest extends TestCase
{
    private const ID = '73333333-3333-4333-8333-333333333333';

    public function testObservedNormalizationIsDeterministicAcrossMetadataOrdering(): void
    {
        $normalizer = new ObservedTableSchemaNormalizer();
        $metadata = $this->observedMetadata();
        $first = $normalizer->normalize('orders', $metadata);

        $metadata['columns'] = array_reverse($metadata['columns']);
        $metadata['indexes'] = array_reverse($metadata['indexes']);
        $second = $normalizer->normalize('orders', $metadata);

        self::assertSame($first->fingerprint, $second->fingerprint);
        self::assertSame($first->canonicalObservedState(), $second->canonicalObservedState());
        self::assertFalse($first->isBlockedByDrift());
    }

    public function testMissingTableProducesDeterministicAdditiveCreatePlanWithoutExecution(): void
    {
        $desired = $this->desired();
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', ['exists' => false]);
        $planner = new TableSchemaDiffPlanner();

        $first = $planner->plan($desired, $observed);
        $second = $planner->plan($desired, $observed);

        self::assertSame($observed->fingerprint, $first->sourceObservedFingerprint);
        self::assertSame($first->id, $second->id);
        self::assertSame($first->fingerprint, $second->fingerprint);
        self::assertSame(MigrationRisk::R1, $first->risk);
        self::assertFalse($first->blocked);
        self::assertFalse($first->recoveryRequired);
        self::assertCount(1, $first->operations);
        self::assertSame('create_table', $first->operations[0]->type);
        self::assertSame('orders', $first->operations[0]->target);
        self::assertSame([], $first->findings);
    }

    public function testMatchingObservedSchemaProducesNoOpR0Plan(): void
    {
        $desired = $this->desired();
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $this->observedMetadata());
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);

        self::assertTrue($plan->isNoOp());
        self::assertSame(MigrationRisk::R0, $plan->risk);
        self::assertFalse($plan->blocked);
        self::assertFalse($plan->recoveryRequired);
        self::assertSame([], $plan->operations);
        self::assertSame([], $plan->findings);
    }

    public function testUnsupportedObservedDriftBlocksPlanningInsteadOfSynthesizingCorrection(): void
    {
        $metadata = $this->observedMetadata();
        $metadata['columns'][2]['type'] = 'geometry';

        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        self::assertTrue($observed->isBlockedByDrift());
        self::assertSame('unsupported_column_type', $observed->findings[0]->code);

        $plan = (new TableSchemaDiffPlanner())->plan($this->desired(), $observed);

        self::assertTrue($plan->blocked);
        self::assertSame(MigrationRisk::R3, $plan->risk);
        self::assertTrue($plan->recoveryRequired);
        self::assertSame([], $plan->operations);
        self::assertSame('observed_unsupported_column_type', $plan->findings[0]->code);
    }

    public function testExtraObservedColumnIsDestructiveBlockedR4RecoveryPlan(): void
    {
        $metadata = $this->observedMetadata();
        $metadata['columns'][] = [
            'key' => 'legacy_note',
            'type' => 'varchar',
            'length' => 64,
            'nullable' => true,
        ];

        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);
        $plan = (new TableSchemaDiffPlanner())->plan($this->desired(), $observed);

        self::assertSame(MigrationRisk::R4, $plan->risk);
        self::assertTrue($plan->blocked);
        self::assertTrue($plan->recoveryRequired);
        $operation = $this->operation($plan->operations, 'drop_column', 'columns.legacy_note');
        self::assertSame(MigrationRisk::R4, $operation->risk);
        self::assertTrue($operation->blocked);
        self::assertTrue($operation->recoveryRequired);
        self::assertContains('verified_restore_point_required', $operation->preconditions);
    }

    public function testDecimalScaleIncreaseThatReducesIntegerCapacityIsR3NotSafeWidening(): void
    {
        $payload = $this->payload();
        $payload['columns'][2]['precision'] = 10;
        $payload['columns'][2]['scale'] = 4;
        $desired = $this->desired($payload);

        $metadata = $this->observedMetadata();
        $metadata['columns'][2]['precision'] = 10;
        $metadata['columns'][2]['scale'] = 2;
        $observed = (new ObservedTableSchemaNormalizer())->normalize('orders', $metadata);

        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);
        $operation = $this->operation($plan->operations, 'alter_column_type', 'columns.amount');

        self::assertSame(MigrationRisk::R3, $operation->risk);
        self::assertTrue($operation->blocked);
        self::assertTrue($operation->recoveryRequired);
        self::assertContains('decimal_values_fit_target', $operation->preconditions);
        self::assertSame(MigrationRisk::R3, $plan->risk);
        self::assertTrue($plan->blocked);
        self::assertTrue($plan->recoveryRequired);
    }

    public function testPlanningSourcesContainNoProviderDdlOrDatabaseMutationPrimitive(): void
    {
        foreach ([ObservedTableSchemaNormalizer::class, TableSchemaDiffPlanner::class] as $class) {
            $reflection = new \ReflectionClass($class);
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
                'NativeWpdbAdapter',
                'DatabaseAdapterInterface',
                'MigrationRunner',
                '$wpdb',
            ] as $forbidden) {
                self::assertStringNotContainsString($forbidden, $source, $class . ' must remain execution-free.');
            }
        }
    }

    /**
     * @param list<\WPEssential\Modules\CustomTables\Migration\MigrationOperation> $operations
     */
    private function operation(array $operations, string $type, string $target): \WPEssential\Modules\CustomTables\Migration\MigrationOperation
    {
        foreach ($operations as $operation) {
            if ($operation->type === $type && $operation->target === $target) {
                return $operation;
            }
        }

        self::fail('Expected migration operation ' . $type . ' for ' . $target . '.');
    }

    /** @param array<string,mixed>|null $payload */
    private function desired(?array $payload = null): \WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor
    {
        $definition = new Definition(
            id: self::ID,
            slug: 'orders',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: $payload ?? $this->payload(),
            revision: 5,
        );

        return (new TableDefinitionCompiler())->compile($definition);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'key' => 'orders',
            'label' => 'Orders',
            'desired_schema_version' => 5,
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
            'data_classification' => 'confidential',
        ];
    }

    /** @return array<string,mixed> */
    private function observedMetadata(): array
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
        ];
    }
}
