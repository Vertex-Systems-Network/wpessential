<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Precondition;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluator;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionProbeInterface;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;

final class PreconditionEvaluatorTest extends TestCase
{
    public function testEvaluatorAggregatesTypedProbeResultsDeterministically(): void
    {
        $probe = new class implements PreconditionProbeInterface {
            public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
            {
                return $requirement->id === 'row_limit'
                    ? new PreconditionEvaluation($requirement->id, PreconditionOutcome::Blocked, ['observed_count' => 11])
                    : new PreconditionEvaluation($requirement->id, PreconditionOutcome::Satisfied);
            }
        };

        $report = (new PreconditionEvaluator($probe))->evaluate([
            new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'table.orders'),
            new PreconditionRequirement('row_limit', PreconditionKind::RowCountUnderThreshold, 'table.orders', ['max' => 10]),
        ]);

        self::assertSame(PreconditionOutcome::Blocked, $report->outcome());
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $report->fingerprint());
    }

    public function testUnsupportedProbeResultFailsClosed(): void
    {
        $probe = new class implements PreconditionProbeInterface {
            public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
            {
                return new PreconditionEvaluation($requirement->id, PreconditionOutcome::Unsupported);
            }
        };

        $report = (new PreconditionEvaluator($probe))->evaluate([
            new PreconditionRequirement('feature', PreconditionKind::DatabaseFeatureAvailable, 'provider.feature', ['feature' => 'instant_ddl']),
        ]);

        self::assertSame(PreconditionOutcome::Unsupported, $report->outcome());
    }

    public function testMismatchedProbeEvidenceIsRejected(): void
    {
        $probe = new class implements PreconditionProbeInterface {
            public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
            {
                return new PreconditionEvaluation('different_requirement', PreconditionOutcome::Satisfied);
            }
        };

        $this->expectException(RuntimeException::class);
        (new PreconditionEvaluator($probe))->evaluate([
            new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'table.orders'),
        ]);
    }
}
