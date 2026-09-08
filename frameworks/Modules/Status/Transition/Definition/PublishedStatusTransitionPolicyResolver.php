<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Transition\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Modules\Status\Transition\StatusTransitionRule;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class PublishedStatusTransitionPolicyResolver
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private StatusTransitionPolicyDefinitionCompiler $compiler = new StatusTransitionPolicyDefinitionCompiler(),
    ) {}

    public function resolve(): StatusTransitionPolicy
    {
        $published = array_values(array_filter(
            $this->definitions->byType(StatusTransitionPolicyDefinitionCompiler::TYPE),
            static fn (Definition $definition): bool => $definition->status === DefinitionStatus::Published,
        ));

        usort(
            $published,
            static fn (Definition $left, Definition $right): int => strcmp($left->id, $right->id),
        );

        /** @var array<string,StatusTransitionRule> $rules */
        $rules = [];
        foreach ($published as $definition) {
            $policy = $this->compiler->compile($definition);
            foreach ($policy->rules() as $rule) {
                $edge = $rule->edgeKey();
                if (isset($rules[$edge])) {
                    throw new RuntimeException('Published Status transition-policy Definitions contain a duplicate edge.');
                }
                $rules[$edge] = $rule;
            }
        }

        ksort($rules, SORT_STRING);

        return new StatusTransitionPolicy(array_values($rules));
    }
}
