<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use Throwable;
use WPEssential\Modules\FormsWorkflows\FormWorkflowDefinition;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Definitions\Definition;

final readonly class DashboardWidgetActionInputCompiler
{
    /** @var null|Closure */
    private ?Closure $abilityResolver;

    public function __construct(
        private AbilityInputValidator $validator,
        ?callable $abilityResolver,
    ) {
        $this->abilityResolver = $abilityResolver !== null
            ? Closure::fromCallable($abilityResolver)
            : null;
    }

    /** @param array<string,mixed> $widget */
    public function compile(Definition $definition, array $widget): ?DashboardWidgetActionInputDescriptor
    {
        if (!array_key_exists('action', $widget)) {
            return null;
        }

        $action = $widget['action'];
        if (!is_array($action) || ($action !== [] && array_is_list($action))) {
            throw new InvalidArgumentException('Dashboard Widget action metadata must be an object/map.');
        }

        if (!array_key_exists('input', $action)) {
            return null;
        }

        if (!array_key_exists('ability_id', $action)) {
            throw new InvalidArgumentException('Dashboard Widget action.input requires action.ability_id.');
        }

        $input = $action['input'];
        if (!is_array($input) || ($input !== [] && array_is_list($input))) {
            throw new InvalidArgumentException('Dashboard Widget action.input must be an object/map.');
        }

        if ($input === []) {
            return null;
        }

        if (count($input) > DashboardWidgetActionInputDescriptor::MAX_BINDINGS) {
            throw new InvalidArgumentException('Dashboard Widget action.input exceeds the bounded binding limit.');
        }

        $encoded = json_encode($input, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if (!is_string($encoded) || strlen($encoded) > DashboardWidgetActionInputDescriptor::MAX_ENCODED_BYTES) {
            throw new InvalidArgumentException('Dashboard Widget action.input exceeds the bounded encoded size.');
        }

        $abilityId = $action['ability_id'];
        if (!is_string($abilityId) || preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $abilityId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget action.ability_id must use the canonical ability name shape.');
        }

        $record = $this->resolveAbility($abilityId);
        $schema = $this->validInputSchema($record, $abilityId);
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        if (!is_array($properties) || ($properties !== [] && array_is_list($properties)) || !is_array($required)) {
            throw new InvalidArgumentException('Dashboard Widget action Ability input schema is malformed.');
        }

        foreach ($required as $requiredKey) {
            if (!is_string($requiredKey) || !array_key_exists($requiredKey, $input)) {
                throw new InvalidArgumentException('Dashboard Widget action.input must bind every required Ability input property.');
            }
        }

        $literal = [];
        $dynamic = [];

        foreach ($input as $key => $envelope) {
            $this->assertBindingKey($key);
            $this->assertNonSensitiveKey($key);

            if (!array_key_exists($key, $properties)) {
                throw new InvalidArgumentException('Dashboard Widget action.input contains an unknown Ability input property.');
            }

            $propertySchema = $properties[$key];
            if (!is_array($propertySchema)) {
                throw new InvalidArgumentException('Dashboard Widget action Ability property schema is malformed.');
            }

            if (!is_array($envelope) || array_is_list($envelope)) {
                throw new InvalidArgumentException('Dashboard Widget action.input binding must be an object/map.');
            }

            $source = $envelope['source'] ?? null;
            if ($source === 'literal') {
                $this->assertKnownKeys($envelope, ['source', 'value']);
                if (!array_key_exists('value', $envelope)) {
                    throw new InvalidArgumentException('Dashboard Widget literal action-input binding requires value.');
                }

                $synthetic = [
                    'type' => 'object',
                    'properties' => [$key => $propertySchema],
                    'required' => [$key],
                    'additionalProperties' => false,
                ];
                $result = $this->validator->validateInput($synthetic, [$key => $envelope['value']]);
                if (!$result->valid) {
                    throw new InvalidArgumentException('Dashboard Widget literal action-input binding does not satisfy the Ability input schema.');
                }

                $literal[$key] = $envelope['value'];
                continue;
            }

            if ($source === 'dynamic') {
                $this->assertKnownKeys($envelope, ['source', 'source_ref', 'value_ref', 'resource']);
                $this->assertDynamicPropertySchema($propertySchema);

                $sourceRef = $this->semanticReference($envelope['source_ref'] ?? null);
                $valueRef = $this->semanticReference($envelope['value_ref'] ?? null);
                $resource = $envelope['resource'] ?? null;
                if (!is_string($resource) || !in_array($resource, ['site', 'user', 'network'], true)) {
                    throw new InvalidArgumentException('Dashboard Widget Dynamic action-input resource must be site, user or network.');
                }

                $dynamic[$key] = [
                    'source_ref' => $sourceRef,
                    'value_ref' => $valueRef,
                    'resource' => $resource,
                ];
                continue;
            }

            throw new InvalidArgumentException('Dashboard Widget action-input source must be literal or dynamic.');
        }

        ksort($literal, SORT_STRING);
        ksort($dynamic, SORT_STRING);

        return new DashboardWidgetActionInputDescriptor(
            definitionId: $definition->id,
            definitionRevision: $definition->revision,
            abilityId: $abilityId,
            literalBindings: $literal,
            dynamicBindings: $dynamic,
        );
    }

    /** @return array<string,mixed> */
    private function resolveAbility(string $abilityId): array
    {
        if ($this->abilityResolver === null) {
            throw new InvalidArgumentException('Dashboard Widget action input requires the canonical Ability Registry.');
        }

        try {
            $record = ($this->abilityResolver)($abilityId);
        } catch (Throwable) {
            throw new InvalidArgumentException('Dashboard Widget action Ability could not be resolved.');
        }

        if (!is_array($record)) {
            throw new InvalidArgumentException('Dashboard Widget action Ability could not be resolved.');
        }

        return $record;
    }

    /**
     * @param array<string,mixed> $record
     * @return array<string,mixed>
     */
    private function validInputSchema(array $record, string $abilityId): array
    {
        $schema = $record['input_schema'] ?? null;
        if (
            ($record['name'] ?? null) !== $abilityId
            || ($record['owner_surface_id'] ?? null) !== FormWorkflowDefinition::OWNER_SURFACE_ID
            || ($record['mutates'] ?? null) !== true
            || ($record['ui_allowed'] ?? null) !== true
            || !is_array($schema)
            || $schema === []
        ) {
            throw new InvalidArgumentException('Dashboard Widget action input requires a non-empty mutating Forms & Workflows UI Ability schema.');
        }

        $schemaResult = $this->validator->validateSchema($schema);
        if (!$schemaResult->valid || ($schema['type'] ?? null) !== 'object' || (($schema['additionalProperties'] ?? false) !== false)) {
            throw new InvalidArgumentException('Dashboard Widget action Ability input schema is unsupported.');
        }

        return $schema;
    }

    /** @param array<string,mixed> $propertySchema */
    private function assertDynamicPropertySchema(array $propertySchema): void
    {
        $type = $propertySchema['type'] ?? null;
        if (in_array($type, ['string', 'integer', 'number', 'boolean'], true)) {
            return;
        }

        if ($type === 'array') {
            $items = $propertySchema['items'] ?? null;
            if (
                is_array($items)
                && in_array($items['type'] ?? null, ['string', 'integer', 'number', 'boolean'], true)
            ) {
                return;
            }
        }

        throw new InvalidArgumentException('Dashboard Widget Dynamic action-input binding requires a scalar or scalar-list Ability property.');
    }

    private function semanticReference(mixed $value): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 160 || preg_match('/^[a-zA-Z0-9_.:-]+$/', $value) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget Dynamic action-input reference must be a bounded semantic identifier.');
        }

        return $value;
    }

    private function assertBindingKey(mixed $key): void
    {
        if (!is_string($key) || preg_match('/^[A-Za-z_][A-Za-z0-9_-]{0,127}$/', $key) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget action-input binding key must be a bounded top-level semantic identifier.');
        }
    }

    private function assertNonSensitiveKey(string $key): void
    {
        $normalized = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $key));
        foreach ([
            'password',
            'passwd',
            'secret',
            'token',
            'apikey',
            'privatekey',
            'authorization',
            'cookie',
            'credential',
            'cardnumber',
            'cvv',
            'cvc',
        ] as $fragment) {
            if (str_contains($normalized, $fragment)) {
                throw new InvalidArgumentException('Dashboard Widget action-input credential-bearing properties are unsupported in V1.');
            }
        }
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $known
     */
    private function assertKnownKeys(array $value, array $known): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $known, true)) {
                throw new InvalidArgumentException('Dashboard Widget action-input binding contains an unsupported key.');
            }
        }
    }
}
