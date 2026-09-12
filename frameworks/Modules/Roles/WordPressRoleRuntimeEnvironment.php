<?php

declare(strict_types=1);

namespace WPEssential\Modules\Roles;

if (!defined('ABSPATH')) {
    exit;
}

final class WordPressRoleRuntimeEnvironment implements RoleRuntimeEnvironmentInterface
{
    public function available(): bool
    {
        return function_exists('wp_roles');
    }

    public function siteExists(int $siteId): bool
    {
        if ($siteId < 1 || !$this->available()) {
            return false;
        }

        if (!$this->isMultisite()) {
            $currentSiteId = function_exists('get_current_blog_id') ? (int) get_current_blog_id() : 1;
            return $siteId === max(1, $currentSiteId);
        }

        if (!function_exists('get_site')) {
            return false;
        }

        return get_site($siteId) instanceof \WP_Site;
    }

    public function networkIdForSite(int $siteId): ?int
    {
        if (!$this->siteExists($siteId)) {
            return null;
        }

        if (!$this->isMultisite()) {
            return function_exists('get_current_network_id')
                ? max(1, (int) get_current_network_id())
                : 1;
        }

        $site = function_exists('get_site') ? get_site($siteId) : null;
        if (!$site instanceof \WP_Site) {
            return null;
        }

        return max(1, (int) $site->network_id);
    }

    public function isMultisite(): bool
    {
        return function_exists('is_multisite') && is_multisite();
    }

    public function rolesForSite(int $siteId): array
    {
        if (!$this->siteExists($siteId)) {
            return [];
        }

        $currentSiteId = function_exists('get_current_blog_id') ? max(1, (int) get_current_blog_id()) : 1;
        $switched = false;

        if ($siteId !== $currentSiteId) {
            if (!function_exists('switch_to_blog') || !function_exists('restore_current_blog')) {
                return [];
            }
            switch_to_blog($siteId);
            $switched = true;
        }

        try {
            $registry = wp_roles();
            if (!$registry instanceof \WP_Roles || !is_array($registry->roles)) {
                return [];
            }

            $catalog = [];
            foreach ($registry->roles as $key => $definition) {
                if (!is_string($key) || !is_array($definition)) {
                    continue;
                }

                $name = $definition['name'] ?? $key;
                $capabilities = $definition['capabilities'] ?? [];
                if (!is_array($capabilities)) {
                    $capabilities = [];
                }

                $normalized = [];
                foreach ($capabilities as $capability => $granted) {
                    if (!is_string($capability) || $capability === '') {
                        continue;
                    }
                    $normalized[$capability] = (bool) $granted;
                }
                ksort($normalized, SORT_STRING);

                $catalog[$key] = [
                    'name' => is_string($name) && $name !== '' ? $name : $key,
                    'capabilities' => $normalized,
                ];
            }
            ksort($catalog, SORT_STRING);

            return $catalog;
        } finally {
            if ($switched) {
                restore_current_blog();
            }
        }
    }
}
