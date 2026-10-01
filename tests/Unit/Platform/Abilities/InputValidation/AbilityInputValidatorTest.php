<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Abilities\InputValidation;

use PHPUnit\Framework\TestCase;
use stdClass;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;

final class AbilityInputValidatorTest extends TestCase
{
    public function testValidatesNestedBoundedInputWithoutMutation(): void
    {
        $validator = new AbilityInputValidator();
        $schema = $this->schema();
        $input = [
            'definition_id' => '11111111-1111-4111-8111-111111111111',
            'revision' => 3,
            'enabled' => true,
            'mode' => 'draft',
            'items' => [
                ['key' => 'alpha', 'weight' => 0.5],
            ],
        ];
        $before = $input;

        $schemaResult = $validator->validateSchema($schema);
        $inputResult = $validator->validateInput($schema, $input);

        self::assertTrue($schemaResult->valid);
        self::assertSame('valid', $schemaResult->code);
        self::assertTrue($inputResult->valid);
        self::assertSame('$', $inputResult->path);
        self::assertSame($before, $input);
    }

    public function testRejectsUnsupportedAndMalformedSchemasFailClosed(): void
    {
        $validator = new AbilityInputValidator();

        $cases = [
            [['type' => 'object', 'pattern' => '.*'], 'unsupported_keyword'],
            [['type' => ['object', 'null']], 'unsupported_type'],
            [['type' => 'array', 'items' => ['type' => 'string']], 'schema_invalid'],
            [['type' => 'object', 'required' => 'id'], 'schema_invalid'],
            [['type' => 'object', 'properties' => ['id' => ['type' => 'string']], 'required' => ['missing']], 'schema_invalid'],
            [['type' => 'object', 'properties' => [], 'additionalProperties' => 'false'], 'schema_invalid'],
            [['type' => 'object', 'properties' => ['items' => ['type' => 'array']]], 'schema_invalid'],
            [['type' => 'object', 'properties' => ['mode' => ['type' => 'string', 'enum' => []]]], 'schema_invalid'],
            [['type' => 'object', 'properties' => ['mode' => ['type' => 'string', 'enum' => ['x', 'x']]]], 'schema_invalid'],
            [['type' => 'object', 'properties' => ['count' => ['type' => 'integer', 'minimum' => 5, 'maximum' => 4]]], 'schema_invalid'],
            [['type' => 'object', 'properties' => ['name' => ['type' => 'string', 'maxLength' => AbilityInputValidator::MAX_STRING_BYTES + 1]]], 'schema_invalid'],
        ];

        foreach ($cases as [$schema, $expectedCode]) {
            $result = $validator->validateSchema($schema);
            self::assertFalse($result->valid);
            self::assertSame($expectedCode, $result->code);
        }
    }

    public function testEnforcesSchemaDepthAndNodeBounds(): void
    {
        $validator = new AbilityInputValidator();

        $deep = ['type' => 'string'];
        for ($index = 0; $index < AbilityInputValidator::MAX_SCHEMA_DEPTH; ++$index) {
            $deep = [
                'type' => 'object',
                'properties' => ['next' => $deep],
                'additionalProperties' => false,
            ];
        }
        $deepResult = $validator->validateSchema($deep);
        self::assertFalse($deepResult->valid);
        self::assertSame('complexity_exceeded', $deepResult->code);

        $properties = [];
        for ($index = 0; $index < AbilityInputValidator::MAX_OBJECT_PROPERTIES; ++$index) {
            $children = [];
            for ($child = 0; $child < 4; ++$child) {
                $children['child_' . $child] = ['type' => 'string'];
            }
            $properties['node_' . $index] = [
                'type' => 'object',
                'properties' => $children,
                'additionalProperties' => false,
            ];
        }
        $wide = [
            'type' => 'object',
            'properties' => $properties,
            'additionalProperties' => false,
        ];

        $wideResult = $validator->validateSchema($wide);
        self::assertFalse($wideResult->valid);
        self::assertSame('complexity_exceeded', $wideResult->code);
    }

    public function testRejectsRuntimeShapeEnumCoercionAndBounds(): void
    {
        $validator = new AbilityInputValidator();
        $schema = $this->schema();

        $cases = [
            [
                [
                    'definition_id' => 'id',
                    'revision' => '3',
                    'enabled' => true,
                    'mode' => 'draft',
                    'items' => [['key' => 'alpha', 'weight' => 0.5]],
                ],
                'input_type_mismatch',
                '$.revision',
            ],
            [
                [
                    'definition_id' => 'id',
                    'enabled' => true,
                    'mode' => 'draft',
                    'items' => [['key' => 'alpha', 'weight' => 0.5]],
                ],
                'required_missing',
                '$.revision',
            ],
            [
                [
                    'definition_id' => 'id',
                    'revision' => 3,
                    'enabled' => true,
                    'mode' => 'draft',
                    'items' => [['key' => 'alpha', 'weight' => 0.5]],
                    'extra' => true,
                ],
                'additional_property',
                '$.extra',
            ],
            [
                [
                    'definition_id' => 'id',
                    'revision' => 3,
                    'enabled' => true,
                    'mode' => 'secret-mode-value',
                    'items' => [['key' => 'alpha', 'weight' => 0.5]],
                ],
                'enum_mismatch',
                '$.mode',
            ],
            [
                [
                    'definition_id' => str_repeat('x', 37),
                    'revision' => 3,
                    'enabled' => true,
                    'mode' => 'draft',
                    'items' => [['key' => 'alpha', 'weight' => 0.5]],
                ],
                'bound_violation',
                '$.definition_id',
            ],
            [
                [
                    'definition_id' => 'id',
                    'revision' => 101,
                    'enabled' => true,
                    'mode' => 'draft',
                    'items' => [['key' => 'alpha', 'weight' => 0.5]],
                ],
                'bound_violation',
                '$.revision',
            ],
            [
                [
                    'definition_id' => 'id',
                    'revision' => 3,
                    'enabled' => true,
                    'mode' => 'draft',
                    'items' => [
                        ['key' => 'a', 'weight' => 0.1],
                        ['key' => 'b', 'weight' => 0.2],
                        ['key' => 'c', 'weight' => 0.3],
                    ],
                ],
                'bound_violation',
                '$.items',
            ],
        ];

        foreach ($cases as [$input, $expectedCode, $expectedPath]) {
            $result = $validator->validateInput($schema, $input);
            self::assertFalse($result->valid);
            self::assertSame($expectedCode, $result->code);
            self::assertSame($expectedPath, $result->path);
        }

        $enumResult = $validator->validateInput($schema, [
            'definition_id' => 'id',
            'revision' => 3,
            'enabled' => true,
            'mode' => 'secret-mode-value',
            'items' => [['key' => 'alpha', 'weight' => 0.5]],
        ]);
        self::assertStringNotContainsString('secret-mode-value', $enumResult->path);
    }

    public function testRejectsRuntimeDepthSizeSparseArraysAndUnsupportedValues(): void
    {
        $validator = new AbilityInputValidator();
        $openSchema = [
            'type' => 'object',
            'additionalProperties' => true,
        ];

        $deep = 'leaf';
        for ($index = 0; $index < AbilityInputValidator::MAX_SCHEMA_DEPTH; ++$index) {
            $deep = ['nested' => $deep];
        }
        $depthResult = $validator->validateInput($openSchema, ['payload' => $deep]);
        self::assertFalse($depthResult->valid);
        self::assertSame('input_depth_exceeded', $depthResult->code);

        $large = [];
        for ($index = 0; $index < 9; ++$index) {
            $large['part_' . $index] = str_repeat('x', 4000);
        }
        $sizeResult = $validator->validateInput($openSchema, $large);
        self::assertFalse($sizeResult->valid);
        self::assertSame('input_too_large', $sizeResult->code);

        $objectResult = $validator->validateInput($openSchema, ['value' => new stdClass()]);
        self::assertFalse($objectResult->valid);
        self::assertSame('unsupported_runtime_value', $objectResult->code);

        $arraySchema = [
            'type' => 'object',
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['items'],
            'additionalProperties' => false,
        ];
        $sparseResult = $validator->validateInput($arraySchema, [
            'items' => [0 => 'a', 2 => 'b'],
        ]);
        self::assertFalse($sparseResult->valid);
        self::assertSame('input_type_mismatch', $sparseResult->code);
        self::assertSame('$.items', $sparseResult->path);
    }

    public function testRejectsInvalidUtf8AndAbsoluteStringBound(): void
    {
        $validator = new AbilityInputValidator();
        $schema = [
            'type' => 'object',
            'properties' => [
                'value' => ['type' => 'string'],
            ],
            'required' => ['value'],
            'additionalProperties' => false,
        ];

        $utf8Result = $validator->validateInput($schema, ['value' => "\xFF"]);
        self::assertFalse($utf8Result->valid);
        self::assertSame('bound_violation', $utf8Result->code);

        $longResult = $validator->validateInput($schema, [
            'value' => str_repeat('x', AbilityInputValidator::MAX_STRING_BYTES + 1),
        ]);
        self::assertFalse($longResult->valid);
        self::assertSame('bound_violation', $longResult->code);
    }

    /** @return array<string,mixed> */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'definition_id' => [
                    'type' => 'string',
                    'minLength' => 1,
                    'maxLength' => 36,
                ],
                'revision' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                ],
                'enabled' => ['type' => 'boolean'],
                'mode' => [
                    'type' => 'string',
                    'enum' => ['draft', 'published'],
                ],
                'items' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 2,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'key' => [
                                'type' => 'string',
                                'minLength' => 1,
                                'maxLength' => 20,
                            ],
                            'weight' => [
                                'type' => 'number',
                                'minimum' => 0,
                                'maximum' => 1,
                            ],
                        ],
                        'required' => ['key', 'weight'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['definition_id', 'revision', 'enabled', 'mode', 'items'],
            'additionalProperties' => false,
        ];
    }
}
