<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\AdminColumns\AdminColumnsPersonalPreferenceResolver;
use WPEssential\Modules\AdminColumns\AdminColumnsPersonalPreferenceStore;

final class AdminColumnsPersonalPreferenceStoreTest extends TestCase
{
    private const VIEW_ID = '01990f6e-1f30-4000-8000-000000000399';

    public function testSavesLoadsAndResetsOneUsersViewPreference(): void
    {
        $meta = [];
        $store = $this->store($meta);
        $columns = $this->columns();
        $preference = [
            'chosen_view_id' => self::VIEW_ID,
            'temporary_sort' => [
                ['column_key' => 'title', 'direction' => 'desc'],
            ],
            'temporary_filters' => [
                ['column_key' => 'title', 'operator' => 'contains', 'value' => 'release'],
            ],
            'hidden_columns' => ['status'],
            'density' => 'compact',
            'saved_filter_state' => [
                'key' => 'my_filter',
                'filters' => [
                    ['column_key' => 'status', 'operator' => 'eq', 'value' => 'publish'],
                ],
                'sort' => [],
            ],
        ];

        $saved = $store->save(42, self::VIEW_ID, 3, $columns, $preference);
        self::assertTrue($saved['applied']);
        self::assertSame('applied', $saved['reason']);
        self::assertSame(3, $saved['stored_view_revision']);
        self::assertSame(['status'], $saved['state']['hidden_columns']);
        self::assertSame('compact', $saved['state']['density']);
        self::assertCount(1, $meta[42]);

        $loaded = $store->load(42, self::VIEW_ID, 3, $columns);
        self::assertSame($saved, $loaded);

        $store->reset(42, self::VIEW_ID);
        $afterReset = $store->load(42, self::VIEW_ID, 3, $columns);
        self::assertFalse($afterReset['applied']);
        self::assertSame('not_found', $afterReset['reason']);
        self::assertNull($afterReset['stored_view_revision']);
        self::assertSame([], $afterReset['state']['hidden_columns']);
        self::assertSame('comfortable', $afterReset['state']['density']);
    }

    public function testRevisionChangeDoesNotApplyStalePersonalState(): void
    {
        $meta = [];
        $store = $this->store($meta);
        $columns = $this->columns();

        $store->save(7, self::VIEW_ID, 1, $columns, [
            'hidden_columns' => ['status'],
            'density' => 'compact',
        ]);

        $loaded = $store->load(7, self::VIEW_ID, 2, $columns);
        self::assertFalse($loaded['applied']);
        self::assertSame('view_revision_changed', $loaded['reason']);
        self::assertSame(1, $loaded['stored_view_revision']);
        self::assertSame([], $loaded['state']['hidden_columns']);
        self::assertSame('comfortable', $loaded['state']['density']);
        self::assertSame(2, $loaded['state']['view_revision']);
    }

    public function testCorruptStoredEnvelopeFailsClosed(): void
    {
        $reader = static fn (int $userId, string $key): array => [
            'contract_version' => 1,
            'view_id' => self::VIEW_ID,
            'view_revision' => 1,
            'preference' => [],
            'secret' => 'must-not-pass',
        ];
        $store = new AdminColumnsPersonalPreferenceStore(
            new AdminColumnsPersonalPreferenceResolver(),
            $reader,
            static function (int $userId, string $key, array $value): void {},
            static function (int $userId, string $key): void {},
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('unsupported keys');
        $store->load(5, self::VIEW_ID, 1, $this->columns());
    }

    public function testSaveRequiresVerifiedReadBack(): void
    {
        $store = new AdminColumnsPersonalPreferenceStore(
            new AdminColumnsPersonalPreferenceResolver(),
            static fn (int $userId, string $key): string => '',
            static function (int $userId, string $key, array $value): void {},
            static function (int $userId, string $key): void {},
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('write-back verification failed');
        $store->save(5, self::VIEW_ID, 1, $this->columns(), []);
    }

    public function testRejectsInvalidUserIdentity(): void
    {
        $meta = [];
        $store = $this->store($meta);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('user id must be positive');
        $store->load(0, self::VIEW_ID, 1, $this->columns());
    }

    /**
     * @param array<int,array<string,mixed>> $meta
     */
    private function store(array &$meta): AdminColumnsPersonalPreferenceStore
    {
        return new AdminColumnsPersonalPreferenceStore(
            new AdminColumnsPersonalPreferenceResolver(),
            static fn (int $userId, string $key): mixed => $meta[$userId][$key] ?? '',
            static function (int $userId, string $key, array $value) use (&$meta): void {
                $meta[$userId][$key] = $value;
            },
            static function (int $userId, string $key) use (&$meta): void {
                unset($meta[$userId][$key]);
            },
        );
    }

    /** @return list<array{key:string,enabled:bool}> */
    private function columns(): array
    {
        return [
            ['key' => 'title', 'enabled' => true],
            ['key' => 'status', 'enabled' => true],
        ];
    }
}
