<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Security;

use RuntimeException;

final class NativeWordPressNonceEnvironment implements NonceEnvironmentInterface
{
    public function create(string $action): string
    {
        if (!function_exists('wp_create_nonce')) {
            throw new RuntimeException('WordPress nonce API is unavailable.');
        }

        return (string) wp_create_nonce($action);
    }

    public function verify(string $nonce, string $action): bool
    {
        return function_exists('wp_verify_nonce') && wp_verify_nonce($nonce, $action) !== false;
    }
}
