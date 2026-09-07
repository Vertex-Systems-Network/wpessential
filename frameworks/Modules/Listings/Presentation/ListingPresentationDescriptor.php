<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Presentation;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ListingPresentationDescriptor
{
    /**
     * @param array<string,string> $tableHeaders field ref => public header
     * @param array<string,int> $responsiveColumns breakpoint => columns
     */
    public function __construct(
        public string $mode,
        public string $label,
        public array $tableHeaders = [],
        public array $responsiveColumns = [],
        public string $emptyMessage = 'No results.',
        public string $errorMessage = 'Results are temporarily unavailable.',
    ) {
        if (!in_array($this->mode, ['list', 'grid', 'table'], true)) {
            throw new InvalidArgumentException('Listing presentation mode is unsupported.');
        }
        $this->assertPublicText($this->label, 'Listing presentation label', 160);
        $this->assertPublicText($this->emptyMessage, 'Listing empty message', 240);
        $this->assertPublicText($this->errorMessage, 'Listing error message', 240);

        if ($this->mode === 'table' && $this->tableHeaders === []) {
            throw new InvalidArgumentException('Table presentation requires explicit headers.');
        }
        if ($this->mode !== 'table' && $this->tableHeaders !== []) {
            throw new InvalidArgumentException('Table headers are only valid for table presentation.');
        }
        foreach ($this->tableHeaders as $fieldRef => $header) {
            if (!is_string($fieldRef) || !preg_match('/^[a-z][a-z0-9._-]{0,127}$/', $fieldRef)) {
                throw new InvalidArgumentException('Listing table header field reference is invalid.');
            }
            $this->assertPublicText($header, 'Listing table header', 120);
        }

        foreach ($this->responsiveColumns as $breakpoint => $columns) {
            if (!is_string($breakpoint) || !in_array($breakpoint, ['base', 'sm', 'md', 'lg', 'xl'], true)) {
                throw new InvalidArgumentException('Listing responsive breakpoint is unsupported.');
            }
            if (!is_int($columns) || $columns < 1 || $columns > 6) {
                throw new InvalidArgumentException('Listing responsive columns must be within 1..6.');
            }
        }
    }

    private function assertPublicText(string $value, string $label, int $maxLength): void
    {
        if ($value === '' || strlen($value) > $maxLength || preg_match('/<[^>]*>|<\?(?:php|=)|javascript:|\b(?:select|insert|update|delete)\b.+\bfrom\b/i', $value)) {
            throw new InvalidArgumentException($label . ' is unsafe or outside the bounded V1 limit.');
        }
    }
}
