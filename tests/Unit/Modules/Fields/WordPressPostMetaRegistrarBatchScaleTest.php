<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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
    public function testLargeBindingPlanReducesFullPlanSnapshotsAndPreservesLiveRevalidation(): void
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
            '' => 257,
            'book' => 65,
            'event' => 65,
            'page' => 65,
            'post' => 65,
        ], $registeredMetaCalls, 'batch preflight must use one shared snapshot phase plus one live ownership recheck per tuple');
        self::assertSame(517, array_sum($registeredMetaCalls), 'ownership-map reads must be 2N + P + 1 instead of the prior 4N');

        ksort($supportCalls, SORT_STRING);
        self::assertCount(8, $supportCalls);
        foreach ($supportCalls as $calls) {
            self::assertSame(65, $calls, 'support checks must be one cached batch check plus one live safety recheck per tuple');
        }
        self::assertSame(520, array_sum($supportCalls), 'feature checks must be N-per-required-feature plus unique batch pairs instead of two full N passes');
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

    public function testLiveRevalidationRejectsForeignOwnershipIntroducedByEarlierRegistrationCallback(): void
    {
        $compiler = new PostMetaRegistrationCompiler();
        $normalizer = new FieldDefinitionNormalizer();
        $headline = $compiler->compile($normalizer->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ]), 'book');
        $rating = $compiler->compile($normalizer->normalize([
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'key' => 'rating',
            'label' => 'Rating',
            'type' => 'number',
        ]), 'book');
        $foreignRating = $rating['args'];
        $foreignRating['description'] = 'Foreign plugin registration.';
        $registry = ['' => [], 'book' => []];
        $registrationCalls = 0;

        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (
                &$registry,
                &$registrationCalls,
                $foreignRating,
            ): bool {
                ++$registrationCalls;
                $registry[$postType][$metaKey] = $args;
                if ($metaKey === 'headline') {
                    $registry[$postType]['rating'] = $foreignRating;
                }
                return true;
            },
            static fn (string $postType, string $feature): bool => true,
            static function (string $objectType, string $objectSubtype) use (&$registry): array {
                return $registry[$objectSubtype] ?? [];
            },
        );

        try {
            $registrar->registerBatch([$headline, $rating]);
            self::fail('A foreign owner introduced by an earlier registration callback must be detected before overwrite.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already owned by another registration', $exception->getMessage());
        }

        self::assertSame(1, $registrationCalls, 'the later foreign-owned tuple must not reach register_post_meta');
        self::assertSame('Foreign plugin registration.', $registry['book']['rating']['description']);
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
