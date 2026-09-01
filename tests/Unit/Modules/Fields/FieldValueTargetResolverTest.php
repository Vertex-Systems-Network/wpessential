<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldValueTargetResolverTest extends TestCase
{
    private const GROUP_ID = '11111111-1111-4111-8111-111111111111';
    private const FIELD_ID = '22222222-2222-4222-8222-222222222222';

    public function testResolvesPublishedTopLevelFieldForMatchingPostTarget(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Published, [
            [
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
                ['source' => 'post_status', 'operator' => 'in', 'value' => ['publish', 'draft']],
            ],
        ]));
        $resolver = $this->resolver($repository, 'book', 'publish');

        $target = $resolver->resolve(self::GROUP_ID, self::FIELD_ID, 41);

        self::assertSame(self::GROUP_ID, $target->groupId);
        self::assertSame(3, $target->groupRevision);
        self::assertSame(self::FIELD_ID, $target->fieldUuid);
        self::assertSame('headline', $target->fieldKey);
        self::assertSame(41, $target->postId);
        self::assertSame('book', $target->postType);
    }

    public function testRejectsDraftGroupAndLocationMismatch(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Draft, [[
            ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
        ]]));
        $resolver = $this->resolver($repository, 'book', 'publish');

        try {
            $resolver->resolve(self::GROUP_ID, self::FIELD_ID, 41);
            self::fail('Draft group must not resolve for value access.');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }

        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Published, [[
            ['source' => 'post_type', 'operator' => 'equals', 'value' => 'page'],
        ]]));
        $resolver = $this->resolver($repository, 'book', 'publish');

        $this->expectException(RuntimeException::class);
        $resolver->resolve(self::GROUP_ID, self::FIELD_ID, 41);
    }

    public function testEntityIdAndNegationAreEvaluatedCanonically(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Published, [[
            ['source' => 'entity_id', 'operator' => 'equals', 'value' => '41'],
            ['source' => 'post_status', 'operator' => 'equals', 'value' => 'trash', 'negate' => true],
        ]]));

        $target = $this->resolver($repository, 'book', 'publish')->resolve(self::GROUP_ID, self::FIELD_ID, 41);
        self::assertSame(41, $target->postId);
    }

    public function testUnsupportedLocationSourceFailsClosedEvenWithAlternativePostRule(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Published, [
            [['source' => 'post_type', 'operator' => 'equals', 'value' => 'book']],
            [['source' => 'page_template', 'operator' => 'equals', 'value' => 'special.php']],
        ]));

        $this->expectException(LogicException::class);
        $this->resolver($repository, 'book', 'publish')->resolve(self::GROUP_ID, self::FIELD_ID, 41);
    }

    public function testNestedFieldUuidIsNotExposedAsIndependentPostMetaTarget(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $normalizer = new FieldGroupDefinitionNormalizer();
        $payload = $normalizer->normalize([
            'group_key' => 'book_meta',
            'title' => 'Book Meta',
            'fields' => [[
                'uuid' => self::FIELD_ID,
                'key' => 'container',
                'label' => 'Container',
                'type' => 'group',
                'subfields' => [[
                    'uuid' => '33333333-3333-4333-8333-333333333333',
                    'key' => 'nested',
                    'label' => 'Nested',
                    'type' => 'text',
                ]],
            ]],
            'locations' => [[['source' => 'post_type', 'operator' => 'equals', 'value' => 'book']]],
        ], true);
        $repository->save(new Definition(
            id: self::GROUP_ID,
            slug: 'field-group-book-meta',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: 3,
        ));

        $this->expectException(RuntimeException::class);
        $this->resolver($repository, 'book', 'publish')->resolve(
            self::GROUP_ID,
            '33333333-3333-4333-8333-333333333333',
            41,
        );
    }

    /** @param list<list<array<string,mixed>>> $locations */
    private function definition(DefinitionStatus $status, array $locations): Definition
    {
        $payload = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'book_meta',
            'title' => 'Book Meta',
            'fields' => [[
                'uuid' => self::FIELD_ID,
                'key' => 'headline',
                'label' => 'Headline',
                'type' => 'text',
            ]],
            'locations' => $locations,
        ], $status === DefinitionStatus::Published);

        return new Definition(
            id: self::GROUP_ID,
            slug: 'field-group-book-meta',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
            revision: 3,
        );
    }

    private function resolver(
        InMemoryDefinitionRepository $repository,
        string|false $postType,
        string|false $postStatus,
    ): FieldValueTargetResolver {
        return new FieldValueTargetResolver(
            $repository,
            new FieldGroupDefinitionNormalizer(),
            getPostType: static fn (int $postId): string|false => $postType,
            getPostStatus: static fn (int $postId): string|false => $postStatus,
        );
    }
}
