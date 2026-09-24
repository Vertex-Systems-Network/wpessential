<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;

final class NativeWordPressDashboardWidgetEnvironment implements DashboardWidgetWordPressEnvironmentInterface
{
    /** @var Closure(string, callable):void */
    private Closure $registerAction;

    /** @var Closure(string, callable, int):void */
    private Closure $registerFilter;

    /** @var Closure(string, string, callable, ?callable, ?array, string, string):void */
    private Closure $registerDashboardWidget;

    /** @var Closure():?int */
    private Closure $currentUserId;

    /** @var Closure():int */
    private Closure $currentSiteId;

    /** @var Closure():?int */
    private Closure $currentNetworkId;

    /** @var Closure(mixed):?string */
    private Closure $screenId;

    /** @var Closure(string):bool */
    private Closure $hasClosedPostboxPreference;

    /** @var Closure(string):array<int,string> */
    private Closure $currentUserHiddenDashboardWidgetIds;

    /** @var Closure(string):array<int,string> */
    private Closure $currentUserCollapsedDashboardWidgetIds;

    /** @var Closure(string):array<int,array{id:string,context:string,priority:string}> */
    private Closure $discoverRegisteredDashboardWidgets;

    /** @var Closure(string):void */
    private Closure $outputTrustedHtml;

    /**
     * @param null|callable(string, callable):void $registerAction
     * @param null|callable(string, callable, int):void $registerFilter
     * @param null|callable(string, string, callable, ?callable, ?array, string, string):void $registerDashboardWidget
     * @param null|callable():?int $currentUserId
     * @param null|callable():int $currentSiteId
     * @param null|callable():?int $currentNetworkId
     * @param null|callable(mixed):?string $screenId
     * @param null|callable(string):bool $hasClosedPostboxPreference
     * @param null|callable(string):array<int,string> $currentUserHiddenDashboardWidgetIds
     * @param null|callable(string):array<int,string> $currentUserCollapsedDashboardWidgetIds
     * @param null|callable(string):array<int,array{id:string,context:string,priority:string}> $discoverRegisteredDashboardWidgets
     * @param null|callable(string):void $outputTrustedHtml
     */
    public function __construct(
        ?callable $registerAction = null,
        ?callable $registerFilter = null,
        ?callable $registerDashboardWidget = null,
        ?callable $currentUserId = null,
        ?callable $currentSiteId = null,
        ?callable $currentNetworkId = null,
        ?callable $screenId = null,
        ?callable $hasClosedPostboxPreference = null,
        ?callable $currentUserHiddenDashboardWidgetIds = null,
        ?callable $currentUserCollapsedDashboardWidgetIds = null,
        ?callable $discoverRegisteredDashboardWidgets = null,
        ?callable $outputTrustedHtml = null,
    ) {
        $this->registerAction = $registerAction !== null
            ? Closure::fromCallable($registerAction)
            : static function (string $hook, callable $callback): void {
                if (!function_exists('add_action')) {
                    throw new LogicException('WordPress action API is unavailable.');
                }
                add_action($hook, $callback);
            };

        $this->registerFilter = $registerFilter !== null
            ? Closure::fromCallable($registerFilter)
            : static function (string $hook, callable $callback, int $acceptedArgs): void {
                if (!function_exists('add_filter')) {
                    throw new LogicException('WordPress filter API is unavailable.');
                }
                if ($acceptedArgs < 1) {
                    throw new LogicException('WordPress filter accepted-args count must be positive.');
                }
                add_filter($hook, $callback, 10, $acceptedArgs);
            };

        $this->registerDashboardWidget = $registerDashboardWidget !== null
            ? Closure::fromCallable($registerDashboardWidget)
            : static function (
                string $id,
                string $title,
                callable $callback,
                ?callable $controlCallback,
                ?array $callbackArgs,
                string $context,
                string $priority,
            ): void {
                if (!function_exists('wp_add_dashboard_widget')) {
                    throw new LogicException('WordPress Dashboard Widget API is unavailable.');
                }
                wp_add_dashboard_widget(
                    $id,
                    $title,
                    $callback,
                    $controlCallback,
                    $callbackArgs,
                    $context,
                    $priority,
                );
            };

        $this->currentUserId = $currentUserId !== null
            ? Closure::fromCallable($currentUserId)
            : static function (): ?int {
                if (!function_exists('get_current_user_id')) {
                    throw new LogicException('WordPress current-user API is unavailable.');
                }
                $userId = (int) get_current_user_id();
                return $userId > 0 ? $userId : null;
            };

        $this->currentSiteId = $currentSiteId !== null
            ? Closure::fromCallable($currentSiteId)
            : static function (): int {
                if (!function_exists('get_current_blog_id')) {
                    throw new LogicException('WordPress current-site API is unavailable.');
                }
                return (int) get_current_blog_id();
            };

        $this->currentNetworkId = $currentNetworkId !== null
            ? Closure::fromCallable($currentNetworkId)
            : static function (): ?int {
                if (!function_exists('get_current_network_id')) {
                    return null;
                }
                $networkId = (int) get_current_network_id();
                return $networkId > 0 ? $networkId : null;
            };

        $this->screenId = $screenId !== null
            ? Closure::fromCallable($screenId)
            : static function (mixed $screen): ?string {
                if (!is_object($screen) || !isset($screen->id) || !is_string($screen->id)) {
                    return null;
                }
                return $screen->id;
            };

        $this->hasClosedPostboxPreference = $hasClosedPostboxPreference !== null
            ? Closure::fromCallable($hasClosedPostboxPreference)
            : static function (string $screenId): bool {
                if (!in_array($screenId, ['dashboard', 'dashboard-network'], true)) {
                    throw new LogicException('Dashboard Widget closed-postbox preference screen is unsupported.');
                }
                if (!function_exists('get_current_user_id') || !function_exists('metadata_exists')) {
                    throw new LogicException('WordPress user-meta API is unavailable.');
                }
                $userId = (int) get_current_user_id();
                if ($userId < 1) {
                    throw new LogicException('WordPress current user is unavailable.');
                }

                return metadata_exists('user', $userId, 'closedpostboxes_' . $screenId);
            };

        $this->currentUserHiddenDashboardWidgetIds = $currentUserHiddenDashboardWidgetIds !== null
            ? Closure::fromCallable($currentUserHiddenDashboardWidgetIds)
            : static function (string $screenId): array {
                if (!in_array($screenId, ['dashboard', 'dashboard-network'], true)) {
                    throw new LogicException('Dashboard Widget hidden preference screen is unsupported.');
                }
                if (!function_exists('get_user_option')) {
                    throw new LogicException('WordPress user-option API is unavailable.');
                }

                $hidden = get_user_option('metaboxhidden_' . $screenId);
                if ($hidden === false || $hidden === null) {
                    return [];
                }
                if (!is_array($hidden)) {
                    throw new LogicException('WordPress hidden Dashboard Widget preference is malformed.');
                }

                $safe = [];
                foreach ($hidden as $id) {
                    if (!is_string($id) || preg_match('/^[A-Za-z0-9._:-]+$/D', $id) !== 1) {
                        throw new LogicException('WordPress hidden Dashboard Widget preference contains an unsafe widget id.');
                    }
                    $safe[$id] = true;
                }

                $ids = array_keys($safe);
                sort($ids, SORT_STRING);
                return $ids;
            };

        $this->currentUserCollapsedDashboardWidgetIds = $currentUserCollapsedDashboardWidgetIds !== null
            ? Closure::fromCallable($currentUserCollapsedDashboardWidgetIds)
            : static function (string $screenId): array {
                if (!in_array($screenId, ['dashboard', 'dashboard-network'], true)) {
                    throw new LogicException('Dashboard Widget collapsed preference screen is unsupported.');
                }
                if (!function_exists('get_user_option')) {
                    throw new LogicException('WordPress user-option API is unavailable.');
                }

                $collapsed = get_user_option('closedpostboxes_' . $screenId);
                if ($collapsed === false || $collapsed === null) {
                    return [];
                }
                if (!is_array($collapsed)) {
                    throw new LogicException('WordPress collapsed Dashboard Widget preference is malformed.');
                }

                $safe = [];
                foreach ($collapsed as $id) {
                    if (!is_string($id) || preg_match('/^[A-Za-z0-9._:-]+$/D', $id) !== 1) {
                        throw new LogicException('WordPress collapsed Dashboard Widget preference contains an unsafe widget id.');
                    }
                    $safe[$id] = true;
                }

                $ids = array_keys($safe);
                sort($ids, SORT_STRING);
                return $ids;
            };

        $this->discoverRegisteredDashboardWidgets = $discoverRegisteredDashboardWidgets !== null
            ? Closure::fromCallable($discoverRegisteredDashboardWidgets)
            : static function (string $screenId): array {
                if (!in_array($screenId, ['dashboard', 'dashboard-network'], true)) {
                    throw new LogicException('Dashboard Widget inventory screen is unsupported.');
                }

                global $wp_meta_boxes;
                if (!isset($wp_meta_boxes)) {
                    return [];
                }
                if (!is_array($wp_meta_boxes)) {
                    throw new LogicException('WordPress meta-box registry is malformed.');
                }

                $screenBoxes = $wp_meta_boxes[$screenId] ?? [];
                if (!is_array($screenBoxes)) {
                    throw new LogicException('WordPress dashboard meta-box registry is malformed.');
                }

                $contexts = ['normal', 'side', 'column3', 'column4'];
                $priorities = ['high', 'sorted', 'core', 'default', 'low'];
                $inventory = [];

                foreach ($contexts as $context) {
                    if (!array_key_exists($context, $screenBoxes)) {
                        continue;
                    }
                    $contextBoxes = $screenBoxes[$context];
                    if (!is_array($contextBoxes)) {
                        throw new LogicException('WordPress dashboard meta-box context is malformed.');
                    }

                    foreach ($priorities as $priority) {
                        if (!array_key_exists($priority, $contextBoxes)) {
                            continue;
                        }
                        $priorityBoxes = $contextBoxes[$priority];
                        if (!is_array($priorityBoxes)) {
                            throw new LogicException('WordPress dashboard meta-box priority is malformed.');
                        }

                        $ids = array_values(array_filter(
                            array_keys($priorityBoxes),
                            static fn (mixed $id): bool => is_string($id) && $id !== '',
                        ));
                        sort($ids, SORT_STRING);

                        foreach ($ids as $id) {
                            $inventory[] = [
                                'id' => $id,
                                'context' => $context,
                                'priority' => $priority,
                            ];
                        }
                    }
                }

                return $inventory;
            };

        $this->outputTrustedHtml = $outputTrustedHtml !== null
            ? Closure::fromCallable($outputTrustedHtml)
            : static function (string $html): void {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML is emitted only after the trusted renderer succeeds.
                echo $html;
            };
    }

    public function registerAction(string $hook, callable $callback): void
    {
        ($this->registerAction)($hook, $callback);
    }

    public function registerFilter(string $hook, callable $callback, int $acceptedArgs = 1): void
    {
        ($this->registerFilter)($hook, $callback, $acceptedArgs);
    }

    public function registerDashboardWidget(
        string $id,
        string $title,
        callable $callback,
        string $context,
        string $priority,
    ): void {
        ($this->registerDashboardWidget)($id, $title, $callback, null, null, $context, $priority);
    }

    public function currentUserId(): ?int
    {
        return ($this->currentUserId)();
    }

    public function currentSiteId(): int
    {
        return ($this->currentSiteId)();
    }

    public function currentNetworkId(): ?int
    {
        return ($this->currentNetworkId)();
    }

    public function screenId(mixed $screen): ?string
    {
        return ($this->screenId)($screen);
    }

    public function hasClosedPostboxPreference(string $screenId): bool
    {
        return ($this->hasClosedPostboxPreference)($screenId);
    }

    public function currentUserHiddenDashboardWidgetIds(string $screenId): array
    {
        return ($this->currentUserHiddenDashboardWidgetIds)($screenId);
    }

    public function currentUserCollapsedDashboardWidgetIds(string $screenId): array
    {
        return ($this->currentUserCollapsedDashboardWidgetIds)($screenId);
    }

    public function discoverRegisteredDashboardWidgets(string $screenId): array
    {
        return ($this->discoverRegisteredDashboardWidgets)($screenId);
    }

    public function outputTrustedHtml(string $html): void
    {
        ($this->outputTrustedHtml)($html);
    }
}
