<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\QueryBinding;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ListingQueryResultEnvelope
{
    /**
     * @param list<string> $projection
     * @param list<array<string,mixed>> $rows
     */
    public function __construct(
        public bool $ok,
        public string $sourceRef,
        public array $projection,
        public array $rows,
        public int $returned,
        public ?string $errorCode = null,
        public ?string $errorPath = null,
        public ?string $errorMessage = null,
    ) {
        if ($this->returned < 0 || $this->returned !== count($this->rows)) {
            throw new InvalidArgumentException('Listing Query returned count must match row count.');
        }
        if ($this->ok && ($this->errorCode !== null || $this->errorPath !== null || $this->errorMessage !== null)) {
            throw new InvalidArgumentException('Successful Listing Query result cannot carry an error.');
        }
        if (!$this->ok && ($this->rows !== [] || $this->projection !== [] || $this->returned !== 0 || $this->errorCode === null)) {
            throw new InvalidArgumentException('Failed Listing Query result must fail closed with Query error evidence.');
        }
    }
}
