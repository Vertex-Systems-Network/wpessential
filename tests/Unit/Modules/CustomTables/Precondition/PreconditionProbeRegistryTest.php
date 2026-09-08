<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Precondition;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionEvaluation;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionProbeInterface;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionProbeRegistry;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;

final class PreconditionProbeRegistryTest extends TestCase
{
    public function testRegisteredKindDispatchesToTypedProbe(): void
    {
        $registry = new PreconditionProbeRegistry();
        $registry->register(PreconditionKind::TableExists, new class implements PreconditionProbeInterface {
            public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
            {
                return new PreconditionEvaluation($requirement->id, PreconditionOutcome::Satisfied);
            }
        });

        $requirement = new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'table.orders');
        self::assertTrue($registry->has(PreconditionKind::TableExists));
        self::assertSame(PreconditionOutcome::Satisfied, $registry->evaluate($requirement)->outcome);
    }

    public function testDuplicateKindRegistrationFailsClosed(): void
    {
        $probe = new class implements PreconditionProbeInterface {
            public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
            {
                return new PreconditionEvaluation($requirement->id, PreconditionOutcome::Satisfied);
            }
        };
        $registry = new PreconditionProbeRegistry();
        $registry->register(PreconditionKind::TableExists, $probe);

        $this->expectException(InvalidArgumentException::class);
        $registry->register(PreconditionKind::TableExists, $probe);
    }

    public function testMissingProbeFailsClosed(): void
    {
        $this->expectException(RuntimeException::class);
        (new PreconditionProbeRegistry())->evaluate(
            new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'table.orders'),
        );
    }

    public function testMismatchedProbeEvidenceFailsClosed(): void
    {
        $registry = new PreconditionProbeRegistry();
        $registry->register(PreconditionKind::TableExists, new class implements PreconditionProbeInterface {
            public function evaluate(PreconditionRequirement $requirement): PreconditionEvaluation
            {
                return new PreconditionEvaluation('different_requirement', PreconditionOutcome::Satisfied);
            }
        });

        $this->expectException(RuntimeException::class);
        $registry->evaluate(
            new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'table.orders'),
        );
    }
}
