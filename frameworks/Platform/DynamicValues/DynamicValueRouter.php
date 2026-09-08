<?php

declare(strict_types=1);

namespace WPEssential\Platform\DynamicValues;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Rendering\RenderFailureCode;

final class DynamicValueRouter implements DynamicValueResolverInterface
{
    /** @var array<string,DynamicValueResolverInterface> */
    private array $resolvers = [];

    public function register(string $sourceRef, DynamicValueResolverInterface $resolver): void
    {
        if ($sourceRef === '' || strlen($sourceRef) > 160 || !preg_match('/^[a-zA-Z0-9_.:-]+$/', $sourceRef)) {
            throw new InvalidArgumentException('Dynamic Value source reference is invalid.');
        }
        if ($resolver instanceof self) {
            throw new InvalidArgumentException('Dynamic Value routers cannot be chained as source resolvers.');
        }
        if (isset($this->resolvers[$sourceRef])) {
            throw new RuntimeException('Dynamic Value source resolver is already registered.');
        }
        $this->resolvers[$sourceRef] = $resolver;
    }

    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult
    {
        $resolver = $this->resolvers[$request->sourceRef] ?? null;
        if ($resolver === null) {
            return new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource);
        }

        try {
            return $resolver->resolve($request, $context);
        } catch (Throwable) {
            return new DynamicValueResult(false, null, RenderFailureCode::DependencyMismatch);
        }
    }
}
