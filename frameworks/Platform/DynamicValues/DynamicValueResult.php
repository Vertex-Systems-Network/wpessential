<?php

declare(strict_types=1);

namespace WPEssential\Platform\DynamicValues;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Rendering\RenderFailureCode;

final readonly class DynamicValueResult
{
    /** @param scalar|list<scalar>|null $value */
    public function __construct(
        public bool $resolved,
        public mixed $value = null,
        public ?RenderFailureCode $failure = null,
        public ?string $sourceEvidence = null,
    ) {
        if ($this->resolved && $this->failure !== null) {
            throw new InvalidArgumentException('Resolved values cannot carry a failure code.');
        }
        if (!$this->resolved && ($this->failure === null || $this->value !== null)) {
            throw new InvalidArgumentException('Unresolved values must fail closed without a value.');
        }
        if ($this->sourceEvidence !== null && strlen($this->sourceEvidence) > 240) {
            throw new InvalidArgumentException('Source evidence must remain bounded.');
        }
    }
}
