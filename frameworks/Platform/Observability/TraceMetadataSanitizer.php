<?php

declare(strict_types=1);

namespace WPEssential\Platform\Observability;

final class TraceMetadataSanitizer
{
    private const SENSITIVE = '/(?:password|passwd|secret|token|authorization|cookie|api[_-]?key|private[_-]?key|card|nonce|signed[_-]?url)/i';

    public function __construct(private readonly int $maxDepth = 6) {}

    /** @param array<string|int,mixed> $metadata @return array<string|int,mixed> */
    public function sanitize(array $metadata, int $depth = 0): array
    {
        if ($depth >= $this->maxDepth) {
            return ['_truncated' => true];
        }
        foreach ($metadata as $key => $value) {
            if (is_string($key) && preg_match(self::SENSITIVE, $key) === 1) {
                $metadata[$key] = '[REDACTED]';
                continue;
            }
            if (is_array($value)) {
                $metadata[$key] = $this->sanitize($value, $depth + 1);
            } elseif (is_object($value) || is_resource($value)) {
                $metadata[$key] = sprintf('[%s]', get_debug_type($value));
            }
        }
        return $metadata;
    }
}
