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
    }
}
