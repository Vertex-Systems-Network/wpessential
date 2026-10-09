<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/**
 * Bounded, read-only snapshot *compatibility* assessment. Never imports,
 * authenticates a source, or authorizes a later mutation.
 */
final readonly class DashboardWidgetPresetImportPreflightService
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetPresetCompiler $compiler,
    ) {}

    /**
     * @param array<mixed> $snapshot
     * @return array{status:string,applicable:false}
     */
    public function preflight(array $snapshot): array
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
        ) {
            return self::status('invalid_snapshot');
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
        ) {
            return self::status('invalid_snapshot');
        }

        // Constrain user-controlled arrays before encoding: never accept
        // arbitrary nested objects or files as a portability envelope.
        foreach ($payload['widget_definition_ids'] as $id) {
            if (!is_string($id) || strlen($id) !== 36) {
                return self::status('invalid_snapshot');
            }
        }
        foreach ($assignment['roles'] as $role) {
            if (!is_string($role) || strlen($role) > 64) {
                return self::status('invalid_snapshot');
            }
        }

        try {
            $definition = new Definition(
                id: $payload['definition_id'],
                slug: 'portable-preflight',
                type: DashboardWidgetPresetDefinition::TYPE,
                schemaVersion: 1,
                ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
                status: DefinitionStatus::Published,
                payload: ['preset' => [
                    'label' => $payload['label'],
                    'widget_definition_ids' => $payload['widget_definition_ids'],
                    'assignment' => $assignment,
                ]],
                revision: $payload['revision'],
            );
            $compiled = $this->compiler->compile($definition)->toArray();
            if ($compiled !== $payload) {
                return self::status('invalid_snapshot');
            }

            // This digest is a non-authenticating content fingerprint only.
            $digest = hash('sha256', json_encode(
                $compiled,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            ));
        } catch (InvalidArgumentException|JsonException) {
            return self::status('invalid_snapshot');
        }

        if (!hash_equals($digest, $snapshot['sha256'])) {
            return self::status('integrity_mismatch');
        }

        // A conflict is advisory, not an import authorization. All outcomes
        // have applicable=false and never reveal definitions, hashes or data.
        if ($this->definitions->get($definition->id) instanceof Definition) {
            return self::status('id_conflict');
        }

        return self::status('valid_candidate');
    }

    /** @return array{status:string,applicable:false} */
    private static function status(string $status): array
    {
        return ['status' => $status, 'applicable' => false];
    }
}
