<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$terminalWorkflows = [
    '.github/workflows/p006-b6a-fp89-platform-api-candidate.yml',
    '.github/workflows/p006-b6b-fp86-fail-once-marker.yml',
    '.github/workflows/p006-b6c-fp94-diagnostics-redaction.yml',
    '.github/workflows/p006-b6d-fp90-disposable-db-snapshot.yml',
    '.github/workflows/p006-manual-replacement-harness.yml',
    '.github/workflows/p006-publication-owner-harness.yml',
    '.github/workflows/p006-wave1-artifact-evidence.yml',
    '.github/workflows/p006-wave1b-local-compatibility.yml',
    '.github/workflows/p006-wave1c-wordpress-runtime-evidence.yml',
    '.github/workflows/p006-wave1d-compatible-pair-runtime-evidence.yml',
    '.github/workflows/p006-wave1e-inactive-dependency-runtime-evidence.yml',
    '.github/workflows/p006-wave1f-marketing-version-mismatch-evidence.yml',
    '.github/workflows/p006-wave1g-mismatch-context-runtime-evidence.yml',
    '.github/workflows/p006-wave1h-static-harness-evidence.yml',
    '.github/workflows/p006-wave1i-runtime-evidence.yml',
    '.github/workflows/p006-wave1j-range-semantics.yml',
    '.github/workflows/p006-wave1k-compatible-update-order.yml',
    '.github/workflows/p006-wave1l-breaking-update-order.yml',
    '.github/workflows/p006-wave1m-fp58-manual-replacement.yml',
    '.github/workflows/p006-wave1n-fp49-free-interruption.yml',
    '.github/workflows/p006-wave1o-fp51-pro-partial-files.yml',
    '.github/workflows/p006-wave1p-fp52-free-partial-files.yml',
    '.github/workflows/p006-wave1q-fp50-pro-interruption.yml',
    '.github/workflows/p006-wave1r-fp21-fp22-overlap-boot.yml',
    '.github/workflows/p006-wave1s-fp33-activation-lifecycle.yml',
    '.github/workflows/p006-wave1t-schema-migration.yml',
];

$failures = [];

foreach ($terminalWorkflows as $relative) {
    $path = $root . '/' . $relative;

    if (!is_file($path)) {
        $failures[] = $relative . ' is missing.';
        continue;
    }

    $workflow = file_get_contents($path);
    if (!is_string($workflow) || $workflow === '') {
        $failures[] = $relative . ' could not be read.';
        continue;
    }

    $onStart = strpos($workflow, "on:\n");
    $permissionsStart = strpos($workflow, "\npermissions:", $onStart === false ? 0 : $onStart);

    if ($onStart === false || $permissionsStart === false || $permissionsStart <= $onStart) {
        $failures[] = $relative . ' does not expose a parseable top-level trigger block.';
        continue;
    }

    $triggerBlock = substr($workflow, $onStart, $permissionsStart - $onStart);

    if (!is_string($triggerBlock) || preg_match('/^\s{2}workflow_dispatch\s*:\s*$/m', $triggerBlock) !== 1) {
        $failures[] = $relative . ' must retain workflow_dispatch.';
    }

    foreach (['pull_request', 'push', 'schedule'] as $forbiddenTrigger) {
        if (preg_match('/^\s{2}' . preg_quote($forbiddenTrigger, '/') . '\s*:/m', $triggerBlock) === 1) {
            $failures[] = sprintf(
                '%s is terminal and must not auto-run on %s.',
                $relative,
                $forbiddenTrigger
            );
        }
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . PHP_EOL);
    }

    exit(1);
}

echo sprintf(
    "runner-benchmark-terminal-p006-workflows-contract: ok (%d terminal workflows are manual-dispatch only)\n",
    count($terminalWorkflows)
);
