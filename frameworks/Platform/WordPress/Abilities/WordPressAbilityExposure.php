<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class WordPressAbilityExposure
{
    public function __construct(
        public string $internalName,
        public string $label,
        public string $description,
        public bool $showInRest = false,
    ) {
        if (!preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $this->internalName)) {
            throw new InvalidArgumentException('Exposure must reference a canonical internal WPE ability name.');
        }
        if (trim($this->label) === '' || trim($this->description) === '') {
            throw new InvalidArgumentException('WordPress ability exposure requires explicit label and description.');
        }
    }
}
