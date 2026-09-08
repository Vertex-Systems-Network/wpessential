<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Definition\TableDefinitionCompiler;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\MigrationPlan;
use WPEssential\Modules\CustomTables\Migration\MigrationRisk;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Migration\ProviderDdlPreviewCompiler;
use WPEssential\Modules\CustomTables\Migration\TableSchemaDiffPlanner;
use WPEssential\Modules\CustomTables\Schema\Ct1ManagedTableIdentity;
use WPEssential\Modules\CustomTables\Schema\ObservedTableSchemaNormalizer;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class ProviderDdlPreviewCompilerSafetyTest extends TestCase
{
    private const ID = '78888888-8888-4888-8888-888888888888';

    public function testNoOpPlanCompilesToStableEmptyExecutionFreePreview(): void
    {
        $desired = $this->desired('safe');
        $plan = new MigrationPlan(
            id: '89999999-9999-4999-8999-999999999999',
            tableKey: 'settings',
            sourceObservedFingerprint: str_repeat('1', 64),
            targetDefinitionId: self::ID,
            targetRevision: $desired->revision,
            targetSchemaVersion: $desired->desiredSchemaVersion,
            targetCompatibilityFingerprint: $desired->compatibilityFingerprint,
            operations: [],
            findings: [],
            risk: MigrationRisk::R0,
            blocked: false,
            recoveryRequired: false,
            fingerprint: str_repeat('2', 64),
        );
        $compiler = new ProviderDdlPreviewCompiler();
        $identity = Ct1ManagedTableIdentity::derive(self::ID, 'settings', 'wp_');
        $profile = ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0');

        $first = $compiler->compile($plan, $desired, $identity, $profile);
        $second = $compiler->compile($plan, $desired, $identity, $profile);

        self::assertTrue($first->isNoOp());
        self::assertFalse($first->executionAllowed);
        self::assertSame([], $first->statements);
        self::assertSame($first->fingerprint, $second->fingerprint);
    }

    public function testBackslashBearingStringDefaultFailsClosedAcrossSqlModes(): void
    {
        $desired = $this->desired("unsafe\\'value");
        $observed = (new ObservedTableSchemaNormalizer())->normalize('settings', ['exists' => false]);
        $plan = (new TableSchemaDiffPlanner())->plan($desired, $observed);

        $this->expectException(InvalidArgumentException::class);
        (new ProviderDdlPreviewCompiler())->compile(
            $plan,
            $desired,
            Ct1ManagedTableIdentity::derive(self::ID, 'settings', 'wp_'),
            ProviderCapabilityProfile::fromTrustedServerInfo('8.4.0'),
        );
    }

    private function desired(string $default): TableSchemaDescriptor
    {
        return (new TableDefinitionCompiler())->compile(new Definition(
            id: self::ID,
            slug: 'settings',
            type: 'table',
            schemaVersion: 1,
            ownerSurfaceId: 7,
            status: DefinitionStatus::Published,
            payload: [
                'key' => 'settings',
                'label' => 'Settings',
                'desired_schema_version' => 1,
                'columns' => [
                    [
                        'key' => 'id',
                        'type' => 'bigint',
                        'nullable' => false,
                        'auto_increment' => true,
                    ],
                    [
                        'key' => 'value',
                        'type' => 'varchar',
                        'length' => 64,
                        'nullable' => false,
                        'default' => $default,
                    ],
                ],
                'primary_key' => ['id'],
                'indexes' => [],
                'data_classification' => 'internal',
            ],
            revision: 2,
        ));
    }
}
