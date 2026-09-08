<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class PreconditionRequirement
{
    private const PARAMETER_KEYS = [
        'min',
        'max',
        'fingerprint',
        'feature',
        'tier',
        'estimate_bytes',
    ];

    /** @param array<string,int|string> $parameters */
    public function __construct(
        public string $id,
        public PreconditionKind $kind,
        public string $target,
        public array $parameters = [],
    ) {
        if (preg_match('/^[a-z][a-z0-9_]{0,63}$/', $this->id) !== 1) {
            throw new InvalidArgumentException('Custom Tables precondition id is invalid.');
        }
        if (preg_match('/^[a-z][a-z0-9_.]{0,127}$/', $this->target) !== 1) {
            throw new InvalidArgumentException('Custom Tables precondition target is invalid.');
        }

        foreach ($this->parameters as $key => $value) {
            if (!in_array($key, self::PARAMETER_KEYS, true)) {
                throw new InvalidArgumentException('Custom Tables precondition parameter key is unsupported.');
            }
            if (!is_int($value) && !is_string($value)) {
                throw new InvalidArgumentException('Custom Tables precondition parameters must be bounded scalar facts.');
            }
            if (is_string($value) && (strlen($value) > 128 || preg_match('/[\x00-\x1F\x7F]/', $value) === 1)) {
                throw new InvalidArgumentException('Custom Tables precondition string parameter is unsafe.');
            }
            if ($key === 'fingerprint' && (is_string($value) === false || preg_match('/^[a-f0-9]{64}$/', $value) !== 1)) {
                throw new InvalidArgumentException('Custom Tables precondition fingerprint parameter is invalid.');
            }
        }
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        $parameters = $this->parameters;
        ksort($parameters);

        return [
            'id' => $this->id,
            'kind' => $this->kind->value,
            'target' => $this->target,
            'parameters' => $parameters,
        ];
    }
}
