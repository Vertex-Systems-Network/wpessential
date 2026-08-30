<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;

final class RegistrationCompilationStatus
{
    private bool $attempted = false;
    private ?int $generation = null;
    private ?string $error = null;

    public function markSuccess(?CompiledRegistrationManifest $manifest): void
    {
        $this->attempted = true;
        $this->generation = $manifest?->generation;
        $this->error = null;
    }

    public function markFailure(Throwable $exception): void
    {
        $this->attempted = true;
        $this->generation = null;
        $message = trim($exception->getMessage());
        $this->error = substr($message === '' ? $exception::class : $message, 0, 500);
    }

    public function attempted(): bool
    {
        return $this->attempted;
    }

    public function generation(): ?int
    {
        return $this->generation;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function passed(): bool
    {
        return $this->attempted && $this->error === null;
    }
}
