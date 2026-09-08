<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Status\Transition\Definition\PublishedStatusTransitionPolicyResolver;
use WPEssential\Modules\Status\Transition\Definition\StatusTransitionPolicyDefinitionCompiler;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class StatusTransitionPolicyDefinitionCompilerTest extends TestCase
{
    public function testCompilesPublishedPolicyAndProjectsRuleMetadata(): void
    {
        $policy = (new StatusTransitionPolicyDefinitionCompiler())->compile($this->definition(
            id: '11111111-1111-4111-8111-111111111111',
            edges: [[
                'from' => 'draft',
                'to' => 'review-ready',
                'capability' => 'edit_posts',
                'reason_required' => true,
                'bulk_allowed' => false,
                'programmatic_allowed' => true,
            ]],
        ));

        $rule = $policy->ruleFor('draft', 'review-ready');
        self::assertNotNull($rule);
        self::assertSame('edit_posts', $rule->capability);
        self::assertTrue($rule->reasonRequired);
        self::assertFalse($rule->bulkAllowed);
        self::assertTrue($rule->programmaticAllowed);
    }

    public function testRejectsNonPublishedWrongOwnerSchemaDependenciesAndUnknownKeys(): void
    {
        $compiler = new StatusTransitionPolicyDefinitionCompiler();

        foreach ([
            $this->definition('11111111-1111-4111-8111-111111111111', [], status: DefinitionStatus::Draft),
            $this->definition('22222222-2222-4222-8222-222222222222', [], owner: 6),
            $this->definition('33333333-3333-4333-8333-333333333333', [], schema: 2),
            $this->definition('44444444-4444-4444-8444-444444444444', [], dependencies: ['55555555-5555-4555-8555-555555555555']),
            $this->definition('66666666-6666-4666-8666-666666666666', [], extraPayload: ['provider' => 'private']),
        ] as $definition) {
            try {
                $compiler->compile($definition);
                self::fail('Invalid transition-policy Definition must fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsMalformedUnknownDuplicateSameStatusAndReservedEdges(): void
    {
        $compiler = new StatusTransitionPolicyDefinitionCompiler();

        $invalidEdgeSets = [
            [['from' => 'draft']],
            [['from' => 'draft', 'to' => 'review-ready', 'unknown' => true]],
            [
                ['from' => 'draft', 'to' => 'review-ready'],
                ['from' => 'draft', 'to' => 'review-ready'],
            ],
            [['from' => 'draft', 'to' => 'draft']],
            [['from' => 'draft', 'to' => 'trash']],
            [['from' => 'draft', 'to' => 'review-ready', 'reason_required' => 'yes']],
        ];

        foreach ($invalidEdgeSets as $index => $edges) {
            try {
                $compiler->compile($this->definition(
                    id: sprintf('77777777-7777-4777-8777-%012d', $index + 1),
                    edges: $edges,
                ));
                self::fail('Malformed transition edge must fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testResolverComposesPublishedDefinitionsDeterministically(): void
    {
        $firstRepository = new InMemoryDefinitionRepository();
        $firstRepository->save($this->definition(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            [['from' => 'review-ready', 'to' => 'publish', 'capability' => 'publish_posts']],
        ));
        $firstRepository->save($this->definition(
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            [['from' => 'draft', 'to' => 'review-ready', 'reason_required' => true]],
        ));

        $secondRepository = new InMemoryDefinitionRepository();
        $secondRepository->save($this->definition(
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            [['from' => 'draft', 'to' => 'review-ready', 'reason_required' => true]],
        ));
        $secondRepository->save($this->definition(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            [['from' => 'review-ready', 'to' => 'publish', 'capability' => 'publish_posts']],
        ));

        $first = (new PublishedStatusTransitionPolicyResolver($firstRepository))->resolve();
        $second = (new PublishedStatusTransitionPolicyResolver($secondRepository))->resolve();

        self::assertSame($first->compatibilityFingerprint, $second->compatibilityFingerprint);
        self::assertSame(
            ['draft->review-ready', 'review-ready->publish'],
            array_map(static fn ($rule): string => $rule->edgeKey(), $first->rules()),
        );
    }

    public function testResolverSkipsNonPublishedAndReturnsExplicitDenyAllWhenNonePublished(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-2222-4333-8444-555555555555',
            [['from' => 'draft', 'to' => 'review-ready']],
            status: DefinitionStatus::Draft,
        ));

        $policy = (new PublishedStatusTransitionPolicyResolver($repository))->resolve();

        self::assertSame([], $policy->rules());
        self::assertFalse($policy->allows('draft', 'review-ready'));
    }

    public function testResolverRejectsDuplicateEdgesAcrossPublishedDefinitions(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            [['from' => 'draft', 'to' => 'review-ready']],
        ));
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            [['from' => 'draft', 'to' => 'review-ready']],
        ));

        $this->expectException(RuntimeException::class);
        (new PublishedStatusTransitionPolicyResolver($repository))->resolve();
    }

    /**
     * @param list<array<string,mixed>> $edges
     * @param list<string> $dependencies
     * @param array<string,mixed> $extraPayload
     */
    private function definition(
        string $id,
        array $edges,
        DefinitionStatus $status = DefinitionStatus::Published,
        int $owner = 5,
        int $schema = 1,
        array $dependencies = [],
        array $extraPayload = [],
    ): Definition {
        return new Definition(
            id: $id,
            slug: 'policy-' . substr(str_replace('-', '', $id), 0, 8),
            type: 'status-transition-policy',
            schemaVersion: $schema,
            ownerSurfaceId: $owner,
            status: $status,
            payload: array_merge(['edges' => $edges], $extraPayload),
            revision: 1,
            dependencies: $dependencies,
        );
    }
}
