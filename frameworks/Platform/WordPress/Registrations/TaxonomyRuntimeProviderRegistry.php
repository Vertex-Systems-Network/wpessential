<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use RuntimeException;

final class TaxonomyRuntimeProviderRegistry
{
    /** @var array<string, class-string> */
    private array $restControllers = [];

    /** @var array<string, mixed> */
    private array $metaBoxCallbacks = [];

    /** @var array<string, mixed> */
    private array $metaBoxSanitizeCallbacks = [];

    /** @var array<string, mixed> */
    private array $termCountCallbacks = [];

    public function __construct()
    {
        // Only lifecycle-safe defaults are registered here. Admin-only WordPress callback
        // functions are not guaranteed to be loaded during frontend init, so explicit
        // editor/count providers are registered by trusted PHP modules when needed.
        $this->registerRestController('wordpress.terms', 'WP_REST_Terms_Controller');
        $this->registerMetaBoxProvider('wordpress.disabled', false);
    }

    /** @param class-string $className */
    public function registerRestController(string $id, string $className): void
    {
        $id = $this->providerId($id);
        if (!$this->className($className) || isset($this->restControllers[$id])) {
            throw new InvalidArgumentException('Taxonomy REST controller provider must use a unique provider id and valid class name.');
        }
        $this->restControllers[$id] = $className;
    }

    public function registerMetaBoxProvider(string $id, mixed $callback): void
    {
        $id = $this->providerId($id);
        if (array_key_exists($id, $this->metaBoxCallbacks)) {
            throw new InvalidArgumentException('Taxonomy meta-box provider id is already registered.');
        }
        $this->assertTrustedCallbackDescriptor($callback, true, 'Taxonomy meta-box provider');
        $this->metaBoxCallbacks[$id] = $callback;
    }

    public function registerMetaBoxSanitizeProvider(string $id, mixed $callback): void
    {
        $id = $this->providerId($id);
        if (array_key_exists($id, $this->metaBoxSanitizeCallbacks)) {
            throw new InvalidArgumentException('Taxonomy meta-box sanitizer provider id is already registered.');
        }
        $this->assertTrustedCallbackDescriptor($callback, false, 'Taxonomy meta-box sanitizer provider');
        $this->metaBoxSanitizeCallbacks[$id] = $callback;
    }

    public function registerTermCountProvider(string $id, mixed $callback): void
    {
        $id = $this->providerId($id);
        if (array_key_exists($id, $this->termCountCallbacks)) {
            throw new InvalidArgumentException('Taxonomy term-count provider id is already registered.');
        }
        $this->assertTrustedCallbackDescriptor($callback, false, 'Taxonomy term-count provider');
        $this->termCountCallbacks[$id] = $callback;
    }

    public function hasRestController(string $id): bool
    {
        return isset($this->restControllers[$id]);
    }

    public function hasMetaBoxProvider(string $id): bool
    {
        return array_key_exists($id, $this->metaBoxCallbacks);
    }

    public function hasMetaBoxSanitizeProvider(string $id): bool
    {
        return array_key_exists($id, $this->metaBoxSanitizeCallbacks);
    }

    public function hasTermCountProvider(string $id): bool
    {
        return array_key_exists($id, $this->termCountCallbacks);
    }

    /**
     * Resolve persisted provider IDs at the last responsible runtime boundary.
     *
     * Compiled registration manifests remain JSON-safe because only provider IDs are
     * persisted. Native PHP callables/classes are introduced here, after loading the
     * compiled manifest and immediately before invoking register_taxonomy().
     *
     * @param array<string,mixed> $args
     * @param array<string,mixed> $providerIds
     * @return array<string,mixed>
     */
    public function apply(array $args, array $providerIds): array
    {
        $allowed = ['rest_controller', 'meta_box', 'meta_box_sanitize', 'term_count'];
        foreach (array_keys($providerIds) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('Unknown Taxonomy runtime provider slot "%s".', (string) $key));
            }
        }

        if (array_key_exists('rest_controller', $providerIds)) {
            $id = $this->requiredPersistedId($providerIds['rest_controller'], 'rest_controller');
            $className = $this->restControllers[$id] ?? null;
            if (!is_string($className) || !class_exists($className)) {
                throw new RuntimeException(sprintf('Taxonomy REST controller provider "%s" is unavailable at runtime.', $id));
            }
            $args['rest_controller_class'] = $className;
        }

        if (array_key_exists('meta_box', $providerIds)) {
            $id = $this->requiredPersistedId($providerIds['meta_box'], 'meta_box');
            if (!array_key_exists($id, $this->metaBoxCallbacks)) {
                throw new RuntimeException(sprintf('Taxonomy meta-box provider "%s" is unavailable at runtime.', $id));
            }
            $callback = $this->metaBoxCallbacks[$id];
            if ($callback !== false && !is_callable($callback)) {
                throw new RuntimeException(sprintf('Taxonomy meta-box provider "%s" is not callable at runtime.', $id));
            }
            $args['meta_box_cb'] = $callback;
        }

        if (array_key_exists('meta_box_sanitize', $providerIds)) {
            $id = $this->requiredPersistedId($providerIds['meta_box_sanitize'], 'meta_box_sanitize');
            $callback = $this->metaBoxSanitizeCallbacks[$id] ?? null;
            if (!is_callable($callback)) {
                throw new RuntimeException(sprintf('Taxonomy meta-box sanitizer provider "%s" is unavailable at runtime.', $id));
            }
            $args['meta_box_sanitize_cb'] = $callback;
        }

        if (array_key_exists('term_count', $providerIds)) {
            $id = $this->requiredPersistedId($providerIds['term_count'], 'term_count');
            $callback = $this->termCountCallbacks[$id] ?? null;
            if (!is_callable($callback)) {
                throw new RuntimeException(sprintf('Taxonomy term-count provider "%s" is unavailable at runtime.', $id));
            }
            $args['update_count_callback'] = $callback;
        }

        return $args;
    }

    private function providerId(string $id): string
    {
        $id = trim($id);
        if (preg_match('/^[a-z0-9][a-z0-9._-]{0,127}$/', $id) !== 1) {
            throw new InvalidArgumentException('Taxonomy runtime provider id has an invalid shape.');
        }
        return $id;
    }

    private function requiredPersistedId(mixed $value, string $slot): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf('Taxonomy runtime provider slot "%s" must contain a provider id string.', $slot));
        }
        return $this->providerId($value);
    }

    private function className(string $className): bool
    {
        return preg_match('/^[A-Za-z_\\][A-Za-z0-9_\\]*$/', $className) === 1;
    }

    private function assertTrustedCallbackDescriptor(mixed $callback, bool $allowFalse, string $label): void
    {
        if ($callback === false) {
            if ($allowFalse) {
                return;
            }
            throw new InvalidArgumentException($label . ' cannot be disabled with false.');
        }

        if ($callback instanceof Closure || is_object($callback) && is_callable($callback)) {
            return;
        }

        if (is_string($callback)) {
            if (preg_match('/^[A-Za-z_\\][A-Za-z0-9_\\]*(?:::[A-Za-z_][A-Za-z0-9_]*)?$/', $callback) === 1) {
                return;
            }
            throw new InvalidArgumentException($label . ' string has an invalid callable identifier shape.');
        }

        if (is_array($callback) && array_is_list($callback) && count($callback) === 2 && is_string($callback[1])) {
            [$target, $method] = $callback;
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $method) !== 1) {
                throw new InvalidArgumentException($label . ' method name has an invalid shape.');
            }
            if (is_object($target)) {
                return;
            }
            if (is_string($target) && $this->className($target)) {
                return;
            }
        }

        throw new InvalidArgumentException($label . ' must be a trusted callable descriptor.');
    }
}
