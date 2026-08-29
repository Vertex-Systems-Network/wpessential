<?php

declare(strict_types=1);

namespace WPEssential\Platform\Observability;

if (!defined('ABSPATH')) {
    exit;
}

final class BoundedInMemoryTraceRecorder implements TraceRecorderInterface, TraceSnapshotReaderInterface
{
    /** @var list<array<string,mixed>> */
    private array $traces = [];

    public function __construct(private readonly int $maxTraces = 50, private readonly int $maxEvents = 500) {}

    public function start(string $correlationId): FlowTrace
    {
        return new FlowTrace($correlationId, maxEvents: $this->maxEvents);
    }

    public function commit(FlowTrace $trace): void
    {
        $this->traces[] = $trace->snapshot();
        if (count($this->traces) > $this->maxTraces) {
            array_shift($this->traces);
        }
    }

    public function all(): array
    {
        return $this->traces;
    }
}
