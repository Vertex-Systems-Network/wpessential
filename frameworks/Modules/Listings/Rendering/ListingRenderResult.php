<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\Listings\State\ListingRuntimeState;

final readonly class ListingRenderResult
{
    /** @param list<string> $assetHandles */
    public function __construct(
        public bool $success,
        public string $html,
        public array $assetHandles,
        public int $returned,
        public ?string $failureCode = null,
        public ListingRuntimeState $state = ListingRuntimeState::Content,
    ) {
        if ($this->returned < 0) {
            throw new InvalidArgumentException('Listing render returned count must be non-negative.');
        }
        if ($this->success && $this->failureCode !== null) {
            throw new InvalidArgumentException('Successful Listing render cannot carry a failure code.');
        }
        if (!$this->success && ($this->html !== '' || $this->assetHandles !== [] || $this->returned !== 0 || $this->failureCode === null)) {
            throw new InvalidArgumentException('Failed Listing render must fail closed.');
        }
        if ($this->failureCode !== null && !preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $this->failureCode)) {
            throw new InvalidArgumentException('Listing render failure code must be a safe semantic identifier.');
        }
        if (count($this->assetHandles) !== count(array_unique($this->assetHandles))) {
            throw new InvalidArgumentException('Listing render asset handles must be unique.');
        }
        foreach ($this->assetHandles as $handle) {
            if (!preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $handle)) {
                throw new InvalidArgumentException('Listing render assets must reference registered WPEssential handles.');
            }
        }
    }
}
