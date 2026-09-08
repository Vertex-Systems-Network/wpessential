<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class PreconditionEvaluation
{
    private const EVIDENCE_KEYS = [
        'observed_count',
        'observed_min',
        'observed_max',
        'fingerprint',
        'estimate_bytes',
    ];

    /** @param array<string,int|string> $evidence */
    public function __construct(
        public string $requirementId,
        public PreconditionOutcome $outcome,
        public array $evidence = [],
    ) {
        if (preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->requirementId) !== 1) {
            throw new InvalidArgumentException('Custom Tables precondition evaluation id is invalid.');
        }

        foreach ($this->evidence as $key => $value) {
            if (!in_array($key, self::EVIDENCE_KEYS, true)) {
                throw new InvalidArgumentException('Custom Tables precondition evaluation evidence is not allowlisted.');
            }
            if (!is_int($value) && !is_string($value)) {
                throw new InvalidArgumentException('Custom Tables precondition evaluation evidence must be bounded scalar metadata.');
            }
            if ($key === 'fingerprint' && (is_string($value) === false || preg_match('/^[a-f0-9]{64}$/', $value) !== 1)) {
                throw new InvalidArgumentException('Custom Tables precondition evaluation fingerprint is invalid.');
            }
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        $evidence = $this->evidence;
        ksort($evidence);

        return [
            'requirement_id' => $this->requirementId,
            'outcome' => $this->outcome->value,
            'evidence' => $evidence,
        ];
    }
}
