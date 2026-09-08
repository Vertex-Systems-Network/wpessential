<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class PreconditionReport
{
    /** @param list<PreconditionEvaluation> $evaluations */
    public function __construct(public array $evaluations)
    {
        $ids = [];
        foreach ($this->evaluations as $evaluation) {
            if (!$evaluation instanceof PreconditionEvaluation) {
                throw new InvalidArgumentException('Custom Tables precondition report requires typed evaluations.');
            }
            if (isset($ids[$evaluation->requirementId])) {
                throw new InvalidArgumentException('Custom Tables precondition report contains duplicate requirement ids.');
            }
            $ids[$evaluation->requirementId] = true;
        }
    }

    public function outcome(): PreconditionOutcome
    {
        foreach ($this->evaluations as $evaluation) {
            if ($evaluation->outcome === PreconditionOutcome::Blocked) {
                return PreconditionOutcome::Blocked;
            }
        }
        foreach ($this->evaluations as $evaluation) {
            if ($evaluation->outcome === PreconditionOutcome::Unsupported) {
                return PreconditionOutcome::Unsupported;
            }
        }

        return PreconditionOutcome::Satisfied;
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        $evaluations = array_map(
            static fn (PreconditionEvaluation $evaluation): array => $evaluation->canonical(),
            $this->evaluations,
        );
        usort(
            $evaluations,
            static fn (array $a, array $b): int => $a['requirement_id'] <=> $b['requirement_id'],
        );

        return [
            'outcome' => $this->outcome()->value,
            'evaluations' => $evaluations,
        ];
    }

    /** @throws JsonException */
    public function fingerprint(): string
    {
        return hash('sha256', json_encode($this->canonical(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
