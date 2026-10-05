<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use RuntimeException;

final readonly class DashboardWidgetPersonalPreferenceStore
{
    public const SCREEN_SITE = 'dashboard';
    public const SCREEN_NETWORK = 'dashboard-network';

    private const SCREENS = [self::SCREEN_SITE, self::SCREEN_NETWORK];
    private const DISMISSED_META_PREFIX = '_wpessential_dashboard_widgets_dismissed_';

    private Closure $metaReader;
    private Closure $metaWriter;
    private Closure $metaDeleter;
    private Closure $optionReader;
    private Closure $optionWriter;

    public function __construct(
        ?Closure $metaReader = null,
        ?Closure $metaWriter = null,
        ?Closure $metaDeleter = null,
        ?Closure $optionReader = null,
        ?Closure $optionWriter = null,
    ) {
        $this->metaReader = $metaReader ?? static fn (int $userId, string $key): mixed =>
            \get_user_meta($userId, $key, true);
        $this->metaWriter = $metaWriter ?? static function (int $userId, string $key, array $value): void {
            \update_user_meta($userId, $key, $value);
        };
        $this->metaDeleter = $metaDeleter ?? static function (int $userId, string $key): void {
            \delete_user_meta($userId, $key);
        };
        $this->optionReader = $optionReader ?? static fn (int $userId, string $key): mixed =>
            \get_user_option($key, $userId);
        $this->optionWriter = $optionWriter ?? static function (int $userId, string $key, mixed $value): void {
            \update_user_option($userId, $key, $value);
        };
    }

    /** @return list<string> */
    public function dismissed(int $userId, string $screenId): array
    {
        $this->assertUserAndScreen($userId, $screenId);
        $raw = ($this->metaReader)($userId, $this->dismissedKey($screenId));
        if ($raw === '' || $raw === null || $raw === false) {
            return [];
        }
        if (!is_array($raw) || !array_is_list($raw)) {
            throw new RuntimeException('Stored Dashboard Widget dismissed preference is malformed.');
        }

        $result = [];
        foreach ($raw as $id) {
            $this->assertWpeWidgetId($id);
            $result[$id] = true;
        }
        $ids = array_keys($result);
        sort($ids, SORT_STRING);
        return $ids;
    }

    /** @return list<string> */
    public function dismiss(int $userId, string $screenId, string $widgetId): array
    {
        $this->assertUserAndScreen($userId, $screenId);
        $this->assertWpeWidgetId($widgetId);

        $ids = $this->dismissed($userId, $screenId);
        if (!in_array($widgetId, $ids, true)) {
            $ids[] = $widgetId;
            sort($ids, SORT_STRING);
            ($this->metaWriter)($userId, $this->dismissedKey($screenId), $ids);
        }

        $stored = $this->dismissed($userId, $screenId);
        if ($stored !== $ids) {
            throw new RuntimeException('Dashboard Widget dismiss write-back verification failed.');
        }

        return $stored;
    }

    /**
     * @return array{
     *   screen_id:string,
     *   removed:array{dismissed:int,hidden:int,collapsed:int,ordered:int}
     * }
     */
    public function reset(int $userId, string $screenId): array
    {
        $this->assertUserAndScreen($userId, $screenId);

        $dismissed = $this->dismissed($userId, $screenId);
        $hidden = $this->listResetPlan($userId, 'metaboxhidden_' . $screenId);
        $collapsed = $this->listResetPlan($userId, 'closedpostboxes_' . $screenId);
        $ordered = $this->orderResetPlan($userId, 'meta-box-order_' . $screenId);

        $dismissedKey = $this->dismissedKey($screenId);
        ($this->metaDeleter)($userId, $dismissedKey);
        $remaining = ($this->metaReader)($userId, $dismissedKey);
        if ($remaining !== '' && $remaining !== null && $remaining !== false) {
            throw new RuntimeException('Dashboard Widget dismiss reset verification failed.');
        }

        $this->applyOptionPlan($userId, $hidden);
        $this->applyOptionPlan($userId, $collapsed);
        $this->applyOptionPlan($userId, $ordered);

        return [
            'screen_id' => $screenId,
            'removed' => [
                'dismissed' => count($dismissed),
                'hidden' => $hidden['removed'],
                'collapsed' => $collapsed['removed'],
                'ordered' => $ordered['removed'],
            ],
        ];
    }

    /**
     * @return array{key:string,changed:bool,value:mixed,removed:int}
     */
    private function listResetPlan(int $userId, string $key): array
    {
        $raw = ($this->optionReader)($userId, $key);
        if ($raw === '' || $raw === null || $raw === false) {
            return ['key' => $key, 'changed' => false, 'value' => $raw, 'removed' => 0];
        }
        if (!is_array($raw) || !array_is_list($raw)) {
            throw new RuntimeException('Stored Dashboard Widget list preference is malformed.');
        }

        $kept = [];
        $removed = 0;
        foreach ($raw as $id) {
            if (!is_string($id) || preg_match('/^[A-Za-z0-9._:-]+$/D', $id) !== 1) {
                throw new RuntimeException('Stored Dashboard Widget list preference contains an unsafe widget id.');
            }
            if (str_starts_with($id, DashboardWidgetWordPressAdapter::WORDPRESS_ID_PREFIX)) {
                ++$removed;
                continue;
            }
            $kept[] = $id;
        }

        return ['key' => $key, 'changed' => $removed > 0, 'value' => $kept, 'removed' => $removed];
    }

    /**
     * @return array{key:string,changed:bool,value:mixed,removed:int}
     */
    private function orderResetPlan(int $userId, string $key): array
    {
        $raw = ($this->optionReader)($userId, $key);
        if ($raw === '' || $raw === null || $raw === false) {
            return ['key' => $key, 'changed' => false, 'value' => $raw, 'removed' => 0];
        }
        if (!is_array($raw) || array_is_list($raw)) {
            throw new RuntimeException('Stored Dashboard Widget order preference is malformed.');
        }

        $supported = ['normal', 'side', 'column3', 'column4'];
        $next = [];
        $removed = 0;
        foreach ($raw as $context => $csv) {
            if (!is_string($context) || !in_array($context, $supported, true) || !is_string($csv)) {
                throw new RuntimeException('Stored Dashboard Widget order preference contains unsupported context data.');
            }

            $kept = [];
            foreach (explode(',', $csv) as $id) {
                if ($id === '') {
                    continue;
                }
                if (preg_match('/^[A-Za-z0-9._:-]+$/D', $id) !== 1) {
                    throw new RuntimeException('Stored Dashboard Widget order preference contains an unsafe widget id.');
                }
                if (str_starts_with($id, DashboardWidgetWordPressAdapter::WORDPRESS_ID_PREFIX)) {
                    ++$removed;
                    continue;
                }
                $kept[] = $id;
            }
            $next[$context] = implode(',', $kept);
        }

        return ['key' => $key, 'changed' => $removed > 0, 'value' => $next, 'removed' => $removed];
    }

    /**
     * @param array{key:string,changed:bool,value:mixed,removed:int} $plan
     */
    private function applyOptionPlan(int $userId, array $plan): void
    {
        if (!$plan['changed']) {
            return;
        }

        ($this->optionWriter)($userId, $plan['key'], $plan['value']);
        if (($this->optionReader)($userId, $plan['key']) !== $plan['value']) {
            throw new RuntimeException('Dashboard Widget preference reset verification failed.');
        }
    }

    private function assertUserAndScreen(int $userId, string $screenId): void
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('Dashboard Widget preference user id must be positive.');
        }
        if (!in_array($screenId, self::SCREENS, true)) {
            throw new InvalidArgumentException('Dashboard Widget preference screen is unsupported.');
        }
    }

    private function assertWpeWidgetId(mixed $widgetId): void
    {
        if (
            !is_string($widgetId)
            || !str_starts_with($widgetId, DashboardWidgetWordPressAdapter::WORDPRESS_ID_PREFIX)
            || preg_match('/^[A-Za-z0-9._:-]+$/D', $widgetId) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget dismissed id must be a bounded WPE-owned widget id.');
        }
    }

    private function dismissedKey(string $screenId): string
    {
        return self::DISMISSED_META_PREFIX . str_replace('-', '_', $screenId);
    }
}
