<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetRegistrationDescriptor
{
    public const CONTEXT_NORMAL = 'normal';
    public const CONTEXT_SIDE = 'side';
    public const CONTEXT_COLUMN3 = 'column3';
    public const CONTEXT_COLUMN4 = 'column4';

    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_CORE = 'core';
    public const PRIORITY_DEFAULT = 'default';
    public const PRIORITY_LOW = 'low';

    public const SITE_SCOPE_ALL_SITES = 'all_sites';
    public const SITE_SCOPE_SITE_IDS = 'site_ids';
    public const MAX_SITE_IDS = 100;

    /** @var list<string> */
    public const SITE_SCOPES = [
        self::SITE_SCOPE_ALL_SITES,
        self::SITE_SCOPE_SITE_IDS,
    ];

    /** @var list<string> */
    public const CONTEXTS = [
        self::CONTEXT_NORMAL,
        self::CONTEXT_SIDE,
        self::CONTEXT_COLUMN3,
        self::CONTEXT_COLUMN4,
    ];

    /** @var list<string> */
    public const PRIORITIES = [
        self::PRIORITY_HIGH,
        self::PRIORITY_CORE,
        self::PRIORITY_DEFAULT,
        self::PRIORITY_LOW,
    ];

    public function __construct(
        public string $definitionId,
        public int $revision,
        public string $key,
        public string $title,
        public string $context,
        public string $priority,
        public bool $networkDashboard,
        public bool $defaultHidden = false,
        public bool $defaultCollapsed = false,
        public ?string $siteScope = null,
        /** @var list<int> */
        public array $siteIds = [],
        public ?string $backgroundJobId = null,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('Dashboard Widget descriptor definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Dashboard Widget descriptor revision must be positive.');
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $this->key)) {
            throw new InvalidArgumentException('Dashboard Widget descriptor key must be bounded canonical WordPress-safe text.');
        }
        if (
            $this->title === ''
            || strlen($this->title) > 160
            || str_contains($this->title, '<')
            || str_contains($this->title, '>')
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $this->title)
        ) {
            throw new InvalidArgumentException('Dashboard Widget descriptor title must be bounded plain text.');
        }
        if (!in_array($this->context, self::CONTEXTS, true)) {
            throw new InvalidArgumentException('Dashboard Widget descriptor context is unsupported.');
        }
        if (!in_array($this->priority, self::PRIORITIES, true)) {
            throw new InvalidArgumentException('Dashboard Widget descriptor priority is unsupported.');
        }
        if ($this->siteScope !== null && !in_array($this->siteScope, self::SITE_SCOPES, true)) {
            throw new InvalidArgumentException('Dashboard Widget descriptor site scope is unsupported.');
        }
        if (!array_is_list($this->siteIds) || count($this->siteIds) > self::MAX_SITE_IDS) {
            throw new InvalidArgumentException('Dashboard Widget descriptor site ids must be a bounded normalized list.');
        }

        $previousSiteId = 0;
        foreach ($this->siteIds as $siteId) {
            if (!is_int($siteId) || $siteId < 1 || $siteId <= $previousSiteId) {
                throw new InvalidArgumentException('Dashboard Widget descriptor site ids must be unique positive integers sorted ascending.');
            }
            $previousSiteId = $siteId;
        }

        if ($this->siteScope === null && $this->siteIds !== []) {
            throw new InvalidArgumentException('Dashboard Widget descriptor site ids require site_ids scope.');
        }
        if ($this->siteScope === self::SITE_SCOPE_SITE_IDS && $this->siteIds === []) {
            throw new InvalidArgumentException('Dashboard Widget descriptor site_ids scope requires at least one site id.');
        }
        if ($this->siteScope === self::SITE_SCOPE_ALL_SITES && $this->siteIds !== []) {
            throw new InvalidArgumentException('Dashboard Widget descriptor all_sites scope forbids site ids.');
        }
        if ($this->networkDashboard && ($this->siteScope !== null || $this->siteIds !== [])) {
            throw new InvalidArgumentException('Dashboard Widget descriptor network target cannot include site targeting.');
        }
        if (
            $this->backgroundJobId !== null
            && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->backgroundJobId) !== 1
        ) {
            throw new InvalidArgumentException('Dashboard Widget descriptor background job id must be a lowercase RFC 4122 UUID.');
        }
    }

    public function isEligibleForSite(int $siteId): bool
    {
        if ($siteId < 1 || $this->networkDashboard) {
            return false;
        }

        return match ($this->siteScope) {
            null, self::SITE_SCOPE_ALL_SITES => true,
            self::SITE_SCOPE_SITE_IDS => in_array($siteId, $this->siteIds, true),
            default => false,
        };
    }
}
