<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetActionInputCompilerTest extends TestCase
{
    public function testCompilesBoundedLiteralAndDynamicBindings(): void
    {
        $abilityId = 'wpessential/forms-workflows/update-entry';
        $compiler = $this->compiler($abilityId, $this->schema());
        $definition = $this->definition();

        $descriptor = $compiler->compile($definition, [
            'action' => [
                'ability_id' => $abilityId,
                'input' => [
                    'entry_id' => ['source' => 'literal', 'value' => 42],
                    'status' => [
                        'source' => 'dynamic',
                        'source_ref' => 'context.site',
                        'value_ref' => 'entry_status',
                        'resource' => 'site',
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(DashboardWidgetActionInputDescriptor::class, $descriptor);
        self::assertSame($abilityId, $descriptor->abilityId);
        self::assertSame(['entry_id' => 42], $descriptor->literalBindings);
        self::assertSame([
            'status' => [
                'source_ref' => 'context.site',
                'value_ref' => 'entry_status',
                'resource' => 'site',
            ],
        ], $descriptor->dynamicBindings);
        self::assertSame($definition->id, $descriptor->definitionId);
        self::assertSame($definition->revision, $descriptor->definitionRevision);
    }

    public function testPreservesZeroInputBackwardCompatibility(): void
    {
        $abilityId = 'wpessential/forms-workflows/submit';
        $compiler = $this->compiler($abilityId, []);

        self::assertNull($compiler->compile($this->definition(), [
            'action' => ['ability_id' => $abilityId],
        ]));
        self::assertNull($compiler->compile($this->definition(), [
            'action' => ['ability_id' => $abilityId, 'input' => []],
        ]));
    }

    public function testRejectsMalformedUnknownMissingRequiredAndUnsupportedSources(): void
    {
        $abilityId = 'wpessential/forms-workflows/update-entry';
        $compiler = $this->compiler($abilityId, $this->schema());

        $cases = [
            ['action' => ['input' => ['entry_id' => ['source' => 'literal', 'value' => 1]]]],
            ['action' => ['ability_id' => $abilityId, 'input' => 'bad']],
            ['action' => ['ability_id' => $abilityId, 'input' => [true]]],
            ['action' => ['ability_id' => $abilityId, 'input' => [
                'entry_id' => ['source' => 'literal', 'value' => 1],
            ]]],
            ['action' => ['ability_id' => $abilityId, 'input' => [
                'entry_id' => ['source' => 'literal', 'value' => 1],
                'status' => ['source' => 'literal', 'value' => 'open'],
                'unknown' => ['source' => 'literal', 'value' => true],
            ]]],
            ['action' => ['ability_id' => $abilityId, 'input' => [
                'entry_id' => ['source' => 'query', 'value' => 1],
                'status' => ['source' => 'literal', 'value' => 'open'],
            ]]],
            ['action' => ['ability_id' => $abilityId, 'input' => [
                'entry_id' => ['source' => 'provider', 'value' => 1],
                'status' => ['source' => 'literal', 'value' => 'open'],
            ]]],
            ['action' => ['ability_id' => $abilityId, 'input' => [
                'entry_id' => ['source' => 'literal', 'value' => 1, 'template' => '{{id}}'],
                'status' => ['source' => 'literal', 'value' => 'open'],
            ]]],
        ];

        foreach ($cases as $widget) {
            try {
                $compiler->compile($this->definition(), $widget);
                self::fail('Expected malformed Dashboard Widget action input to fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsInvalidAbilitySchemasAndCredentialLikeProperties(): void
    {
        $abilityId = 'wpessential/forms-workflows/update-entry';

        $invalidSchemas = [
            ['type' => 'string'],
            ['type' => 'object', 'properties' => [], 'additionalProperties' => true],
            ['type' => 'object', 'properties' => ['id' => ['type' => 'string', 'pattern' => '.*']]],
            [],
        ];

        foreach ($invalidSchemas as $schema) {
            $compiler = $this->compiler($abilityId, $schema);
            try {
                $compiler->compile($this->definition(), [
                    'action' => [
                        'ability_id' => $abilityId,
                        'input' => ['id' => ['source' => 'literal', 'value' => 'x']],
                    ],
                ]);
                self::fail('Expected unsupported Ability input schema to fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }

        $credentialSchema = [
            'type' => 'object',
            'properties' => [
                'api_key' => ['type' => 'string', 'maxLength' => 32],
            ],
            'required' => ['api_key'],
            'additionalProperties' => false,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->compiler($abilityId, $credentialSchema)->compile($this->definition(), [
            'action' => [
                'ability_id' => $abilityId,
                'input' => [
                    'api_key' => ['source' => 'literal', 'value' => 'private-value'],
                ],
            ],
        ]);
    }

    public function testRejectsLiteralTypeMismatchAndUnsupportedDynamicPropertyShapes(): void
    {
        $abilityId = 'wpessential/forms-workflows/update-entry';

        $compiler = $this->compiler($abilityId, $this->schema());
        $this->expectException(InvalidArgumentException::class);
        $compiler->compile($this->definition(), [
            'action' => [
                'ability_id' => $abilityId,
                'input' => [
                    'entry_id' => ['source' => 'literal', 'value' => '42'],
                    'status' => ['source' => 'literal', 'value' => 'open'],
                ],
            ],
        ]);
    }

    public function testRejectsDynamicObjectAndNullProperties(): void
    {
        $abilityId = 'wpessential/forms-workflows/update-entry';
        $schema = [
            'type' => 'object',
            'properties' => [
                'payload' => [
                    'type' => 'object',
                    'properties' => ['name' => ['type' => 'string']],
                    'required' => ['name'],
                    'additionalProperties' => false,
                ],
                'nullable' => ['type' => 'null'],
            ],
            'required' => ['payload', 'nullable'],
            'additionalProperties' => false,
        ];

        foreach (['payload', 'nullable'] as $key) {
            $input = [
                'payload' => ['source' => 'literal', 'value' => ['name' => 'A']],
                'nullable' => ['source' => 'literal', 'value' => null],
            ];
            $input[$key] = [
                'source' => 'dynamic',
                'source_ref' => 'context.site',
                'value_ref' => 'value',
                'resource' => 'site',
            ];

            try {
                $this->compiler($abilityId, $schema)->compile($this->definition(), [
                    'action' => ['ability_id' => $abilityId, 'input' => $input],
                ]);
                self::fail('Expected unsupported Dynamic property shape to fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testEnforcesBindingCountAndEncodedEnvelopeBounds(): void
    {
        $abilityId = 'wpessential/forms-workflows/bulk-update';
        $properties = [];
        $required = [];
        $input = [];

        for ($i = 0; $i < DashboardWidgetActionInputDescriptor::MAX_BINDINGS + 1; ++$i) {
            $key = 'field_' . $i;
            $properties[$key] = ['type' => 'string', 'maxLength' => 4096];
            $required[] = $key;
            $input[$key] = ['source' => 'literal', 'value' => 'x'];
        }

        $schema = [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
            'additionalProperties' => false,
        ];

        try {
            $this->compiler($abilityId, $schema)->compile($this->definition(), [
                'action' => ['ability_id' => $abilityId, 'input' => $input],
            ]);
            self::fail('Expected over-bound binding count to fail closed.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $schema = [
            'type' => 'object',
            'properties' => [
                'content' => ['type' => 'string', 'maxLength' => 4096],
                'content_two' => ['type' => 'string', 'maxLength' => 4096],
                'content_three' => ['type' => 'string', 'maxLength' => 4096],
            ],
            'required' => ['content', 'content_two', 'content_three'],
            'additionalProperties' => false,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->compiler($abilityId, $schema)->compile($this->definition(), [
            'action' => [
                'ability_id' => $abilityId,
                'input' => [
                    'content' => ['source' => 'literal', 'value' => str_repeat('a', 3000)],
                    'content_two' => ['source' => 'literal', 'value' => str_repeat('b', 3000)],
                    'content_three' => ['source' => 'literal', 'value' => str_repeat('c', 3000)],
                ],
            ],
        ]);
    }

    private function compiler(string $abilityId, array $schema): DashboardWidgetActionInputCompiler
    {
        return new DashboardWidgetActionInputCompiler(
            new AbilityInputValidator(),
            static fn (string $name): ?array => [
                'name' => $name === $abilityId ? $abilityId : $name,
                'owner_surface_id' => 17,
                'mutates' => true,
                'ui_allowed' => true,
                'input_schema' => $schema,
            ],
        );
    }

    /** @return array<string,mixed> */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'entry_id' => [
                    'type' => 'integer',
                    'minimum' => 1,
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['open', 'closed'],
                ],
            ],
            'required' => ['entry_id', 'status'],
            'additionalProperties' => false,
        ];
    }

    private function definition(): Definition
    {
        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'action-input-test',
            type: DashboardWidgetDefinition::TYPE,
            schemaVersion: 1,
            ownerSurfaceId: DashboardWidgetDefinition::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: ['widget' => []],
            revision: 4,
            dependencies: [],
        );
    }
}
