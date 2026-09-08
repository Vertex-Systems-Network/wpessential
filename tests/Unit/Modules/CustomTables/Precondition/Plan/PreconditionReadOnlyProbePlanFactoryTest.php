<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Precondition\Plan;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Precondition\Plan\PreconditionProbeOperation;
use WPEssential\Modules\CustomTables\Migration\Precondition\Plan\PreconditionReadOnlyProbePlanFactory;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;

final class PreconditionReadOnlyProbePlanFactoryTest extends TestCase
{
    public function testCompilesBoundedFingerprintProbeWithoutSqlPayload(): void
    {
        $requirement = new PreconditionRequirement(
            'column_match',
            PreconditionKind::ColumnMatchesFingerprint,
            'table.orders.status',
            ['fingerprint' => str_repeat('a', 64)],
        );

        $plan = (new PreconditionReadOnlyProbePlanFactory())->create($requirement);
        $canonical = $plan->canonical();

        self::assertSame(PreconditionProbeOperation::ColumnMatchesFingerprint, $plan->operation);
        self::assertSame($requirement->canonical()['parameters'], $canonical['parameters']);
        self::assertArrayNotHasKey('sql', $canonical);
    }

    public function testRowCountRangeIsValidatedAndCanonicalized(): void
    {
        $requirement = new PreconditionRequirement(
            'row_range',
            PreconditionKind::RowCountRange,
            'table.orders',
            ['max' => 1000, 'min' => 10],
        );

        $plan = (new PreconditionReadOnlyProbePlanFactory())->create($requirement);

        self::assertSame([
            'requirement_id' => 'row_range',
            'operation' => 'row_count_range',
            'target' => 'table.orders',
            'parameters' => ['max' => 1000, 'min' => 10],
        ], $plan->canonical());
    }

    public function testNoParameterKindRejectsExtraneousParameter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PreconditionReadOnlyProbePlanFactory())->create(new PreconditionRequirement(
            'table_exists',
            PreconditionKind::TableExists,
            'table.orders',
            ['min' => 1],
        ));
    }

    public function testFingerprintKindRequiresFingerprint(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PreconditionReadOnlyProbePlanFactory())->create(new PreconditionRequirement(
            'column_match',
            PreconditionKind::ColumnMatchesFingerprint,
            'table.orders.status',
        ));
    }

    public function testInvalidRowRangesFailClosed(): void
    {
        foreach ([
            [],
            ['min' => -1],
            ['max' => -1],
            ['min' => 10, 'max' => 1],
        ] as $parameters) {
            try {
                (new PreconditionReadOnlyProbePlanFactory())->create(new PreconditionRequirement(
                    'row_range',
                    PreconditionKind::RowCountRange,
                    'table.orders',
                    $parameters,
                ));
                self::fail('Invalid row range should fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testStringLengthLimitMustBePositiveInteger(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new PreconditionReadOnlyProbePlanFactory())->create(new PreconditionRequirement(
            'string_length',
            PreconditionKind::MaxStringLengthFits,
            'table.orders.note',
            ['max' => 0],
        ));
    }
}
