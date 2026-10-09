<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionCreateOnlyRepositoryInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetDraftImportService;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetPresetImportPreflightService;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class DashboardWidgetPresetDraftImportServiceTest extends TestCase
{
    public function testCreatesOnlyDraftWithDestinationRevisionOneAndPreservedPayload(): void
    {
        $repo = $this->repository();
        $beforeWidgets = [$repo->get(self::WIDGET_A), $repo->get(self::WIDGET_B)];
        $result = $this->service($repo)->importDraft($this->context(), $this->snapshot(), 'imported-safe-preset');
        self::assertSame(['status' => 'created_draft', 'definition_id' => self::PRESET_ID], $result);

        $draft = $repo->get(self::PRESET_ID);
        self::assertInstanceOf(Definition::class, $draft);
        self::assertSame(DashboardWidgetPresetDefinition::TYPE, $draft->type);
        self::assertSame(DefinitionStatus::Draft, $draft->status);
        self::assertSame(1, $draft->revision);
        self::assertSame('imported-safe-preset', $draft->slug);
        self::assertSame(['preset' => [
            'label' => 'Portable Preset',
            'widget_definition_ids' => [self::WIDGET_B, self::WIDGET_A],
            'assignment' => ['roles' => ['administrator', 'editor'], 'network_default' => false],
        ]], $draft->payload);
        self::assertSame($beforeWidgets, [$repo->get(self::WIDGET_A), $repo->get(self::WIDGET_B)]);
        self::assertSame(['status' => 'id_conflict'], $this->service($repo)->importDraft(
            $this->context(), $this->snapshot(), 'another-slug',
        ));
        self::assertSame($draft, $repo->get(self::PRESET_ID));
    }

    public function testSharedInternalAuthorizationGateMatchesDraftImporterDenyBeforeParsing(): void
    {
        $repo = $this->repository();
        $authorized = $this->service($repo);
        self::assertTrue($authorized->isAuthorizedContext($this->context()));
        self::assertFalse($authorized->isAuthorizedContext($this->context(ExecutionChannel::Rest)));
        self::assertFalse($authorized->isAuthorizedContext($this->context(ExecutionChannel::Internal, null)));
        self::assertFalse($this->service($repo, false)->isAuthorizedContext($this->context()));
        self::assertNull($repo->get(self::PRESET_ID));
    }

    public function testForbiddenCallersNeverWriteIncludingGuestsAndImpersonatedPrincipal(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        foreach ([
            $this->context(ExecutionChannel::Rest),
            $this->context(ExecutionChannel::Ui),
            $this->context(ExecutionChannel::Cli),
            $this->context(ExecutionChannel::Workflow),
            $this->context(ExecutionChannel::Ai),
            $this->context(ExecutionChannel::Internal, null),
            $this->context(ExecutionChannel::Internal, 9, 'agent'),
            $this->context(ExecutionChannel::Internal, 31),
        ] as $context) {
            self::assertSame(['status' => 'forbidden'], $service->importDraft(
                $context, $this->snapshot(), 'safe-import',
            ));
        }
        self::assertSame(['status' => 'forbidden'], $this->service($repo, false)->importDraft(
            $this->context(), $this->snapshot(), 'safe-import',
        ));
        self::assertNull($repo->get(self::PRESET_ID));
    }

    public function testNetworkDefaultAndInvalidSnapshotsAreDeniedWithNoWrites(): void
    {
        $repo = $this->repository();
        $service = $this->service($repo);
        self::assertSame(['status' => 'forbidden'], $service->importDraft(
            $this->context(), $this->snapshot(networkDefault: true), 'network-preset',
        ));
        $wrongHash = $this->snapshot();
        $wrongHash['sha256'] = str_repeat('0', 64);
        self::assertSame(['status' => 'invalid_snapshot'], $service->importDraft(
            $this->context(), $wrongHash, 'safe-import',
        ));
        self::assertSame(['status' => 'invalid_snapshot'], $service->importDraft(
            $this->context(), $this->snapshot(), 'Invalid Slug!',
        ));
        self::assertNull($repo->get(self::PRESET_ID));
    }

    public function testPublishedWidgetChangingAfterAtomicCreateNeverClaimsAcceptedDraft(): void
    {
        $fixtures = $this->repository();
        $repo = new class($fixtures) implements DefinitionCreateOnlyRepositoryInterface {
            public int $createCalls = 0;

            public function __construct(private InMemoryDefinitionRepository $inner) {}

            public function create(Definition $definition): void
            {
                ++$this->createCalls;
                $this->inner->create($definition);

                // Simulate a separate actor changing a target widget between
                // the successful preflight and post-create readback.
                $this->inner->save(new Definition(
                    id: DashboardWidgetPresetDraftImportServiceTest::widgetB(),
                    slug: 'widget-1',
                    type: DashboardWidgetDefinition::TYPE,
                    schemaVersion: 1,
                    ownerSurfaceId: 10,
                    status: DefinitionStatus::Draft,
                    payload: [],
                    revision: 2,
                ));
            }

            public function save(Definition $definition): void
            {
                throw new RuntimeException('No fallback write/rollback is allowed.');
            }

            public function get(string $id): ?Definition { return $this->inner->get($id); }

            public function byType(string $type): array { return $this->inner->byType($type); }

            public function dependentsOf(string $id): array { return $this->inner->dependentsOf($id); }
        };

        self::assertSame(
            ['status' => 'write_failed'],
            $this->service($repo)->importDraft($this->context(), $this->snapshot(), 'imported-safe-preset'),
        );
        self::assertSame(1, $repo->createCalls, 'Do not retry uncertain atomic creation.');

        // The insert already happened. Never claim rollback or publication.
        $inserted = $fixtures->get(self::PRESET_ID);
        self::assertInstanceOf(Definition::class, $inserted);
        self::assertSame(DefinitionStatus::Draft, $inserted->status);
        self::assertSame(1, $inserted->revision);
        self::assertSame(DefinitionStatus::Draft, $fixtures->get(self::WIDGET_B)?->status);
    }

    public function testPublishedWidgetRevisionOrContentDriftAfterCreateNeverClaimsSuccess(): void
    {
        foreach (['revision', 'payload', 'slug'] as $mode) {
            $fixtures = $this->repository();
            $createCalls = 0;
            $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
            $repo->expects(self::once())->method('create')->willReturnCallback(
                static function (Definition $draft) use ($fixtures, $mode, &$createCalls): void {
                    ++$createCalls;
                    $fixtures->create($draft);

                    // Concurrent edit leaves target Published and the
                    // exact same widget UUID, unlike RB-0121 Draft status
                    // transition. An importer must not claim stale success.
                    $fixtures->save(new Definition(
                        id: self::WIDGET_B,
                        slug: $mode === 'slug' ? 'renamed-widget' : 'widget-1',
                        type: DashboardWidgetDefinition::TYPE,
                        schemaVersion: 1,
                        ownerSurfaceId: 10,
                        status: DefinitionStatus::Published,
                        payload: $mode === 'payload' ? ['new-data' => 'changed'] : [],
                        revision: 2,
                    ));
                },
            );
            $repo->method('get')->willReturnCallback(
                static fn (string $id): ?Definition => $fixtures->get($id),
            );
            $repo->expects(self::never())->method('save');
            $result = $this->service($repo)->importDraft(
                $this->context(), $this->snapshot(), 'imported-safe-preset',
            );
            self::assertSame(['status' => 'write_failed'], $result, $mode);
            self::assertSame(1, $createCalls, $mode);
            self::assertSame(DefinitionStatus::Draft, $fixtures->get(self::PRESET_ID)?->status, $mode);
            self::assertSame(DefinitionStatus::Published, $fixtures->get(self::WIDGET_B)?->status, $mode);
            self::assertSame(2, $fixtures->get(self::WIDGET_B)?->revision, $mode);
        }
    }

    public function testRevokedCapabilityAfterOneAtomicCreateNeverClaimsDraftSuccess(): void
    {
        $fixtures = $this->repository();
        $authority = (object) ['granted' => true, 'checks' => 0];
        $checker = new class($authority) implements CapabilityCheckerInterface {
            public function __construct(private object $state) {}
            public function can(ExecutionContext $context, string $capability): bool
            {
                ++$this->state->checks;
                return $this->state->granted && $capability === 'manage_options';
            }
        };
        $createCalls = 0;
        $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
        $repo->expects(self::once())->method('create')->willReturnCallback(
            static function (Definition $draft) use ($fixtures, $authority, &$createCalls): void {
                ++$createCalls;
                $fixtures->create($draft);
                // Privilege is revoked *after* the real insert, while widget
                // references remain unchanged and Published.
                $authority->granted = false;
            },
        );
        $repo->method('get')->willReturnCallback(
            static fn (string $id): ?Definition => $fixtures->get($id),
        );
        $repo->expects(self::never())->method('save');
        $importer = new DashboardWidgetPresetDraftImportService(
            $repo,
            new DashboardWidgetPresetImportPreflightService(
                $repo, new DashboardWidgetPresetCompiler($repo),
            ),
            $checker,
        );

        self::assertSame(
            ['status' => 'write_failed'],
            $importer->importDraft($this->context(), $this->snapshot(), 'revoked-draft'),
        );
        self::assertSame(1, $createCalls);
        self::assertGreaterThanOrEqual(2, $authority->checks);
        self::assertSame(DefinitionStatus::Draft, $fixtures->get(self::PRESET_ID)?->status);
        self::assertSame(1, $fixtures->get(self::PRESET_ID)?->revision);
        self::assertSame(DefinitionStatus::Published, $fixtures->get(self::WIDGET_B)?->status);
    }

    public function testMissingWidgetAfterPreflightBeforeCreateFailsWithoutWriting(): void
    {
        $fixtures = $this->repository();
        $reads = 0;
        $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
        $repo->method('get')->willReturnCallback(
            static function (string $id) use ($fixtures, &$reads): ?Definition {
                if ($id === self::WIDGET_B) {
                    ++$reads;
                    if ($reads >= 2) {
                        return null;
                    }
                }
                return $fixtures->get($id);
            },
        );
        $repo->expects(self::never())->method('create');
        $repo->expects(self::never())->method('save');

        self::assertSame(
            ['status' => 'invalid_snapshot'],
            $this->service($repo)->importDraft($this->context(), $this->snapshot(), 'safe-import'),
        );
        self::assertGreaterThanOrEqual(2, $reads);
        self::assertNull($fixtures->get(self::PRESET_ID));
    }

    public static function widgetB(): string
    {
        return self::WIDGET_B;
    }

    public function testCreateReturnWithoutPersistedRecordNeverClaimsSuccess(): void
    {
        $fixtures = $this->repository();
        $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
        $repo->method('get')->willReturnCallback(
            static fn (string $id): ?Definition => $fixtures->get($id),
        );
        $repo->expects(self::once())->method('create');

        self::assertSame(
            ['status' => 'write_failed'],
            $this->service($repo)->importDraft($this->context(), $this->snapshot(), 'imported-safe-preset'),
        );
        self::assertNull($fixtures->get(self::PRESET_ID));
    }

    public function testChangedPersistedDraftAndFailedReadbackNeverClaimSuccess(): void
    {
        foreach (['slug', 'payload', 'status', 'revision', 'read_exception'] as $mode) {
            $fixtures = $this->repository();
            $created = null;
            $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
            $repo->expects(self::once())->method('create')->willReturnCallback(
                static function (Definition $value) use (&$created): void {
                    $created = $value;
                },
            );
            $repo->method('get')->willReturnCallback(
                static function (string $id) use ($fixtures, $mode, &$created): ?Definition {
                    if ($id !== self::PRESET_ID || !$created instanceof Definition) {
                        return $fixtures->get($id);
                    }
                    if ($mode === 'read_exception') {
                        throw new RuntimeException('Readback unavailable');
                    }
                    $payload = $created->payload;
                    if ($mode === 'payload') {
                        $payload['preset']['label'] = 'Silently altered';
                    }
                    return new Definition(
                        id: $created->id,
                        slug: $mode === 'slug' ? 'changed-slug' : $created->slug,
                        type: $created->type,
                        schemaVersion: $created->schemaVersion,
                        ownerSurfaceId: $created->ownerSurfaceId,
                        status: $mode === 'status' ? DefinitionStatus::Published : $created->status,
                        payload: $payload,
                        revision: $mode === 'revision' ? 2 : $created->revision,
                        dependencies: $created->dependencies,
                    );
                },
            );

            self::assertSame(
                ['status' => 'write_failed'],
                $this->service($repo)->importDraft($this->context(), $this->snapshot(), 'imported-safe-preset'),
                $mode,
            );
            self::assertNull($fixtures->get(self::PRESET_ID), $mode);
        }
    }

    public function testEquivalentRehydratedPayloadWithReorderedMapKeysPassesCanonicalReadback(): void
    {
        $fixtures = $this->repository();
        $created = null;
        $repo = $this->createMock(DefinitionCreateOnlyRepositoryInterface::class);
        $repo->expects(self::once())->method('create')->willReturnCallback(
            static function (Definition $draft) use (&$created): void {
                $created = $draft;
            },
        );
        $repo->method('get')->willReturnCallback(
            static function (string $id) use ($fixtures, &$created): ?Definition {
                if ($id !== self::PRESET_ID || !$created instanceof Definition) {
                    return $fixtures->get($id);
                }
                $preset = $created->payload['preset'];
                return new Definition(
                    id: $created->id,
                    slug: $created->slug,
                    type: $created->type,
                    schemaVersion: $created->schemaVersion,
                    ownerSurfaceId: $created->ownerSurfaceId,
                    status: $created->status,
                    payload: ['preset' => [
                        'assignment' => [
                            'network_default' => $preset['assignment']['network_default'],
                            'roles' => $preset['assignment']['roles'],
                        ],
                        'widget_definition_ids' => $preset['widget_definition_ids'],
                        'label' => $preset['label'],
                    ]],
                    revision: $created->revision,
                );
            },
        );

        self::assertSame(
            ['status' => 'created_draft', 'definition_id' => self::PRESET_ID],
            $this->service($repo)->importDraft($this->context(), $this->snapshot(), 'imported-safe-preset'),
        );
    }

    private const WIDGET_A = '11111111-1111-4111-8111-111111111111';
    private const WIDGET_B = '22222222-2222-4222-8222-222222222222';
    private const PRESET_ID = '33333333-3333-4333-8333-333333333333';

    private function repository(): InMemoryDefinitionRepository
    {
        $repo = new InMemoryDefinitionRepository();
        foreach ([self::WIDGET_A, self::WIDGET_B] as $index => $id) {
            $repo->create(new Definition(
                id: $id, slug: 'widget-' . $index, type: DashboardWidgetDefinition::TYPE,
                schemaVersion: 1, ownerSurfaceId: 10,
                status: DefinitionStatus::Published, payload: [],
            ));
        }
        return $repo;
    }

    /** @return array<string,mixed> */
    private function snapshot(
        string $id = self::PRESET_ID,
        int $revision = 4,
        bool $networkDefault = false,
    ): array {
        $payload = [
            'definition_id' => $id,
            'revision' => $revision,
            'label' => 'Portable Preset',
            'widget_definition_ids' => [self::WIDGET_B, self::WIDGET_A],
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

    private function service(
        DefinitionCreateOnlyRepositoryInterface $repo,
        bool $permitted = true,
    ): DashboardWidgetPresetDraftImportService {
        $checker = new class($permitted) implements CapabilityCheckerInterface {
            public function __construct(private bool $permitted) {}
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->permitted && $capability === 'manage_options'
                    && $context->principal->userId === 9;
            }
        };
        return new DashboardWidgetPresetDraftImportService(
            $repo,
            new DashboardWidgetPresetImportPreflightService(
                $repo, new DashboardWidgetPresetCompiler($repo),
            ),
            $checker,
        );
    }
}
