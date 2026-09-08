<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Precondition;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionReport;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;

final class PreconditionContractTest extends TestCase
{
    public function testRequirementIsTypedAndCanonical(): void
    {
        $requirement = new PreconditionRequirement(
            id: 'no_duplicate_email',
            kind: PreconditionKind::NoDuplicateValues,
            target: 'columns.email',
            parameters: ['max' => 0],
        );

        self::assertSame('no_duplicate_values', $requirement->canonical()['kind']);
        self::assertSame('columns.email', $requirement->canonical()['target']);
    }

    public function testBlockedOutcomeDominatesAggregateReport(): void
    {
        $report = new PreconditionReport([
            new PreconditionEvaluation('table_exists', PreconditionOutcome::Satisfied),
            new PreconditionEvaluation('capacity', PreconditionOutcome::Unsupported),
            new PreconditionEvaluation('no_duplicates', PreconditionOutcome::Blocked, ['observed_count' => 2]),
        ]);

        self::assertSame(PreconditionOutcome::Blocked, $report->outcome());
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $report->fingerprint());
    }

    public function testUnsupportedOutcomeFailsClosedWhenNothingIsBlocked(): void
    {
        $report = new PreconditionReport([
            new PreconditionEvaluation('table_exists', PreconditionOutcome::Satisfied),
            new PreconditionEvaluation('capacity', PreconditionOutcome::Unsupported),
        ]);

        self::assertSame(PreconditionOutcome::Unsupported, $report->outcome());
    }

    public function testArbitraryEvidenceKeyIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PreconditionEvaluation(
            'no_duplicates',
            PreconditionOutcome::Blocked,
            ['row_value' => 'secret'],
        );
    }

    public function testRawSqlLikeParameterKeyIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PreconditionRequirement(
            id: 'unsafe',
            kind: PreconditionKind::TableExists,
            target: 'table.settings',
            parameters: ['sql' => 'SELECT * FROM wp_settings'],
        );
    }
}
