<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetFormActionExecutionResultAdapter
{
    public const RESULT_STATUS_CHANGED = 'status_changed';
    public const RESULT_ALREADY_TARGET_STATUS = 'already_target_status';

    /**
     * @param array<string,mixed> $input
     * @return array{result_code:string,notice:string}|null
     */
    public function adapt(mixed $result, array $input): ?array
    {
        if (!$this->validInput($input) || !is_array($result)) {
            return null;
        }

        $keys = array_keys($result);
        sort($keys, SORT_STRING);
        if ($keys !== [
            'changed',
            'definition_id',
            'enabled',
            'previous_revision',
            'result_code',
            'revision',
            'status',
        ]) {
            return null;
        }

        $definitionId = $result['definition_id'] ?? null;
        $previousRevision = $result['previous_revision'] ?? null;
        $revision = $result['revision'] ?? null;
        $status = $result['status'] ?? null;
        $enabled = $result['enabled'] ?? null;
        $changed = $result['changed'] ?? null;
        $resultCode = $result['result_code'] ?? null;

        $expectedStatus = $input['enabled']
            ? DefinitionStatus::Published->value
            : DefinitionStatus::Disabled->value;

        if (
            !is_string($definitionId)
            || !is_int($previousRevision)
            || !is_int($revision)
            || !is_string($status)
            || !is_bool($enabled)
            || !is_bool($changed)
            || !is_string($resultCode)
            || $definitionId !== $input['definition_id']
            || $previousRevision !== $input['expected_revision']
            || $enabled !== $input['enabled']
            || $status !== $expectedStatus
        ) {
            return null;
        }

        if (
            $resultCode === self::RESULT_STATUS_CHANGED
            && $changed === true
            && $revision === $input['expected_revision'] + 1
        ) {
            return [
                'result_code' => self::RESULT_STATUS_CHANGED,
                'notice' => 'Action completed successfully. Refresh to view the current state.',
            ];
        }

        if (
            $resultCode === self::RESULT_ALREADY_TARGET_STATUS
            && $changed === false
            && $revision === $input['expected_revision']
        ) {
            return [
                'result_code' => self::RESULT_ALREADY_TARGET_STATUS,
                'notice' => 'The action was already in the requested state. Refresh to view the current state.',
            ];
        }

        return null;
    }

    /** @param array<string,mixed> $input */
    private function validInput(array $input): bool
    {
        $keys = array_keys($input);
        sort($keys, SORT_STRING);
        if ($keys !== ['definition_id', 'enabled', 'expected_revision']) {
            return false;
        }

        return is_string($input['definition_id'])
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $input['definition_id']) === 1
            && is_int($input['expected_revision'])
            && $input['expected_revision'] >= 1
            && is_bool($input['enabled']);
    }
}
