<?php

declare(strict_types=1);

namespace WPEssential\Platform\Abilities\InputValidation;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class AbilityInputValidationResult
{
    private function __construct(
        public bool $valid,
        public string $code,
        public string $path,
    ) {
    }

    public static function valid(): self
    {
        return new self(true, 'valid', '$');
    }

    public static function invalid(string $code, string $path): self
    {
        return new self(false, $code, $path);
    }
}
