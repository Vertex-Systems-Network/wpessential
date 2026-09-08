<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Transition\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Modules\Status\Transition\StatusTransitionRule;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class StatusTransitionPolicyDefinitionCompiler
{
    public const OWNER_SURFACE_ID = 5;
    public const TYPE = 'status-transition-policy';
    public const SCHEMA_VERSION = 1;
    private const MAX_EDGES = 256;

    /** @var list<string> */
    private const PAYLOAD_KEYS = ['edges'];

    /** @var list<string> */
    private const EDGE_KEYS = [
        'from',
        'to',
        'capability',
        'reason_required',
        'bulk_allowed',
        'programmatic_allowed',
    ];

    public function compile(Definition $definition): StatusTransitionPolicy
    {
        if ($definition->type !== self::TYPE || $definition->ownerSurfaceId !== self::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Definition is not owned by the canonical Status transition-policy surface contract.');
        }
        if ($definition->schemaVersion !== self::SCHEMA_VERSION) {
            throw new InvalidArgumentException('Status transition-policy Definition schema version is unsupported.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Status transition-policy Definitions may compile.');
        }
        if ($definition->dependencies !== []) {
            throw new InvalidArgumentException('Status transition-policy Definition dependencies are outside bounded V1.');
        }

        $payload = $definition->payload;
        if (array_is_list($payload)) {
            throw new InvalidArgumentException('Status transition-policy payload must be an object/map.');
        }
        $this->assertKnownKeys($payload, self::PAYLOAD_KEYS, 'Status transition-policy payload');

        $edges = $payload['edges'] ?? null;
        if (!is_array($edges) || !array_is_list($edges) || count($edges) > self::MAX_EDGES) {
            throw new InvalidArgumentException('Status transition-policy edges must be a bounded list.');
        }

        $rules = [];
        foreach ($edges as $edge) {
            if (!is_array($edge) || array_is_list($edge)) {
                throw new InvalidArgumentException('Status transition-policy edge must be an object/map.');
            }
            $this->assertKnownKeys($edge, self::EDGE_KEYS, 'Status transition-policy edge');

            $from = $edge['from'] ?? null;
            $to = $edge['to'] ?? null;
            $capability = $edge['capability'] ?? null;
            $reasonRequired = $edge['reason_required'] ?? false;
            $bulkAllowed = $edge['bulk_allowed'] ?? false;
            $programmaticAllowed = $edge['programmatic_allowed'] ?? false;

            if (!is_string($from) || !is_string($to)) {
                throw new InvalidArgumentException('Status transition-policy edge from/to values must be strings.');
            }
            if ($capability !== null && !is_string($capability)) {
                throw new InvalidArgumentException('Status transition-policy capability must be null or string.');
            }
            foreach ([$reasonRequired, $bulkAllowed, $programmaticAllowed] as $flag) {
                if (!is_bool($flag)) {
                    throw new InvalidArgumentException('Status transition-policy flags must be boolean.');
                }
            }

            $rules[] = new StatusTransitionRule(
                from: $from,
                to: $to,
                capability: $capability,
                reasonRequired: $reasonRequired,
                bulkAllowed: $bulkAllowed,
                programmaticAllowed: $programmaticAllowed,
            );
        }

        return new StatusTransitionPolicy($rules);
    }

    /**
     * @param array<string,mixed> $value
     * @param list<string> $allowed
     */
    private function assertKnownKeys(array $value, array $allowed, string $label): void
    {
        foreach (array_keys($value) as $key) {
            if (!is_string($key) || !in_array($key, $allowed, true)) {
                throw new InvalidArgumentException($label . ' contains an unsupported key.');
            }
        }
    }
}
