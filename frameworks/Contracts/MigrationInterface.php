<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

interface MigrationInterface
{
    public function id(): string;

    public function sequence(): int;

    public function isDestructive(): bool;

    public function recoveryPlan(): ?string;

    public function apply(): void;
}
