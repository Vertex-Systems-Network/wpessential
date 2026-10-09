<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetMappedDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetPortabilityMappingPreviewService;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetMappedDraftImportServiceTest extends TestCase
{
    private const SOURCE_A = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    private const SOURCE_B = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
    private const TARGET_A = '11111111-1111-4111-8111-111111111111';
    private const TARGET_B = '22222222-2222-4222-8222-222222222222';
    private const TARGET_PRESET = '33333333-3333-4333-8333-333333333333';

    public function testMapsOrderedWidgetReferencesIntoOnlyOneDraftWithRevisionOne(): void
    {
        $repo = $this->repository();
        $beforeA = $repo->get(self::TARGET_A);
        $beforeB = $repo->get(self::TARGET_B);
        $service = $this->service($repo);
        self::assertSame(
            ['status' => 'created_draft', 'definition_id' => self::TARGET_PRESET],
            $service->importMappedDraft($this->context(), $this->source(), self::TARGET_PRESET, $this->mapping(), 'mapped-preset'),
        );
        $draft = $repo->get(self::TARGET_PRESET);
        self::assertInstanceOf(Definition::class, $draft);
        self::assertSame(DashboardWidgetPresetDefinition::TYPE, $draft->type);
        self::assertSame(DefinitionStatus::Draft, $draft->status);
        self::assertSame(1, $draft->revision);
        self::assertSame('mapped-preset', $draft->slug);
        self::assertSame(['preset' => [
            'label' => 'Portable from another site',
            'widget_definition_ids' => [self::TARGET_B, self::TARGET_A],
            'assignment' => ['roles' => ['administrator', 'editor'], 'network_default' => false],
        ]], $draft->payload);
        self::assertSame($beforeA, $repo->get(self::TARGET_A));
        self::assertSame($beforeB, $repo->get(self::TARGET_B));
        self::assertSame(
            ['status' => 'id_conflict'],
            $service->importMappedDraft($this->context(), $this->source(), self::TARGET_PRESET, $this->mapping(), 'mapped-again'),
        );
        self::assertSame($draft, $repo->get(self::TARGET_PRESET));
    }

    public function testUnauthorizedCallersFailBeforeMalformedSourceContentIsEvaluated(): void
    {
        $repo = $this->repository();
        foreach ([
            [$this->context(ExecutionChannel::Rest), true],
            [$this->context(ExecutionChannel::Ui), true],
            [$this->context(ExecutionChannel::Cli), true],
            [$this->context(ExecutionChannel::Workflow), true],
            [$this->context(ExecutionChannel::Ai), true],
            [$this->context(ExecutionChannel::Internal, null), true],
            [$this->context(ExecutionChannel::Internal, 9, 'agent'), true],
            [$this->context(ExecutionChannel::Internal, 31), true],
            [$this->context(), false],
        ] as [$context, $allow]) {
            // Missing format, bad target and map must not reveal validity
            // to an unprivileged actor before authorization is checked.
            self::assertSame(
                ['status' => 'forbidden'],
                $this->service($repo, $allow)->importMappedDraft(
                    $context, ['untrusted' => true], 'invalid', ['raw' => []], 'unsafe',
                ),
            );
        }
        self::assertNull($repo->get(self::TARGET_PRESET));
    }

    public function testIntegrityMappingAndDestinationFailuresNeverCreateDraft(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        $badHash = $this->source();
        $badHash['sha256'] = str_repeat('0', 64);
        self::assertSame(['status' => 'invalid_snapshot'], $service->importMappedDraft(
            $this->context(), $badHash, self::TARGET_PRESET, $this->mapping(), 'safe-slug',
        ));
        foreach ([
            [self::SOURCE_A => self::TARGET_A],
            [self::SOURCE_A => self::TARGET_A, self::SOURCE_B => self::TARGET_A],
            [self::SOURCE_A => self::TARGET_A, 'cccccccc-cccc-4ccc-8ccc-cccccccccccc' => self::TARGET_B],
            [self::SOURCE_A => self::TARGET_A, self::SOURCE_B => 'invalid'],
            [self::SOURCE_A => self::TARGET_A, self::SOURCE_B => '99999999-9999-4999-8999-999999999999'],
        ] as $mapping) {
            self::assertSame(['status' => 'invalid_snapshot'], $service->importMappedDraft(
                $this->context(), $this->source(), self::TARGET_PRESET, $mapping, 'safe-slug',
            ));
        }

        $repo->create($this->widget('55555555-5555-4555-8555-555555555555', 'draft-target', DefinitionStatus::Draft));
        $repo->create($this->widget('66666666-6666-4666-8666-666666666666', 'foreign-target', owner: 9));
        foreach ([
            '55555555-5555-4555-8555-555555555555',
            '66666666-6666-4666-8666-666666666666',
        ] as $unpublished) {
            $mapping = $this->mapping();
            $mapping[self::SOURCE_B] = $unpublished;
            self::assertSame(['status' => 'invalid_snapshot'], $service->importMappedDraft(
                $this->context(), $this->source(), self::TARGET_PRESET, $mapping, 'safe-slug',
            ));
        }

        self::assertSame(['status' => 'invalid_snapshot'], $service->importMappedDraft(
            $this->context(), $this->source(), self::TARGET_PRESET, $this->mapping(), 'Invalid Slug!',
        ));
        self::assertNull($repo->get(self::TARGET_PRESET));
    }

    public function testNetworkDefaultIsDeniedAndAtomicSlugCollisionNeverOverwrites(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        self::assertSame(['status' => 'forbidden'], $service->importMappedDraft(
            $this->context(), $this->source(networkDefault: true), self::TARGET_PRESET,
            $this->mapping(), 'network-default',
        ));
        self::assertNull($repo->get(self::TARGET_PRESET));

        $collisionId = '77777777-7777-4777-8777-777777777777';
        $repo->create(new Definition(
            id: $collisionId, slug: 'taken-slug', type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Draft,
            payload: ['preset' => []],
        ));
        $original = $repo->get($collisionId);
        self::assertSame(['status' => 'write_failed'], $service->importMappedDraft(
            $this->context(), $this->source(), self::TARGET_PRESET, $this->mapping(), 'taken-slug',
        ));
        self::assertSame($original, $repo->get($collisionId));
        self::assertNull($repo->get(self::TARGET_PRESET));
    }

    public function testMappedImporterDoesNotClaimCreatedWhenAtomicAdapterAcknowledgesButDoesNotPersist(): void
    {
        $fixtures = $this->repository();
        $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
        $repo->method('get')->willReturnCallback(
            static fn (string $id): ?Definition => $fixtures->get($id),
        );
        $repo->expects(self::once())->method('create');
        self::assertSame(
            ['status' => 'write_failed'],
            $this->service($repo)->importMappedDraft(
                $this->context(),
                $this->source(),
                self::TARGET_PRESET,
                $this->mapping(),
                'mapped-preset',
            ),
        );
        self::assertNull($fixtures->get(self::TARGET_PRESET));
    }

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        $repo->create($this->widget(self::TARGET_A, 'widget-a'));
        $repo->create($this->widget(self::TARGET_B, 'widget-b'));
        return $repo;
    }

    private function widget(string $id, string $slug, DefinitionStatus $status = DefinitionStatus::Published, int $owner = 10): Definition
    {
        return new Definition(
            id: $id, slug: $slug, type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1, ownerSurfaceId: $owner, status: $status, payload: [],
        );
    }

    /** @return array<string,string> */
    private function mapping(): array
    {
        return [self::SOURCE_A => self::TARGET_A, self::SOURCE_B => self::TARGET_B];
    }

    /** @return array<string,mixed> */
    private function source(bool $networkDefault = false): array
    {
        $payload = [
            'definition_id' => '44444444-4444-4444-8444-444444444444',
            'revision' => 7,
            'label' => 'Portable from another site',
            'widget_definition_ids' => [self::SOURCE_B, self::SOURCE_A],
            'assignment' => [
                'roles' => $networkDefault ? [] : ['administrator', 'editor'],
                'network_default' => $networkDefault,
            ],
        ];
        return [
            'format' => 'wpessential-dashboard-preset',
            'version' => 1,
            'payload' => $payload,
            'sha256' => hash('sha256', json_encode(
                $payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            )),
        ];
    }

    private function context(
        ExecutionChannel $channel = ExecutionChannel::Internal,
        ?int $userId = 9,
        string $actor = 'user',
    ): ExecutionContext {
        return new ExecutionContext(new Principal($userId, $actor), 1, $channel);
    }

    private function service(DefinitionCreateOnlyRepositoryInterface $repo, bool $allowed = true): DashboardWidgetPresetMappedDraftImportService
    {
        $checker = new class($allowed) implements CapabilityCheckerInterface {
            public function __construct(private bool $allowed) {}
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed && $capability === 'manage_options'
                    && $context->principal->userId === 9;
            }
        };
        $preflight = new DashboardWidgetPresetImportPreflightService($repo, new DashboardWidgetPresetCompiler($repo));
        return new DashboardWidgetPresetMappedDraftImportService(
            new DashboardWidgetPresetPortabilityMappingPreviewService($preflight),
            new DashboardWidgetPresetDraftImportService($repo, $preflight, $checker),
        );
    }
}
