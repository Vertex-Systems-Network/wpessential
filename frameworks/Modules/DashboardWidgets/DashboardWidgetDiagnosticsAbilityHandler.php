<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
	exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetDiagnosticsAbilityHandler implements AbilityHandlerInterface
{
	private DashboardWidgetRuntimeClock $clock;

	public function __construct(
		private DefinitionRepositoryInterface $definitions,
		private DashboardWidgetContentClassCompiler $contentClassCompiler,
		private DashboardWidgetRegistrationCompiler $registrationCompiler,
		?DashboardWidgetRuntimeClock $clock = null,
	) {
		$this->clock = $clock ?? new DashboardWidgetRuntimeClock();
	}

	public function handle(array $input, ExecutionContext $context): mixed
	{
		foreach (array_keys($input) as $key) {
			if ($key !== 'definition_id') {
				throw new InvalidArgumentException('Dashboard Widget diagnostics input contains an unsupported key.');
			}
		}

		$definition_id = null;
		if (array_key_exists('definition_id', $input)) {
			$definition_id = $input['definition_id'];
			if (!is_string($definition_id) || !$this->isUuid($definition_id)) {
				throw new InvalidArgumentException(
					'Dashboard Widget diagnostics definition_id must be a lowercase RFC 4122 UUID.',
				);
			}
		}

		$definitions = $definition_id === null
			? $this->ownedDefinitions()
			: [$this->owned($definition_id)];

		$diagnostics = array_map($this->diagnose(...), $definitions);
		$published = count(array_filter(
			$diagnostics,
			static fn (array $item): bool => $item['status'] === DefinitionStatus::Published->value,
		));
		$ready = count(array_filter(
			$diagnostics,
			static fn (array $item): bool => $item['runtime_state'] === 'ready',
		));
		$blocked = count(array_filter(
			$diagnostics,
			static fn (array $item): bool => $item['runtime_state'] === 'blocked',
		));
		$inactive = count(array_filter(
			$diagnostics,
			static fn (array $item): bool => $item['runtime_state'] === 'inactive',
		));
		$unhealthy = count(array_filter(
			$diagnostics,
			static fn (array $item): bool => $item['issues'] !== [],
		));

		return [
			'summary' => [
				'definitions' => count($diagnostics),
				'published' => $published,
				'inactive' => $inactive,
				'ready' => $ready,
				'blocked' => $blocked,
				'unhealthy' => $unhealthy,
			],
			'definitions' => $diagnostics,
		];
	}

	/** @return list<Definition> */
	private function ownedDefinitions(): array
	{
		$definitions = array_values(array_filter(
			$this->definitions->byType(DashboardWidgetDefinition::TYPE),
			static fn (Definition $definition): bool =>
				$definition->ownerSurfaceId === DashboardWidgetDefinition::OWNER_SURFACE_ID,
		));
		usort(
			$definitions,
			static fn (Definition $left, Definition $right): int =>
				[$left->slug, $left->id] <=> [$right->slug, $right->id],
		);

		return $definitions;
	}

	/** @return array<string,mixed> */
	private function diagnose(Definition $definition): array
	{
		$issues = [];
		$checksum_status = 'missing';

		try {
			if ($definition->checksum === null) {
				$issues[] = $this->issue(
					'dashboard-widget.diagnostics.checksum-missing',
					'checksum',
					'Stored Dashboard Widget definition does not have a canonical checksum.',
				);
			} elseif (hash_equals($definition->checksum, $definition->computedChecksum())) {
				$checksum_status = 'valid';
			} else {
				$checksum_status = 'mismatch';
				$issues[] = $this->issue(
					'dashboard-widget.diagnostics.checksum-mismatch',
					'checksum',
					'Stored Dashboard Widget checksum does not match the canonical payload.',
				);
			}
		} catch (Throwable) {
			$checksum_status = 'error';
			$issues[] = $this->issue(
				'dashboard-widget.diagnostics.checksum-error',
				'checksum',
				'Dashboard Widget checksum could not be recomputed.',
			);
		}

		$runtime_state = 'inactive';
		$content_type = null;
		$target = null;
		$references = [
			'background_job_id' => null,
			'action_ability_id' => null,
			'action_input_present' => false,
		];
		$lifecycle = null;

		if ($definition->status === DefinitionStatus::Published) {
			$runtime_state = 'blocked';

			try {
				$content_class = $this->contentClassCompiler->compile($definition);
				$content_type = $content_class->contentType;
			} catch (Throwable) {
				$issues[] = $this->issue(
					'dashboard-widget.diagnostics.content-invalid',
					'widget.type',
					'Published Dashboard Widget content class is not runtime-ready.',
				);
			}

			if ($content_type !== null) {
				try {
					$descriptor = $this->registrationCompiler->compile($definition);
					$runtime_state = 'ready';
					$target = [
						'context' => $descriptor->context,
						'priority' => $descriptor->priority,
						'network_dashboard' => $descriptor->networkDashboard,
						'site_scope' => $descriptor->siteScope,
						'site_ids' => $descriptor->siteIds,
						'default_hidden' => $descriptor->defaultHidden,
						'default_collapsed' => $descriptor->defaultCollapsed,
						'manual_refresh' => $descriptor->manualRefresh,
					];
					$references = [
						'background_job_id' => $descriptor->backgroundJobId,
						'action_ability_id' => $descriptor->actionAbilityId,
						'action_input_present' => $descriptor->actionInput !== null,
					];
					$lifecycle = [
						'schedule_start' => $descriptor->scheduleStartAt !== null
							? gmdate('Y-m-d\TH:i:s\Z', $descriptor->scheduleStartAt)
							: null,
						'schedule_end' => $descriptor->scheduleEndAt !== null
							? gmdate('Y-m-d\TH:i:s\Z', $descriptor->scheduleEndAt)
							: null,
						'state' => null,
					];
					try {
						$lifecycle['state'] = $descriptor->lifecycleStateAt($this->clock->now());
					} catch (Throwable) {
						$runtime_state = 'blocked';
						$issues[] = $this->issue(
							'dashboard-widget.diagnostics.lifecycle-clock-unavailable',
							'widget.lifecycle',
							'Dashboard Widget lifecycle state could not be evaluated safely.',
						);
					}
				} catch (Throwable) {
					$issues[] = $this->issue(
						'dashboard-widget.diagnostics.registration-invalid',
						'widget',
						'Published Dashboard Widget registration is not runtime-ready.',
					);
				}
			}
		}

		return [
			'id' => $definition->id,
			'slug' => $definition->slug,
			'status' => $definition->status->value,
			'schema_version' => $definition->schemaVersion,
			'definition_revision' => $definition->revision,
			'checksum_status' => $checksum_status,
			'runtime_state' => $runtime_state,
			'content_type' => $content_type,
			'target' => $target,
			'lifecycle' => $lifecycle,
			'references' => $references,
			'dependency_count' => count($definition->dependencies),
			'issues' => $issues,
		];
	}

	/** @return array{id:string,severity:string,field:string,message:string} */
	private function issue(string $id, string $field, string $message): array
	{
		return [
			'id' => $id,
			'severity' => 'blocked',
			'field' => $field,
			'message' => $message,
		];
	}

	private function owned(string $id): Definition
	{
		$definition = $this->definitions->get($id);
		if (
			!$definition instanceof Definition
			|| $definition->type !== DashboardWidgetDefinition::TYPE
			|| $definition->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID
		) {
			throw new RuntimeException('Dashboard Widget definition was not found in canonical Surface 10.');
		}

		return $definition;
	}

	private function isUuid(string $value): bool
	{
		return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
	}
}
