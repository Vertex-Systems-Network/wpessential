<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;

final class AdminColumnsPersonalPreferenceAbilityWiringTest extends TestCase
{
    public function testModuleRegistersDistinctLoadSaveResetAbilitiesAndNonceOperations(): void
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

        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_LOAD', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::AJAX_LOAD', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_SAVE', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::AJAX_SAVE', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_RESET', $module);
        self::assertStringContainsString('AdminColumnsPersonalPreferenceAbilityHandler::AJAX_RESET', $module);

        self::assertStringContainsString(
            "AdminColumnsPersonalPreferenceAbilityHandler::LOAD => [\n                AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_LOAD,\n                AdminColumnsPersonalPreferenceAbilityHandler::AJAX_LOAD,\n                false,\n                NonceOperation::Apply,",
            $module,
        );
        self::assertStringContainsString(
            "AdminColumnsPersonalPreferenceAbilityHandler::SAVE => [\n                AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_SAVE,\n                AdminColumnsPersonalPreferenceAbilityHandler::AJAX_SAVE,\n                true,\n                NonceOperation::Update,",
            $module,
        );
        self::assertStringContainsString(
            "AdminColumnsPersonalPreferenceAbilityHandler::RESET => [\n                AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_RESET,\n                AdminColumnsPersonalPreferenceAbilityHandler::AJAX_RESET,\n                true,\n                NonceOperation::Update,",
            $module,
        );

        self::assertStringContainsString('personalPreferenceAbilityInputSchema($preferenceAction)', $module);
        self::assertStringContainsString("$required = ['view_id', 'expected_view_revision'];", $module);
        self::assertStringContainsString("$required[] = 'preference';", $module);
        self::assertStringNotContainsString(
            "'action' => ['type' => 'string', 'enum' => ['load', 'save', 'reset']]",
            $module,
        );
        self::assertStringNotContainsString("'user_id' =>", $module);
    }

    public function testHandlerBindsOperationAtConstructionAndUsesOnlyExecutionPrincipalForUserIdentity(): void
    {
        $handler = file_get_contents(
            dirname(__DIR__, 4) . '/frameworks/Modules/AdminColumns/AdminColumnsPersonalPreferenceAbilityHandler.php',
        );
        self::assertIsString($handler);

        self::assertStringContainsString("public const ABILITY_LOAD = 'wpessential/admin-columns/personal-preference/load'", $handler);
        self::assertStringContainsString("public const ABILITY_SAVE = 'wpessential/admin-columns/personal-preference/save'", $handler);
        self::assertStringContainsString("public const ABILITY_RESET = 'wpessential/admin-columns/personal-preference/reset'", $handler);
        self::assertStringContainsString('private string $action', $handler);
        self::assertStringContainsString('$context->principal->userId', $handler);
        self::assertStringContainsString("actorType !== 'user'", $handler);
        self::assertStringContainsString("$allowed = ['view_id', 'expected_view_revision'];", $handler);
        self::assertStringContainsString("$allowed[] = 'preference';", $handler);
        self::assertStringNotContainsString("$input['action']", $handler);
        self::assertStringNotContainsString("$input['user_id']", $handler);
    }
}
