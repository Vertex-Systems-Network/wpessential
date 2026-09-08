<?php

declare(strict_types=1);

namespace WPEssential\Platform\Rendering;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final class BlueprintRendererDispatcher implements RendererInterface
{
    /** @var array<string,RendererInterface> */
    private array $renderers = [];

    public function __construct(private ComponentBlueprintRegistryInterface $blueprints)
    {
    }

    public function register(string $componentType, RendererInterface $renderer): void
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]{0,127}$/', $componentType)) {
            throw new InvalidArgumentException('Renderer component type must be a bounded semantic identifier.');
        }
        if ($renderer instanceof self) {
            throw new InvalidArgumentException('Blueprint Renderer dispatchers cannot be chained as component renderers.');
        }
        if (isset($this->renderers[$componentType])) {
            throw new RuntimeException('Renderer component type is already registered.');
        }
        $this->renderers[$componentType] = $renderer;
    }

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        $blueprint = $this->blueprints->get($input->blueprintId, $input->blueprintRevision);
        if ($blueprint === null) {
            return new RenderOutput(false, '', [], RenderFailureCode::MissingBlueprint);
        }

        $renderer = $this->renderers[$blueprint->componentType] ?? null;
        if ($renderer === null) {
            return new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch);
        }

        try {
            return $renderer->render($input, $context);
        } catch (Throwable) {
            return new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch);
        }
    }
}
