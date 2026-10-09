<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class DashboardWidgetPresetPortabilityMappingPreviewService
{
    public function __construct(private DashboardWidgetPresetImportPreflightService $preflight) {}

    /**
     * Pure read-only V1 UUID translation. SHA-256 verifies content integrity,
     * NOT source authenticity, authorization, or permission to import.
     *
     * @param array<mixed> $snapshot
     * @param array<mixed> $widgetIdMap
     * @return array{status:string,applicable:false,candidate_snapshot:array<string,mixed>|null}
     */
    public function preview(array $snapshot, string $targetId, array $widgetIdMap): array
    {
        if (
            array_keys($snapshot) !== ['format', 'version', 'payload', 'sha256']
            || $snapshot['format'] !== DashboardWidgetPresetPortabilityReadService::FORMAT
            || $snapshot['version'] !== DashboardWidgetPresetPortabilityReadService::VERSION
            || !is_array($snapshot['payload'])
            || array_keys($snapshot['payload']) !== [
                'definition_id', 'revision', 'label', 'widget_definition_ids', 'assignment',
            ]
            || !is_string($snapshot['sha256'])
            || preg_match('/^[0-9a-f]{64}$/', $snapshot['sha256']) !== 1
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $targetId) !== 1
        ) {
            return self::result('invalid_snapshot');
        }

        $payload = $snapshot['payload'];
        $assignment = $payload['assignment'];
        if (
            !is_string($payload['definition_id'])
            || !is_int($payload['revision']) || $payload['revision'] < 1
            || !is_string($payload['label']) || strlen($payload['label']) > 160
            || !is_array($payload['widget_definition_ids'])
            || !array_is_list($payload['widget_definition_ids'])
            || count($payload['widget_definition_ids']) < 1
            || count($payload['widget_definition_ids']) > DashboardWidgetPresetDescriptor::MAX_WIDGETS
            || !is_array($assignment)
            || array_keys($assignment) !== ['roles', 'network_default']
            || !is_array($assignment['roles'])
            || !array_is_list($assignment['roles'])
            || count($assignment['roles']) > DashboardWidgetPresetDescriptor::MAX_ROLES
            || !is_bool($assignment['network_default'])
            || count($widgetIdMap) !== count($payload['widget_definition_ids'])
        ) {
            return self::result('invalid_snapshot');
        }

        try {
            $source = new DashboardWidgetPresetDescriptor(
                definitionId: $payload['definition_id'],
                revision: $payload['revision'],
                label: $payload['label'],
                widgetDefinitionIds: $payload['widget_definition_ids'],
                roles: $assignment['roles'],
                networkDefault: $assignment['network_default'],
            );
            if ($source->toArray() !== $payload) {
                return self::result('invalid_snapshot');
            }

            $sourceDigest = hash('sha256', json_encode(
                $payload,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ));
        } catch (InvalidArgumentException|JsonException) {
            return self::result('invalid_snapshot');
        }

        if (!hash_equals($sourceDigest, $snapshot['sha256'])) {
            return self::result('integrity_mismatch');
        }

        $remappedIds = [];
        $seen = [];
        foreach ($source->widgetDefinitionIds as $sourceId) {
            if (!array_key_exists($sourceId, $widgetIdMap)) {
                return self::result('invalid_snapshot');
            }
            $targetWidgetId = $widgetIdMap[$sourceId];
            if (
                !is_string($targetWidgetId)
                || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $targetWidgetId) !== 1
                || isset($seen[$targetWidgetId])
            ) {
                return self::result('invalid_snapshot');
            }
            $remappedIds[] = $targetWidgetId;
            $seen[$targetWidgetId] = true;
        }

        // The exact cardinality requirement also forbids map entries for
        // unspecified source widgets, site IDs, role rewrites or other values.
        try {
            $candidatePayload = (new DashboardWidgetPresetDescriptor(
                definitionId: $targetId,
                revision: $source->revision,
                label: $source->label,
                widgetDefinitionIds: $remappedIds,
                roles: $source->roles,
                networkDefault: $source->networkDefault,
            ))->toArray();
            $candidateSnapshot = [
                'format' => DashboardWidgetPresetPortabilityReadService::FORMAT,
                'version' => DashboardWidgetPresetPortabilityReadService::VERSION,
                'payload' => $candidatePayload,
                'sha256' => hash('sha256', json_encode(
                    $candidatePayload,
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                )),
            ];
        } catch (InvalidArgumentException|JsonException) {
            return self::result('invalid_snapshot');
        }

        $assessment = $this->preflight->preflight($candidateSnapshot);
        if ($assessment['status'] !== 'valid_candidate') {
            // Never expose transformed candidate on an invalid, unavailable,
            // conflicting or otherwise unverified target catalog.
            return self::result($assessment['status']);
        }

        return [
            'status' => 'valid_candidate',
            'applicable' => false,
            'candidate_snapshot' => $candidateSnapshot,
        ];
    }

    /** @return array{status:string,applicable:false,candidate_snapshot:null} */
    private static function result(string $status): array
    {
        return ['status' => $status, 'applicable' => false, 'candidate_snapshot' => null];
    }
}
