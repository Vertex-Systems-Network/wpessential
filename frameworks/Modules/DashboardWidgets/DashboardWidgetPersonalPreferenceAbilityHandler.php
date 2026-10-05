<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class DashboardWidgetPersonalPreferenceAbilityHandler implements AbilityHandlerInterface
{
    public const DISMISS = 'dismiss';
    public const RESET = 'reset';

    public const ABILITY_DISMISS = 'wpessential/dashboard-widgets/personal-preference/dismiss';
    public const ABILITY_RESET = 'wpessential/dashboard-widgets/personal-preference/reset';

    public const AJAX_DISMISS = 'dashboard-widgets.personal-preference.dismiss';
    public const AJAX_RESET = 'dashboard-widgets.personal-preference.reset';

    private const ACTIONS = [self::DISMISS, self::RESET];

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private DashboardWidgetRegistrationCompiler $registrationCompiler,
        private DashboardWidgetPersonalPreferenceStore $preferences,
        private string $action,
    ) {
        if (!in_array($this->action, self::ACTIONS, true)) {
            throw new InvalidArgumentException('Dashboard Widget personal preference action is unsupported.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $this->assertKnownKeys($input);
        $userId = $this->authenticatedUserId($context);
        $screen = $input['screen'] ?? null;
        if (!is_string($screen) || !in_array($screen, ['site', 'network'], true)) {
            throw new InvalidArgumentException('Dashboard Widget personal preference screen must be site or network.');
        }
        $screenId = $screen === 'network'
            ? DashboardWidgetPersonalPreferenceStore::SCREEN_NETWORK
            : DashboardWidgetPersonalPreferenceStore::SCREEN_SITE;

        if ($this->action === self::RESET) {
            return $this->preferences->reset($userId, $screenId);
        }

        $definitionId = $input['definition_id'] ?? null;
        if (!is_string($definitionId) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $definitionId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget dismiss definition_id must be a lowercase RFC 4122 UUID.');
        }
        $expectedRevision = $input['expected_revision'] ?? null;
        if (!is_int($expectedRevision) || $expectedRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget dismiss expected_revision must be positive.');
        }

        $definition = $this->definitions->get($definitionId);
        if (
            !$definition instanceof Definition
            || $definition->type !== DashboardWidgetDefinition::TYPE
            || $definition->ownerSurfaceId !== DashboardWidgetDefinition::OWNER_SURFACE_ID
            || $definition->status !== DefinitionStatus::Published
        ) {
            throw new RuntimeException('Dashboard Widget dismiss target was not found in canonical Published Surface 10.');
        }
        if ($definition->revision !== $expectedRevision) {
            throw new RuntimeException('Dashboard Widget dismiss target revision changed.');
        }

        $descriptor = $this->registrationCompiler->compile($definition);
        if (!$descriptor->dismissible) {
            throw new RuntimeException('Dashboard Widget dismiss target is not dismissible.');
        }
        if ($screen === 'network') {
            if (!$descriptor->networkDashboard) {
                throw new RuntimeException('Dashboard Widget dismiss target is not eligible for network Dashboard.');
            }
        } else {
            if ($descriptor->networkDashboard || !$descriptor->isEligibleForSite($context->siteId)) {
                throw new RuntimeException('Dashboard Widget dismiss target is not eligible for current site Dashboard.');
            }
        }

        $widgetId = DashboardWidgetWordPressAdapter::WORDPRESS_ID_PREFIX . $descriptor->key;
        return [
            'screen_id' => $screenId,
            'dismissed' => $this->preferences->dismiss($userId, $screenId, $widgetId),
        ];
    }

    /** @param array<string,mixed> $input */
    private function assertKnownKeys(array $input): void
    {
        if (array_is_list($input)) {
            throw new InvalidArgumentException('Dashboard Widget personal preference input must be an object/map.');
        }

        $allowed = $this->action === self::DISMISS
            ? ['definition_id', 'expected_revision', 'screen']
            : ['screen'];
        foreach (array_keys($input) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('Dashboard Widget personal preference input contains an unsupported key.');
            }
        }
    }

    private function authenticatedUserId(ExecutionContext $context): int
    {
        if ($context->principal->actorType !== 'user' || !$context->principal->isAuthenticated()) {
            throw new RuntimeException('Dashboard Widget personal preference access requires an authenticated user.');
        }
        $userId = $context->principal->userId;
        if (!is_int($userId) || $userId < 1) {
            throw new RuntimeException('Dashboard Widget personal preference user id is unavailable.');
        }
        return $userId;
    }
}
