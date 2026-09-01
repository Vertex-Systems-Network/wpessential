<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class FieldGroupRuntimeRegistrar
{
    private bool $processed = false;

    /** @var list<string> */
    private array $bound = [];

    /** @var array<string,string> */
    private array $errors = [];

    /** @var Closure(string,callable,int):void */
    private Closure $addAction;

    /** @param null|callable(string,callable,int):void $addAction */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly FieldGroupPostMetaBinder $binder,
        ?callable $addAction = null,
    ) {
        $this->addAction = $addAction !== null
            ? Closure::fromCallable($addAction)
            : static function (string $hook, callable $callback, int $priority): void {
                if (function_exists('add_action')) {
                    add_action($hook, $callback, $priority);
                }
            };
    }

    public function register(): void
    {
        ($this->addAction)('init', [$this, 'registerActive'], 30);
    }

    public function registerActive(): void
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        try {
            $definitions = array_values(array_filter(
                $this->definitions->byType(FieldGroupDefinitionNormalizer::DEFINITION_TYPE),
                static fn (Definition $definition): bool =>
                    $definition->ownerSurfaceId === FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
                    && $definition->status === DefinitionStatus::Published,
            ));

            usort(
                $definitions,
                static fn (Definition $left, Definition $right): int =>
                    [$left->slug, $left->id] <=> [$right->slug, $right->id],
            );

            $this->binder->bindAll($definitions);
            $this->bound = array_map(
                static fn (Definition $definition): string => $definition->id,
                $definitions,
            );
        } catch (Throwable $error) {
            $this->errors['runtime'] = $error->getMessage();
        }
    }

    public function processed(): bool
    {
        return $this->processed;
    }

    /** @return list<string> */
    public function bound(): array
    {
        return $this->bound;
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
