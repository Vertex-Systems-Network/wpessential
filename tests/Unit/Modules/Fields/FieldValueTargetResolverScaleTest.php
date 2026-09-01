<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class FieldValueTargetResolverScaleTest extends TestCase
{
    public function testLargePublishedGroupUsesOneDefinitionLookupAndFixedPostTargetLookups(): void
    {
        $fields = [];
        for ($index = 1; $index <= 512; ++$index) {
            $fields[] = [
                'uuid' => $this->uuid($index),
                'key' => sprintf('field_%03d', $index),
                'label' => sprintf('Field %03d', $index),
                'type' => 'text',
            ];
        }
        $normalizer = new FieldGroupDefinitionNormalizer();
        $payload = $normalizer->normalize([
            'group_key' => 'resolver_scale',
            'title' => 'Resolver Scale',
            'fields' => $fields,
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'book'],
                ['source' => 'post_status', 'operator' => 'in', 'value' => ['publish', 'draft']],
            ]],
        ], true);
        $definition = new Definition(
            id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            slug: 'field-group-resolver-scale',
            type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
            revision: 7,
        );

        $repository = new class($definition) implements DefinitionRepositoryInterface {
            public int $getCalls = 0;
            public int $byTypeCalls = 0;
            public int $dependentsCalls = 0;

            public function __construct(private Definition $definition) {}

            public function save(Definition $definition): void
            {
                $this->definition = $definition;
            }

            public function get(string $id): ?Definition
            {
                ++$this->getCalls;
                return $id === $this->definition->id ? $this->definition : null;
            }

            public function byType(string $type): array
            {
                ++$this->byTypeCalls;
                return $type === $this->definition->type ? [$this->definition] : [];
            }

            public function dependentsOf(string $id): array
            {
                ++$this->dependentsCalls;
                return [];
            }
        };
        $postTypeCalls = 0;
        $postStatusCalls = 0;
        $resolver = new FieldValueTargetResolver(
            $repository,
            $normalizer,
            getPostType: static function (int $postId) use (&$postTypeCalls): string|false {
                ++$postTypeCalls;
                return 'book';
            },
            getPostStatus: static function (int $postId) use (&$postStatusCalls): string|false {
                ++$postStatusCalls;
                return 'publish';
            },
        );

        $target = $resolver->resolve(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            $this->uuid(512),
            41,
        );

        self::assertSame('field_512', $target->fieldKey);
        self::assertSame(7, $target->groupRevision);
        self::assertSame(1, $repository->getCalls, 'resolver must address one selected Definition directly');
        self::assertSame(0, $repository->byTypeCalls, 'resolver must not scan all Field Group definitions');
        self::assertSame(0, $repository->dependentsCalls);
        self::assertSame(1, $postTypeCalls);
        self::assertSame(1, $postStatusCalls);
    }

    private function uuid(int $index): string
    {
        return sprintf('%08x-0000-4000-8000-%012x', $index, $index);
    }
}
