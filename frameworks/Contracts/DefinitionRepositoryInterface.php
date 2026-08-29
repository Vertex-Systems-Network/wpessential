<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

use WPEssential\Platform\Definitions\Definition;

interface DefinitionRepositoryInterface
{
    public function save(Definition $definition): void;

    public function get(string $id): ?Definition;

    /** @return list<Definition> */
    public function byType(string $type): array;

    /** @return list<Definition> */
    public function dependentsOf(string $id): array;
}
