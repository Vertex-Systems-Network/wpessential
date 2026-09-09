<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Precondition\Probe;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionKind;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionOutcome;
use WPEssential\Modules\CustomTables\Migration\Precondition\PreconditionRequirement;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionFacts;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\MetadataPreconditionProbe;

final class MetadataPreconditionProbeTest extends TestCase
{
    public function testTableExistenceUsesInjectedMetadataOnly(): void
    {
        $probe = new MetadataPreconditionProbe(new MetadataPreconditionFacts(true));

        self::assertSame(
            PreconditionOutcome::Satisfied,
            $probe->evaluate(new PreconditionRequirement('table_exists', PreconditionKind::TableExists, 'table.orders'))->outcome,
        );
        self::assertSame(
            PreconditionOutcome::Blocked,
            $probe->evaluate(new PreconditionRequirement('table_missing', PreconditionKind::TableMissing, 'table.orders'))->outcome,
        );
    }

    public function testColumnFingerprintAndProviderFeatureAreBoundedMetadataFacts(): void
    {
        $fingerprint = str_repeat('a', 64);
        $probe = new MetadataPreconditionProbe(new MetadataPreconditionFacts(
            tableExists: true,
            features: ['instant_add_column' => true],
            columnFingerprints: ['column.orders.status' => $fingerprint],
        ));

        $column = $probe->evaluate(new PreconditionRequirement(
            'column_match',
            PreconditionKind::ColumnMatchesFingerprint,
            'column.orders.status',
            ['fingerprint' => $fingerprint],
        ));
        $feature = $probe->evaluate(new PreconditionRequirement(
            'feature_available',
            PreconditionKind::DatabaseFeatureAvailable,
            'provider.mysql',
            ['feature' => 'instant_add_column'],
        ));

        self::assertSame(PreconditionOutcome::Satisfied, $column->outcome);
        self::assertSame(['fingerprint' => $fingerprint], $column->evidence);
        self::assertSame(PreconditionOutcome::Satisfied, $feature->outcome);
    }

    public function testRowScanningKindsRemainUnsupported(): void
    {
        $probe = new MetadataPreconditionProbe(new MetadataPreconditionFacts(true));

        self::assertSame(
            PreconditionOutcome::Unsupported,
            $probe->evaluate(new PreconditionRequirement('row_count', PreconditionKind::RowCountRange, 'table.orders'))->outcome,
        );
    }
}
