<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsCsvExportScopePolicy;

final class AdminColumnsCsvExportScopePolicyTest extends TestCase
{
    public function testNormalizesSelectedRowsAndPreservesCallerOrder(): void
    {
        $result = (new AdminColumnsCsvExportScopePolicy())->normalize(
            [
                'scope' => 'selected_rows',
                'selected_row_ids' => [19, 7, 31],
                'selected_columns' => ['title', 'id'],
                'respect_filters' => true,
                'respect_sort' => false,
            ],
            ['id', 'title', 'status'],
            ['current_page', 'selected_rows'],
        );

        self::assertSame(AdminColumnsCsvExportScopePolicy::CONTRACT_VERSION, $result['contract_version']);
        self::assertSame('selected_rows', $result['scope']);
        self::assertSame([19, 7, 31], $result['selected_row_ids']);
        self::assertSame(['title', 'id'], $result['selected_columns']);
        self::assertTrue($result['respect_filters']);
        self::assertFalse($result['respect_sort']);
    }

    public function testCurrentPageCannotSmuggleSelectedRowIds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('row IDs are only valid');

        (new AdminColumnsCsvExportScopePolicy())->normalize(
            [
                'scope' => 'current_page',
                'selected_row_ids' => [7],
                'selected_columns' => ['id'],
                'respect_filters' => true,
                'respect_sort' => true,
            ],
            ['id'],
            ['current_page'],
        );
    }

    public function testSelectedRowsRequiresAtLeastOneUniquePositiveId(): void
    {
        $policy = new AdminColumnsCsvExportScopePolicy();

        try {
            $policy->normalize(
                [
                    'scope' => 'selected_rows',
                    'selected_row_ids' => [],
                    'selected_columns' => ['id'],
                    'respect_filters' => true,
                    'respect_sort' => true,
                ],
                ['id'],
                ['selected_rows'],
            );
            self::fail('selected_rows without IDs must fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('requires at least one row ID', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unique values');
        $policy->normalize(
            [
                'scope' => 'selected_rows',
                'selected_row_ids' => [7, 7],
                'selected_columns' => ['id'],
                'respect_filters' => true,
                'respect_sort' => true,
            ],
            ['id'],
            ['selected_rows'],
        );
    }

    public function testSelectedColumnsMustBeEnabledCanonicalSubset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not an enabled canonical Column');

        (new AdminColumnsCsvExportScopePolicy())->normalize(
            [
                'scope' => 'all_matching',
                'selected_row_ids' => [],
                'selected_columns' => ['id', 'private_secret'],
                'respect_filters' => true,
                'respect_sort' => true,
            ],
            ['id', 'title'],
            ['all_matching'],
        );
    }

    public function testRequestedScopeMustBeAdvertisedByTarget(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not advertised');

        (new AdminColumnsCsvExportScopePolicy())->normalize(
            [
                'scope' => 'all_matching',
                'selected_row_ids' => [],
                'selected_columns' => ['id'],
                'respect_filters' => false,
                'respect_sort' => false,
            ],
            ['id'],
            ['current_page'],
        );
    }

    public function testDuplicateColumnOrScopeEvidenceFailsClosed(): void
    {
        $policy = new AdminColumnsCsvExportScopePolicy();

        try {
            $policy->normalize(
                [
                    'scope' => 'current_page',
                    'selected_row_ids' => [],
                    'selected_columns' => ['id'],
                    'respect_filters' => true,
                    'respect_sort' => true,
                ],
                ['id', 'id'],
                ['current_page'],
            );
            self::fail('Duplicate canonical Column keys must fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('unique values', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('scopes must be unique');
        $policy->normalize(
            [
                'scope' => 'current_page',
                'selected_row_ids' => [],
                'selected_columns' => ['id'],
                'respect_filters' => true,
                'respect_sort' => true,
            ],
            ['id'],
            ['current_page', 'current_page'],
        );
    }

    public function testFilterAndSortBehaviorMustBeExplicitBooleans(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('explicit booleans');

        (new AdminColumnsCsvExportScopePolicy())->normalize(
            [
                'scope' => 'current_page',
                'selected_row_ids' => [],
                'selected_columns' => ['id'],
                'respect_filters' => 'yes',
                'respect_sort' => true,
            ],
            ['id'],
            ['current_page'],
        );
    }
}
