<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if (!defined('ABSPATH')) {
    exit;
}

final class TaxonomyRuntimeRegistrar
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
            add_action('init', [$this, 'registerActive'], 21);
        }
    }

    public function registerActive(): void
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        if (!function_exists('taxonomy_exists') || !function_exists('register_taxonomy')) {
            $this->errors['runtime'] = 'WordPress taxonomy APIs are unavailable.';
            return;
        }

        foreach ($this->runtime->forKind(RegistrationKind::Taxonomy) as $key => $entry) {
            $payload = $entry['payload'] ?? null;
            if (!is_array($payload)) {
                $this->errors[$key] = 'Compiled taxonomy payload is invalid.';
                continue;
            }

            $objectTypes = $payload['object_types'] ?? null;
            $args = $payload['args'] ?? null;
            if (!is_array($objectTypes) || !array_is_list($objectTypes) || $objectTypes === [] || !is_array($args)) {
                $this->errors[$key] = 'Compiled taxonomy registration contract is invalid.';
                continue;
            }
            if (taxonomy_exists($key)) {
                $this->conflicts[] = $key;
                continue;
            }

            $result = register_taxonomy($key, $objectTypes, $args);
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
