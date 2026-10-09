<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftPublishReviewService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetDraftPublishReviewServiceTest extends TestCase
{
    private const DRAFT_ID = '33333333-3333-4333-8333-333333333333';
    private const WIDGET_ID = '11111111-1111-4111-8111-111111111111';

    public function testValidDraftReviewIsDeterministicReadOnlyAndNeverAuthorizesPublication(): void
    {
        $repo = $this->repo();
        $draft = $this->draft();
        $repo->save($draft);
        $beforeWidget = $repo->get(self::WIDGET_ID);
        $service = $this->service($repo);
        $digest = $draft->computedChecksum();
        $result = ['status' => 'review_candidate', 'publish_authorized' => false];

        self::assertSame($result, $service->inspect($this->context(), self::DRAFT_ID, $digest));
        self::assertSame($result, $service->inspect($this->context(), self::DRAFT_ID, $digest));
        self::assertSame($draft, $repo->get(self::DRAFT_ID));
        self::assertSame($beforeWidget, $repo->get(self::WIDGET_ID));
        self::assertSame(DefinitionStatus::Draft, $repo->get(self::DRAFT_ID)?->status);
    }

    public function testStaleOrInvalidChecksumNeverApprovesReview(): void
    {
        $repo = $this->repo();
        $draft = $this->draft();
        $repo->save($draft);
        $service = $this->service($repo);
        self::assertSame(
            ['status' => 'stale_draft', 'publish_authorized' => false],
            $service->inspect($this->context(), self::DRAFT_ID, str_repeat('0', 64)),
        );
        self::assertSame(
            ['status' => 'invalid_input', 'publish_authorized' => false],
            $service->inspect($this->context(), self::DRAFT_ID, str_repeat('A', 64)),
        );
        self::assertSame(
            ['status' => 'invalid_input', 'publish_authorized' => false],
            $service->inspect($this->context(), strtoupper('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $draft->computedChecksum()),
        );
        self::assertSame($draft, $repo->get(self::DRAFT_ID));

        // A later edit invalidates the fingerprint held by an earlier caller,
        // but this service must not try to rewrite either revision.
        $edited = $this->draft(revision: 2, label: 'Changed while reviewing');
        $repo->save($edited);
        self::assertSame(
            ['status' => 'stale_draft', 'publish_authorized' => false],
            $service->inspect($this->context(), self::DRAFT_ID, $draft->computedChecksum()),
        );
        self::assertSame($edited, $repo->get(self::DRAFT_ID));
    }

    public function testOnlySiteLocalDraftPresetCanBeReviewed(): void
    {
        foreach ([
            $this->draft(status: DefinitionStatus::Published),
            $this->draft(owner: 11),
            $this->draft(type: DashboardWidgetDefinition::TYPE),
            $this->draft(schemaVersion: 2),
        ] as $wrong) {
            $repo = $this->repo();
            $repo->save($wrong);
            self::assertSame(
                ['status' => 'unavailable', 'publish_authorized' => false],
                $this->service($repo)->inspect($this->context(), self::DRAFT_ID, $wrong->computedChecksum()),
            );
        }
        $missing = $this->repo();
        self::assertSame(
            ['status' => 'unavailable', 'publish_authorized' => false],
            $this->service($missing)->inspect($this->context(), self::DRAFT_ID, str_repeat('a', 64)),
        );

        $network = $this->repo();
        $networkDraft = $this->draft(networkDefault: true);
        $network->save($networkDraft);
        self::assertSame(
            ['status' => 'forbidden', 'publish_authorized' => false],
            $this->service($network)->inspect($this->context(), self::DRAFT_ID, $networkDraft->computedChecksum()),
        );
    }

    public function testUnpublishedOrMalformedReferencedWidgetFailsCurrentCatalog(): void
    {
        $repo = $this->repo();
        $draft = $this->draft();
        $repo->save($draft);
        $digest = $draft->computedChecksum();
        $repo->save($this->widget(DefinitionStatus::Draft, revision: 2));
        self::assertSame(
            ['status' => 'invalid_catalog', 'publish_authorized' => false],
            $this->service($repo)->inspect($this->context(), self::DRAFT_ID, $digest),
        );
        $another = $this->repo();
        $bad = $this->draft(widgetIds: ['aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa']);
        $another->save($bad);
        self::assertSame(
            ['status' => 'invalid_catalog', 'publish_authorized' => false],
            $this->service($another)->inspect($this->context(), self::DRAFT_ID, $bad->computedChecksum()),
        );
        $third = $this->repo();
        $badRoles = $this->draft(roles: ['editor', 'administrator']);
        $third->save($badRoles);
        self::assertSame(
            ['status' => 'invalid_catalog', 'publish_authorized' => false],
            $this->service($third)->inspect($this->context(), self::DRAFT_ID, $badRoles->computedChecksum()),
        );
    }

    public function testDenyBeforeLookingUpEvenMalformedInputsOnAllUnprivilegedChannels(): void
    {
        foreach ([
            $this->context(ExecutionChannel::Rest),
            $this->context(ExecutionChannel::Ui),
            $this->context(ExecutionChannel::Ai),
            $this->context(ExecutionChannel::Cli),
            $this->context(ExecutionChannel::Workflow),
            $this->context(ExecutionChannel::Internal, null),
            $this->context(ExecutionChannel::Internal, 9, 'agent'),
            $this->context(ExecutionChannel::Internal, 31),
        ] as $context) {
            $repo = $this->repo();
            self::assertSame(
                ['status' => 'forbidden', 'publish_authorized' => false],
                $this->service($repo)->inspect($context, 'invalid-id', 'invalid-sha'),
            );
            self::assertNull($repo->get(self::DRAFT_ID));
        }
        self::assertSame(
            ['status' => 'forbidden', 'publish_authorized' => false],
            $this->service($this->repo(), false)->inspect($this->context(), 'invalid-id', 'invalid-sha'),
        );
    }

    private function repo(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        $repo->save($this->widget());
        return $repo;
    }

    private function widget(
        DefinitionStatus $status = DefinitionStatus::Published,
        int $revision = 1,
    ): Definition {
        return new Definition(
            id: self::WIDGET_ID,
            slug: 'published-dashboard-widget',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 10,
            status: $status,
            payload: [],
            revision: $revision,
        );
    }

    /** @param list<string> $widgetIds @param list<string> $roles */
    private function draft(
        DefinitionStatus $status = DefinitionStatus::Draft,
        int $owner = 10,
        string $type = DashboardWidgetPresetDefinition::TYPE,
        int $schemaVersion = 1,
        int $revision = 1,
        string $label = 'Imported Draft',
        array $widgetIds = [self::WIDGET_ID],
        array $roles = ['editor'],
        bool $networkDefault = false,
    ): Definition {
        return new Definition(
            id: self::DRAFT_ID,
            slug: 'imported-preset',
            type: $type,
            schemaVersion: $schemaVersion,
            ownerSurfaceId: $owner,
            status: $status,
            payload: ['preset' => [
                'label' => $label,
                'widget_definition_ids' => $widgetIds,
                'assignment' => [
                    'roles' => $roles,
                    'network_default' => $networkDefault,
                ],
            ]],
            revision: $revision,
        );
    }

    private function context(
        ExecutionChannel $channel = ExecutionChannel::Internal,
        ?int $userId = 9,
        string $actor = 'user',
    ): ExecutionContext {
        return new ExecutionContext(new Principal($userId, $actor), 1, $channel);
    }

    private function service(
        InMemoryDefinitionRepository $repo,
        bool $allowed = true,
    ): DashboardWidgetPresetDraftPublishReviewService {
        $capability = new class($allowed) implements CapabilityCheckerInterface {
            public function __construct(private bool $allowed) {}
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed
                    && $capability === 'manage_options'
                    && $context->principal->userId === 9;
            }
        };
        return new DashboardWidgetPresetDraftPublishReviewService(
            $repo,
            new DashboardWidgetPresetCompiler($repo),
            new DashboardWidgetPresetDraftImportService(
                $repo,
                new DashboardWidgetPresetImportPreflightService(
                    $repo,
                    new DashboardWidgetPresetCompiler($repo),
                ),
                $capability,
            ),
        );
    }
}
