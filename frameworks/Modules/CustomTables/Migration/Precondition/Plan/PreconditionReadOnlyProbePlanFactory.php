<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition\Plan;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;

final class PreconditionReadOnlyProbePlanFactory
{
    public function create(PreconditionRequirement $requirement): PreconditionProbePlan
    {
        $operation = match ($requirement->kind) {
            PreconditionKind::TableExists => PreconditionProbeOperation::TableExists,
            PreconditionKind::TableMissing => PreconditionProbeOperation::TableMissing,
            PreconditionKind::ColumnMatchesFingerprint => PreconditionProbeOperation::ColumnMatchesFingerprint,
            PreconditionKind::RowCountRange => PreconditionProbeOperation::RowCountRange,
            PreconditionKind::NoNullValues => PreconditionProbeOperation::NoNullValues,
            PreconditionKind::NoDuplicateValues => PreconditionProbeOperation::NoDuplicateValues,
            PreconditionKind::MaxValueFits => PreconditionProbeOperation::MaxValueFits,
            PreconditionKind::MaxStringLengthFits => PreconditionProbeOperation::MaxStringLengthFits,
            PreconditionKind::NoOrphanReferences => PreconditionProbeOperation::NoOrphanReferences,
            PreconditionKind::DatabaseFeatureAvailable => PreconditionProbeOperation::DatabaseFeatureAvailable,
            PreconditionKind::CapacityAvailable => PreconditionProbeOperation::CapacityAvailable,
            PreconditionKind::NoActiveMigration => PreconditionProbeOperation::NoActiveMigration,
            PreconditionKind::BackupTierAvailable => PreconditionProbeOperation::BackupTierAvailable,
        };

        $this->validateParameters($requirement);

        return new PreconditionProbePlan(
            requirementId: $requirement->id,
            operation: $operation,
            target: $requirement->target,
            parameters: $requirement->parameters,
        );
    }

    private function validateParameters(PreconditionRequirement $requirement): void
    {
        $allowed = match ($requirement->kind) {
            PreconditionKind::TableExists,
            PreconditionKind::TableMissing,
            PreconditionKind::NoNullValues,
            PreconditionKind::NoDuplicateValues,
            PreconditionKind::NoOrphanReferences,
            PreconditionKind::NoActiveMigration => [],
            PreconditionKind::ColumnMatchesFingerprint => ['fingerprint'],
            PreconditionKind::RowCountRange => ['min', 'max'],
            PreconditionKind::MaxValueFits => ['max'],
            PreconditionKind::MaxStringLengthFits => ['max'],
            PreconditionKind::DatabaseFeatureAvailable => ['feature'],
            PreconditionKind::CapacityAvailable => ['estimate_bytes'],
            PreconditionKind::BackupTierAvailable => ['tier'],
        };

        foreach (array_keys($requirement->parameters) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException('Custom Tables precondition probe-plan parameter is not allowed for this kind.');
            }
        }

        match ($requirement->kind) {
            PreconditionKind::ColumnMatchesFingerprint => $this->requireString($requirement, 'fingerprint'),
            PreconditionKind::RowCountRange => $this->validateRange($requirement),
            PreconditionKind::MaxValueFits => $this->requireScalar($requirement, 'max'),
            PreconditionKind::MaxStringLengthFits => $this->requirePositiveInt($requirement, 'max'),
            PreconditionKind::DatabaseFeatureAvailable => $this->requireString($requirement, 'feature'),
            PreconditionKind::CapacityAvailable => $this->requireNonNegativeInt($requirement, 'estimate_bytes'),
            PreconditionKind::BackupTierAvailable => $this->requireString($requirement, 'tier'),
            default => null,
        };
    }

    private function validateRange(PreconditionRequirement $requirement): void
    {
        $min = $requirement->parameters['min'] ?? null;
        $max = $requirement->parameters['max'] ?? null;
        if ($min === null && $max === null) {
            throw new InvalidArgumentException('Custom Tables row-count probe plan requires min or max.');
        }
        if ($min !== null && (!is_int($min) || $min < 0)) {
            throw new InvalidArgumentException('Custom Tables row-count probe plan min must be a non-negative integer.');
        }
        if ($max !== null && (!is_int($max) || $max < 0)) {
            throw new InvalidArgumentException('Custom Tables row-count probe plan max must be a non-negative integer.');
        }
        if (is_int($min) && is_int($max) && $min > $max) {
            throw new InvalidArgumentException('Custom Tables row-count probe plan min cannot exceed max.');
        }
    }

    private function requireString(PreconditionRequirement $requirement, string $key): void
    {
        $value = $requirement->parameters[$key] ?? null;
        if (!is_string($value) || $value === '') {
            throw new InvalidArgumentException('Custom Tables precondition probe-plan string parameter is required.');
        }
    }

    private function requireScalar(PreconditionRequirement $requirement, string $key): void
    {
        $value = $requirement->parameters[$key] ?? null;
        if (!is_int($value) && !is_string($value)) {
            throw new InvalidArgumentException('Custom Tables precondition probe-plan scalar parameter is required.');
        }
    }

    private function requirePositiveInt(PreconditionRequirement $requirement, string $key): void
    {
        $value = $requirement->parameters[$key] ?? null;
        if (!is_int($value) || $value < 1) {
            throw new InvalidArgumentException('Custom Tables precondition probe-plan integer parameter must be positive.');
        }
    }

    private function requireNonNegativeInt(PreconditionRequirement $requirement, string $key): void
    {
        $value = $requirement->parameters[$key] ?? null;
        if (!is_int($value) || $value < 0) {
            throw new InvalidArgumentException('Custom Tables precondition probe-plan integer parameter must be non-negative.');
        }
    }
}
