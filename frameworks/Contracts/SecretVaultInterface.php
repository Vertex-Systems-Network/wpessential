<?php

declare(strict_types=1);

namespace WPEssential\Contracts;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Secrets\SecretReference;
use WPEssential\Platform\Secrets\SensitiveValue;

interface SecretVaultInterface
{
    /** @param array<string, scalar|null> $metadata */
    public function store(string $name, int $ownerSurfaceId, string $plaintext, array $metadata = []): SecretReference;

    public function resolve(SecretReference $reference): SensitiveValue;

    public function rotate(SecretReference $reference, string $plaintext): SecretReference;

    public function revoke(SecretReference $reference): void;
}
