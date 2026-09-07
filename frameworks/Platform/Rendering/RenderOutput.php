<?php

declare(strict_types=1);

namespace WPEssential\Platform\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RenderOutput
{
    /** @param list<string> $assetHandles */
    public function __construct(
        public bool $success,
        public string $html,
        public array $assetHandles = [],
        public ?RenderFailureCode $failure = null,
    ) {
        if ($this->success && $this->failure !== null) {
            throw new InvalidArgumentException('Successful render output cannot carry a failure code.');
        }
        if (!$this->success && ($this->failure === null || $this->html !== '')) {
            throw new InvalidArgumentException('Failed render output must fail closed without HTML.');
        }
        if (count($this->assetHandles) !== count(array_unique($this->assetHandles))) {
            throw new InvalidArgumentException('Render asset handles must be unique.');
        }
        foreach ($this->assetHandles as $handle) {
            if (!preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $handle)) {
                throw new InvalidArgumentException('Render assets must reference registered WPEssential handles.');
            }
        }
    }
}
