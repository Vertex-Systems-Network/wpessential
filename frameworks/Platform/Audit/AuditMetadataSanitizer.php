<?php

declare(strict_types=1);

namespace WPEssential\Platform\Audit;

final class AuditMetadataSanitizer
{
    private const REDACTED = '[REDACTED]';
    private const TRUNCATED = '[TRUNCATED]';
    private const MAX_DEPTH = 8;
    private const MAX_STRING_BYTES = 2048;

    /**
     * @param array<string|int, mixed> $metadata
     * @return array<string|int, mixed>
     */
    public static function sanitize(array $metadata): array
    {
        return self::sanitizeArray($metadata, 0);
    }

    /**
     * @param array<string|int, mixed> $value
     * @return array<string|int, mixed>
     */
    private static function sanitizeArray(array $value, int $depth): array
    {
        if ($depth >= self::MAX_DEPTH) {
            return ['_truncated' => self::TRUNCATED];
        }

        $sanitized = [];
        foreach ($value as $key => $item) {
            if (is_string($key) && self::isSensitiveKey($key)) {
                $sanitized[$key] = self::REDACTED;
                continue;
            }
            $sanitized[$key] = self::sanitizeValue($item, $depth + 1);
        }

        return $sanitized;
    }

    private static function sanitizeValue(mixed $value, int $depth): mixed
    {
        if (is_array($value)) {
            return self::sanitizeArray($value, $depth);
        }
        if (is_string($value)) {
            if (strlen($value) > self::MAX_STRING_BYTES) {
                return substr($value, 0, self::MAX_STRING_BYTES) . self::TRUNCATED;
            }
            return $value;
        }
        if (is_scalar($value) || $value === null) {
            return $value;
        }

        return '[UNSUPPORTED]';
    }

    private static function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(str_replace(['-', ' '], '_', $key));

        return (bool) preg_match(
            '/(^|_)(password|passwd|secret|token|authorization|cookie|api_key|apikey|private_key|card|cvv|cvc|reset_token|signed_url)($|_)/',
            $normalized
        );
    }
}
