<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetContentClassDescriptor
{
    public const TYPE_RICH_TEXT = 'rich_text';
    public const TYPE_KPI = 'kpi';
    public const TYPE_CHART = 'chart';
    public const TYPE_QUICK_LINKS = 'quick_links';
    public const TYPE_ANNOUNCEMENT = 'announcement';
    public const TYPE_SUPPORT_ONBOARDING = 'support_onboarding';
    public const TYPE_ICON_LINK = 'icon_link';

    /** @var list<string> */
    public const TRUSTED_TYPES = [
        self::TYPE_RICH_TEXT,
        self::TYPE_KPI,
        self::TYPE_CHART,
        self::TYPE_QUICK_LINKS,
        self::TYPE_ANNOUNCEMENT,
        self::TYPE_SUPPORT_ONBOARDING,
        self::TYPE_ICON_LINK,
    ];

    public function __construct(
        public string $definitionId,
        public int $revision,
        public string $contentType,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('Dashboard Widget content-class descriptor definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Dashboard Widget content-class descriptor revision must be positive.');
        }
        if (!in_array($this->contentType, self::TRUSTED_TYPES, true)) {
            throw new InvalidArgumentException('Dashboard Widget content-class descriptor type is not trusted for V1.');
        }
    }
}
