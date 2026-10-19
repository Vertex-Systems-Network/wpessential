<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Review;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshot;
use WPEssential\Modules\CustomTables\Migration\Review\MigrationReviewSnapshotStoreInterface;
use WPEssential\Modules\CustomTables\Migration\Review\R1MigrationRunLifecycleService;
use WPEssential\Modules\CustomTables\Migration\Review\WordPressProviderCapabilityProfileResolver;
use WPEssential\Modules\CustomTables\Migration\Run\InMemoryMigrationRunRepository;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRun;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunState;
use WPEssential\Modules\CustomTables\Migration\Run\MigrationRunTransitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class R1MigrationRunLifecycleServiceTest extends TestCase
{
    private const RUN_ID = '11111111-1111-4111-8111-111111111111';

    public function testAuthorizedPathMovesPlannedRunToRevalidating(): void
    {
        [$service, $runs] = $this->service(snapshot: true);

        $run = $service->submit(self::RUN_ID, 1);
        self::assertSame(MigrationRunState::AwaitingReview, $run->state);
        $run = $service->approve(self::admin(), self::RUN_ID, $run->stateRevision);
        self::assertSame(MigrationRunState::Approved, $run->state);
        $run = $service->startRevalidation(self::admin(), self::RUN_ID, $run->stateRevision);

        self::assertSame(MigrationRunState::Revalidating, $run->state);
        self::assertSame(MigrationRunState::Revalidating, $runs->get(self::RUN_ID)?->state);
    }

    public function testSubmitWithoutReviewSnapshotIsRefused(): void
    {
        [$service, $runs] = $this->service(snapshot: false);

        try {
            $service->submit(self::RUN_ID, 1);
            self::fail('Submission without a review snapshot must be refused.');
        } catch (RuntimeException) {
        }

        self::assertSame(MigrationRunState::Planned, $runs->get(self::RUN_ID)?->state);
    }

    public function testApprovalByUnauthorizedUserLeavesRunUnchanged(): void
    {
        [$service, $runs] = $this->service(snapshot: true);
        $run = $service->submit(self::RUN_ID, 1);

        try {
            $service->approve(new ExecutionContext(new Principal(8), 1), self::RUN_ID, $run->stateRevision);
            self::fail('Unauthorized approval must be refused.');
        } catch (RuntimeException) {
        }

        self::assertSame(MigrationRunState::AwaitingReview, $runs->get(self::RUN_ID)?->state);
    }

    public function testUnauthenticatedOrOtherSiteContextsAreRefused(): void
    {
        [$service] = $this->service(snapshot: true);
        $run = $service->submit(self::RUN_ID, 1);

        foreach ([
            new ExecutionContext(new Principal(null), 1),
            new ExecutionContext(new Principal(7, 'system'), 1),
            new ExecutionContext(new Principal(7), 2),
        ] as $context) {
            try {
                $service->approve($context, self::RUN_ID, $run->stateRevision);
                self::fail('Context must be refused.');
            } catch (RuntimeException) {
            }
        }
        self::assertTrue(true);
    }

    public function testStaleRevisionIsRefused(): void
    {
        [$service] = $this->service(snapshot: true);
        $service->submit(self::RUN_ID, 1);

        $this->expectException(RuntimeException::class);
        $service->approve(self::admin(), self::RUN_ID, 1);
    }

    public function testSkippingApprovalIsAnIllegalTransition(): void
    {
        [$service] = $this->service(snapshot: true);
        $run = $service->submit(self::RUN_ID, 1);

        $this->expectException(InvalidArgumentException::class);
        $service->startRevalidation(self::admin(), self::RUN_ID, $run->stateRevision);
    }

    public function testAuthorizedCancelBeforeMutation(): void
    {
        [$service] = $this->service(snapshot: true);
        $run = $service->submit(self::RUN_ID, 1);

        $run = $service->cancel(self::admin(), self::RUN_ID, $run->stateRevision);

        self::assertSame(MigrationRunState::CancelledBeforeMutation, $run->state);
    }

    public function testProviderProfileComesFromServerInfo(): void
    {
        $profile = (new WordPressProviderCapabilityProfileResolver(new class {
            public function db_server_info(): string
            {
                return '5.5.5-10.11.14-MariaDB-0ubuntu0.24.04.1';
            }
        }))->resolve();

        self::assertSame('mariadb', $profile->provider);
        self::assertSame('10.11.14', $profile->version);
    }

    public function testProviderProfileResolverRequiresServerInfo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new WordPressProviderCapabilityProfileResolver(new class {
        });
    }

    /** @return array{0: R1MigrationRunLifecycleService, 1: InMemoryMigrationRunRepository} */
    private function service(bool $snapshot): array
    {
        $runs = new InMemoryMigrationRunRepository();
        $runs->create(new MigrationRun(
            id: self::RUN_ID,
            planFingerprint: str_repeat('b', 64),
            tableKey: 'settings',
            targetDefinitionId: '78888888-8888-4888-8888-888888888888',
            targetRevision: 2,
            targetSchemaVersion: 1,
            state: MigrationRunState::Planned,
        ));
        $snapshots = new LifecycleSnapshotStore($snapshot ? new MigrationReviewSnapshot(
            self::RUN_ID,
            str_repeat('a', 64),
            new MigrationGenerationStamp(str_repeat('b', 64), str_repeat('c', 64), '78888888-8888-4888-8888-888888888888', 2, 1, 'mysql', '8.0.36'),
        ) : null);
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $context->principal->userId === 7 && $capability === 'wpessential_apply_table_migrations';
            }
        };

        return [
            new R1MigrationRunLifecycleService(new MigrationRunTransitionService($runs), $snapshots, new PolicyEngine($checker), 1),
            $runs,
        ];
    }

    private static function admin(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class LifecycleSnapshotStore implements MigrationReviewSnapshotStoreInterface
{
    public function __construct(private ?MigrationReviewSnapshot $snapshot)
    {
    }

    public function record(MigrationReviewSnapshot $snapshot): MigrationReviewSnapshot
    {
        throw new RuntimeException('not used');
    }

    public function snapshotFor(string $runId): ?MigrationReviewSnapshot
    {
        return $this->snapshot !== null && $this->snapshot->runId === strtolower($runId) ? $this->snapshot : null;
    }
}
