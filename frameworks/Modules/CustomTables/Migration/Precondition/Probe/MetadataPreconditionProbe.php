<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition\Probe;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionProbeInterface;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;

final readonly class MetadataPreconditionProbe implements PreconditionProbeInterface
{
    public function __construct(private MetadataPreconditionFacts $facts)
    {
    }

    public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
    {
        return match ($requirement->kind) {
            PreconditionKind::TableExists => new PreconditionEvaluation(
                $requirement->id,
                $this->facts->tableExists ? PreconditionOutcome::Satisfied : PreconditionOutcome::Blocked,
            ),
            PreconditionKind::TableMissing => new PreconditionEvaluation(
                $requirement->id,
                $this->facts->tableExists ? PreconditionOutcome::Blocked : PreconditionOutcome::Satisfied,
            ),
            PreconditionKind::ColumnMatchesFingerprint => $this->columnFingerprint($requirement),
            PreconditionKind::DatabaseFeatureAvailable => $this->feature($requirement),
            default => new PreconditionEvaluation($requirement->id, PreconditionOutcome::Unsupported),
        };
    }

    private function columnFingerprint(PreconditionRequirement $requirement): PreconditionEvaluation
    {
        $expected = $requirement->parameters['fingerprint'] ?? null;
        $observed = $this->facts->columnFingerprints[$requirement->target] ?? null;
        if (!is_string($expected) || !is_string($observed)) {
            return new PreconditionEvaluation($requirement->id, PreconditionOutcome::Blocked);
        }

        return new PreconditionEvaluation(
            $requirement->id,
            hash_equals($expected, $observed) ? PreconditionOutcome::Satisfied : PreconditionOutcome::Blocked,
            ['fingerprint' => $observed],
        );
    }

    private function feature(PreconditionRequirement $requirement): PreconditionEvaluation
    {
        $feature = $requirement->parameters['feature'] ?? null;
        if (!is_string($feature) || !array_key_exists($feature, $this->facts->features)) {
            return new PreconditionEvaluation($requirement->id, PreconditionOutcome::Blocked);
        }

        return new PreconditionEvaluation(
            $requirement->id,
            $this->facts->features[$feature] ? PreconditionOutcome::Satisfied : PreconditionOutcome::Blocked,
        );
    }
}
