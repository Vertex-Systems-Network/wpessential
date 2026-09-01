<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldIdentityAssigner;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldIdentityAssignerTest extends TestCase
{
    public function testSeedsAndPreservesNestedFieldUuids(): void
    {
        $assigner = new FieldIdentityAssigner();
        $payload = [
            'fields' => [
                [
                    'key' => 'contact',
                    'type' => 'group',
                    'subfields' => [
                        ['key' => 'email', 'type' => 'email'],
                    ],
                ],
            ],
        ];

        $first = $assigner->assign($payload);
        $topUuid = $first['fields'][0]['uuid'];
        $nestedUuid = $first['fields'][0]['subfields'][0]['uuid'];
        self::assertIsString($topUuid);
        self::assertIsString($nestedUuid);
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $topUuid);
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $nestedUuid);
        self::assertNotSame($topUuid, $nestedUuid);

        $second = $assigner->assign($payload, $first);
        self::assertSame($topUuid, $second['fields'][0]['uuid']);
        self::assertSame($nestedUuid, $second['fields'][0]['subfields'][0]['uuid']);
    }

    public function testRejectsReplacingExistingIdentityForSameMachineKey(): void
    {
        $existing = [
            'fields' => [[
                'uuid' => '11111111-1111-4111-8111-111111111111',
                'key' => 'headline',
                'type' => 'text',
            ]],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('identity cannot be replaced');
        (new FieldIdentityAssigner())->assign([
            'fields' => [[
                'uuid' => '22222222-2222-4222-8222-222222222222',
                'key' => 'headline',
                'type' => 'text',
            ]],
        ], $existing);
    }

    public function testRejectsUuidAlreadyOwnedByAnotherFieldGroup(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save(new Definition(
            id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            slug: 'field-group-existing',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'group_key' => 'existing',
                'fields' => [[
                    'uuid' => '33333333-3333-4333-8333-333333333333',
                    'key' => 'headline',
                    'type' => 'text',
                ]],
            ],
        ));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already owned by another canonical Field Group');
        (new FieldIdentityAssigner($repository))->assign([
            'fields' => [[
                'uuid' => '33333333-3333-4333-8333-333333333333',
                'key' => 'subtitle',
                'type' => 'text',
            ]],
        ]);
    }
}
