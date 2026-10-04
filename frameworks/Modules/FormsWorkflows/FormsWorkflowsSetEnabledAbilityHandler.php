<?php

declare(strict_types=1);

namespace WPEssential\Modules\FormsWorkflows;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class FormsWorkflowsSetEnabledAbilityHandler implements InputAuthorizingAbilityHandlerInterface
{
    public const DENY_INVALID_INPUT = 'forms_workflows_set_enabled_invalid_input';
    public const DENY_NOT_FOUND = 'forms_workflows_set_enabled_not_found';
    public const DENY_RESOURCE_UNAVAILABLE = 'forms_workflows_set_enabled_resource_unavailable';
    public const DENY_WRONG_OWNER = 'forms_workflows_set_enabled_wrong_owner';
    public const DENY_INVALID_STATE = 'forms_workflows_set_enabled_invalid_state';
    public const DENY_REVISION_CONFLICT = 'forms_workflows_set_enabled_revision_conflict';

    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private AbilityInputValidator $validator,
    ) {
    }

    /** @return array<string,mixed> */
    public static function inputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['definition_id', 'expected_revision', 'enabled'],
            'properties' => [
                'definition_id' => [
                    'type' => 'string',
                    'minLength' => 36,
                    'maxLength' => 36,
                ],
                'expected_revision' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'enabled' => [
                    'type' => 'boolean',
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    /** @return array<string,mixed> */
    public static function outputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => [
                'definition_id',
                'previous_revision',
                'revision',
                'status',
                'enabled',
                'changed',
                'result_code',
            ],
            'properties' => [
                'definition_id' => ['type' => 'string'],
                'previous_revision' => ['type' => 'integer'],
                'revision' => ['type' => 'integer'],
                'status' => [
                    'type' => 'string',
                    'enum' => [
                        DefinitionStatus::Published->value,
                        DefinitionStatus::Disabled->value,
                    ],
                ],
                'enabled' => ['type' => 'boolean'],
                'changed' => ['type' => 'boolean'],
                'result_code' => [
                    'type' => 'string',
                    'enum' => ['status_changed', 'already_target_status'],
                ],
            ],
            'additionalProperties' => false,
        ];
    }

    public function authorizeInput(array $input, ExecutionContext $context): PolicyDecision
    {
        $validated = $this->validatedInput($input);
        if ($validated === null) {
            return PolicyDecision::deny(self::DENY_INVALID_INPUT);
        }

        try {
            $definition = $this->definitions->get($validated['definition_id']);
        } catch (Throwable) {
            return PolicyDecision::deny(self::DENY_RESOURCE_UNAVAILABLE);
        }

        if (!$definition instanceof Definition) {
            return PolicyDecision::deny(self::DENY_NOT_FOUND);
        }

        try {
            FormWorkflowDefinition::assertOwned($definition);
        } catch (InvalidArgumentException) {
            return PolicyDecision::deny(self::DENY_WRONG_OWNER);
        }

        if (!$this->isMutableLifecycleState($definition->status)) {
            return PolicyDecision::deny(self::DENY_INVALID_STATE);
        }

        if ($definition->revision !== $validated['expected_revision']) {
            return PolicyDecision::deny(self::DENY_REVISION_CONFLICT);
        }

        return PolicyDecision::allow('forms_workflows_set_enabled_authorized');
    }

    /** @return array<string,mixed> */
    public function handle(array $input, ExecutionContext $context): array
    {
        $validated = $this->validatedInput($input);
        if ($validated === null) {
            throw new InvalidArgumentException('Forms & Workflows set-enabled input is invalid.');
        }

        try {
            $definition = $this->definitions->get($validated['definition_id']);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Forms & Workflows set-enabled resource is unavailable.',
                0,
                $exception,
            );
        }

        if (!$definition instanceof Definition) {
            throw new RuntimeException('Forms & Workflows set-enabled definition was not found.');
        }

        try {
            FormWorkflowDefinition::assertOwned($definition);
        } catch (InvalidArgumentException $exception) {
            throw new RuntimeException(
                'Forms & Workflows set-enabled definition ownership is invalid.',
                0,
                $exception,
            );
        }

        if (!$this->isMutableLifecycleState($definition->status)) {
            throw new RuntimeException('Forms & Workflows set-enabled lifecycle state is not mutable.');
        }

        if ($definition->revision !== $validated['expected_revision']) {
            throw new RuntimeException('Forms & Workflows set-enabled revision conflict.');
        }

        $target = $validated['enabled']
            ? DefinitionStatus::Published
            : DefinitionStatus::Disabled;

        if ($definition->status === $target) {
            return $this->result(
                definition: $definition,
                previousRevision: $definition->revision,
                changed: false,
                resultCode: 'already_target_status',
            );
        }

        $next = new Definition(
            id: $definition->id,
            slug: $definition->slug,
            type: $definition->type,
            schemaVersion: $definition->schemaVersion,
            ownerSurfaceId: $definition->ownerSurfaceId,
            status: $target,
            payload: $definition->payload,
            revision: $definition->revision + 1,
            dependencies: $definition->dependencies,
            checksum: $definition->checksum,
        );

        try {
            $this->definitions->save($next);
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'Forms & Workflows set-enabled persistence failed.',
                0,
                $exception,
            );
        }

        return $this->result(
            definition: $next,
            previousRevision: $definition->revision,
            changed: true,
            resultCode: 'status_changed',
        );
    }

    /**
     * @param array<string,mixed> $input
     * @return array{definition_id:string,expected_revision:int,enabled:bool}|null
     */
    private function validatedInput(array $input): ?array
    {
        $result = $this->validator->validateInput(self::inputSchema(), $input);
        if (!$result->valid) {
            return null;
        }

        $definitionId = $input['definition_id'] ?? null;
        $expectedRevision = $input['expected_revision'] ?? null;
        $enabled = $input['enabled'] ?? null;

        if (
            !is_string($definitionId)
            || preg_match(self::UUID_PATTERN, $definitionId) !== 1
            || !is_int($expectedRevision)
            || $expectedRevision < 1
            || !is_bool($enabled)
        ) {
            return null;
        }

        return [
            'definition_id' => $definitionId,
            'expected_revision' => $expectedRevision,
            'enabled' => $enabled,
        ];
    }

    private function isMutableLifecycleState(DefinitionStatus $status): bool
    {
        return $status === DefinitionStatus::Published
            || $status === DefinitionStatus::Disabled;
    }

    /** @return array<string,mixed> */
    private function result(
        Definition $definition,
        int $previousRevision,
        bool $changed,
        string $resultCode,
    ): array {
        return [
            'definition_id' => $definition->id,
            'previous_revision' => $previousRevision,
            'revision' => $definition->revision,
            'status' => $definition->status->value,
            'enabled' => $definition->status === DefinitionStatus::Published,
            'changed' => $changed,
            'result_code' => $resultCode,
        ];
    }
}
