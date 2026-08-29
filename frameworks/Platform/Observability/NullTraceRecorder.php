<?php

declare(strict_types=1);

namespace WPEssential\Platform\Observability;


if (!defined('ABSPATH')) {
    exit;
}

final class NullTraceRecorder implements TraceRecorderInterface
{
    public function start(string $correlationId): FlowTrace
    {
        return new FlowTrace($correlationId, maxEvents: 1);
    }

    public function commit(FlowTrace $trace): void {}
}
