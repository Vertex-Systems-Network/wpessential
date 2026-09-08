<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\DynamicValues;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\DynamicValues\DynamicValueRouter;
use WPEssential\Platform\Rendering\RenderFailureCode;

final class DynamicValueRouterTest extends TestCase
{
    public function testDispatchesExactSourceAndPreservesContext(): void
    {
        $delegate = new DynamicValueRouterDelegate();
        $router = new DynamicValueRouter();
        $router->register('fields.value', $delegate);
        $context = new ExecutionContext(new Principal(7), 3);

        $result = $router->resolve($this->request('fields.value'), $context);

        self::assertTrue($result->resolved);
        self::assertSame('resolved', $result->value);
        self::assertSame($context, $delegate->context);
    }

    public function testUnknownSourceFailsClosed(): void
    {
        $result = (new DynamicValueRouter())->resolve($this->request('unknown.source'), $this->context());

        self::assertFalse($result->resolved);
        self::assertSame(RenderFailureCode::UnsupportedValueSource, $result->failure);
        self::assertNull($result->value);
    }

    public function testRejectsDuplicateSourceRegistration(): void
    {
        $router = new DynamicValueRouter();
        $router->register('fields.value', new DynamicValueRouterDelegate());

        $this->expectException(RuntimeException::class);
        $router->register('fields.value', new DynamicValueRouterDelegate());
    }

    public function testRejectsSelfRegistration(): void
    {
        $router = new DynamicValueRouter();

        $this->expectException(InvalidArgumentException::class);
        $router->register('router.self', $router);
    }

    public function testRejectsRouterChainingToPreventCycles(): void
    {
        $router = new DynamicValueRouter();

        $this->expectException(InvalidArgumentException::class);
        $router->register('router.other', new DynamicValueRouter());
    }

    public function testDelegateExceptionNormalizesToSafeFailure(): void
    {
        $delegate = new DynamicValueRouterDelegate();
        $delegate->throw = true;
        $router = new DynamicValueRouter();
        $router->register('fields.value', $delegate);

        $result = $router->resolve($this->request('fields.value'), $this->context());

        self::assertFalse($result->resolved);
        self::assertSame(RenderFailureCode::DependencyMismatch, $result->failure);
        self::assertNull($result->value);
    }

    private function request(string $source): DynamicValueRequest
    {
        return new DynamicValueRequest($source, 'title', 'post', 11);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class DynamicValueRouterDelegate implements DynamicValueResolverInterface
{
    public bool $throw = false;
    public ?ExecutionContext $context = null;

    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult
    {
        $this->context = $context;
        if ($this->throw) {
            throw new RuntimeException('provider detail that must not escape');
        }
        return new DynamicValueResult(true, 'resolved');
    }
}
