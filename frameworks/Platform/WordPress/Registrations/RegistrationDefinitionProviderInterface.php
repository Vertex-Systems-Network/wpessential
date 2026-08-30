<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if (!defined('ABSPATH')) {
    exit;
}

interface RegistrationDefinitionProviderInterface
{
    public function id(): string;

    /** @return iterable<RegistrationDefinition> */
    public function definitions(): iterable;
}
