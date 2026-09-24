<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDynamicBindingExecutor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceDescriptor;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\Rendering\RenderFailureCode;

final class DashboardWidgetDynamicBindingExecutorTest extends TestCase
{
    public function testDerivesSiteUserAndNetworkResourceIdsFromExecutionContext(): void
    {
        $context = new ExecutionContext(new Principal(7), 3, networkId: 9);

        foreach ([
            'site' => 3,
            'user' => 7,
            'network' => 9,
        ] as $resource => $expectedId) {
            $resolver = new DynamicBindingCapturingResolver(new DynamicValueResult(true, 'Resolved'));
            $result = (new DashboardWidgetDynamicBindingExecutor($resolver))->resolve(
                $this->descriptor($resource),
                $context,
            );

            self::assertSame(['content' => 'Resolved'], $result->bindings);
            self::assertSame([], $result->dynamicBindings);
            self::assertSame($context, $resolver->context);
            self::assertSame('context.' . $resource, $resolver->request?->sourceRef);
            self::assertSame('display_name', $resolver->request?->valueRef);
            self::assertSame($resource, $resolver->request?->resourceType);
            self::assertSame($expectedId, $resolver->request?->resourceId);
        }
    }

    public function testFailsClosedWhenContextResourceIsUnavailable(): void
    {
        $resolver = new DynamicBindingCapturingResolver(new DynamicValueResult(true, 'Resolved'));
        $executor = new DashboardWidgetDynamicBindingExecutor($resolver);

        foreach ([
            [$this->descriptor('user'), new ExecutionContext(new Principal(null), 3)],
            [$this->descriptor('network'), new ExecutionContext(new Principal(7), 3)],
        ] as [$descriptor, $context]) {
            try {
                $executor->resolve($descriptor, $context);
                self::fail('Expected missing context resource to fail closed.');
            } catch (RuntimeException) {
                self::assertTrue(true);
            }
        }

        self::assertSame(0, $resolver->calls);
    }

    public function testRejectsUnresolvedTypeMismatchUnsafeAndThrowingProviderResults(): void
    {
        $context = new ExecutionContext(new Principal(7), 3);

        $cases = [
            new DynamicBindingCapturingResolver(
                new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource),
            ),
            new DynamicBindingCapturingResolver(new DynamicValueResult(true, 12)),
            new DynamicBindingCapturingResolver(new DynamicValueResult(true, '<script>alert(1)</script>')),
        ];

        foreach ($cases as $resolver) {
            try {
                (new DashboardWidgetDynamicBindingExecutor($resolver))->resolve($this->descriptor('site'), $context);
                self::fail('Expected unsafe or incompatible Dynamic value to fail closed.');
            } catch (RuntimeException) {
                self::assertTrue(true);
            }
        }

        $throwing = new DynamicBindingCapturingResolver(new DynamicValueResult(true, 'unused'));
        $throwing->throw = true;
        $this->expectException(RuntimeException::class);
        (new DashboardWidgetDynamicBindingExecutor($throwing))->resolve($this->descriptor('site'), $context);
    }

    private function descriptor(string $resource): DashboardWidgetRenderSourceDescriptor
    {
        return new DashboardWidgetRenderSourceDescriptor(
            definitionId: '11111111-1111-4111-8111-111111111111',
            definitionRevision: 1,
            blueprintId: '22222222-2222-4222-8222-222222222222',
            blueprintRevision: 1,
            bindings: [],
            dynamicBindings: [
                'content' => [
                    'source_ref' => 'context.' . $resource,
                    'value_ref' => 'display_name',
                    'resource' => $resource,
                    'binding_type' => 'string',
                ],
            ],
        );
    }
}

final class DynamicBindingCapturingResolver implements DynamicValueResolverInterface
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
            throw new RuntimeException('private provider failure');
        }

        return $this->result;
    }
}
