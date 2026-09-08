<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Rendering;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class BlueprintRendererDispatcherTest extends TestCase
{
    public function testDispatchesExactBlueprintComponentAndPreservesContext(): void
    {
        $blueprint = $this->blueprint();
        $delegate = new BlueprintDispatcherDelegate();
        $dispatcher = new BlueprintRendererDispatcher(new BlueprintDispatcherRegistry($blueprint));
        $dispatcher->register('listing.card', $delegate);
        $context = $this->context();

        $result = $dispatcher->render(new RenderInput($blueprint->id, 2, ['title' => 'Hello']), $context);

        self::assertTrue($result->success);
        self::assertSame('<strong>Hello</strong>', $result->html);
        self::assertSame($context, $delegate->context);
    }

    public function testMissingBlueprintFailsClosed(): void
    {
        $dispatcher = new BlueprintRendererDispatcher(new BlueprintDispatcherRegistry(null));
        $result = $dispatcher->render(
            new RenderInput('22222222-2222-4222-8222-222222222222', 2, ['title' => 'Hello']),
            $this->context(),
        );

        self::assertFalse($result->success);
        self::assertSame('', $result->html);
        self::assertSame(RenderFailureCode::MissingBlueprint, $result->failure);
    }

    public function testUnknownComponentRendererFailsClosed(): void
    {
        $blueprint = $this->blueprint();
        $dispatcher = new BlueprintRendererDispatcher(new BlueprintDispatcherRegistry($blueprint));

        $result = $dispatcher->render(new RenderInput($blueprint->id, 2, ['title' => 'Hello']), $this->context());

        self::assertFalse($result->success);
        self::assertSame(RenderFailureCode::DependencyMismatch, $result->failure);
        self::assertSame('', $result->html);
    }

    public function testRejectsDuplicateAndSelfRegistration(): void
    {
        $dispatcher = new BlueprintRendererDispatcher(new BlueprintDispatcherRegistry($this->blueprint()));
        $dispatcher->register('listing.card', new BlueprintDispatcherDelegate());

        try {
            $dispatcher->register('listing.card', new BlueprintDispatcherDelegate());
            self::fail('duplicate component type must be rejected');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        $dispatcher->register('listing.self', $dispatcher);
    }

    public function testDelegateExceptionNormalizesToSafeFailure(): void
    {
        $blueprint = $this->blueprint();
        $delegate = new BlueprintDispatcherDelegate();
        $delegate->throw = true;
        $dispatcher = new BlueprintRendererDispatcher(new BlueprintDispatcherRegistry($blueprint));
        $dispatcher->register('listing.card', $delegate);

        $result = $dispatcher->render(new RenderInput($blueprint->id, 2, ['title' => 'Hello']), $this->context());

        self::assertFalse($result->success);
        self::assertSame('', $result->html);
        self::assertSame(RenderFailureCode::DependencyMismatch, $result->failure);
    }

    private function blueprint(): ComponentBlueprintDescriptor
    {
        return new ComponentBlueprintDescriptor(
            id: '22222222-2222-4222-8222-222222222222',
            revision: 2,
            ownerSurfaceId: 9,
            componentType: 'listing.card',
            bindingSchema: ['title' => 'string'],
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class BlueprintDispatcherRegistry implements ComponentBlueprintRegistryInterface
{
    public function __construct(private ?ComponentBlueprintDescriptor $blueprint) {}

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        if ($this->blueprint === null) {
            return null;
        }
        return $this->blueprint->id === $blueprintId && $this->blueprint->revision === $revision
            ? $this->blueprint
            : null;
    }
}

final class BlueprintDispatcherDelegate implements RendererInterface
{
    public bool $throw = false;
    public ?ExecutionContext $context = null;

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        $this->context = $context;
        if ($this->throw) {
            throw new RuntimeException('private renderer detail');
        }
        return new RenderOutput(true, '<strong>' . (string) ($input->bindings['title'] ?? '') . '</strong>');
    }
}
