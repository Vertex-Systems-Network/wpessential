<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPostMetaBinder;
use WPEssential\Modules\Fields\FieldGroupPostTypeTargetCompiler;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class WordPressPostMetaRegistrarBatchScaleTest extends TestCase
{
    public function testLargeBindingPlanSnapshotsOwnershipAndSupportsPerUniqueScope(): void
    {
        $registrationCalls = 0;
        $registeredMetaCalls = [];
        $supportCalls = [];

        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$registrationCalls): bool {
                ++$registrationCalls;
                return true;
            },
            static function (string $postType, string $feature) use (&$supportCalls): bool {
                $key = $postType . ':' . $feature;
                $supportCalls[$key] = ($supportCalls[$key] ?? 0) + 1;
                return true;
            },
            static function (string $objectType, string $objectSubtype) use (&$registeredMetaCalls): array {
                self::assertSame('post', $objectType);
                $registeredMetaCalls[$objectSubtype] = ($registeredMetaCalls[$objectSubtype] ?? 0) + 1;
                return [];
            },
        );
        $binder = new FieldGroupPostMetaBinder(
            new FieldGroupDefinitionNormalizer(),
            new FieldGroupRuntimeStorageProjection(),
            new FieldGroupPostTypeTargetCompiler(),
            new PostMetaRegistrationCompiler(),
            $registrar,
        );

        $binder->bind($this->largeDefinition(fieldCount: 64, postTypes: ['book', 'event', 'page', 'post']));

        self::assertSame(256, $registrationCalls, 'binding must emit one registration per field/post-type tuple');
        ksort($registeredMetaCalls, SORT_STRING);
        self::assertSame([
            '' => 1,
            'book' => 1,
            'event' => 1,
            'page' => 1,
            'post' => 1,
        ], $registeredMetaCalls, 'registered-meta ownership maps must be snapshotted per unique scope, not per registration');

        ksort($supportCalls, SORT_STRING);
        self::assertCount(8, $supportCalls);
        foreach ($supportCalls as $calls) {
            self::assertSame(1, $calls, 'post-type feature checks must be cached per unique post-type/feature pair');
        }
    }

    public function testDuplicateBatchTupleFailsBeforeSnapshotsOrMutation(): void
    {
        $registrationCalls = 0;
        $registeredMetaCalls = 0;
        $field = (new FieldDefinitionNormalizer())->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ]);
        $registration = (new PostMetaRegistrationCompiler())->compile($field, 'book');
        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$registrationCalls): bool {
                ++$registrationCalls;
                return true;
            },
            static fn (string $postType, string $feature): bool => true,
            static function (string $objectType, string $objectSubtype) use (&$registeredMetaCalls): array {
                ++$registeredMetaCalls;
                return [];
            },
        );

        try {
            $registrar->registerBatch([$registration, $registration]);
            self::fail('Duplicate subtype/meta-key tuples must fail before shared snapshot preflight.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('contains duplicate key "headline"', $exception->getMessage());
        }

        self::assertSame(0, $registeredMetaCalls);
        self::assertSame(0, $registrationCalls);
    }

    /** @param list<string> $postTypes */
    private function largeDefinition(int $fieldCount, array $postTypes): Definition
    {
        $fields = [];
        for ($index = 1; $index <= $fieldCount; ++$index) {
            $fields[] = [
                'uuid' => $this->uuid($index),
                'key' => sprintf('field_%03d', $index),
                'label' => sprintf('Field %03d', $index),
                'type' => 'text',
                'show_in_rest' => true,
            ];
        }

        return new Definition(
            id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            slug: 'field-group-scale-fixture',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: [
                'group_key' => 'scale_fixture',
                'title' => 'Scale Fixture',
                'fields' => $fields,
                'locations' => [[
                    ['source' => 'post_type', 'operator' => 'in', 'value' => $postTypes],
                ]],
                'storage' => ['mode' => 'native_post_meta'],
                'show_in_rest' => true,
                'revision_policy' => 'enabled',
            ],
            revision: 1,
        );
    }

    private function uuid(int $index): string
    {
        return sprintf('%08x-0000-4000-8000-%012x', $index, $index);
    }
}
