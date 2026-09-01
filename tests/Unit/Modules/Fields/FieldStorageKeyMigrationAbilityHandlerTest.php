<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldStorageKeyMigrationAbilityHandler;
use WPEssential\Modules\Fields\FieldStorageKeyMigrationResult;
use WPEssential\Modules\Fields\FieldStorageKeyMigrationService;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class FieldStorageKeyMigrationAbilityHandlerTest extends TestCase
{
    public function testSerializesExplicitMigrationResult(): void
    {
        $definition = new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'catalog-fields',
            type: 'field_group',
            schemaVersion: 1,
            ownerSurfaceId: 3,
            status: DefinitionStatus::Published,
            payload: ['fields' => []],
            revision: 4,
        );
        $service = $this->createMock(FieldStorageKeyMigrationService::class);
        $service->expects(self::once())
            ->method('migrate')
            ->with(
                '11111111-1111-4111-8111-111111111111',
                3,
                '22222222-2222-4222-8222-222222222222',
                'title',
            )
            ->willReturn(new FieldStorageKeyMigrationResult(
                definition: $definition,
                fieldUuid: '22222222-2222-4222-8222-222222222222',
                sourceKey: 'headline',
                destinationKey: 'title',
                postTypes: ['book'],
                migratedObjects: 2,
                changed: true,
            ));

        $handler = new FieldStorageKeyMigrationAbilityHandler($service);
        $result = $handler->handle([
            'group_id' => '11111111-1111-4111-8111-111111111111',
            'expected_group_revision' => 3,
            'field_uuid' => '22222222-2222-4222-8222-222222222222',
            'destination_key' => 'title',
        ], new ExecutionContext(
            actorId: 1,
            channel: ExecutionChannel::Internal,
            networkId: 1,
            siteId: 1,
        ));

        self::assertSame('headline', $result['source_key']);
        self::assertSame('title', $result['destination_key']);
        self::assertSame(['book'], $result['post_types']);
        self::assertSame(2, $result['migrated_objects']);
        self::assertSame(4, $result['group_revision']);
        self::assertTrue($result['changed']);
    }
}
