<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class FieldValuePersistenceGuard
{
    public function assertSafe(mixed $value): mixed
    {
        if ($value === null || is_string($value) || is_int($value) || is_bool($value)) {
            return $value;
        }

        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new InvalidArgumentException('Persisted numeric Field values must be finite.');
            }
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->assertSafe($item);
            }
            return $value;
        }

        throw new InvalidArgumentException('Persisted Field values must contain only canonical scalar/array data.');
    }
}
