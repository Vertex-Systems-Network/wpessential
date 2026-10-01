<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;

final readonly class DashboardWidgetActionInputBinder
{
    /** @var Closure */
    private Closure $abilityResolver;

    public function __construct(
        private AbilityInputValidator $validator,
        private DynamicValueResolverInterface $dynamicValues,
        callable $abilityResolver,
    ) {
        $this->abilityResolver = Closure::fromCallable($abilityResolver);
    }

    /** @return array<string,mixed> */
    public function bind(
        DashboardWidgetActionInputDescriptor $descriptor,
        ExecutionContext $context,
    ): array {
        $record = $this->resolveAbility($descriptor->abilityId);
        $schema = $record['input_schema'] ?? null;

        if (
            ($record['name'] ?? null) !== $descriptor->abilityId
            || ($record['owner_surface_id'] ?? null) !== FormWorkflowDefinition::OWNER_SURFACE_ID
            || ($record['mutates'] ?? null) !== true
            || ($record['ui_allowed'] ?? null) !== true
            || !is_array($schema)
            || $schema === []
        ) {
            throw new RuntimeException('Dashboard Widget action Ability no longer matches the compiled input contract.');
        }

        $schemaResult = $this->validator->validateSchema($schema);
        if (!$schemaResult->valid || ($schema['type'] ?? null) !== 'object' || (($schema['additionalProperties'] ?? false) !== false)) {
            throw new RuntimeException('Dashboard Widget action Ability input schema is no longer supported.');
        }

        $properties = $schema['properties'] ?? null;
        if (!is_array($properties)) {
            throw new RuntimeException('Dashboard Widget action Ability input schema is malformed.');
        }

        $input = $descriptor->literalBindings;

        foreach ($descriptor->dynamicBindings as $key => $binding) {
            $propertySchema = $properties[$key] ?? null;
            if (!is_array($propertySchema) || !$this->dynamicPropertySupported($propertySchema)) {
                throw new RuntimeException('Dashboard Widget Dynamic action-input schema drifted.');
            }

            $resourceId = match ($binding['resource']) {
                'site' => $context->siteId,
                'user' => $context->principal->userId
                    ?? throw new RuntimeException('Dashboard Widget Dynamic action input requires an authenticated principal.'),
                'network' => $context->networkId
                    ?? throw new RuntimeException('Dashboard Widget Dynamic action input requires a network context.'),
                default => throw new RuntimeException('Dashboard Widget Dynamic action-input resource is unsupported.'),
            };

            try {
                $resolved = $this->dynamicValues->resolve(
                    new DynamicValueRequest(
                        sourceRef: $binding['source_ref'],
                        valueRef: $binding['value_ref'],
                        resourceType: $binding['resource'],
                        resourceId: $resourceId,
                    ),
                    $context,
                );
            } catch (Throwable) {
                throw new RuntimeException('Dashboard Widget Dynamic action-input value could not be resolved.');
            }

            if (!$resolved->resolved || $resolved->failure !== null || $resolved->value === null) {
                throw new RuntimeException('Dashboard Widget Dynamic action-input value could not be resolved.');
            }

            $input[$key] = $resolved->value;
        }

        ksort($input, SORT_STRING);

        $result = $this->validator->validateInput($schema, $input);
        if (!$result->valid) {
            throw new RuntimeException(
                sprintf(
                    'Dashboard Widget action input validation failed: %s at %s.',
                    $result->code,
                    $result->path,
                ),
            );
        }

        return $input;
    }

    /** @return array<string,mixed> */
    private function resolveAbility(string $abilityId): array
    {
        try {
            $record = ($this->abilityResolver)($abilityId);
        } catch (Throwable) {
            throw new RuntimeException('Dashboard Widget action Ability could not be resolved.');
        }

        if (!is_array($record)) {
            throw new RuntimeException('Dashboard Widget action Ability could not be resolved.');
        }

        return $record;
    }

    /** @param array<string,mixed> $propertySchema */
    private function dynamicPropertySupported(array $propertySchema): bool
    {
        $type = $propertySchema['type'] ?? null;
        if (in_array($type, ['string', 'integer', 'number', 'boolean'], true)) {
            return true;
        }

        if ($type !== 'array') {
            return false;
        }

        $items = $propertySchema['items'] ?? null;

        return is_array($items)
            && in_array($items['type'] ?? null, ['string', 'integer', 'number', 'boolean'], true);
    }
}
