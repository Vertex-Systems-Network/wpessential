<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use Throwable;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/**
 * Deliberately internal-only and Draft-only. No Ability, AJAX, UI or REST
 * route may expose this service. A checksum is never an authorization token.
 */
final readonly class DashboardWidgetPresetDraftImportService
{
    public function __construct(
        private DefinitionCreateOnlyRepositoryInterface $definitions,
        private DashboardWidgetPresetImportPreflightService $preflight,
        private CapabilityCheckerInterface $capabilities,
    ) {}

    /**
     * @param array<mixed> $snapshot
     * @return array{status:string,definition_id?:string}
     */
    public function importDraft(ExecutionContext $context, array $snapshot, string $slug): array
    {
        if (!$this->isAuthorizedContext($context)) {
            return ['status' => 'forbidden'];
        }

        // No remote trust is inferred from sha256. Preflight validates the
        // complete canonical V1 envelope and all Published widget references.
        $assessment = $this->preflight->preflight($snapshot);
        if ($assessment['status'] === 'id_conflict') {
            return ['status' => 'id_conflict'];
        }
        if ($assessment['status'] !== 'valid_candidate') {
            return ['status' => 'invalid_snapshot'];
        }

        $payload = $snapshot['payload'];
        if ($payload['assignment']['network_default'] !== false) {
            // Never assert network-wide authorization using site capability.
            return ['status' => 'forbidden'];
        }

        try {
            $draft = new Definition(
                id: $payload['definition_id'],
                slug: $slug,
                type: DashboardWidgetPresetDefinition::TYPE,
                schemaVersion: 1,
                ownerSurfaceId: DashboardWidgetPresetDefinition::OWNER_SURFACE_ID,
                status: DefinitionStatus::Draft,
                payload: [
                    'preset' => [
                        'label' => $payload['label'],
                        'widget_definition_ids' => $payload['widget_definition_ids'],
                        'assignment' => $payload['assignment'],
                    ],
                ],
                revision: 1,
            );
        } catch (InvalidArgumentException) {
            return ['status' => 'invalid_snapshot'];
        }

        // A Published widget may change while the Draft is being inserted.
        // Snapshot every bounded reference independently AFTER preflight and
        // BEFORE the one atomic create, without trusting its source checksum
        // as authorization or touching any WordPress native preferences.
        try {
            $widgetFingerprints = [];
            foreach ($payload['widget_definition_ids'] as $widgetId) {
                $fingerprint = $this->publishedWidgetFingerprint($widgetId);
                if ($fingerprint === null) {
                    return ['status' => 'invalid_snapshot'];
                }
                $widgetFingerprints[$widgetId] = $fingerprint;
            }
        } catch (Throwable) {
            // A failed precreate read must not issue even the first write.
            return ['status' => 'invalid_snapshot'];
        }

        try {
            // The opt-in implementation enforces id + type/slug collision
            // checks atomically. Never emulate create() with get() + save().
            $this->definitions->create($draft);
        } catch (Throwable) {
            try {
                if ($this->definitions->get($draft->id) instanceof Definition) {
                    return ['status' => 'id_conflict'];
                }
            } catch (Throwable) {
                // Storage failure stays generic and non-disclosing.
            }

            return ['status' => 'write_failed'];
        }

        // A successful adapter return alone is not persistence evidence.
        // Verify the stored Draft once, without issuing a second write or
        // claiming rollback if a partial insert already occurred.
        try {
            $persisted = $this->definitions->get($draft->id);
            if (
                !$persisted instanceof Definition
                || $persisted->id !== $draft->id
                || $persisted->slug !== $draft->slug
                || $persisted->type !== $draft->type
                || $persisted->schemaVersion !== $draft->schemaVersion
                || $persisted->ownerSurfaceId !== $draft->ownerSurfaceId
                || $persisted->status !== DefinitionStatus::Draft
                || $persisted->revision !== 1
                || $persisted->dependencies !== []
                || !hash_equals($draft->computedChecksum(), $persisted->computedChecksum())
            ) {
                return ['status' => 'write_failed'];
            }

            // Recheck current Published Surface-10 widget references AFTER
            // atomic insertion, not only during preflight. A widget may have
            // changed between validation and create(). Do not report success
            // for a persisted Draft whose target catalog is now invalid.
            // This is read-only; never rollback or retry an uncertain insert.
            (new DashboardWidgetPresetCompiler($this->definitions))->compile($persisted);

            // Published is not sufficient if a concurrent actor silently
            // revised the referenced widget. An existing inserted Draft may
            // remain; report generic failure, NEVER retry or claim rollback.
            foreach ($widgetFingerprints as $widgetId => $priorFingerprint) {
                if ($this->publishedWidgetFingerprint($widgetId) !== $priorFingerprint) {
                    return ['status' => 'write_failed'];
                }
            }

            // A caller can lose capability or switch WordPress user/site/
            // network context while create() is in flight. A precreate allow
            // does not certify the *response* as authorized after insertion.
            // An inserted Draft may already exist: never retry or roll back.
            if (!$this->isAuthorizedContext($context)) {
                return ['status' => 'write_failed'];
            }
        } catch (Throwable) {
            // Do not leak stored data or report created_draft on a failed read.
            return ['status' => 'write_failed'];
        }

        return ['status' => 'created_draft', 'definition_id' => $draft->id];
    }

    /**
     * A bounded value fingerprint of a currently Published Surface-10 widget.
     * Rehydrated Definition objects compare by data, not object identity.
     * No signing/trust/authorization is inferred from the computed checksum.
     *
     * @return array<string,mixed>|null
     */
    private function publishedWidgetFingerprint(string $widgetId): ?array
    {
        $widget = $this->definitions->get($widgetId);
        if (
            !$widget instanceof Definition
            || $widget->id !== $widgetId
            || $widget->type !== DashboardWidgetDefinition::TYPE
            || $widget->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID
            || $widget->status !== DefinitionStatus::Published
        ) {
            return null;
        }

        $payloadChecksum = $widget->computedChecksum();
        if ($widget->checksum !== null && !hash_equals($widget->checksum, $payloadChecksum)) {
            return null;
        }

        return [
            'id' => $widget->id,
            'slug' => $widget->slug,
            'type' => $widget->type,
            'owner_surface_id' => $widget->ownerSurfaceId,
            'schema_version' => $widget->schemaVersion,
            'status' => $widget->status->value,
            'revision' => $widget->revision,
            'dependencies' => $widget->dependencies,
            'declared_checksum' => $widget->checksum,
            'computed_checksum' => $payloadChecksum,
        ];
    }

    /**
     * Pure internal caller gate reusable by mapped import BEFORE processing
     * untrusted external snapshot contents. The atomic importer also enforces
     * this same gate, never relying on a preview as authorization.
     */
    public function isAuthorizedContext(ExecutionContext $context): bool
    {
        return $context->channel === ExecutionChannel::Internal
            && $context->principal->isAuthenticated()
            && $context->principal->actorType === 'user'
            && $this->isCurrentWordPressContext($context)
            && $this->capabilities->can($context, 'manage_options');
    }

    private function isCurrentWordPressContext(ExecutionContext $context): bool
    {
        // Protect normal WordPress integration against forged or stale
        // execution-context identities, including cross-blog impersonation.
        if (
            function_exists('get_current_user_id')
            && (int) get_current_user_id() !== $context->principal->userId
        ) {
            return false;
        }
        if (
            function_exists('get_current_blog_id')
            && (int) get_current_blog_id() !== $context->siteId
        ) {
            return false;
        }
        if (
            $context->networkId !== null
            && function_exists('get_current_network_id')
            && (int) get_current_network_id() !== $context->networkId
        ) {
            return false;
        }

        return true;
    }
}
