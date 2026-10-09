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
        if (
            $context->channel !== ExecutionChannel::Internal
            || !$context->principal->isAuthenticated()
            || $context->principal->actorType !== 'user'
            || !$this->isCurrentWordPressContext($context)
            || !$this->capabilities->can($context, 'manage_options')
        ) {
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

        return ['status' => 'created_draft', 'definition_id' => $draft->id];
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
