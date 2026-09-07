<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class AdminColumnsCsvExportAbilityHandlerTest extends TestCase
{
    public function testRejectsUnknownTopLevelInputBeforeExportExecution(): void
    {
        $handler = new AdminColumnsCsvExportAbilityHandler($this->uninitializedExportService());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported top-level input');

        $handler->handle([
            'view_id' => '01990f6e-1f30-4000-8000-000000000301',
            'expected_view_revision' => 1,
            'scope_request' => [],
            'controls' => [],
            'runtime' => [],
            'unexpected' => true,
        ], $this->context());
    }

    public function testRequiresEveryTopLevelContractFieldBeforeExportExecution(): void
    {
        $handler = new AdminColumnsCsvExportAbilityHandler($this->uninitializedExportService());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requires "runtime"');

        $handler->handle([
            'view_id' => '01990f6e-1f30-4000-8000-000000000301',
            'expected_view_revision' => 1,
            'scope_request' => [],
            'controls' => [],
        ], $this->context());
    }

    public function testRequiresPositiveExpectedViewRevisionBeforeExportExecution(): void
    {
        $handler = new AdminColumnsCsvExportAbilityHandler($this->uninitializedExportService());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expected_view_revision must be a positive integer');

        $handler->handle([
            'view_id' => '01990f6e-1f30-4000-8000-000000000301',
            'expected_view_revision' => 0,
            'scope_request' => [],
            'controls' => [],
            'runtime' => [],
        ], $this->context());
    }

    private function uninitializedExportService(): AdminColumnsCsvExportService
    {
        $reflection = new ReflectionClass(AdminColumnsCsvExportService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        self::assertInstanceOf(AdminColumnsCsvExportService::class, $service);
        return $service;
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(
            principal: new Principal(7),
            siteId: 1,
        );
    }
}
