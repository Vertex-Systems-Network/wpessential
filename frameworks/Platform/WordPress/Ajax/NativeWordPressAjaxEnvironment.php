<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

use JsonException;
use RuntimeException;

final class NativeWordPressAjaxEnvironment implements WordPressAjaxEnvironmentInterface
{
    public function registerAction(string $hook, callable $callback): void
    {
        if (!function_exists('add_action')) {
            throw new RuntimeException('WordPress action API is unavailable.');
        }
        add_action($hook, $callback);
    }

    public function request(): array
    {
        $request = is_array($_POST) ? wp_unslash($_POST) : [];
        $payloadJson = $request['payload_json'] ?? null;
        if (!is_string($payloadJson)) {
            return $request;
        }

        unset($request['payload_json']);
        try {
            $payload = json_decode($payloadJson, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $request['payload'] = null;
            return $request;
        }

        $request['payload'] = is_array($payload) ? $payload : null;
        return $request;
    }

    public function isAuthenticated(): bool
    {
        return function_exists('is_user_logged_in') && is_user_logged_in();
    }

    public function currentUserCan(string $capability): bool
    {
        return function_exists('current_user_can') && current_user_can($capability);
    }

    public function respond(AjaxResponse $response): void
    {
        if (!function_exists('wp_send_json')) {
            throw new RuntimeException('WordPress JSON response API is unavailable.');
        }
        wp_send_json($response->payload(), $response->status);
    }
}
