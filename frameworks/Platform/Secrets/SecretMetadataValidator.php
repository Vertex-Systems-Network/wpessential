<?php

declare(strict_types=1);

namespace WPEssential\Platform\Secrets;

use InvalidArgumentException;

final class SecretMetadataValidator
{
    private const FORBIDDEN = [
        'authorization',
        'cookie',
        'password',
        'passwd',
        'secret',
        'token',
        'api_key',
        'apikey',
        'private_key',
        'client_secret',
    ];

    /** @param array<string, scalar|null> $metadata */
    public function validate(array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            $normalized = strtolower(str_replace('-', '_', (string) $key));
            foreach (self::FORBIDDEN as $needle) {
                if ($normalized === $needle || str_ends_with($normalized, '_' . $needle)) {
                    throw new InvalidArgumentException(sprintf('Secret-bearing metadata key "%s" is not allowed.', $key));
                }
            }
            if (!is_scalar($value) && $value !== null) {
                throw new InvalidArgumentException('Secret metadata values must be scalar or null.');
            }
        }
    }
}
