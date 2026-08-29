<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Security;

interface NonceEnvironmentInterface
{
    public function create(string $action): string;

    public function verify(string $nonce, string $action): bool;
}
