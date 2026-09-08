<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Execution;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class StatusTransitionExecutionResult
{
    public function __construct(
        public int $postId,
        public string $fromStatus,
        public string $toStatus,
        public string $postType,
        public ?string $reason,
        public bool $programmatic,
    ) {
        if ($this->postId < 1) {
            throw new InvalidArgumentException('Status transition result post id must be positive.');
        }
        foreach ([$this->fromStatus, $this->toStatus] as $status) {
            if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $status)) {
                throw new InvalidArgumentException('Status transition result contains an invalid status key.');
            }
        }
        if ($this->fromStatus === $this->toStatus) {
            throw new InvalidArgumentException('Status transition result must represent an actual transition.');
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $this->postType)) {
            throw new InvalidArgumentException('Status transition result contains an invalid post type.');
        }
        if ($this->reason !== null) {
            if ($this->reason === '' || strlen($this->reason) > 500 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $this->reason)) {
                throw new InvalidArgumentException('Status transition result reason must be bounded and control-safe.');
            }
        }
    }
}
