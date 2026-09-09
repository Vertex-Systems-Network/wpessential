<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class RuntimeCompositionReadinessPackage
{
    public bool $executionAllowed;

    /** @param list<string> $reasons */
    public function __construct(
        public string $runId,
        public string $planFingerprint,
        public int $readinessStateRevision,
        public bool $compositionReady,
        public array $reasons,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $this->runId) !== 1) {
            throw new InvalidArgumentException('Custom Tables runtime composition package run id must be a canonical UUID.');
        }
        if (preg_match('/^[a-f0-9]{64}$/', $this->planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables runtime composition package plan fingerprint is invalid.');
        }
        if ($this->readinessStateRevision < 1) {
            throw new InvalidArgumentException('Custom Tables runtime composition package readiness revision must be positive.');
        }

        $normalized = array_values(array_unique($this->reasons));
        sort($normalized);
        if ($normalized !== $this->reasons) {
            throw new InvalidArgumentException('Custom Tables runtime composition package reasons must be unique and sorted.');
        }
        if ($this->compositionReady && $this->reasons !== []) {
            throw new InvalidArgumentException('Custom Tables runtime composition package cannot be ready with blocking reasons.');
        }

        $this->executionAllowed = false;
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'run_id' => strtolower($this->runId),
            'plan_fingerprint' => $this->planFingerprint,
            'readiness_state_revision' => $this->readinessStateRevision,
            'composition_ready' => $this->compositionReady,
            'execution_allowed' => false,
            'reasons' => $this->reasons,
        ];
    }
}
