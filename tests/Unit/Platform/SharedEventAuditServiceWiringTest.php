<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Audit\AuditServices;
use WPEssential\Platform\Events\EventServices;

final class SharedEventAuditServiceWiringTest extends TestCase
{
    public function testStablePlatformOwnedServiceIds(): void
    {
        self::assertSame('platform.events', EventServices::BUS);
        self::assertSame('platform.audit', AuditServices::LOGGER);
    }

    public function testBootstrapPublishesOneCanonicalEventBusAndAuditLoggerBeforeModules(): void
    {
        $pluginPath = dirname(__DIR__, 3) . '/frameworks/Bootstrap/Plugin.php';
        $source = file_get_contents($pluginPath);

        self::assertIsString($source);
        self::assertSame(1, substr_count($source, '$events = new EventBus();'));
        self::assertSame(1, substr_count($source, '$services->set(EventServices::BUS, $events);'));
        self::assertSame(1, substr_count($source, '$services->set(AuditServices::LOGGER, $audit);'));

        $eventPublication = strpos($source, '$services->set(EventServices::BUS, $events);');
        $auditPublication = strpos($source, '$services->set(AuditServices::LOGGER, $audit);');
        $moduleRegistration = strpos($source, 'self::$kernel->registerModule(new CustomPostTypeModule());');

        self::assertIsInt($eventPublication);
        self::assertIsInt($auditPublication);
        self::assertIsInt($moduleRegistration);
        self::assertLessThan($moduleRegistration, $eventPublication);
        self::assertLessThan($moduleRegistration, $auditPublication);
    }

    public function testAuditPersistenceSelectionMatchesBootstrapPersistenceAvailability(): void
    {
        $pluginPath = dirname(__DIR__, 3) . '/frameworks/Bootstrap/Plugin.php';
        $source = file_get_contents($pluginPath);

        self::assertIsString($source);
        self::assertStringContainsString('$audit = $database instanceof NativeWpdbAdapter', $source);
        self::assertStringContainsString('? new PersistentAuditLogger($database)', $source);
        self::assertStringContainsString(': new InMemoryAuditLogger();', $source);
        self::assertStringContainsString(
            '$migrationCoordinator->register(new CreateAuditEventsTableMigration($database));',
            $source,
        );

        $migration = strpos($source, '$migrationCoordinator->register(new CreateAuditEventsTableMigration($database));');
        $runPending = strpos($source, '$migrationCoordinator->runPending();');
        self::assertIsInt($migration);
        self::assertIsInt($runPending);
        self::assertLessThan($runPending, $migration);
    }

    public function testSharedWiringContainsNoStatusPrivateCoupling(): void
    {
        $eventServices = file_get_contents(dirname(__DIR__, 3) . '/frameworks/Platform/Events/EventServices.php');
        $auditServices = file_get_contents(dirname(__DIR__, 3) . '/frameworks/Platform/Audit/AuditServices.php');

        self::assertIsString($eventServices);
        self::assertIsString($auditServices);
        self::assertStringNotContainsString('Modules\\Status', $eventServices);
        self::assertStringNotContainsString('Modules\\Status', $auditServices);
    }
}
