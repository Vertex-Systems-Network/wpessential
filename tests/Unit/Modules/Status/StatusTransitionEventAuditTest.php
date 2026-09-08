<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\AuditLoggerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\Status\Events\StatusTransitionEvidencePublisher;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutor;
use WPEssential\Modules\Status\Execution\StatusTransitionObservationException;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Modules\Status\Transition\StatusTransitionRule;
use WPEssential\Platform\Audit\AuditOutcome;
use WPEssential\Platform\Audit\AuditRecord;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Events\DomainEvent;
use WPEssential\Platform\Events\EventBus;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

final class StatusTransitionEventAuditTest extends TestCase
{
    private const POST_ID = 91;

    public function testVerifiedTransitionPublishesExactlyOneBoundedAuditAndEvent(): void
    {
        [$executor, $probe, $post] = $this->harness([
            'policy' => new StatusTransitionPolicy([
                new StatusTransitionRule('draft', 'publish', reasonRequired: true),
            ]),
        ]);
        $context = $this->context();

        $result = $executor->execute(
            $context,
            self::POST_ID,
            'draft',
            'publish',
            '  editorial approval  ',
        );

        self::assertSame('publish', $post->status);
        self::assertSame(1, $post->mutationCount);
        self::assertSame('editorial approval', $result->reason);
        self::assertCount(1, $probe->records);
        self::assertCount(1, $probe->events);

        $record = $probe->records[0];
        self::assertSame($context, $record->context);
        self::assertSame(5, $record->ownerSurfaceId);
        self::assertSame(StatusTransitionEvidencePublisher::AUDIT_ACTION, $record->action);
        self::assertSame(AuditOutcome::Success, $record->outcome);
        self::assertSame('post', $record->resourceType);
        self::assertSame(self::POST_ID, $record->resourceId);
        self::assertSame('editorial approval', $record->reason);
        self::assertSame([
            'post_type' => 'post',
            'from_status' => 'draft',
            'to_status' => 'publish',
            'programmatic' => false,
            'reason_provided' => true,
        ], $record->metadata);
        self::assertArrayNotHasKey('reason', $record->metadata);

        $event = $probe->events[0];
        self::assertSame(StatusTransitionEvidencePublisher::EVENT_NAME, $event->name);
        self::assertSame('transition-correlation', $event->correlationId);
        self::assertSame([
            'owner_surface_id' => 5,
            'post_id' => self::POST_ID,
            'post_type' => 'post',
            'from_status' => 'draft',
            'to_status' => 'publish',
            'programmatic' => false,
            'reason_provided' => true,
            'actor_type' => 'user',
            'actor_user_id' => 7,
            'site_id' => 3,
            'network_id' => 2,
            'channel' => 'rest',
        ], $event->payload);
        self::assertArrayNotHasKey('reason', $event->payload);
    }

    public function testAllBoundedFailureClassesProduceZeroEvidence(): void
    {
        $inapplicable = new InMemoryDefinitionRepository();
        $inapplicable->save($this->statusDefinition('review-ready', ['page']));

        $cases = [
            'authorization denial' => [
                'options' => ['allow_object_edit' => false],
                'expected' => 'draft',
                'target' => 'publish',
            ],
            'stale current status' => [
                'options' => [],
                'expected' => 'pending',
                'target' => 'publish',
            ],
            'same status' => [
                'options' => [],
                'expected' => 'draft',
                'target' => 'draft',
            ],
            'undeclared edge' => [
                'options' => [],
                'expected' => 'draft',
                'target' => 'private',
            ],
            'invalid reason' => [
                'options' => [
                    'policy' => new StatusTransitionPolicy([
                        new StatusTransitionRule('draft', 'publish', reasonRequired: true),
                    ]),
                ],
                'expected' => 'draft',
                'target' => 'publish',
            ],
            'capability denial' => [
                'options' => [
                    'allow_edge_capability' => false,
                    'policy' => new StatusTransitionPolicy([
                        new StatusTransitionRule('draft', 'publish', capability: 'publish_posts'),
                    ]),
                ],
                'expected' => 'draft',
                'target' => 'publish',
            ],
            'applicability failure' => [
                'options' => [
                    'definitions' => $inapplicable,
                    'policy' => new StatusTransitionPolicy([
                        new StatusTransitionRule('draft', 'review-ready'),
                    ]),
                    'registered_statuses' => ['draft', 'review-ready'],
                ],
                'expected' => 'draft',
                'target' => 'review-ready',
            ],
            'wordpress mutation failure' => [
                'options' => ['invalid_mutation_result' => true],
                'expected' => 'draft',
                'target' => 'publish',
            ],
            'post-write verification failure' => [
                'options' => ['preserve_state_after_mutation' => true],
                'expected' => 'draft',
                'target' => 'publish',
            ],
        ];

        foreach ($cases as $name => $case) {
            [$executor, $probe] = $this->harness($case['options']);
            try {
                $executor->execute(
                    $this->context(),
                    self::POST_ID,
                    $case['expected'],
                    $case['target'],
                );
                self::fail($name . ' must fail closed.');
            } catch (RuntimeException) {
                self::assertCount(0, $probe->records, $name . ' must not write Audit evidence.');
                self::assertCount(0, $probe->events, $name . ' must not emit Event evidence.');
            }
        }
    }

    public function testAuditFailureReportsCommittedMutationAndPreventsSilentRetry(): void
    {
        [$executor, $probe, $post] = $this->harness(['fail_audit' => true]);

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Audit failure after verification must surface committed observation semantics.');
        } catch (StatusTransitionObservationException $exception) {
            self::assertTrue($exception->mutationCommitted());
            self::assertFalse($exception->retryMutationAllowed());
            self::assertFalse($exception->auditRecorded);
            self::assertFalse($exception->eventDispatchCompleted);
            self::assertSame('publish', $exception->committedResult->toStatus);
            self::assertSame('publish', $post->status);
            self::assertSame(1, $post->mutationCount);
            self::assertCount(0, $probe->records);
            self::assertCount(0, $probe->events);
        }

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Retry with the original expected status must be rejected as stale.');
        } catch (RuntimeException $exception) {
            self::assertNotInstanceOf(StatusTransitionObservationException::class, $exception);
            self::assertSame(1, $post->mutationCount);
            self::assertCount(0, $probe->records);
            self::assertCount(0, $probe->events);
        }
    }

    public function testEventFailureOccursAfterDurableAuditAndCannotDoubleTransition(): void
    {
        [$executor, $probe, $post] = $this->harness(['fail_event' => true]);

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Event dispatch failure must expose committed observation semantics.');
        } catch (StatusTransitionObservationException $exception) {
            self::assertTrue($exception->mutationCommitted());
            self::assertTrue($exception->auditRecorded);
            self::assertFalse($exception->eventDispatchCompleted);
            self::assertSame('publish', $post->status);
            self::assertSame(1, $post->mutationCount);
            self::assertCount(1, $probe->records);
            self::assertCount(1, $probe->events);
        }

        try {
            $executor->execute($this->context(), self::POST_ID, 'draft', 'publish');
            self::fail('Retry after committed Event failure must be stale rather than mutating twice.');
        } catch (RuntimeException) {
            self::assertSame(1, $post->mutationCount);
            self::assertCount(1, $probe->records);
            self::assertCount(1, $probe->events);
        }
    }

    /**
     * @param array{
     *   definitions?:InMemoryDefinitionRepository,
     *   policy?:StatusTransitionPolicy,
     *   allow_object_edit?:bool,
     *   allow_edge_capability?:bool,
     *   registered_statuses?:list<string>,
     *   invalid_mutation_result?:bool,
     *   preserve_state_after_mutation?:bool,
     *   fail_audit?:bool,
     *   fail_event?:bool
     * } $options
     * @return array{StatusTransitionExecutor,StatusTransitionEvidenceProbe,StatusMutablePost}
     */
    private function harness(array $options = []): array
    {
        $definitions = $options['definitions'] ?? new InMemoryDefinitionRepository();
        $policy = $options['policy'] ?? new StatusTransitionPolicy([
            new StatusTransitionRule('draft', 'publish'),
        ]);
        $allowObjectEdit = $options['allow_object_edit'] ?? true;
        $allowEdgeCapability = $options['allow_edge_capability'] ?? true;
        $registeredStatuses = $options['registered_statuses'] ?? ['draft', 'publish', 'private'];
        $post = new StatusMutablePost();
        $probe = new StatusTransitionEvidenceProbe($options['fail_audit'] ?? false);
        $events = new EventBus();
        $failEvent = $options['fail_event'] ?? false;
        $events->listen(
            StatusTransitionEvidencePublisher::EVENT_NAME,
            static function (DomainEvent $event) use ($probe, $post, $failEvent): void {
                self::assertSame('publish', $post->status, 'Event evidence must occur only after the verified mutation.');
                $probe->events[] = $event;
                if ($failEvent) {
                    throw new RuntimeException('Synthetic Event listener failure.');
                }
            },
        );
        $evidence = new StatusTransitionEvidencePublisher($events, $probe);
        $resources = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentNetworkId: static fn (): ?int => 2,
            currentUserCan: static fn (string $capability, int $postId): bool => $allowObjectEdit,
        );
        $capabilities = new class($allowEdgeCapability) implements CapabilityCheckerInterface {
            public function __construct(private readonly bool $allowed) {}

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed;
            }
        };

        $executor = new StatusTransitionExecutor(
            definitions: $definitions,
            policy: $policy,
            resources: $resources,
            capabilities: $capabilities,
            readPostStatus: static fn (int $postId): string => $post->status,
            readPostType: static fn (int $postId): string => 'post',
            statusRegistered: static fn (string $status): bool => in_array($status, $registeredStatuses, true),
            updatePostStatus: static function (int $postId, string $targetStatus) use ($post, $options): mixed {
                $post->mutationCount++;
                if ($options['invalid_mutation_result'] ?? false) {
                    return false;
                }
                if (!($options['preserve_state_after_mutation'] ?? false)) {
                    $post->status = $targetStatus;
                }
                return $postId;
            },
            evidence: $evidence,
        );

        return [$executor, $probe, $post];
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
        return new ExecutionContext(
            new Principal(7),
            siteId: 3,
            channel: ExecutionChannel::Rest,
            networkId: 2,
            correlationId: 'transition-correlation',
        );
    }
}

final class StatusTransitionEvidenceProbe implements AuditLoggerInterface
{
    /** @var list<AuditRecord> */
    public array $records = [];

    /** @var list<DomainEvent> */
    public array $events = [];

    public function __construct(private readonly bool $failAudit) {}

    public function record(AuditRecord $record): void
    {
        if ($this->failAudit) {
            throw new RuntimeException('Synthetic Audit failure.');
        }
        $this->records[] = $record;
    }
}

final class StatusMutablePost
{
    public string $status = 'draft';
    public int $mutationCount = 0;
}
