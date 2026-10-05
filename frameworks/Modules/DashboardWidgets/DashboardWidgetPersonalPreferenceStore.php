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
        $key = $this->dismissedKey($screenId);
        ($this->metaDeleter)($userId, $key);
        if (($this->metaReader)($userId, $key) !== '' && ($this->metaReader)($userId, $key) !== null && ($this->metaReader)($userId, $key) !== false) {
            throw new RuntimeException('Dashboard Widget dismiss reset verification failed.');
        }

        $hiddenRemoved = $this->stripListOption($userId, 'metaboxhidden_' . $screenId);
        $collapsedRemoved = $this->stripListOption($userId, 'closedpostboxes_' . $screenId);
        $orderedRemoved = $this->stripOrderOption($userId, 'meta-box-order_' . $screenId);

        return [
            'screen_id' => $screenId,
            'removed' => [
                'dismissed' => count($dismissed),
                'hidden' => $hiddenRemoved,
                'collapsed' => $collapsedRemoved,
                'ordered' => $orderedRemoved,
            ],
        ];
    }

    private function stripListOption(int $userId, string $key): int
    {
        $raw = ($this->optionReader)($userId, $key);
        if ($raw === '' || $raw === null || $raw === false) {
            return 0;
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

        if ($removed > 0) {
            ($this->optionWriter)($userId, $key, $kept);
            if (($this->optionReader)($userId, $key) !== $kept) {
                throw new RuntimeException('Dashboard Widget list preference reset verification failed.');
            }
        }

        return $removed;
    }

    private function stripOrderOption(int $userId, string $key): int
    {
        $raw = ($this->optionReader)($userId, $key);
        if ($raw === '' || $raw === null || $raw === false) {
            return 0;
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

        if ($removed > 0) {
            ($this->optionWriter)($userId, $key, $next);
            if (($this->optionReader)($userId, $key) !== $next) {
                throw new RuntimeException('Dashboard Widget order preference reset verification failed.');
            }
        }

        return $removed;
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
