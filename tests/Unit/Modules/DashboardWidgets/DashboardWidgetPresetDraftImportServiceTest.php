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
