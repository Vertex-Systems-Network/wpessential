<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/**
 * Internal-only, read-only site-local Draft *review* check, NEVER publication
 * approval. No UI, REST, Ability, provider, WordPress usermeta, or writes.
 */
final readonly class DashboardWidgetPresetDraftPublishReviewService
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetPresetCompiler $compiler,
        private DashboardWidgetPresetDraftImportService $draftImporter,
    ) {}

    /**
     * @return array{status:string,publish_authorized:false}
     */
    public function inspect(ExecutionContext $context, string $presetId, string $expectedSha256): array
    {
        // Do not reveal even whether the input or record is valid before the
        // canonical importer-authenticated current WP user/site/network gate.
        if (!$this->draftImporter->isAuthorizedContext($context)) {
            return self::result('forbidden');
        }
        if (
            preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $presetId) !== 1
            || preg_match('/^[0-9a-f]{64}$/', $expectedSha256) !== 1
        ) {
            return self::result('invalid_input');
        }

        try {
            $draft = $this->definitions->get($presetId);
            if (
                !$draft instanceof Definition
                || $draft->type !== DashboardWidgetPresetDefinition::TYPE
                || $draft->ownerSurfaceId !== DashboardWidgetPresetDefinition::OWNER_SURFACE_ID
                || $draft->schemaVersion !== 1
                || $draft->status !== DefinitionStatus::Draft
            ) {
                return self::result('unavailable');
            }

            $currentHash = $draft->computedChecksum();
            if ($draft->checksum !== null && !hash_equals($draft->checksum, $currentHash)) {
                return self::result('invalid_catalog');
            }
            if (!hash_equals($currentHash, $expectedSha256)) {
                return self::result('stale_draft');
            }

            // Compile against CURRENT target Published widget definitions,
            // including canonical bounds, assignment, role and ownership.
            $descriptor = $this->compiler->compile($draft);
            if ($descriptor->networkDefault) {
                // A site-local manage_options principal cannot approve any
                // network-default setting; no network authorization inferred.
                return self::result('forbidden');
            }
        } catch (Throwable) {
            // Untrusted, unreadable or inconsistent repository state must not
            // become positive publish review evidence.
            return self::result('invalid_catalog');
        }

        // A transient eligibility finding is NOT a grant or mutation token.
        return self::result('review_candidate');
    }

    /** @return array{status:string,publish_authorized:false} */
    private static function result(string $status): array
    {
        return ['status' => $status, 'publish_authorized' => false];
    }
}
