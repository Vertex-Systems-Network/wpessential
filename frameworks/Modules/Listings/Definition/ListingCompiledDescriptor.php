<?php

declare(strict_types=1);

namespace WPEssential\Modules\Listings\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\Listings\Presentation\ListingPresentationDescriptor;

final readonly class ListingCompiledDescriptor
{
    /**
     * @param list<string> $assetHandles
     * @param list<ListingRenderBinding> $renderBindings
     */
    public function __construct(
        public string $listingId,
        public int $revision,
        public string $querySourceRef,
        public string $blueprintId,
        public int $blueprintRevision,
        public string $layoutMode,
        public int $columns,
        public array $assetHandles,
        public string $compatibilityFingerprint,
        public array $renderBindings = [],
        public ?ListingPresentationDescriptor $presentation = null,
    ) {
        if ($this->revision < 1 || $this->blueprintRevision < 1) {
            throw new InvalidArgumentException('Listing and blueprint revisions must be positive.');
        }
        if (!in_array($this->layoutMode, ['list', 'grid'], true)) {
            throw new InvalidArgumentException('Listing layout mode is unsupported.');
        }
        if ($this->columns < 1 || $this->columns > 6) {
            throw new InvalidArgumentException('Listing grid columns must be within 1..6.');
        }
        if ($this->presentation !== null && $this->presentation->mode !== $this->layoutMode) {
            throw new InvalidArgumentException('Compiled Listing presentation mode must match the runtime layout mode.');
        }
        if (!preg_match('/^[0-9a-f]{64}$/', $this->compatibilityFingerprint)) {
            throw new InvalidArgumentException('Listing compatibility fingerprint must be SHA-256 hex.');
        }
        if (count($this->renderBindings) > 128) {
            throw new InvalidArgumentException('Listing render binding plan exceeds the bounded V1 limit.');
        }
        $seen = [];
        foreach ($this->renderBindings as $binding) {
            if (!$binding instanceof ListingRenderBinding) {
                throw new InvalidArgumentException('Listing render binding plan must contain typed bindings.');
            }
            if (isset($seen[$binding->bindingKey])) {
                throw new InvalidArgumentException('Listing render binding keys must be unique.');
            }
            $seen[$binding->bindingKey] = true;
        }
    }
}
