<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;

final class AdminColumnsPersonalPreferenceAbilityWiringTest extends TestCase
{
    public function testModuleRegistersCanonicalPersonalPreferenceServiceAbilityAndNonceRoute(): void
    {
        $module = file_get_contents(
            dirname(__DIR__, 4) . '/frameworks/Modules/AdminColumns/AdminColumnsModule.php',
        );
        self::assertIsString($module);

        self::assertStringContainsString(
            "SERVICE_PERSONAL_PREFERENCES = 'module.admin-columns.personal-preferences'",
            $module,
        );
        self::assertStringContainsString('new AdminColumnsPersonalPreferenceStore(', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::ABILITY', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::AJAX_TYPE', $module);
        self::assertStringContainsString('operation: NonceOperation::Update', $module);
        self::assertStringContainsString("'action' => ['type' => 'string', 'enum' => ['load', 'save', 'reset']]", $module);
        self::assertStringNotContainsString("'user_id' =>", $module);
    }

    public function testHandlerUsesOnlyExecutionPrincipalForUserIdentity(): void
    {
        $handler = file_get_contents(
            dirname(__DIR__, 4) . '/frameworks/Modules/AdminColumns/AdminColumnsPersonalPreferenceAbilityHandler.php',
        );
        self::assertIsString($handler);

        self::assertStringContainsString('$context->principal->userId', $handler);
        self::assertStringContainsString("actorType !== 'user'", $handler);
        self::assertStringContainsString("private const INPUT_KEYS = ['action', 'view_id', 'expected_view_revision', 'preference']", $handler);
        self::assertStringNotContainsString('$input[\'user_id\']', $handler);
    }
}
