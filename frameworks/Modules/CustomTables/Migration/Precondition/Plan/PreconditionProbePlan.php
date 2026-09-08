<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition\Plan;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class PreconditionProbePlan
{
    /** @param array<string,int|string> $parameters */
    public function __construct(
        public string $requirementId,
        public PreconditionProbeOperation $operation,
        public string $target,
        public array $parameters,
    ) {
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        $parameters = $this->parameters;
        ksort($parameters);

        return [
            'requirement_id' => $this->requirementId,
            'operation' => $this->operation->value,
            'target' => $this->target,
            'parameters' => $parameters,
        ];
    }
}
