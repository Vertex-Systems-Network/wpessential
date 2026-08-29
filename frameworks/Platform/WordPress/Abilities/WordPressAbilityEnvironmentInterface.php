<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

interface WordPressAbilityEnvironmentInterface
{
    public function abilitiesApiAvailable(): bool;
    public function doingAction(string $hook): bool;
    public function currentUserId(): ?int;
    public function currentSiteId(): int;
    public function currentNetworkId(): ?int;
    public function currentUserCan(string $capability): bool;
    public function isRestRequest(): bool;
    public function isCli(): bool;

    /** @param array<string, mixed> $args */
    public function registerCategory(string $slug, array $args): bool;

    /** @param array<string, mixed> $args */
    public function registerAbility(string $name, array $args): bool;
}
