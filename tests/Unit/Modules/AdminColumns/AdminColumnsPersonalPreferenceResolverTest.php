<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsPersonalPreferenceResolver;

final class AdminColumnsPersonalPreferenceResolverTest extends TestCase
{
    private const VIEW_ID = '01990f6e-1f30-4000-8000-000000000398';

    public function testResolvesBoundedPersonalStateWithoutChangingCanonicalColumns(): void
    {
        $columns = [
            ['key' => 'id', 'enabled' => true],
            ['key' => 'title', 'enabled' => true],
            ['key' => 'hidden_server', 'enabled' => false],
        ];
        $originalColumns = $columns;

        $result = (new AdminColumnsPersonalPreferenceResolver())->resolve(
            self::VIEW_ID,
            7,
            $columns,
            [
                'chosen_view_id' => self::VIEW_ID,
                'temporary_sort' => [
                    ['column_key' => 'title', 'direction' => 'asc'],
                ],
                'temporary_filters' => [
                    ['column_key' => 'title', 'operator' => 'contains', 'value' => 'Bounded'],
                ],
                'hidden_columns' => ['id'],
                'density' => 'compact',
                'saved_filter_state' => [
                    'key' => 'my_filter',
                    'filters' => [
                        ['column_key' => 'id', 'operator' => 'in', 'value' => [1, 2]],
                    ],
                    'sort' => [
                        ['column_key' => 'id', 'direction' => 'desc'],
                    ],
                ],
            ],
        );

        self::assertSame(AdminColumnsPersonalPreferenceResolver::CONTRACT_VERSION, $result['contract_version']);
        self::assertSame(self::VIEW_ID, $result['view_id']);
        self::assertSame(7, $result['view_revision']);
        self::assertSame('compact', $result['density']);
        self::assertSame(['id'], $result['hidden_columns']);
        self::assertSame('title', $result['temporary_sort'][0]['column_key']);
        self::assertSame('my_filter', $result['saved_filter_state']['key']);
        self::assertSame($originalColumns, $columns);
    }

    public function testDefaultsRemainPersonalAndDoNotInventState(): void
    {
        $result = (new AdminColumnsPersonalPreferenceResolver())->resolve(
            self::VIEW_ID,
            1,
            $this->columns(),
            [],
        );

        self::assertSame(self::VIEW_ID, $result['chosen_view_id']);
        self::assertSame([], $result['temporary_sort']);
        self::assertSame([], $result['temporary_filters']);
        self::assertSame([], $result['hidden_columns']);
        self::assertSame('comfortable', $result['density']);
        self::assertNull($result['saved_filter_state']);
    }

    public function testRejectsPreferenceForAnotherView(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not match');

        (new AdminColumnsPersonalPreferenceResolver())->resolve(
            self::VIEW_ID,
            1,
            $this->columns(),
            ['chosen_view_id' => '01990f6e-1f30-4000-8000-000000000399'],
        );
    }

    public function testRejectsUnknownOrDisabledColumnReferences(): void
    {
        $resolver = new AdminColumnsPersonalPreferenceResolver();

        try {
            $resolver->resolve(
                self::VIEW_ID,
                1,
                [
                    ['key' => 'title', 'enabled' => true],
                    ['key' => 'secret', 'enabled' => false],
                ],
                ['hidden_columns' => ['secret']],
            );
            self::fail('Disabled columns must not become valid personal-state references.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('unavailable column', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unavailable column');
        $resolver->resolve(
            self::VIEW_ID,
            1,
            $this->columns(),
            [
                'temporary_filters' => [
                    ['column_key' => 'missing', 'operator' => 'eq', 'value' => 'x'],
                ],
            ],
        );
    }

    public function testRejectsDuplicateHiddenAndSortReferences(): void
    {
        $resolver = new AdminColumnsPersonalPreferenceResolver();

        try {
            $resolver->resolve(
                self::VIEW_ID,
                1,
                $this->columns(),
                ['hidden_columns' => ['title', 'title']],
            );
            self::fail('Duplicate hidden columns must fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('unique', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('more than once');
        $resolver->resolve(
            self::VIEW_ID,
            1,
            $this->columns(),
            [
                'temporary_sort' => [
                    ['column_key' => 'title', 'direction' => 'asc'],
                    ['column_key' => 'title', 'direction' => 'desc'],
                ],
            ],
        );
    }

    public function testRejectsUnsupportedAndOverBudgetValues(): void
    {
        $resolver = new AdminColumnsPersonalPreferenceResolver();

        try {
            $resolver->resolve(
                self::VIEW_ID,
                1,
                $this->columns(),
                ['density' => 'ultra-compact'],
            );
            self::fail('Unsupported density must fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('density', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('over budget');
        $resolver->resolve(
            self::VIEW_ID,
            1,
            $this->columns(),
            [
                'temporary_filters' => [
                    [
                        'column_key' => 'title',
                        'operator' => 'in',
                        'value' => range(1, 21),
                    ],
                ],
            ],
        );
    }

    /** @return list<array{key:string,enabled:bool}> */
    private function columns(): array
    {
        return [
            ['key' => 'id', 'enabled' => true],
            ['key' => 'title', 'enabled' => true],
        ];
    }
}
