<?php

declare(strict_types=1);

namespace WPEssential\Platform\Secrets;


if (!defined('ABSPATH')) {
    exit;
}

use JsonSerializable;

final class SensitiveValue implements JsonSerializable
{
    public function __construct(private string $plaintext)
    {
    }

    public function reveal(): string
    {
        return $this->plaintext;
    }

    /** @return array{value:string} */
    public function __debugInfo(): array
    {
        return ['value' => '[REDACTED]'];
    }

    public function jsonSerialize(): string
    {
        return '[REDACTED]';
    }
}
