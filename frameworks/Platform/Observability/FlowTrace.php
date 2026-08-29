<?php

declare(strict_types=1);

namespace WPEssential\Platform\Observability;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class FlowTrace
{
    /** @var array<string,array<string,mixed>> */
    private array $nodes = [];
    /** @var list<array<string,mixed>> */
    private array $edges = [];
    /** @var list<array<string,mixed>> */
    private array $events = [];
    /** @var array<string,mixed>|null */
    private ?array $failureBoundary = null;

    public function __construct(
        public readonly string $correlationId,
        private readonly TraceMetadataSanitizer $sanitizer = new TraceMetadataSanitizer(),
        private readonly int $maxEvents = 500,
    ) {
        if (trim($this->correlationId) === '' || $this->maxEvents < 1) {
            throw new InvalidArgumentException('Trace identity or budget is invalid.');
        }
    }

    /** @param array<string,mixed> $metadata */
    public function node(string $id, string $component, array $metadata = []): void
    {
        $this->nodes[$id] = ['component' => $component, 'metadata' => $this->sanitizer->sanitize($metadata)];
    }

    /** @param array<string,mixed> $metadata */
    public function edge(string $from, string $to, string $operation, array $metadata = []): void
    {
        $this->edges[] = ['from' => $from, 'to' => $to, 'operation' => $operation, 'metadata' => $this->sanitizer->sanitize($metadata)];
    }

    /** @param array<string,mixed> $metadata */
    public function checkpoint(string $component, string $operation, array $metadata = []): void
    {
        if (count($this->events) >= $this->maxEvents) {
            return;
        }
        $this->events[] = [
            'sequence' => count($this->events) + 1,
            'component' => $component,
            'operation' => $operation,
            'status' => 'ok',
            'metadata' => $this->sanitizer->sanitize($metadata),
        ];
    }

    /** @param array<string,mixed> $metadata */
    public function fail(string $component, string $operation, string $reason, array $metadata = []): void
    {
        $last = $this->events === [] ? null : $this->events[array_key_last($this->events)];
        $failed = [
            'sequence' => count($this->events) + 1,
            'component' => $component,
            'operation' => $operation,
            'status' => 'failed',
            'reason' => $reason,
            'metadata' => $this->sanitizer->sanitize($metadata),
        ];
        if (count($this->events) < $this->maxEvents) {
            $this->events[] = $failed;
        }
        $this->failureBoundary = ['last_successful' => $last, 'failed' => $failed];
    }

    /** @return array<string,mixed> */
    public function snapshot(): array
    {
        return [
            'correlation_id' => $this->correlationId,
            'nodes' => $this->nodes,
            'edges' => $this->edges,
            'events' => $this->events,
            'failure_boundary' => $this->failureBoundary,
        ];
    }
}
