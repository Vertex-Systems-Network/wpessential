<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Transition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class StatusTransitionRule
{
    /** @var list<string> */
    private const CORE_LIFECYCLE_RESERVED = [
        'future',
        'trash',
        'auto-draft',
        'inherit',
        'request-pending',
        'request-confirmed',
        'request-failed',
        'request-completed',
    ];

    public function __construct(
        public string $from,
        public string $to,
        public ?string $capability = null,
        public bool $reasonRequired = false,
        public bool $bulkAllowed = false,
        public bool $programmaticAllowed = false,
    ) {
        $this->assertStatusKey($this->from, 'source');
        $this->assertStatusKey($this->to, 'target');
        if ($this->from === $this->to) {
            throw new InvalidArgumentException('Same-status updates are not transition edges.');
        }
        if (in_array($this->from, self::CORE_LIFECYCLE_RESERVED, true) || in_array($this->to, self::CORE_LIFECYCLE_RESERVED, true)) {
            throw new InvalidArgumentException('Core lifecycle Status edges are reserved from the bounded generic transition policy.');
        }
        if ($this->capability !== null && !preg_match('/^[a-z][a-z0-9_-]{0,127}$/', $this->capability)) {
            throw new InvalidArgumentException('Status transition capability must be a bounded WordPress capability identifier.');
        }
    }

    public function edgeKey(): string
    {
        return $this->from . '->' . $this->to;
    }

    /** @return array<string,mixed> */
    public function fingerprintData(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'capability' => $this->capability,
            'reason_required' => $this->reasonRequired,
            'bulk_allowed' => $this->bulkAllowed,
            'programmatic_allowed' => $this->programmaticAllowed,
        ];
    }

    private function assertStatusKey(string $value, string $label): void
    {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $value)) {
            throw new InvalidArgumentException('Status transition ' . $label . ' key must be canonical and fit the WordPress 20-character storage bound.');
        }
    }
}
