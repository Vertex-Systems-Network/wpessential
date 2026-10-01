<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputBinder;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputDescriptor;
use WPEssential\Platform\Abilities\InputValidation\AbilityInputValidator;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\Rendering\RenderFailureCode;

final class DashboardWidgetActionInputBinderTest extends TestCase
{
    public function testResolvesSiteUserAndNetworkBindingsFromExactContext(): void
    {
        $context = new ExecutionContext(new Principal(7), 3, networkId: 9);
        $schema = $this->dynamicSchema();

        foreach ([
            'site' => 3,
            'user' => 7,
            'network' => 9,
        ] as $resource => $expectedId) {
            $resolver = new ActionInputCapturingResolver(new DynamicValueResult(true, 'open'));
            $binder = $this->binder($schema, $resolver);

            $result = $binder->bind(
                $this->descriptor($resource),
                $context,
            );

            self::assertSame(['entry_id' => 42, 'status' => 'open'], $result);
            self::assertSame($context, $resolver->context);
            self::assertSame('context.' . $resource, $resolver->request?->sourceRef);
            self::assertSame('entry_status', $resolver->request?->valueRef);
            self::assertSame($resource, $resolver->request?->resourceType);
            self::assertSame($expectedId, $resolver->request?->resourceId);
        }
    }

    public function testFailsClosedWhenUserOrNetworkContextIsMissing(): void
    {
        $resolver = new ActionInputCapturingResolver(new DynamicValueResult(true, 'open'));
        $binder = $this->binder($this->dynamicSchema(), $resolver);

        foreach ([
            [$this->descriptor('user'), new ExecutionContext(new Principal(null), 3)],
            [$this->descriptor('network'), new ExecutionContext(new Principal(7), 3)],
        ] as [$descriptor, $context]) {
            try {
                $binder->bind($descriptor, $context);
                self::fail('Expected missing context resource to fail closed.');
            } catch (RuntimeException) {
                self::assertTrue(true);
            }
        }
    }

    public function testFailsClosedOnResolverFailureWithoutLeakingProviderValue(): void
    {
        $context = new ExecutionContext(new Principal(7), 3);

        foreach ([
            new ActionInputCapturingResolver(
                new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource),
            ),
            new ActionInputCapturingResolver(new DynamicValueResult(true, null)),
        ] as $resolver) {
            try {
                $this->binder($this->dynamicSchema(), $resolver)->bind($this->descriptor('site'), $context);
                self::fail('Expected unresolved Dynamic action input to fail closed.');
            } catch (RuntimeException $exception) {
                self::assertStringNotContainsString('private-provider-value', $exception->getMessage());
            }
        }

        $throwing = new ActionInputCapturingResolver(new DynamicValueResult(true, 'private-provider-value'));
        $throwing->throw = true;

        try {
            $this->binder($this->dynamicSchema(), $throwing)->bind($this->descriptor('site'), $context);
            self::fail('Expected throwing Dynamic resolver to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringNotContainsString('private-provider-value', $exception->getMessage());
        }
    }

    public function testFailsClosedOnCurrentAbilityMissingOrSchemaDrift(): void
    {
        $resolver = new ActionInputCapturingResolver(new DynamicValueResult(true, 'open'));
        $descriptor = $this->descriptor('site');
        $context = new ExecutionContext(new Principal(7), 3);

        $missing = new DashboardWidgetActionInputBinder(
            new AbilityInputValidator(),
            $resolver,
            static fn (string $name): ?array => null,
        );
        $this->expectException(RuntimeException::class);
        $missing->bind($descriptor, $context);
    }

    public function testFailsClosedWhenCurrentSchemaRequiresNewPropertyOrRejectsResolvedType(): void
    {
        $context = new ExecutionContext(new Principal(7), 3);

        $schemaDrift = $this->dynamicSchema();
        $schemaDrift['properties']['revision'] = ['type' => 'integer', 'minimum' => 1];
        $schemaDrift['required'][] = 'revision';

        try {
            $this->binder(
                $schemaDrift,
                new ActionInputCapturingResolver(new DynamicValueResult(true, 'open')),
            )->bind($this->descriptor('site'), $context);
            self::fail('Expected schema drift to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('required_missing', $exception->getMessage());
        }

        try {
            $this->binder(
                $this->dynamicSchema(),
                new ActionInputCapturingResolver(new DynamicValueResult(true, 123)),
            )->bind($this->descriptor('site'), $context);
            self::fail('Expected resolved type mismatch to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('input_type_mismatch', $exception->getMessage());
        }
    }

    public function testPreservesLiteralInputAndNeverExecutesAbility(): void
    {
        $resolver = new ActionInputCapturingResolver(new DynamicValueResult(true, 'unused'));
        $schema = [
            'type' => 'object',
            'properties' => [
                'entry_id' => ['type' => 'integer', 'minimum' => 1],
            ],
            'required' => ['entry_id'],
            'additionalProperties' => false,
        ];

        $descriptor = new DashboardWidgetActionInputDescriptor(
            definitionId: '11111111-1111-4111-8111-111111111111',
            definitionRevision: 4,
            abilityId: 'wpessential/forms-workflows/update-entry',
            literalBindings: ['entry_id' => 42],
            dynamicBindings: [],
        );

        $result = $this->binder($schema, $resolver)->bind(
            $descriptor,
            new ExecutionContext(new Principal(7), 3),
        );

        self::assertSame(['entry_id' => 42], $result);
        self::assertSame(0, $resolver->calls);
    }

    private function binder(
        array $schema,
        DynamicValueResolverInterface $resolver,
    ): DashboardWidgetActionInputBinder {
        return new DashboardWidgetActionInputBinder(
            new AbilityInputValidator(),
            $resolver,
            static fn (string $name): ?array => [
                'name' => $name,
                'owner_surface_id' => 17,
                'mutates' => true,
                'ui_allowed' => true,
                'input_schema' => $schema,
            ],
        );
    }

    private function descriptor(string $resource): DashboardWidgetActionInputDescriptor
    {
        return new DashboardWidgetActionInputDescriptor(
            definitionId: '11111111-1111-4111-8111-111111111111',
            definitionRevision: 4,
            abilityId: 'wpessential/forms-workflows/update-entry',
            literalBindings: ['entry_id' => 42],
            dynamicBindings: [
                'status' => [
                    'source_ref' => 'context.' . $resource,
                    'value_ref' => 'entry_status',
                    'resource' => $resource,
                ],
            ],
        );
    }

    /** @return array<string,mixed> */
    private function dynamicSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'entry_id' => ['type' => 'integer', 'minimum' => 1],
                'status' => ['type' => 'string', 'enum' => ['open', 'closed']],
            ],
            'required' => ['entry_id', 'status'],
            'additionalProperties' => false,
        ];
    }
}

final class ActionInputCapturingResolver implements DynamicValueResolverInterface
{
    public int $calls = 0;
    public bool $throw = false;
    public ?DynamicValueRequest $request = null;
    public ?ExecutionContext $context = null;

    public function __construct(private DynamicValueResult $result) {}

    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult
    {
        ++$this->calls;
        $this->request = $request;
        $this->context = $context;

        if ($this->throw) {
            throw new RuntimeException('private-provider-value');
        }

        return $this->result;
    }
}
