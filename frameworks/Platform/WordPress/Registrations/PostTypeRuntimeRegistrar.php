<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if (!defined('ABSPATH')) {
    exit;
}

final class PostTypeRuntimeRegistrar
{
    private bool $processed = false;

    /** @var list<string> */
    private array $registered = [];

    /** @var list<string> */
    private array $conflicts = [];

    /** @var array<string, string> */
    private array $errors = [];

    public function __construct(private readonly RegistrationRuntimeLoader $runtime) {}

    public function register(): void
    {
        if (function_exists('add_action')) {
            add_action('init', [$this, 'registerActive'], 20);
        }
    }

    public function registerActive(): void
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        if (!function_exists('post_type_exists') || !function_exists('register_post_type')) {
            $this->errors['runtime'] = 'WordPress post type APIs are unavailable.';
            return;
        }

        foreach ($this->runtime->forKind(RegistrationKind::PostType) as $key => $entry) {
            $payload = $entry['payload'] ?? null;
            if (!is_array($payload)) {
                $this->errors[$key] = 'Compiled post type payload is invalid.';
                continue;
            }
            if (post_type_exists($key)) {
                $this->conflicts[] = $key;
                continue;
            }

            $result = register_post_type($key, $payload);
            if (function_exists('is_wp_error') && is_wp_error($result)) {
                $this->errors[$key] = $result->get_error_message();
                continue;
            }
            $this->registered[] = $key;
        }
    }

    /** @return list<string> */
    public function registered(): array
    {
        return $this->registered;
    }

    /** @return list<string> */
    public function conflicts(): array
    {
        return $this->conflicts;
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
