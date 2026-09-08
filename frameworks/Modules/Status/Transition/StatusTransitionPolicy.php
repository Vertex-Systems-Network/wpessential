<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Transition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;

final readonly class StatusTransitionPolicy
{
    /** @var array<string,StatusTransitionRule> */
    private array $rules;

    public string $compatibilityFingerprint;

    /** @param list<StatusTransitionRule> $rules */
    public function __construct(array $rules)
    {
        if (count($rules) > 256) {
            throw new InvalidArgumentException('Status transition policy exceeds the bounded V1 edge limit.');
        }

        $indexed = [];
        foreach ($rules as $rule) {
            if (!$rule instanceof StatusTransitionRule) {
                throw new InvalidArgumentException('Status transition policy must contain typed transition rules.');
            }
            $edge = $rule->edgeKey();
            if (isset($indexed[$edge])) {
                throw new InvalidArgumentException('Status transition policy contains a duplicate edge.');
            }
            $indexed[$edge] = $rule;
        }
        ksort($indexed, SORT_STRING);
        $this->rules = $indexed;
        $this->compatibilityFingerprint = $this->fingerprint($indexed);
    }

    public function ruleFor(string $from, string $to): ?StatusTransitionRule
    {
        if ($from === $to) {
            return null;
        }
        if (!$this->validStatusKey($from) || !$this->validStatusKey($to)) {
            return null;
        }
        return $this->rules[$from . '->' . $to] ?? null;
    }

    public function allows(
        string $from,
        string $to,
        bool $bulk = false,
        bool $programmatic = false,
    ): bool {
        $rule = $this->ruleFor($from, $to);
        if ($rule === null) {
            return false;
        }
        if ($bulk && !$rule->bulkAllowed) {
            return false;
        }
        if ($programmatic && !$rule->programmaticAllowed) {
            return false;
        }
        return true;
    }

    public function acceptsReason(StatusTransitionRule $rule, ?string $reason): bool
    {
        if ($reason === null) {
            return !$rule->reasonRequired;
        }

        $trimmed = trim($reason);
        if ($trimmed === '' || strlen($trimmed) > 500 || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $trimmed)) {
            return false;
        }

        return true;
    }

    /** @return list<StatusTransitionRule> */
    public function rules(): array
    {
        return array_values($this->rules);
    }

    /**
     * @param array<string,StatusTransitionRule> $rules
     * @throws JsonException
     */
    private function fingerprint(array $rules): string
    {
        $data = array_map(
            static fn (StatusTransitionRule $rule): array => $rule->fingerprintData(),
            array_values($rules),
        );

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function validStatusKey(string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $value);
    }
}
