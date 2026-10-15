<?php
/**
 * Import a mapped dashboard preset through an authorization-checked Draft boundary.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
	exit;
}

use WPEssential\Platform\Auth\ExecutionContext;

/**
 * Internal-only orchestration: validated source UUID mapping -> destination
 * candidate -> reauthorized atomic Draft create. No public Ability or route.
 */
final readonly class DashboardWidgetPresetMappedDraftImportService
{
	/**
	 * Construct the mapping preview and atomic Draft import orchestration.
	 *
	 * @param DashboardWidgetPresetPortabilityMappingPreviewService $mapping Preview service.
	 * @param DashboardWidgetPresetDraftImportService               $draftImporter Trusted importer.
	 */
	public function __construct(
		private DashboardWidgetPresetPortabilityMappingPreviewService $mapping,
		private DashboardWidgetPresetDraftImportService $draftImporter,
	) {}

	/**
	 * Preview a source mapping, then reauthorize the atomic Draft write.
	 *
	 * @param ExecutionContext $context Current authorized context.
	 * @param array            $sourceSnapshot Untrusted source snapshot.
	 * @param string           $targetPresetId Canonical target preset UUID.
	 * @param array            $widgetIdMap Mapping of source to target widget IDs.
	 * @param string           $targetSlug Destination slug.
	 * @return array{status:string,definition_id?:string}
	 */
	public function importMappedDraft(
		ExecutionContext $context,
		array $sourceSnapshot,
		string $targetPresetId,
		array $widgetIdMap,
		string $targetSlug,
	): array {
		// Before processing any untrusted source contents, reject callers
		// outside the existing current WordPress user/site/network capability
		// and internal execution channel gate. The atomic importer rechecks.
		if (!$this->draftImporter->isAuthorizedContext($context)) {
			return ['status' => 'forbidden'];
		}

		$assessment = $this->mapping->preview($sourceSnapshot, $targetPresetId, $widgetIdMap);
		if ($assessment['status'] === 'id_conflict') {
			return ['status' => 'id_conflict'];
		}
		if ($assessment['status'] !== 'valid_candidate' || !is_array($assessment['candidate_snapshot'])) {
			return ['status' => 'invalid_snapshot'];
		}

		// Mapping preview cannot authorize a write; this second boundary
		// independently verifies Published widget references, current caller
		// privileges and inserts one Draft only via atomic create().
		return $this->draftImporter->importDraft(
			$context,
			$assessment['candidate_snapshot'],
			$targetSlug,
		);
	}
}
