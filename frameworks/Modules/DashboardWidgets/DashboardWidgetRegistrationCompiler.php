<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
	exit;
}

use Closure;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Throwable;
use WPEssential\Modules\Cron\CronDefinition;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetRegistrationCompiler
{
	public const SCHEMA_VERSION = 1;

	/** @var list<string> */
	private const PAYLOAD_KEYS = ['widget'];

	/** @var list<string> */
	private const WIDGET_KEYS = ['key', 'title', 'type', 'context', 'priority', 'network_dashboard', 'target', 'inventory', 'presentation', 'visibility', 'render_source', 'refresh', 'action', 'lifecycle'];

	/** @var list<string> */
	private const INVENTORY_KEYS = ['default_hidden'];

	/** @var list<string> */
	private const PRESENTATION_KEYS = ['collapsible', 'default_collapsed', 'dismissible'];

	/** @var list<string> */
	private const TARGET_KEYS = ['scope', 'site_ids', 'network_dashboard'];

	/** @var list<string> */
	private const REFRESH_KEYS = ['background_job', 'manual'];

	/** @var list<string> */
	private const ACTION_KEYS = ['ability_id', 'confirmation', 'input'];

	/** @var list<string> */
	private const LIFECYCLE_KEYS = ['schedule_start', 'schedule_end'];

	private DashboardWidgetVisibilityCompiler $visibilityCompiler;
	private DashboardWidgetContentClassCompiler $contentClassCompiler;

	/** @var null|Closure */
	private ?Closure $backgroundJobResolver;

	/** @var null|Closure */
	private ?Closure $formActionAbilityResolver;

	private ?DashboardWidgetActionInputCompiler $actionInputCompiler;

	public function __construct(
		?DashboardWidgetVisibilityCompiler $visibilityCompiler = null,
		?DashboardWidgetContentClassCompiler $contentClassCompiler = null,
		private ?DashboardWidgetRenderSourceCompiler $renderSourceCompiler = null,
		?callable $backgroundJobResolver = null,
		?callable $formActionAbilityResolver = null,
	) {
		$this->visibilityCompiler = $visibilityCompiler ?? new DashboardWidgetVisibilityCompiler();
		$this->contentClassCompiler = $contentClassCompiler ?? new DashboardWidgetContentClassCompiler();
		$this->backgroundJobResolver = $backgroundJobResolver !== null
			? Closure::fromCallable( $backgroundJobResolver )
			: null;
		$this->formActionAbilityResolver = $formActionAbilityResolver !== null
			? Closure::fromCallable( $formActionAbilityResolver )
			: null;
		$this->actionInputCompiler = $formActionAbilityResolver !== null
			? new DashboardWidgetActionInputCompiler( new AbilityInputValidator(), $formActionAbilityResolver )
			: null;
	}

	public function compile( Definition $definition ): DashboardWidgetRegistrationDescriptor
	{
		DashboardWidgetDefinition::assertOwned( $definition );

		if ( $definition->schemaVersion !== self::SCHEMA_VERSION ) {
			throw new InvalidArgumentException('Dashboard Widget definition schema version is unsupported.');
		}
		if ( $definition->status !== DefinitionStatus::Published ) {
			throw new InvalidArgumentException('Only Published Dashboard Widget definitions may compile for registration.');
		}

		$payload = $definition->payload;
		if ( array_is_list( $payload ) ) {
			throw new InvalidArgumentException('Dashboard Widget definition payload must be an object/map.');
		}
		$this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Dashboard Widget definition payload');

		$widget = $payload['widget'] ?? null;
		if ( !is_array( $widget ) || array_is_list( $widget ) ) {
			throw new InvalidArgumentException('Dashboard Widget registration metadata must be an object/map.');
		}
		$this->assertKnownKeys($widget, self::WIDGET_KEYS, 'Dashboard Widget registration metadata');

		$content_class = $this->contentClassCompiler->compile( $definition );
		$this->visibilityCompiler->compile( $definition );
		$manual_refresh = $this->compileManualRefresh( $widget );
		if ( $content_class->contentType === DashboardWidgetContentClassDescriptor::TYPE_FORM_ACTION ) {
			if ( $manual_refresh ) {
				throw new InvalidArgumentException('Dashboard Widget form_action does not support manual refresh V1.');
			}
			if (array_key_exists('render_source', $widget)) {
				throw new InvalidArgumentException('Dashboard Widget form_action must use the dedicated trusted action presenter, not render_source.');
			}
		} else {
			if ( $this->renderSourceCompiler === null ) {
				throw new InvalidArgumentException('Dashboard Widget trusted render-source compiler is required for registration compilation.');
			}
			$compiled_render_source = $this->renderSourceCompiler->compile( $definition );
			if ( $manual_refresh && $compiled_render_source->loadingState === null ) {
				throw new InvalidArgumentException('Dashboard Widget manual refresh requires a trusted loading_state.');
			}
			if ( !$manual_refresh && $compiled_render_source->loadingState !== null ) {
				throw new InvalidArgumentException('Dashboard Widget loading_state requires refresh.manual=true in bounded V1.');
			}
		}

		$key = $widget['key'] ?? null;
		if (!is_string($key) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $key)) {
			throw new InvalidArgumentException('Dashboard Widget key must be bounded canonical WordPress-safe text.');
		}

		$title = $widget['title'] ?? null;
		if (
			!is_string( $title )
			|| $title === ''
			|| strlen( $title ) > 160
			|| str_contains($title, '<')
			|| str_contains($title, '>')
			|| preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $title)
		) {
			throw new InvalidArgumentException('Dashboard Widget title must be bounded plain text.');
		}

		$context = $widget['context'] ?? null;
		if ( !is_string( $context ) || !in_array( $context, DashboardWidgetRegistrationDescriptor::CONTEXTS, true ) ) {
			throw new InvalidArgumentException('Dashboard Widget context is unsupported.');
		}

		$priority = $widget['priority'] ?? null;
		if (!is_string($priority) || !in_array($priority, DashboardWidgetRegistrationDescriptor::PRIORITIES, true)) {
			throw new InvalidArgumentException('Dashboard Widget priority is unsupported.');
		}

		[$network_dashboard, $site_scope, $site_ids] = $this->compileTarget( $widget );
		$default_hidden = $this->compileDefaultHidden( $widget );
		$this->assertNativeCollapsibleCapability( $widget );
		$default_collapsed = $this->compileDefaultCollapsed( $widget );
		$dismissible = $this->compileDismissible( $widget );
		[$schedule_start_at, $schedule_end_at] = $this->compileLifecycle( $widget );
		$background_job_id = $this->compileBackgroundJobReference( $widget );
		$action_ability_id = $this->compileFormsActionAbilityReference( $widget );
		$action_confirmation = $this->compileActionConfirmation( $widget );
		$action_input = $this->compileActionInput( $definition, $widget );

		if (
			$content_class->contentType === DashboardWidgetContentClassDescriptor::TYPE_FORM_ACTION
			&& ($action_ability_id === null || $action_confirmation === null || $action_input === null)
		) {
			throw new InvalidArgumentException(
				'Dashboard Widget form_action requires canonical action Ability, confirmation and non-empty input descriptors.',
			);
		}

		return new DashboardWidgetRegistrationDescriptor(
			definitionId: $definition->id,
			revision: $definition->revision,
			key: $key,
			title: $title,
			context: $context,
			priority: $priority,
			networkDashboard: $network_dashboard,
			defaultHidden: $default_hidden,
			defaultCollapsed: $default_collapsed,
			dismissible: $dismissible,
			siteScope: $site_scope,
			siteIds: $site_ids,
			backgroundJobId: $background_job_id,
			actionAbilityId: $action_ability_id,
			actionConfirmation: $action_confirmation,
			actionInput: $action_input,
			scheduleStartAt: $schedule_start_at,
			scheduleEndAt: $schedule_end_at,
			manualRefresh: $manual_refresh,
		);
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileActionInput(
		Definition $definition,
		array $widget,
	): ?DashboardWidgetActionInputDescriptor {
		$action = $widget['action'] ?? null;
		if (!is_array($action) || !array_key_exists('input', $action)) {
			return null;
		}

		if ($action['input'] === []) {
			return null;
		}

		if ( $this->actionInputCompiler === null ) {
			throw new InvalidArgumentException('Dashboard Widget action.input requires the canonical Ability Registry.');
		}

		return $this->actionInputCompiler->compile( $definition, $widget );
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileActionConfirmation( array $widget ): ?DashboardWidgetActionConfirmationDescriptor
	{
		if (!array_key_exists('action', $widget)) {
			return null;
		}

		$action = $widget['action'];
		if ( !is_array( $action ) || ( $action !== [] && array_is_list( $action ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget action metadata must be an object/map.');
		}
		$this->assertKnownKeys($action, self::ACTION_KEYS, 'Dashboard Widget action metadata');

		if (!array_key_exists('confirmation', $action)) {
			return null;
		}

		$confirmation = $action['confirmation'];
		if ( !is_array( $confirmation ) || array_is_list( $confirmation ) ) {
			throw new InvalidArgumentException('Dashboard Widget action.confirmation must be an object/map.');
		}

		$keys = ['title', 'message', 'confirm_label', 'cancel_label'];
		$this->assertKnownKeys($confirmation, $keys, 'Dashboard Widget action.confirmation');

		foreach ( $keys as $key ) {
			if ( !array_key_exists( $key, $confirmation ) || !is_string( $confirmation[$key] ) ) {
				throw new InvalidArgumentException(
					'Dashboard Widget action.confirmation requires bounded string title, message, confirm_label and cancel_label fields.',
				);
			}
		}

		return new DashboardWidgetActionConfirmationDescriptor(
			$confirmation['title'],
			$confirmation['message'],
			$confirmation['confirm_label'],
			$confirmation['cancel_label'],
		);
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileFormsActionAbilityReference( array $widget ): ?string
	{
		if (!array_key_exists('action', $widget)) {
			return null;
		}

		$action = $widget['action'];
		if ( !is_array( $action ) || ( $action !== [] && array_is_list( $action ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget action metadata must be an object/map.');
		}
		$this->assertKnownKeys($action, self::ACTION_KEYS, 'Dashboard Widget action metadata');

		if (!array_key_exists('ability_id', $action)) {
			return null;
		}

		$ability_id = $action['ability_id'];
		if (
			!is_string( $ability_id )
			|| preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $ability_id) !== 1
		) {
			throw new InvalidArgumentException('Dashboard Widget action.ability_id must use the canonical ability name shape.');
		}
		if ( $this->formActionAbilityResolver === null ) {
			throw new InvalidArgumentException('Dashboard Widget action.ability_id requires the canonical Ability Registry.');
		}

		try {
			$record = ( $this->formActionAbilityResolver )( $ability_id );
		} catch ( Throwable ) {
			throw new InvalidArgumentException('Dashboard Widget action ability reference could not be resolved.');
		}

		$input_schema = is_array($record) ? ($record['input_schema'] ?? null) : null;
		if (
			!is_array( $record )
			|| ($record['name'] ?? null) !== $ability_id
			|| ($record['owner_surface_id'] ?? null) !== FormWorkflowDefinition::OWNER_SURFACE_ID
			|| ($record['mutates'] ?? null) !== true
			|| ($record['ui_allowed'] ?? null) !== true
			|| !is_array( $input_schema )
		) {
			throw new InvalidArgumentException('Dashboard Widget action ability must be a mutating Forms & Workflows UI ability.');
		}

		$has_authored_input = array_key_exists('input', $action) && $action['input'] !== [];
		if ( ( $has_authored_input && $input_schema === [] ) || ( !$has_authored_input && $input_schema !== [] ) ) {
			throw new InvalidArgumentException(
				'Dashboard Widget action Ability input schema must match whether bounded action.input is authored.',
			);
		}

		return $ability_id;
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileManualRefresh( array $widget ): bool
	{
		if (!array_key_exists('refresh', $widget)) {
			return false;
		}

		$refresh = $widget['refresh'];
		if ( !is_array( $refresh ) || ( $refresh !== [] && array_is_list( $refresh ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget refresh metadata must be an object/map.');
		}
		$this->assertKnownKeys($refresh, self::REFRESH_KEYS, 'Dashboard Widget refresh metadata');

		if (!array_key_exists('manual', $refresh)) {
			return false;
		}
		if (!is_bool($refresh['manual'])) {
			throw new InvalidArgumentException('Dashboard Widget refresh.manual must be boolean.');
		}

		return $refresh['manual'];
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileBackgroundJobReference( array $widget ): ?string
	{
		if (!array_key_exists('refresh', $widget)) {
			return null;
		}

		$refresh = $widget['refresh'];
		if ( !is_array( $refresh ) || ( $refresh !== [] && array_is_list( $refresh ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget refresh metadata must be an object/map.');
		}
		$this->assertKnownKeys($refresh, self::REFRESH_KEYS, 'Dashboard Widget refresh metadata');

		if (!array_key_exists('background_job', $refresh)) {
			return null;
		}

		$background_job_id = $refresh['background_job'];
		if (
			!is_string( $background_job_id )
			|| preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $background_job_id) !== 1
		) {
			throw new InvalidArgumentException('Dashboard Widget refresh.background_job must be a lowercase RFC 4122 UUID.');
		}
		if ( $this->backgroundJobResolver === null ) {
			throw new InvalidArgumentException('Dashboard Widget refresh.background_job requires the canonical Cron read service.');
		}

		try {
			$record = ( $this->backgroundJobResolver )( $background_job_id );
		} catch ( Throwable ) {
			throw new InvalidArgumentException('Dashboard Widget background job reference could not be resolved.');
		}

		if (
			!is_array( $record )
			|| ($record['id'] ?? null) !== $background_job_id
			|| ($record['type'] ?? null) !== CronDefinition::TYPE
			|| ($record['owner_surface_id'] ?? null) !== CronDefinition::OWNER_SURFACE_ID
			|| ($record['status'] ?? null) !== DefinitionStatus::Published->value
		) {
			throw new InvalidArgumentException('Dashboard Widget background job reference must resolve to a Published canonical Cron definition.');
		}

		return $background_job_id;
	}

	/**
	 * @param array<string,mixed> $widget
	 * @return array{0:?int,1:?int}
	 */
	private function compileLifecycle( array $widget ): array
	{
		if (!array_key_exists('lifecycle', $widget)) {
			return [null, null];
		}

		$lifecycle = $widget['lifecycle'];
		if ( !is_array( $lifecycle ) || ( $lifecycle !== [] && array_is_list( $lifecycle ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget lifecycle metadata must be an object/map.');
		}
		$this->assertKnownKeys($lifecycle, self::LIFECYCLE_KEYS, 'Dashboard Widget lifecycle metadata');

		$start = array_key_exists('schedule_start', $lifecycle)
			? $this->canonicalUtcTimestamp($lifecycle['schedule_start'], 'schedule_start')
			: null;
		$end = array_key_exists('schedule_end', $lifecycle)
			? $this->canonicalUtcTimestamp($lifecycle['schedule_end'], 'schedule_end')
			: null;

		if ( $start !== null && $end !== null && $start >= $end ) {
			throw new InvalidArgumentException('Dashboard Widget lifecycle schedule_start must be earlier than schedule_end.');
		}

		return [$start, $end];
	}

	private function canonicalUtcTimestamp( mixed $value, string $label ): int
	{
		if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D', $value) !== 1) {
			throw new InvalidArgumentException(sprintf(
				'Dashboard Widget lifecycle %s must be canonical UTC RFC3339 second-precision text.',
				$label,
			));
		}

		$date = DateTimeImmutable::createFromFormat(
			'!Y-m-d\TH:i:s\Z',
			$value,
			new DateTimeZone('UTC'),
		);
		$errors = DateTimeImmutable::getLastErrors();
		if (
			!$date instanceof DateTimeImmutable
			|| ($errors !== false && (($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0))
			|| $date->format('Y-m-d\TH:i:s\Z') !== $value
		) {
			throw new InvalidArgumentException(sprintf(
				'Dashboard Widget lifecycle %s is not a valid canonical UTC timestamp.',
				$label,
			));
		}

		return $date->getTimestamp();
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function assertNativeCollapsibleCapability( array $widget ): void
	{
		if (!array_key_exists('presentation', $widget)) {
			return;
		}

		$presentation = $widget['presentation'];
		if ( !is_array( $presentation ) || ( $presentation !== [] && array_is_list( $presentation ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget presentation metadata must be an object/map.');
		}
		$this->assertKnownKeys($presentation, self::PRESENTATION_KEYS, 'Dashboard Widget presentation metadata');

		if (!array_key_exists('collapsible', $presentation)) {
			return;
		}
		if (!is_bool($presentation['collapsible'])) {
			throw new InvalidArgumentException('Dashboard Widget presentation.collapsible must be boolean.');
		}
		if ($presentation['collapsible'] !== true) {
			throw new InvalidArgumentException('Dashboard Widget presentation.collapsible=false is unsupported by bounded native V1.');
		}
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileDismissible( array $widget ): bool
	{
		if (!array_key_exists('presentation', $widget)) {
			return false;
		}

		$presentation = $widget['presentation'];
		if ( !is_array( $presentation ) || ( $presentation !== [] && array_is_list( $presentation ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget presentation metadata must be an object/map.');
		}
		$this->assertKnownKeys($presentation, self::PRESENTATION_KEYS, 'Dashboard Widget presentation metadata');

		if (!array_key_exists('dismissible', $presentation)) {
			return false;
		}
		if (!is_bool($presentation['dismissible'])) {
			throw new InvalidArgumentException('Dashboard Widget presentation.dismissible must be boolean.');
		}

		return $presentation['dismissible'];
	}

	private function compileDefaultCollapsed( array $widget ): bool
	{
		if (!array_key_exists('presentation', $widget)) {
			return false;
		}

		$presentation = $widget['presentation'];
		if ( !is_array( $presentation ) || ( $presentation !== [] && array_is_list( $presentation ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget presentation metadata must be an object/map.');
		}
		$this->assertKnownKeys($presentation, self::PRESENTATION_KEYS, 'Dashboard Widget presentation metadata');

		if (!array_key_exists('default_collapsed', $presentation)) {
			return false;
		}
		if (!is_bool($presentation['default_collapsed'])) {
			throw new InvalidArgumentException('Dashboard Widget presentation.default_collapsed must be boolean.');
		}

		return $presentation['default_collapsed'];
	}

	/**
	 * @param array<string,mixed> $widget
	 */
	private function compileDefaultHidden( array $widget ): bool
	{
		if (!array_key_exists('inventory', $widget)) {
			return false;
		}

		$inventory = $widget['inventory'];
		if ( !is_array( $inventory ) || ( $inventory !== [] && array_is_list( $inventory ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget inventory metadata must be an object/map.');
		}
		$this->assertKnownKeys($inventory, self::INVENTORY_KEYS, 'Dashboard Widget inventory metadata');

		if (!array_key_exists('default_hidden', $inventory)) {
			return false;
		}
		if (!is_bool($inventory['default_hidden'])) {
			throw new InvalidArgumentException('Dashboard Widget inventory.default_hidden must be boolean.');
		}

		return $inventory['default_hidden'];
	}

	/**
	 * @param array<string,mixed> $widget
	 * @return array{0:bool,1:?string,2:list<int>}
	 */
	private function compileTarget( array $widget ): array
	{
		$legacy_network_present = array_key_exists('network_dashboard', $widget);
		$legacy_network_dashboard = $legacy_network_present ? $widget['network_dashboard'] : false;
		if ( !is_bool( $legacy_network_dashboard ) ) {
			throw new InvalidArgumentException('Dashboard Widget network_dashboard target must be boolean.');
		}

		if (!array_key_exists('target', $widget)) {
			return [$legacy_network_dashboard, null, []];
		}

		$target = $widget['target'];
		if ( !is_array( $target ) || ( $target !== [] && array_is_list( $target ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget target must be an object/map.');
		}
		$this->assertKnownKeys($target, self::TARGET_KEYS, 'Dashboard Widget target');

		$target_network_present = array_key_exists('network_dashboard', $target);
		$target_network_dashboard = $target_network_present ? $target['network_dashboard'] : $legacy_network_dashboard;
		if ( !is_bool( $target_network_dashboard ) ) {
			throw new InvalidArgumentException('Dashboard Widget target.network_dashboard must be boolean.');
		}
		if ( $legacy_network_present && $target_network_present
			&& $legacy_network_dashboard !== $target_network_dashboard ) {
			throw new InvalidArgumentException('Dashboard Widget network target declarations conflict.');
		}

		$scope_present = array_key_exists('scope', $target);
		$scope = $scope_present ? $target['scope'] : null;
		if ( $scope_present && ( !is_string( $scope ) || !in_array( $scope, DashboardWidgetRegistrationDescriptor::SITE_SCOPES, true ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget target.scope is unsupported.');
		}

		$site_ids_present = array_key_exists('site_ids', $target);
		$authored_site_ids = $site_ids_present ? $target['site_ids'] : [];
		if ( $site_ids_present && ( !is_array( $authored_site_ids ) || !array_is_list( $authored_site_ids ) ) ) {
			throw new InvalidArgumentException('Dashboard Widget target.site_ids must be a list.');
		}
		if ( $site_ids_present && count( $authored_site_ids ) > DashboardWidgetRegistrationDescriptor::MAX_SITE_IDS ) {
			throw new InvalidArgumentException('Dashboard Widget target.site_ids exceeds the bounded maximum.');
		}

		/** @var array<int,true> $site_id_set */
		$site_id_set = [];
		if ( $site_ids_present ) {
			foreach ( $authored_site_ids as $site_id ) {
				if ( !is_int( $site_id ) || $site_id < 1 ) {
					throw new InvalidArgumentException('Dashboard Widget target.site_ids must contain positive integers only.');
				}
				$site_id_set[$site_id] = true;
			}
		}

		/** @var list<int> $site_ids */
		$site_ids = array_keys( $site_id_set );
		sort( $site_ids, SORT_NUMERIC );

		if ( $site_ids_present && $scope !== DashboardWidgetRegistrationDescriptor::SITE_SCOPE_SITE_IDS ) {
			throw new InvalidArgumentException('Dashboard Widget target.site_ids requires scope=site_ids.');
		}
		if ( $scope === DashboardWidgetRegistrationDescriptor::SITE_SCOPE_SITE_IDS && $site_ids === [] ) {
			throw new InvalidArgumentException('Dashboard Widget target.scope=site_ids requires a non-empty site_ids list.');
		}
		if ( $scope === DashboardWidgetRegistrationDescriptor::SITE_SCOPE_ALL_SITES && $site_ids_present ) {
			throw new InvalidArgumentException('Dashboard Widget target.scope=all_sites forbids site_ids.');
		}
		if ( $target_network_dashboard && ( $scope_present || $site_ids !== [] ) ) {
			throw new InvalidArgumentException('Dashboard Widget network target cannot include site targeting intent.');
		}

		return [$target_network_dashboard, $scope, $site_ids];
	}

	/**
	 * @param array<string,mixed> $value
	 * @param list<string> $allowed
	 */
	private function assertKnownKeys( array $value, array $allowed, string $label ): void
	{
		foreach ( array_keys( $value ) as $key ) {
			if ( !is_string( $key ) || !in_array( $key, $allowed, true ) ) {
				throw new InvalidArgumentException($label . ' contains an unsupported key.');
			}
		}
	}
}
