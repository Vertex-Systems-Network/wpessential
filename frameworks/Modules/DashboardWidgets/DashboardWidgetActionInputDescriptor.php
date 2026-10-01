<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetActionInputDescriptor
{
    public const MAX_BINDINGS = 32;
    public const MAX_ENCODED_BYTES = 8192;

    /**
     * @param array<string,mixed> $literalBindings
     * @param array<string,array{source_ref:string,value_ref:string,resource:string}> $dynamicBindings
     */
    public function __construct(
        public string $definitionId,
        public int $definitionRevision,
        public string $abilityId,
        public array $literalBindings,
        public array $dynamicBindings,
    ) {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget action-input descriptor definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->definitionRevision < 1) {
            throw new InvalidArgumentException('Dashboard Widget action-input descriptor revision must be positive.');
        }
        if (preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $this->abilityId) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget action-input descriptor ability id is invalid.');
        }

        if (count($this->literalBindings) + count($this->dynamicBindings) > self::MAX_BINDINGS) {
            throw new InvalidArgumentException('Dashboard Widget action-input bindings exceed the bounded V1 limit.');
        }

        foreach ($this->literalBindings as $key => $_value) {
            $this->assertBindingKey($key);
            if (array_key_exists($key, $this->dynamicBindings)) {
                throw new InvalidArgumentException('Dashboard Widget action-input binding cannot be both literal and dynamic.');
            }
        }

        foreach ($this->dynamicBindings as $key => $binding) {
            $this->assertBindingKey($key);
            foreach (['source_ref', 'value_ref'] as $refKey) {
                $ref = $binding[$refKey] ?? null;
                if (!is_string($ref) || $ref === '' || strlen($ref) > 160 || preg_match('/^[a-zA-Z0-9_.:-]+$/', $ref) !== 1) {
                    throw new InvalidArgumentException('Dashboard Widget action-input Dynamic references must be bounded semantic identifiers.');
                }
            }
            if (!in_array($binding['resource'] ?? null, ['site', 'user', 'network'], true)) {
                throw new InvalidArgumentException('Dashboard Widget action-input Dynamic resource is unsupported.');
            }
        }

        $encoded = json_encode(
            [
                'literal' => $this->literalBindings,
                'dynamic' => $this->dynamicBindings,
            ],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
        if (!is_string($encoded) || strlen($encoded) > self::MAX_ENCODED_BYTES) {
            throw new InvalidArgumentException('Dashboard Widget action-input descriptor exceeds the bounded encoded size.');
        }
    }

    public function hasInput(): bool
    {
        return $this->literalBindings !== [] || $this->dynamicBindings !== [];
    }

    private function assertBindingKey(mixed $key): void
    {
        if (!is_string($key) || preg_match('/^[A-Za-z_][A-Za-z0-9_-]{0,127}$/', $key) !== 1) {
            throw new InvalidArgumentException('Dashboard Widget action-input binding keys must be bounded top-level semantic identifiers.');
        }
    }
}
