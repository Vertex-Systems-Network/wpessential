<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\CustomTablesAdminController;
use WPEssential\Modules\CustomTables\CustomTablesCapabilities;
use WPEssential\Modules\CustomTables\CustomTablesModule;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunLifecycleService;

final class CustomTablesCapabilitiesTest extends TestCase
{
    private const CAP = 'wpessential_apply_table_migrations';

    public function testModuleAndAdminControllerUseDedicatedCapability(): void
    {
        self::assertSame(self::CAP, CustomTablesCapabilities::APPLY_MIGRATIONS);
        self::assertSame(self::CAP, CustomTablesModule::CAPABILITY);
        self::assertSame(self::CAP, CustomTablesAdminController::CAPABILITY);
    }

    public function testServicesDefaultToDedicatedCapability(): void
    {
        foreach ([
            R1MigrationRunLifecycleService::class,
            WpdbMigrationExecutionConfirmationIssuer::class,
            MigrationExecutionAuthorizationPolicyAdapter::class,
        ] as $class) {
            $default = null;
            foreach ((new \ReflectionClass($class))->getConstructor()?->getParameters() ?? [] as $parameter) {
                if ($parameter->getName() === 'capability') {
                    $default = $parameter->getDefaultValue();
                }
            }
            self::assertSame(self::CAP, $default, $class);
        }
    }

    public function testGrantsToManageOptionsUsers(): void
    {
        $caps = CustomTablesCapabilities::filterUserCaps(['manage_options' => true], [self::CAP], [], (object) ['ID' => 3]);

        self::assertTrue($caps[self::CAP]);
    }

    public function testNoGrantWithoutManageOptions(): void
    {
        self::assertArrayNotHasKey(self::CAP, CustomTablesCapabilities::filterUserCaps(['edit_posts' => true], [self::CAP]));
        self::assertArrayNotHasKey(self::CAP, CustomTablesCapabilities::filterUserCaps(['manage_options' => false], [self::CAP]));
    }

    public function testExplicitDenyIsPreserved(): void
    {
        $caps = CustomTablesCapabilities::filterUserCaps(['manage_options' => true, self::CAP => false], [self::CAP]);

        self::assertFalse($caps[self::CAP]);
    }

    public function testUnrelatedCapabilityChecksAreUntouched(): void
    {
        $caps = CustomTablesCapabilities::filterUserCaps(['manage_options' => true], ['edit_posts']);

        self::assertSame(['manage_options' => true], $caps);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testFilterCanRevokeDefaultGrant(): void
    {
        $GLOBALS['wpe_ct_cap_filter_calls'] = [];
        eval('function apply_filters($hook, $value, ...$args) { $GLOBALS["wpe_ct_cap_filter_calls"][] = [$hook, $value, ...$args]; return $hook === "wpessential_custom_tables_grant_migration_capability" && $args[0] === 9 ? false : $value; }');

        $denied  = CustomTablesCapabilities::filterUserCaps(['manage_options' => true], [self::CAP], [], (object) ['ID' => 9]);
        $granted = CustomTablesCapabilities::filterUserCaps(['manage_options' => true], [self::CAP], [], (object) ['ID' => 4]);

        self::assertArrayNotHasKey(self::CAP, $denied);
        self::assertTrue($granted[self::CAP]);
        self::assertSame([CustomTablesCapabilities::GRANT_FILTER, true, 9], $GLOBALS['wpe_ct_cap_filter_calls'][0]);
    }
}
