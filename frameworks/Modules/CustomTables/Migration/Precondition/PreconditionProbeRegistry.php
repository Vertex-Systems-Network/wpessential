<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final class PreconditionProbeRegistry implements PreconditionProbeInterface
{
    /** @var array<string,PreconditionProbeInterface> */
    private array $probes = [];

    public function register(PreconditionKind $kind, PreconditionProbeInterface $probe): void
    {
        $key = $kind->value;
        if (isset($this->probes[$key])) {
            throw new InvalidArgumentException('Custom Tables precondition probe kind is already registered.');
        }

        $this->probes[$key] = $probe;
    }

    public function has(PreconditionKind $kind): bool
    {
        return isset($this->probes[$kind->value]);
    }

    public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
    {
        $probe = $this->probes[$requirement->kind->value] ?? null;
        if ($probe === null) {
            throw new RuntimeException('Custom Tables precondition probe is not registered for this kind.');
        }

        $evaluation = $probe->evaluate($requirement);
        if ($evaluation->requirementId !== $requirement->id) {
            throw new RuntimeException('Custom Tables precondition probe returned mismatched evidence.');
        }

        return $evaluation;
    }
}
