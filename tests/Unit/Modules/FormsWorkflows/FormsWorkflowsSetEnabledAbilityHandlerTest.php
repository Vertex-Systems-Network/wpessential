<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\FormsWorkflows;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsSetEnabledAbilityHandler;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FormsWorkflowsSetEnabledAbilityHandlerTest extends TestCase
{
    public function testAuthorizesPublishedAndDisabledDefinitionsAtExactRevision(): void
    {
        foreach ([DefinitionStatus::Published, DefinitionStatus::Disabled] as $status) {
            $repository = new SetEnabledCountingRepository();
            $definition = $this->definition(status: $status);
            $repository->seed($definition);

            $decision = $this->handler($repository)->authorizeInput(
                $this->input($definition, enabled: $status === DefinitionStatus::Disabled),
                $this->context(),
            );

            self::assertTrue($decision->allowed);
            self::assertSame('forms_workflows_set_enabled_authorized', $decision->reason);
        }
    }

    public function testAuthorizationFailsClosedForMalformedInput(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition();
        $repository->seed($definition);
        $handler = $this->handler($repository);

        $cases = [
            [],
            ['definition_id' => $definition->id],
            [
                'definition_id' => 'NOT-A-VALID-LOWERCASE-RFC4122-UUID',
                'expected_revision' => 1,
                'enabled' => false,
            ],
            [
                'definition_id' => $definition->id,
                'expected_revision' => '1',
                'enabled' => false,
            ],
            [
                'definition_id' => $definition->id,
                'expected_revision' => 1,
                'enabled' => 1,
            ],
            [
                'definition_id' => $definition->id,
                'expected_revision' => 1,
                'enabled' => false,
                'extra' => true,
            ],
        ];

        foreach ($cases as $input) {
            $decision = $handler->authorizeInput($input, $this->context());
            self::assertFalse($decision->allowed);
            self::assertSame(
                FormsWorkflowsSetEnabledAbilityHandler::DENY_INVALID_INPUT,
                $decision->reason,
            );
        }
    }

    public function testAuthorizationDeniesMissingWrongOwnerInvalidStateAndStaleRevision(): void
    {
        $missing = new SetEnabledCountingRepository();
        $missingDecision = $this->handler($missing)->authorizeInput([
            'definition_id' => '11111111-1111-4111-8111-111111111111',
            'expected_revision' => 1,
            'enabled' => false,
        ], $this->context());
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::DENY_NOT_FOUND,
            $missingDecision->reason,
        );

        $wrongOwner = new SetEnabledCountingRepository();
        $wrongOwnerDefinition = $this->definition(ownerSurfaceId: 18);
        $wrongOwner->seed($wrongOwnerDefinition);
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::DENY_WRONG_OWNER,
            $this->handler($wrongOwner)->authorizeInput(
                $this->input($wrongOwnerDefinition, false),
                $this->context(),
            )->reason,
        );

        $wrongType = new SetEnabledCountingRepository();
        $wrongTypeDefinition = $this->definition(type: 'other-definition');
        $wrongType->seed($wrongTypeDefinition);
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::DENY_WRONG_OWNER,
            $this->handler($wrongType)->authorizeInput(
                $this->input($wrongTypeDefinition, false),
                $this->context(),
            )->reason,
        );

        foreach ([DefinitionStatus::Draft, DefinitionStatus::Archived] as $status) {
            $invalidState = new SetEnabledCountingRepository();
            $invalidDefinition = $this->definition(status: $status);
            $invalidState->seed($invalidDefinition);
            self::assertSame(
                FormsWorkflowsSetEnabledAbilityHandler::DENY_INVALID_STATE,
                $this->handler($invalidState)->authorizeInput(
                    $this->input($invalidDefinition, false),
                    $this->context(),
                )->reason,
            );
        }

        $stale = new SetEnabledCountingRepository();
        $staleDefinition = $this->definition(revision: 3);
        $stale->seed($staleDefinition);
        self::assertSame(
            FormsWorkflowsSetEnabledAbilityHandler::DENY_REVISION_CONFLICT,
            $this->handler($stale)->authorizeInput(
                [
                    'definition_id' => $staleDefinition->id,
                    'expected_revision' => 2,
                    'enabled' => false,
                ],
                $this->context(),
            )->reason,
        );
    }

    public function testPublishedToDisabledWritesExactlyOneRevisionAndPreservesDefinitionData(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition(status: DefinitionStatus::Published);
        $repository->seed($definition);

        $result = $this->handler($repository)->handle(
            $this->input($definition, false),
            $this->context(),
        );

        self::assertSame([
            'definition_id' => $definition->id,
            'previous_revision' => 1,
            'revision' => 2,
            'status' => 'disabled',
            'enabled' => false,
            'changed' => true,
            'result_code' => 'status_changed',
        ], $result);
        self::assertSame(1, $repository->saveCalls);

        $saved = $repository->get($definition->id);
        self::assertInstanceOf(Definition::class, $saved);
        self::assertSame($definition->id, $saved->id);
        self::assertSame($definition->slug, $saved->slug);
        self::assertSame($definition->type, $saved->type);
        self::assertSame($definition->schemaVersion, $saved->schemaVersion);
        self::assertSame($definition->ownerSurfaceId, $saved->ownerSurfaceId);
        self::assertSame($definition->payload, $saved->payload);
        self::assertSame($definition->dependencies, $saved->dependencies);
        self::assertSame($definition->checksum, $saved->checksum);
        self::assertSame(DefinitionStatus::Disabled, $saved->status);
        self::assertSame(2, $saved->revision);
    }

    public function testDisabledToPublishedWritesExactlyOneRevision(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition(status: DefinitionStatus::Disabled, revision: 4);
        $repository->seed($definition);

        $result = $this->handler($repository)->handle(
            $this->input($definition, true),
            $this->context(),
        );

        self::assertSame(4, $result['previous_revision']);
        self::assertSame(5, $result['revision']);
        self::assertSame('published', $result['status']);
        self::assertTrue($result['enabled']);
        self::assertTrue($result['changed']);
        self::assertSame('status_changed', $result['result_code']);
        self::assertSame(1, $repository->saveCalls);
    }

    public function testSameTargetIsNoOpWithoutSaveOrRevisionChange(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition(status: DefinitionStatus::Published, revision: 6);
        $repository->seed($definition);

        $result = $this->handler($repository)->handle(
            $this->input($definition, true),
            $this->context(),
        );

        self::assertSame([
            'definition_id' => $definition->id,
            'previous_revision' => 6,
            'revision' => 6,
            'status' => 'published',
            'enabled' => true,
            'changed' => false,
            'result_code' => 'already_target_status',
        ], $result);
        self::assertSame(0, $repository->saveCalls);
    }

    public function testStaleReplayAfterChangedMutationFailsClosed(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition();
        $repository->seed($definition);
        $handler = $this->handler($repository);
        $input = $this->input($definition, false);

        $handler->handle($input, $this->context());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('revision conflict');
        $handler->handle($input, $this->context());
    }

    public function testExecutionRereadCatchesRevisionDriftAfterAuthorization(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition();
        $repository->seed($definition);
        $handler = $this->handler($repository);
        $input = $this->input($definition, false);

        self::assertTrue($handler->authorizeInput($input, $this->context())->allowed);

        $drifted = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: DefinitionStatus::Disabled,
            payload: $definition->payload,
            revision: 2,
            dependencies: $definition->dependencies,
            checksum: $definition->checksum,
        );
        $repository->save($drifted);
        $repository->saveCalls = 0;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('revision conflict');
        $handler->handle($input, $this->context());
    }

    public function testPersistenceFailureDoesNotFabricateSuccessOrLeakStorageDetail(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition();
        $repository->seed($definition);
        $repository->failSaves = true;

        try {
            $this->handler($repository)->handle(
                $this->input($definition, false),
                $this->context(),
            );
            self::fail('Expected persistence failure to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertSame('Forms & Workflows set-enabled persistence failed.', $exception->getMessage());
            self::assertStringNotContainsString('private-storage-detail', $exception->getMessage());
        }

        $current = $repository->get($definition->id);
        self::assertInstanceOf(Definition::class, $current);
        self::assertSame(DefinitionStatus::Published, $current->status);
        self::assertSame(1, $current->revision);
    }

    public function testHandleRejectsInvalidInputBeforeRepositoryMutation(): void
    {
        $repository = new SetEnabledCountingRepository();
        $definition = $this->definition();
        $repository->seed($definition);

        $this->expectException(InvalidArgumentException::class);
        $this->handler($repository)->handle([
            'definition_id' => $definition->id,
            'expected_revision' => 1,
            'enabled' => 'false',
        ], $this->context());
    }

    private function handler(
        DefinitionRepositoryInterface $repository,
    ): FormsWorkflowsSetEnabledAbilityHandler {
        return new FormsWorkflowsSetEnabledAbilityHandler(
            $repository,
            new AbilityInputValidator(),
        );
    }

    /** @return array{definition_id:string,expected_revision:int,enabled:bool} */
    private function input(Definition $definition, bool $enabled): array
    {
        return [
            'definition_id' => $definition->id,
            'expected_revision' => $definition->revision,
            'enabled' => $enabled,
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    private function definition(
        DefinitionStatus $status = DefinitionStatus::Published,
        int $revision = 1,
        int $ownerSurfaceId = FormWorkflowDefinition::OWNER_SURFACE_ID,
        string $type = FormWorkflowDefinition::TYPE,
    ): Definition {
        $base = new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'customer-intake',
            type: $type,
            schemaVersion: 2,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: [
                'form' => ['title' => 'Customer Intake'],
                'workflow' => ['steps' => ['review']],
            ],
            revision: $revision,
            dependencies: ['22222222-2222-4222-8222-222222222222'],
        );

        return new Definition(
            id: $base->id,
            slug: $base->slug,
            type: $base->type,
            schemaVersion: $base->schemaVersion,
            ownerSurfaceId: $base->ownerSurfaceId,
            status: $base->status,
            payload: $base->payload,
            revision: $base->revision,
            dependencies: $base->dependencies,
            checksum: $base->computedChecksum(),
        );
    }
}

final class SetEnabledCountingRepository implements DefinitionRepositoryInterface
{
    private InMemoryDefinitionRepository $inner;

    public int $saveCalls = 0;
    public bool $failSaves = false;

    public function __construct()
    {
        $this->inner = new InMemoryDefinitionRepository();
    }

    public function seed(Definition $definition): void
    {
        $this->inner->save($definition);
        $this->saveCalls = 0;
    }

    public function save(Definition $definition): void
    {
        ++$this->saveCalls;

        if ($this->failSaves) {
            throw new RuntimeException('private-storage-detail');
        }

        $this->inner->save($definition);
    }

    public function get(string $id): ?Definition
    {
        return $this->inner->get($id);
    }

    public function byType(string $type): array
    {
        return $this->inner->byType($type);
    }

    public function dependentsOf(string $id): array
    {
        return $this->inner->dependentsOf($id);
    }
}
