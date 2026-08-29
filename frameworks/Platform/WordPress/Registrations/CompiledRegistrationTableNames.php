<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

use RuntimeException;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CompiledRegistrationTableNames
{
    public string $generations;
    public string $state;

    public function __construct(DatabaseAdapterInterface $database)
    {
        $prefix = $database->networkTablePrefix();
        if (preg_match('/^[A-Za-z0-9_]+$/', $prefix) !== 1) {
            throw new RuntimeException('Compiled registration table prefix is unsafe.');
        }
        $this->generations = $prefix . 'wpessential_registration_generations';
        $this->state = $prefix . 'wpessential_registration_state';
    }
}
