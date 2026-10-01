<?php

declare(strict_types=1);

namespace WPEssential\Platform\Abilities\InputValidation;

if (!defined('ABSPATH')) {
    exit;
}

use JsonException;

final class AbilityInputValidator
{
    public const MAX_SCHEMA_DEPTH = 8;
    public const MAX_SCHEMA_NODES = 256;
    public const MAX_OBJECT_PROPERTIES = 64;
    public const MAX_REQUIRED_ENTRIES = 64;
    public const MAX_ENUM_VALUES = 64;
    public const MAX_ARRAY_ITEMS = 100;
    public const MAX_STRING_BYTES = 4096;
    public const MAX_INPUT_BYTES = 32768;
    public const MAX_PATH_BYTES = 512;

    /** @var list<string> */
    private const SUPPORTED_TYPES = [
        'object',
        'array',
        'string',
        'integer',
        'number',
        'boolean',
        'null',
    ];

    /** @var list<string> */
    private const SUPPORTED_KEYWORDS = [
        'type',
        'required',
        'properties',
        'additionalProperties',
        'items',
        'enum',
        'minLength',
        'maxLength',
        'minimum',
        'maximum',
        'minItems',
        'maxItems',
    ];

    /** @param array<string,mixed> $schema */
    public function validateSchema(array $schema): AbilityInputValidationResult
    {
        $nodes = 0;

        return $this->validateSchemaNode($schema, '$', 1, $nodes, true);
    }

    /** @param array<string,mixed> $schema */
    public function validateInput(array $schema, mixed $input): AbilityInputValidationResult
    {
        $schemaResult = $this->validateSchema($schema);
        if (!$schemaResult->valid) {
            return $schemaResult;
        }

        $envelopeResult = $this->validateRuntimeEnvelope($input, '$', 1);
        if (!$envelopeResult->valid) {
            return $envelopeResult;
        }

        try {
            $encoded = json_encode(
                $input,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException) {
            return AbilityInputValidationResult::invalid('unsupported_runtime_value', '$');
        }

        if (!is_string($encoded)) {
            return AbilityInputValidationResult::invalid('unsupported_runtime_value', '$');
        }

        if (strlen($encoded) > self::MAX_INPUT_BYTES) {
            return AbilityInputValidationResult::invalid('input_too_large', '$');
        }

        return $this->validateValue($schema, $input, '$');
    }

    private function validateSchemaNode(
        mixed $schema,
        string $path,
        int $depth,
        int &$nodes,
        bool $root = false,
    ): AbilityInputValidationResult {
        if (!is_array($schema) || ($schema !== [] && array_is_list($schema))) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        ++$nodes;
        if ($depth > self::MAX_SCHEMA_DEPTH || $nodes > self::MAX_SCHEMA_NODES) {
            return AbilityInputValidationResult::invalid('complexity_exceeded', $path);
        }

        foreach ($schema as $keyword => $_value) {
            if (!is_string($keyword)) {
                return AbilityInputValidationResult::invalid('schema_invalid', $path);
            }
            if (!in_array($keyword, self::SUPPORTED_KEYWORDS, true)) {
                return AbilityInputValidationResult::invalid(
                    'unsupported_keyword',
                    $this->childPath($path, $keyword) ?? '$',
                );
            }
        }

        $type = $schema['type'] ?? null;
        if (!is_string($type) || !in_array($type, self::SUPPORTED_TYPES, true)) {
            return AbilityInputValidationResult::invalid(
                'unsupported_type',
                $this->childPath($path, 'type') ?? '$',
            );
        }

        if ($root && $type !== 'object') {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        foreach (array_keys($schema) as $keyword) {
            if (!is_string($keyword)) {
                return AbilityInputValidationResult::invalid('schema_invalid', $path);
            }
            if (!in_array($keyword, $this->keywordsForType($type), true)) {
                return AbilityInputValidationResult::invalid(
                    'schema_invalid',
                    $this->childPath($path, $keyword) ?? '$',
                );
            }
        }

        return match ($type) {
            'object' => $this->validateObjectSchema($schema, $path, $depth, $nodes),
            'array' => $this->validateArraySchema($schema, $path, $depth, $nodes),
            'string' => $this->validateStringSchema($schema, $path),
            'integer', 'number' => $this->validateNumberSchema($schema, $type, $path),
            'boolean', 'null' => $this->validateEnumSchema($schema, $type, $path),
            default => AbilityInputValidationResult::invalid('unsupported_type', $path),
        };
    }

    /** @param array<string,mixed> $schema */
    private function validateObjectSchema(
        array $schema,
        string $path,
        int $depth,
        int &$nodes,
    ): AbilityInputValidationResult {
        $properties = $schema['properties'] ?? [];
        if (!is_array($properties) || ($properties !== [] && array_is_list($properties))) {
            return AbilityInputValidationResult::invalid(
                'schema_invalid',
                $this->childPath($path, 'properties') ?? '$',
            );
        }

        if (count($properties) > self::MAX_OBJECT_PROPERTIES) {
            return AbilityInputValidationResult::invalid('complexity_exceeded', $path);
        }

        foreach ($properties as $name => $propertySchema) {
            if (
                !is_string($name)
                || $name === ''
                || preg_match('//u', $name) !== 1
            ) {
                return AbilityInputValidationResult::invalid('schema_invalid', $path);
            }

            $propertiesPath = $this->childPath($path, 'properties');
            if ($propertiesPath === null) {
                return AbilityInputValidationResult::invalid('complexity_exceeded', '$');
            }

            $propertyPath = $this->childPath($propertiesPath, $name);
            if ($propertyPath === null) {
                return AbilityInputValidationResult::invalid('complexity_exceeded', '$');
            }

            $result = $this->validateSchemaNode(
                $propertySchema,
                $propertyPath,
                $depth + 1,
                $nodes,
            );
            if (!$result->valid) {
                return $result;
            }
        }

        $required = $schema['required'] ?? [];
        if (
            !is_array($required)
            || ($required !== [] && !array_is_list($required))
            || count($required) > self::MAX_REQUIRED_ENTRIES
        ) {
            return AbilityInputValidationResult::invalid(
                'schema_invalid',
                $this->childPath($path, 'required') ?? '$',
            );
        }

        $seen = [];
        foreach ($required as $name) {
            if (
                !is_string($name)
                || $name === ''
                || isset($seen[$name])
                || !array_key_exists($name, $properties)
            ) {
                return AbilityInputValidationResult::invalid(
                    'schema_invalid',
                    $this->childPath($path, 'required') ?? '$',
                );
            }
            $seen[$name] = true;
        }

        if (
            array_key_exists('additionalProperties', $schema)
            && !is_bool($schema['additionalProperties'])
        ) {
            return AbilityInputValidationResult::invalid(
                'schema_invalid',
                $this->childPath($path, 'additionalProperties') ?? '$',
            );
        }

        return AbilityInputValidationResult::valid();
    }

    /** @param array<string,mixed> $schema */
    private function validateArraySchema(
        array $schema,
        string $path,
        int $depth,
        int &$nodes,
    ): AbilityInputValidationResult {
        if (!array_key_exists('items', $schema)) {
            return AbilityInputValidationResult::invalid(
                'schema_invalid',
                $this->childPath($path, 'items') ?? '$',
            );
        }

        $itemsPath = $this->childPath($path, 'items');
        if ($itemsPath === null) {
            return AbilityInputValidationResult::invalid('complexity_exceeded', '$');
        }

        $result = $this->validateSchemaNode(
            $schema['items'],
            $itemsPath,
            $depth + 1,
            $nodes,
        );
        if (!$result->valid) {
            return $result;
        }

        $min = $schema['minItems'] ?? 0;
        $max = $schema['maxItems'] ?? self::MAX_ARRAY_ITEMS;
        if (
            !is_int($min)
            || !is_int($max)
            || $min < 0
            || $max < 0
            || $min > $max
            || $min > self::MAX_ARRAY_ITEMS
            || $max > self::MAX_ARRAY_ITEMS
        ) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        return AbilityInputValidationResult::valid();
    }

    /** @param array<string,mixed> $schema */
    private function validateStringSchema(array $schema, string $path): AbilityInputValidationResult
    {
        $min = $schema['minLength'] ?? 0;
        $max = $schema['maxLength'] ?? self::MAX_STRING_BYTES;
        if (
            !is_int($min)
            || !is_int($max)
            || $min < 0
            || $max < 0
            || $min > $max
            || $min > self::MAX_STRING_BYTES
            || $max > self::MAX_STRING_BYTES
        ) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        return $this->validateEnumSchema($schema, 'string', $path);
    }

    /** @param array<string,mixed> $schema */
    private function validateNumberSchema(
        array $schema,
        string $type,
        string $path,
    ): AbilityInputValidationResult {
        $minimum = $schema['minimum'] ?? null;
        $maximum = $schema['maximum'] ?? null;

        if ($minimum !== null && !$this->isFiniteNumber($minimum)) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }
        if ($maximum !== null && !$this->isFiniteNumber($maximum)) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }
        if ($minimum !== null && $maximum !== null && $minimum > $maximum) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        return $this->validateEnumSchema($schema, $type, $path);
    }

    /** @param array<string,mixed> $schema */
    private function validateEnumSchema(
        array $schema,
        string $type,
        string $path,
    ): AbilityInputValidationResult {
        if (!array_key_exists('enum', $schema)) {
            return AbilityInputValidationResult::valid();
        }

        $enum = $schema['enum'];
        if (
            !is_array($enum)
            || !array_is_list($enum)
            || $enum === []
            || count($enum) > self::MAX_ENUM_VALUES
        ) {
            return AbilityInputValidationResult::invalid(
                'schema_invalid',
                $this->childPath($path, 'enum') ?? '$',
            );
        }

        foreach ($enum as $index => $value) {
            if (!$this->enumValueMatchesType($value, $type)) {
                return AbilityInputValidationResult::invalid(
                    'schema_invalid',
                    $this->indexPath($this->childPath($path, 'enum') ?? '$', $index),
                );
            }

            for ($previous = 0; $previous < $index; ++$previous) {
                if ($enum[$previous] === $value) {
                    return AbilityInputValidationResult::invalid(
                        'schema_invalid',
                        $this->childPath($path, 'enum') ?? '$',
                    );
                }
            }
        }

        return AbilityInputValidationResult::valid();
    }

    private function validateRuntimeEnvelope(
        mixed $value,
        string $path,
        int $depth,
    ): AbilityInputValidationResult {
        if ($depth > self::MAX_SCHEMA_DEPTH) {
            return AbilityInputValidationResult::invalid('input_depth_exceeded', $path);
        }

        if (is_string($value)) {
            if (strlen($value) > self::MAX_STRING_BYTES || preg_match('//u', $value) !== 1) {
                return AbilityInputValidationResult::invalid('bound_violation', $path);
            }

            return AbilityInputValidationResult::valid();
        }

        if (is_int($value) || is_bool($value) || $value === null) {
            return AbilityInputValidationResult::valid();
        }

        if (is_float($value)) {
            return is_finite($value)
                ? AbilityInputValidationResult::valid()
                : AbilityInputValidationResult::invalid('unsupported_runtime_value', $path);
        }

        if (!is_array($value)) {
            return AbilityInputValidationResult::invalid('unsupported_runtime_value', $path);
        }

        if (array_is_list($value)) {
            if (count($value) > self::MAX_ARRAY_ITEMS) {
                return AbilityInputValidationResult::invalid('bound_violation', $path);
            }

            foreach ($value as $index => $item) {
                $result = $this->validateRuntimeEnvelope(
                    $item,
                    $this->indexPath($path, $index),
                    $depth + 1,
                );
                if (!$result->valid) {
                    return $result;
                }
            }

            return AbilityInputValidationResult::valid();
        }

        foreach ($value as $key => $item) {
            if (!is_string($key)) {
                return AbilityInputValidationResult::invalid('unsupported_runtime_value', $path);
            }

            $childPath = $this->childPath($path, $key);
            if ($childPath === null) {
                return AbilityInputValidationResult::invalid('input_depth_exceeded', '$');
            }

            $result = $this->validateRuntimeEnvelope($item, $childPath, $depth + 1);
            if (!$result->valid) {
                return $result;
            }
        }

        return AbilityInputValidationResult::valid();
    }

    /** @param array<string,mixed> $schema */
    private function validateValue(
        array $schema,
        mixed $value,
        string $path,
    ): AbilityInputValidationResult {
        $type = $schema['type'] ?? null;
        if (!is_string($type) || !in_array($type, self::SUPPORTED_TYPES, true)) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        if (!$this->runtimeValueMatchesType($value, $type)) {
            return AbilityInputValidationResult::invalid('input_type_mismatch', $path);
        }

        if (array_key_exists('enum', $schema)) {
            $enum = $schema['enum'];
            if (!is_array($enum) || !array_is_list($enum)) {
                return AbilityInputValidationResult::invalid('schema_invalid', $path);
            }

            $matched = false;
            foreach ($enum as $enumValue) {
                if ($enumValue === $value) {
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                return AbilityInputValidationResult::invalid('enum_mismatch', $path);
            }
        }

        return match ($type) {
            'object' => $this->validateObjectValue($schema, $value, $path),
            'array' => $this->validateArrayValue($schema, $value, $path),
            'string' => $this->validateStringValue($schema, $value, $path),
            'integer', 'number' => $this->validateNumberValue($schema, $value, $path),
            'boolean', 'null' => AbilityInputValidationResult::valid(),
            default => AbilityInputValidationResult::invalid('input_type_mismatch', $path),
        };
    }

    /** @param array<string,mixed> $schema */
    private function validateObjectValue(
        array $schema,
        mixed $value,
        string $path,
    ): AbilityInputValidationResult {
        if (!$this->isObjectArray($value)) {
            return AbilityInputValidationResult::invalid('input_type_mismatch', $path);
        }

        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];
        $allowAdditional = $schema['additionalProperties'] ?? false;

        if (!is_array($properties) || !is_array($required) || !is_bool($allowAdditional)) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        foreach ($required as $name) {
            if (!is_string($name)) {
                return AbilityInputValidationResult::invalid('schema_invalid', $path);
            }
            if (!array_key_exists($name, $value)) {
                return AbilityInputValidationResult::invalid(
                    'required_missing',
                    $this->childPath($path, $name) ?? '$',
                );
            }
        }

        foreach ($value as $name => $item) {
            if (!is_string($name)) {
                return AbilityInputValidationResult::invalid('input_type_mismatch', $path);
            }

            if (!array_key_exists($name, $properties)) {
                if (!$allowAdditional) {
                    return AbilityInputValidationResult::invalid(
                        'additional_property',
                        $this->childPath($path, $name) ?? '$',
                    );
                }
                continue;
            }

            $childPath = $this->childPath($path, $name);
            if ($childPath === null) {
                return AbilityInputValidationResult::invalid('input_depth_exceeded', '$');
            }

            $propertySchema = $properties[$name];
            if (!is_array($propertySchema)) {
                return AbilityInputValidationResult::invalid('schema_invalid', $childPath);
            }

            $result = $this->validateValue($propertySchema, $item, $childPath);
            if (!$result->valid) {
                return $result;
            }
        }

        return AbilityInputValidationResult::valid();
    }

    /** @param array<string,mixed> $schema */
    private function validateArrayValue(
        array $schema,
        mixed $value,
        string $path,
    ): AbilityInputValidationResult {
        if (!is_array($value) || !array_is_list($value)) {
            return AbilityInputValidationResult::invalid('input_type_mismatch', $path);
        }

        $itemsSchema = $schema['items'] ?? null;
        $min = $schema['minItems'] ?? 0;
        $max = $schema['maxItems'] ?? self::MAX_ARRAY_ITEMS;
        if (!is_array($itemsSchema) || !is_int($min) || !is_int($max)) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        $count = count($value);
        if ($count < $min || $count > $max) {
            return AbilityInputValidationResult::invalid('bound_violation', $path);
        }

        foreach ($value as $index => $item) {
            $result = $this->validateValue(
                $itemsSchema,
                $item,
                $this->indexPath($path, $index),
            );
            if (!$result->valid) {
                return $result;
            }
        }

        return AbilityInputValidationResult::valid();
    }

    /** @param array<string,mixed> $schema */
    private function validateStringValue(
        array $schema,
        mixed $value,
        string $path,
    ): AbilityInputValidationResult {
        if (!is_string($value)) {
            return AbilityInputValidationResult::invalid('input_type_mismatch', $path);
        }

        $min = $schema['minLength'] ?? 0;
        $max = $schema['maxLength'] ?? self::MAX_STRING_BYTES;
        if (!is_int($min) || !is_int($max)) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        $length = strlen($value);

        return ($length < $min || $length > $max)
            ? AbilityInputValidationResult::invalid('bound_violation', $path)
            : AbilityInputValidationResult::valid();
    }

    /** @param array<string,mixed> $schema */
    private function validateNumberValue(
        array $schema,
        mixed $value,
        string $path,
    ): AbilityInputValidationResult {
        if (!is_int($value) && !(is_float($value) && is_finite($value))) {
            return AbilityInputValidationResult::invalid('input_type_mismatch', $path);
        }

        $minimum = $schema['minimum'] ?? null;
        $maximum = $schema['maximum'] ?? null;
        if (
            ($minimum !== null && !$this->isFiniteNumber($minimum))
            || ($maximum !== null && !$this->isFiniteNumber($maximum))
        ) {
            return AbilityInputValidationResult::invalid('schema_invalid', $path);
        }

        if (($minimum !== null && $value < $minimum) || ($maximum !== null && $value > $maximum)) {
            return AbilityInputValidationResult::invalid('bound_violation', $path);
        }

        return AbilityInputValidationResult::valid();
    }

    /** @return list<string> */
    private function keywordsForType(string $type): array
    {
        return match ($type) {
            'object' => ['type', 'required', 'properties', 'additionalProperties'],
            'array' => ['type', 'items', 'minItems', 'maxItems'],
            'string' => ['type', 'enum', 'minLength', 'maxLength'],
            'integer', 'number' => ['type', 'enum', 'minimum', 'maximum'],
            'boolean', 'null' => ['type', 'enum'],
            default => ['type'],
        };
    }

    private function runtimeValueMatchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'object' => $this->isObjectArray($value),
            'array' => is_array($value) && array_is_list($value),
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || (is_float($value) && is_finite($value)),
            'boolean' => is_bool($value),
            'null' => $value === null,
            default => false,
        };
    }

    private function isObjectArray(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if ($value === []) {
            return true;
        }

        if (array_is_list($value)) {
            return false;
        }

        foreach (array_keys($value) as $key) {
            if (!is_string($key)) {
                return false;
            }
        }

        return true;
    }

    private function enumValueMatchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || (is_float($value) && is_finite($value)),
            'boolean' => is_bool($value),
            'null' => $value === null,
            default => false,
        };
    }

    private function isFiniteNumber(mixed $value): bool
    {
        return is_int($value) || (is_float($value) && is_finite($value));
    }

    private function childPath(string $path, string $key): ?string
    {
        $segment = preg_match('/^[A-Za-z_][A-Za-z0-9_-]*$/', $key) === 1
            ? '.' . $key
            : '[' . rawurlencode($key) . ']';
        $candidate = $path . $segment;

        return strlen($candidate) <= self::MAX_PATH_BYTES ? $candidate : null;
    }

    private function indexPath(string $path, int $index): string
    {
        $candidate = $path . '[' . $index . ']';

        return strlen($candidate) <= self::MAX_PATH_BYTES ? $candidate : '$';
    }
}
