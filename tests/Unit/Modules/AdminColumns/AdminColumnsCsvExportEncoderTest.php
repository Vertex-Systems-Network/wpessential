<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use LengthException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportEncoder;

final class AdminColumnsCsvExportEncoderTest extends TestCase
{
    public function testEncodesDeterministicRfc4180CsvInDeclaredColumnOrder(): void
    {
        $encoder = new AdminColumnsCsvExportEncoder();

        $csv = $encoder->encode(
            [
                ['key' => 'title', 'label' => 'Title'],
                ['key' => 'notes', 'label' => 'Notes'],
            ],
            [
                ['notes' => "line 1\nline 2", 'title' => 'Hello, "world"'],
            ],
        );

        self::assertSame(
            "\"Title\",\"Notes\"\r\n\"Hello, \"\"world\"\"\",\"line 1\nline 2\"\r\n",
            $csv,
        );
    }

    public function testNeutralizesSpreadsheetFormulaMarkersAfterLeadingHorizontalWhitespace(): void
    {
        $encoder = new AdminColumnsCsvExportEncoder();
        $columns = [['key' => 'value', 'label' => 'Value']];

        $csv = $encoder->encode($columns, [
            ['value' => '=2+2'],
            ['value' => ' +SUM(A1:A2)'],
            ['value' => "\t-cmd|'/C calc'!A0"],
            ['value' => '@SUM(A1:A2)'],
            ['value' => 'safe'],
        ]);

        self::assertSame(
            "\"Value\"\r\n"
            . "\"'=2+2\"\r\n"
            . "\"' +SUM(A1:A2)\"\r\n"
            . "\"'\t-cmd|'/C calc'!A0\"\r\n"
            . "\"'@SUM(A1:A2)\"\r\n"
            . "\"safe\"\r\n",
            $csv,
        );
    }

    public function testAcceptsBoundedScalarAndNullValues(): void
    {
        $encoder = new AdminColumnsCsvExportEncoder();

        $csv = $encoder->encode(
            [
                ['key' => 'null', 'label' => 'Null'],
                ['key' => 'bool', 'label' => 'Bool'],
                ['key' => 'int', 'label' => 'Int'],
                ['key' => 'float', 'label' => 'Float'],
            ],
            [[
                'null' => null,
                'bool' => true,
                'int' => 42,
                'float' => 1.5,
            ]],
        );

        self::assertSame("\"Null\",\"Bool\",\"Int\",\"Float\"\r\n\"\",\"true\",\"42\",\"1.5\"\r\n", $csv);
    }

    public function testRejectsNonScalarCells(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('only scalar or null');

        (new AdminColumnsCsvExportEncoder())->encode(
            [['key' => 'value', 'label' => 'Value']],
            [['value' => ['nested']]],
        );
    }

    public function testRejectsRowsWhoseKeysDoNotExactlyMatchColumns(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('exactly match');

        (new AdminColumnsCsvExportEncoder())->encode(
            [['key' => 'value', 'label' => 'Value']],
            [['other' => 'x']],
        );
    }

    public function testRejectsDuplicateOrMalformedColumnDescriptors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unique');

        (new AdminColumnsCsvExportEncoder())->encode(
            [
                ['key' => 'value', 'label' => 'Value'],
                ['key' => 'value', 'label' => 'Again'],
            ],
            [],
        );
    }

    public function testEnforcesColumnRowCellAndOutputBudgets(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('cell budget');

        (new AdminColumnsCsvExportEncoder(maxColumns: 1, maxRows: 1, maxCellBytes: 3, maxOutputBytes: 64))->encode(
            [['key' => 'v', 'label' => 'V']],
            [['v' => 'four']],
        );
    }

    public function testRejectsNonFiniteFloats(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('non-finite');

        (new AdminColumnsCsvExportEncoder())->encode(
            [['key' => 'value', 'label' => 'Value']],
            [['value' => INF]],
        );
    }
}
