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

final class DashboardWidgetPresetDraftImportCollisionTest extends TestCase
{
    public function testSlugConflictCannotUpdateExistingDefinitionOrInsertCandidate(): void
    {
        $repo = $this->repository();
        $otherId = '44444444-4444-4444-8444-444444444444';
        $repo->create(new Definition(
            id: $otherId, slug: 'occupied', type: DashboardWidgetPresetDefinition::TYPE,
            schemaVersion: 1, ownerSurfaceId: 10, status: DefinitionStatus::Draft,
            payload: ['preset' => ['label' => 'Before']],
        ));
        $before = $repo->get($otherId);
        self::assertSame(['status' => 'write_failed'], $this->service($repo)->importDraft(
            $this->context(), $this->snapshot(), 'occupied',
        ));
        self::assertSame($before, $repo->get($otherId));
        self::assertNull($repo->get(self::PRESET_ID));
    }

    public function testAtomicIdCollisionRemainsFailClosedAfterPreflightRace(): void
    {
        $inner = $this->repository();
        $wrapper = new class($inner) implements DefinitionCreateOnlyRepositoryInterface {
            public function __construct(private InMemoryDefinitionRepository $inner) {}
            public function create(Definition $definition): void
            {
                // Simulate another writer winning between preflight and create:
                // an atomic create MUST NOT overwrite that winner.
                if ($definition->id === DashboardWidgetPresetDraftImportCollisionTest::candidateId()) {
                    $this->inner->create(new Definition(
                        id: $definition->id, slug: 'winning-writer',
                        type: $definition->type, schemaVersion: 1,
                        ownerSurfaceId: $definition->ownerSurfaceId,
                        status: DefinitionStatus::Draft, payload: ['preset' => ['label' => 'winner']],
                    ));
                }
                $this->inner->create($definition);
            }
            public function save(Definition $definition): void { throw new RuntimeException('save forbidden'); }
            public function get(string $id): ?Definition { return $this->inner->get($id); }
            public function byType(string $type): array { return $this->inner->byType($type); }
            public function dependentsOf(string $id): array { return $this->inner->dependentsOf($id); }
        };
        self::assertSame(['status' => 'id_conflict'], $this->service($wrapper)->importDraft(
            $this->context(), $this->snapshot(), 'candidate',
        ));
        self::assertSame('winning-writer', $inner->get(self::PRESET_ID)?->slug);
        self::assertSame(1, $inner->get(self::PRESET_ID)?->revision);
    }

    public function testStorageFailureNeverExposesInternalError(): void
    {
        $inner = $this->repository();
        $broken = new class($inner) implements DefinitionCreateOnlyRepositoryInterface {
            public function __construct(private InMemoryDefinitionRepository $inner) {}
            public function create(Definition $definition): void { throw new RuntimeException('SQL password / private diagnostics'); }
            public function save(Definition $definition): void { throw new RuntimeException('forbidden'); }
            public function get(string $id): ?Definition { return $this->inner->get($id); }
            public function byType(string $type): array { return $this->inner->byType($type); }
            public function dependentsOf(string $id): array { return $this->inner->dependentsOf($id); }
        };
        self::assertSame(['status' => 'write_failed'], $this->service($broken)->importDraft(
            $this->context(), $this->snapshot(), 'safe',
        ));
        self::assertNull($inner->get(self::PRESET_ID));
    }

    public static function candidateId(): string { return self::PRESET_ID; }

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
