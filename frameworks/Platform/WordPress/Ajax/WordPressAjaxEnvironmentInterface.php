<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

interface WordPressAjaxEnvironmentInterface
{
    public function registerAction(string $hook, callable $callback): void;

    /** @return array<string,mixed> */
    public function request(): array;

    public function isAuthenticated(): bool;

    public function currentUserCan(string $capability): bool;

    public function respond(AjaxResponse $response): void;
}
