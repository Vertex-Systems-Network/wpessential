<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Run\Persistence;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;

final class MigrationRunRecordCodecTest extends TestCase
{
    public function testRoundTripPreservesCanonicalRunAndStateRevision(): void
    {
        $run = new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 3,
            targetSchemaVersion: 4,
            state: MigrationRunState::Revalidating,
            stateRevision: 7,
        );
        $codec = new MigrationRunRecordCodec();

        $record = $codec->encode($run);
        $decoded = $codec->decode($record);

        self::assertSame(MigrationRunRecordCodec::STORAGE_VERSION, $record['storage_version']);
        self::assertSame($run->canonical(), $decoded->canonical());
        self::assertSame($run->fingerprint(), $decoded->fingerprint());
        self::assertSame(7, $decoded->stateRevision);
    }

    public function testUnknownStorageVersionFailsClosed(): void
    {
        $record = $this->validRecord();
        $record['storage_version'] = 2;

        $this->expectException(InvalidArgumentException::class);
        (new MigrationRunRecordCodec())->decode($record);
    }

    public function testMissingOrAdditionalStorageKeysFailClosed(): void
    {
        $record = $this->validRecord();
        unset($record['state_revision']);

        try {
            (new MigrationRunRecordCodec())->decode($record);
            self::fail('Missing storage key should fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $record = $this->validRecord();
        $record['unexpected'] = 'value';

        $this->expectException(InvalidArgumentException::class);
        (new MigrationRunRecordCodec())->decode($record);
    }

    public function testWrongScalarTypesAndUnknownStateFailClosed(): void
    {
        $record = $this->validRecord();
        $record['state_revision'] = '1';

        try {
            (new MigrationRunRecordCodec())->decode($record);
            self::fail('String revision should fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $record = $this->validRecord();
        $record['state'] = 'future_state';

        $this->expectException(InvalidArgumentException::class);
        (new MigrationRunRecordCodec())->decode($record);
    }

    /** @return array<string,mixed> */
    private function validRecord(): array
    {
        return (new MigrationRunRecordCodec())->encode(new MigrationRun(
            id: '11111111-1111-4111-8111-111111111111',
            planFingerprint: str_repeat('a', 64),
            tableKey: 'orders',
            targetDefinitionId: '22222222-2222-4222-8222-222222222222',
            targetRevision: 1,
            targetSchemaVersion: 1,
            state: MigrationRunState::Planned,
        ));
    }
}
