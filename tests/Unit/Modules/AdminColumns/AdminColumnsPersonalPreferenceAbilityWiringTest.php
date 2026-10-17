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

        // Check operation-to-ability/nonce wiring without relying on tabs vs spaces.
        $normalizedModule = preg_replace('/\\s+/', ' ', $module);
        self::assertIsString($normalizedModule);
        self::assertStringContainsString(
            'AdminColumnsPersonalPreferenceAbilityHandler::LOAD => [ AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_LOAD, AdminColumnsPersonalPreferenceAbilityHandler::AJAX_LOAD, false, NonceOperation::Apply,',
            $normalizedModule,
        );
        self::assertStringContainsString(
            'AdminColumnsPersonalPreferenceAbilityHandler::SAVE => [ AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_SAVE, AdminColumnsPersonalPreferenceAbilityHandler::AJAX_SAVE, true, NonceOperation::Update,',
            $normalizedModule,
        );
        self::assertStringContainsString(
            'AdminColumnsPersonalPreferenceAbilityHandler::RESET => [ AdminColumnsPersonalPreferenceAbilityHandler::ABILITY_RESET, AdminColumnsPersonalPreferenceAbilityHandler::AJAX_RESET, true, NonceOperation::Update,',
            $normalizedModule,
        );

        self::assertStringContainsString('personalPreferenceAbilityInputSchema($preferenceAction)', $module);
        self::assertStringContainsString("\$required = ['view_id', 'expected_view_revision'];", $module);
        self::assertStringContainsString("\$required[] = 'preference';", $module);
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

        $normalizedDeclarations = preg_replace('/\\s+/', ' ', $handler);
        self::assertIsString($normalizedDeclarations);
        self::assertStringContainsString("public const ABILITY_LOAD = 'wpessential/admin-columns/personal-preference-load'", $normalizedDeclarations);
        self::assertStringContainsString("public const ABILITY_SAVE = 'wpessential/admin-columns/personal-preference-save'", $normalizedDeclarations);
        self::assertStringContainsString("public const ABILITY_RESET = 'wpessential/admin-columns/personal-preference-reset'", $normalizedDeclarations);
        self::assertStringContainsString('private string $action', $handler);
        self::assertStringContainsString('$context->principal->userId', $handler);
        self::assertStringContainsString("actorType !== 'user'", $handler);
        self::assertStringContainsString("\$allowed = ['view_id', 'expected_view_revision'];", $handler);
        self::assertStringContainsString("\$allowed[] = 'preference';", $handler);
        self::assertStringNotContainsString("\$input['action']", $handler);
        self::assertStringNotContainsString("\$input['user_id']", $handler);
    }
}
