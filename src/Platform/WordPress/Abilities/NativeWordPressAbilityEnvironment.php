<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;

final class NativeWordPressAbilityEnvironment implements WordPressAbilityEnvironmentInterface
{
    public function abilitiesApiAvailable(): bool
    {
        return class_exists('WP_Ability')
            && function_exists('wp_register_ability')
            && function_exists('wp_register_ability_category');
    }

    public function doingAction(string $hook): bool
    {
        return function_exists('doing_action') && doing_action($hook);
    }

    public function currentUserId(): ?int
    {
        if (!function_exists('get_current_user_id')) return null;
        $id = (int) get_current_user_id();
        return $id > 0 ? $id : null;
    }

    public function currentSiteId(): int
    {
        if (!function_exists('get_current_blog_id')) return 1;
        return max(1, (int) get_current_blog_id());
    }

    public function currentNetworkId(): ?int
    {
        if (!function_exists('get_current_network_id')) return null;
        $id = (int) get_current_network_id();
        return $id > 0 ? $id : null;
    }

    public function currentUserCan(string $capability): bool
    {
        return function_exists('current_user_can') && current_user_can($capability);
    }

    public function isRestRequest(): bool
    {
        return defined('REST_REQUEST') && REST_REQUEST === true;
    }

    public function isCli(): bool
    {
        return defined('WP_CLI') && WP_CLI === true;
    }

    public function registerCategory(string $slug, array $args): bool
    {
        return function_exists('wp_register_ability_category')
            && wp_register_ability_category($slug, $args) !== null;
    }

    public function registerAbility(string $name, array $args): bool
    {
        return function_exists('wp_register_ability')
            && wp_register_ability($name, $args) !== null;
    }
}
