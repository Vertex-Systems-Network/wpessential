<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

final readonly class PreconditionEvaluator
{
    public function __construct(private PreconditionProbeInterface $probe)
    {
    }

    /** @param list<PreconditionRequirement> $requirements */
    public function evaluate(array $requirements): PreconditionReport
    {
        $evaluations = [];
        $ids = [];

        foreach ($requirements as $requirement) {
            if (!$requirement instanceof PreconditionRequirement) {
                throw new InvalidArgumentException('Custom Tables precondition evaluator requires typed requirements.');
            }
            if (isset($ids[$requirement->id])) {
                throw new InvalidArgumentException('Custom Tables precondition evaluator contains duplicate requirement ids.');
            }
            $ids[$requirement->id] = true;

            $evaluation = $this->probe->evaluate($requirement);
            if ($evaluation->requirementId !== $requirement->id) {
                throw new RuntimeException('Custom Tables precondition probe returned mismatched requirement evidence.');
            }

            $evaluations[] = $evaluation;
        }

        return new PreconditionReport($evaluations);
    }
}
