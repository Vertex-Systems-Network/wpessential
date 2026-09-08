<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutor;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Modules\Status\Transition\StatusTransitionRule;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

final class StatusTransitionExecutorTest extends TestCase
{
    private const POST_ID = 91;

    public function testExecutesAuthorizedTransitionAndVerifiesServerState(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->statusDefinition('review-ready', ['post']));
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];

        $executor = $this->executor(
            repository: $repository,
            policy: new StatusTransitionPolicy([
                new StatusTransitionRule('draft', 'review-ready', capability: 'edit_posts', reasonRequired: true),
            ]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
        );

        $result = $executor->execute(
            $this->context(),
            self::POST_ID,
            'draft',
            'review-ready',
            '  Ready for review.  ',
        );

        self::assertSame('review-ready', $state);
        self::assertSame([['edit_post', self::POST_ID]], $metaCaps);
        self::assertSame(['edit_posts'], $edgeCaps);
        self::assertSame([[self::POST_ID, 'review-ready']], $mutations);
        self::assertSame('draft', $result->fromStatus);
        self::assertSame('review-ready', $result->toStatus);
        self::assertSame('post', $result->postType);
        self::assertSame('Ready for review.', $result->reason);
        self::assertFalse($result->programmatic);
    }

    public function testStaleExpectedStatusFailsBeforeAuthorizationOrMutation(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'publish')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'pending', 'publish');
            self::fail('Stale expected status must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([], $metaCaps);
            self::assertSame([], $edgeCaps);
            self::assertSame([], $mutations);
            self::assertSame('draft', $state);
        }
    }

    public function testUndeclaredAndSameStatusTransitionsFailClosed(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'publish')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'private');
            self::fail('Undeclared edge must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([], $mutations);
        }

        $this->expectException(RuntimeException::class);
        $executor->execute($this->context(), self::POST_ID, 'draft', 'draft');
    }

    public function testReasonAndProgrammaticDeclarationsAreEnforcedBeforeMutation(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([
                new StatusTransitionRule('draft', 'publish', reasonRequired: true, programmaticAllowed: false),
            ]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Required reason must be enforced.');
        } catch (RuntimeException) {
            self::assertSame([], $mutations);
        }

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish', 'approved', true);
            self::fail('Programmatic transition must require explicit edge permission.');
        } catch (RuntimeException) {
            self::assertSame([], $mutations);
        }
    }

    public function testObjectEditAuthorizationDenialPreventsMutation(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'publish')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
            allowObjectEdit: false,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Object edit denial must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([['edit_post', self::POST_ID]], $metaCaps);
            self::assertSame([], $mutations);
        }
    }

    public function testAdditionalEdgeCapabilityDenialPreventsMutation(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([
                new StatusTransitionRule('draft', 'publish', capability: 'publish_posts'),
            ]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
            allowEdgeCapability: false,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Additional edge capability denial must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([['edit_post', self::POST_ID]], $metaCaps);
            self::assertSame(['publish_posts'], $edgeCaps);
            self::assertSame([], $mutations);
        }
    }

    public function testWpeStatusPostTypeApplicabilityIsEnforced(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->statusDefinition('review-ready', ['page']));
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: $repository,
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'review-ready')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'review-ready');
            self::fail('Inapplicable WPE Status target must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([], $metaCaps);
            self::assertSame([], $mutations);
        }
    }

    public function testUnregisteredStatusAndReservedCoreLifecycleAreRejected(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'publish')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
            registeredStatuses: ['draft'],
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Unregistered target status must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([], $mutations);
        }

        $futureState = 'future';
        $futureMetaCaps = [];
        $futureEdgeCaps = [];
        $futureMutations = [];
        $futureExecutor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([]),
            state: $futureState,
            metaCaps: $futureMetaCaps,
            edgeCaps: $futureEdgeCaps,
            mutations: $futureMutations,
        );

        $this->expectException(RuntimeException::class);
        $futureExecutor->execute($this->context(), self::POST_ID, 'future', 'publish');
    }

    public function testInvalidMutationResultFailsClosed(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'publish')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
            invalidMutationResult: true,
        );

        $this->expectException(RuntimeException::class);
        $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
    }

    public function testPostWriteVerificationFailureIsReported(): void
    {
        $state = 'draft';
        $metaCaps = [];
        $edgeCaps = [];
        $mutations = [];
        $executor = $this->executor(
            repository: new InMemoryDefinitionRepository(),
            policy: new StatusTransitionPolicy([new StatusTransitionRule('draft', 'publish')]),
            state: $state,
            metaCaps: $metaCaps,
            edgeCaps: $edgeCaps,
            mutations: $mutations,
            preserveStateAfterMutation: true,
        );

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Unverified mutation must fail closed.');
        } catch (RuntimeException) {
            self::assertSame([[self::POST_ID, 'publish']], $mutations);
            self::assertSame('draft', $state);
        }
    }

    /**
     * @param list<array{string,int}> $metaCaps
     * @param list<string> $edgeCaps
     * @param list<array{int,string}> $mutations
     * @param list<string> $registeredStatuses
     */
    private function executor(
        InMemoryDefinitionRepository $repository,
        StatusTransitionPolicy $policy,
        string &$state,
        array &$metaCaps,
        array &$edgeCaps,
        array &$mutations,
        bool $allowObjectEdit = true,
        bool $allowEdgeCapability = true,
        array $registeredStatuses = ['draft', 'publish', 'private', 'review-ready'],
        bool $invalidMutationResult = false,
        bool $preserveStateAfterMutation = false,
    ): StatusTransitionExecutor {
        $resources = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentNetworkId: static fn (): ?int => 2,
            currentUserCan: static function (string $capability, int $postId) use (&$metaCaps, $allowObjectEdit): bool {
                $metaCaps[] = [$capability, $postId];
                return $allowObjectEdit;
            },
        );
        $capabilities = new StatusExecutionCapabilityChecker($edgeCaps, $allowEdgeCapability);

        return new StatusTransitionExecutor(
            definitions: $repository,
            policy: $policy,
            resources: $resources,
            capabilities: $capabilities,
            readPostStatus: static function (int $postId) use (&$state): string {
                return $state;
            },
            readPostType: static fn (int $postId): string => 'post',
            statusRegistered: static fn (string $status): bool => in_array($status, $registeredStatuses, true),
            updatePostStatus: static function (int $postId, string $targetStatus) use (&$state, &$mutations, $invalidMutationResult, $preserveStateAfterMutation): mixed {
                $mutations[] = [$postId, $targetStatus];
                if ($invalidMutationResult) {
                    return false;
                }
                if (!$preserveStateAfterMutation) {
                    $state = $targetStatus;
                }
                return $postId;
            },
        );
    }

    /** @param list<string> $postTypes */
    private function statusDefinition(string $key, array $postTypes): Definition
    {
        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: $key,
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: DefinitionStatus::Published,
            payload: [
                'key' => $key,
                'labels' => [
                    'label' => 'Review Ready',
                    'count_singular' => 'Review Ready',
                    'count_plural' => 'Review Ready',
                ],
                'post_types' => $postTypes,
            ],
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3, networkId: 2);
    }
}

final class StatusExecutionCapabilityChecker implements CapabilityCheckerInterface
{
    /** @var list<string> */
    private array $calls;

    /** @param list<string> $calls */
    public function __construct(array &$calls, private readonly bool $allowed)
    {
        $this->calls =& $calls;
    }

    public function can(ExecutionContext $context, string $capability): bool
    {
        $this->calls[] = $capability;
        return $this->allowed;
    }
}
