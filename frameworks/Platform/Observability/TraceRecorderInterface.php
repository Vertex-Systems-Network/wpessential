<?php

declare(strict_types=1);

namespace WPEssential\Platform\Observability;

interface TraceRecorderInterface
{
    public function start(string $correlationId): FlowTrace;

    public function commit(FlowTrace $trace): void;
}
