<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$workflowDir = $root . '/.github/workflows';
$architectureWorkflowPath = $workflowDir . '/architecture-guards.yml';
$failures = [];

if (!is_dir($workflowDir)) {
    fwrite(STDERR, "workflow directory is missing\n");
    exit(1);
}

$workflowPaths = glob($workflowDir . '/*.yml');
if (!is_array($workflowPaths) || $workflowPaths === []) {
    fwrite(STDERR, "no tracked workflows were found\n");
    exit(1);
}

sort($workflowPaths, SORT_STRING);

foreach ($workflowPaths as $workflowPath) {
    $workflow = file_get_contents($workflowPath);
    if (!is_string($workflow) || $workflow === '') {
        $failures[] = 'Workflow could not be read: ' . basename($workflowPath);
        continue;
    }

    $relative = '.github/workflows/' . basename($workflowPath);
    $lines = preg_split('/\R/', $workflow);
    if (!is_array($lines)) {
        $failures[] = 'Workflow could not be parsed: ' . $relative;
        continue;
    }

    foreach ($lines as $index => $line) {
        if (preg_match('/^\s*uses:\s*([^\s#]+)(?:\s+#.*)?$/', $line, $match) !== 1) {
            continue;
        }

        $reference = $match[1];

        if (str_starts_with($reference, './') || str_starts_with($reference, 'docker://')) {
            continue;
        }

        if (preg_match('/@[0-9a-f]{40}$/i', $reference) !== 1) {
            $failures[] = sprintf(
                '%s:%d external action must be pinned to an immutable 40-hex commit SHA: %s',
                $relative,
                $index + 1,
                $reference
            );
        }
    }

    for ($index = 0, $count = count($lines); $index < $count; $index++) {
        if (preg_match('/^(\s*)uses:\s*actions\/checkout@[0-9a-f]{40}\s*$/i', $lines[$index], $match) !== 1) {
            continue;
        }

        $usesIndent = strlen($match[1]);
        $stepIndent = max(0, $usesIndent - 2);
        $persistCredentialsDisabled = false;

        for ($cursor = $index + 1; $cursor < $count; $cursor++) {
            $candidate = $lines[$cursor];
            $trimmed = trim($candidate);

            if ($trimmed !== '') {
                preg_match('/^(\s*)/', $candidate, $indentMatch);
                $candidateIndent = strlen($indentMatch[1] ?? '');

                if ($candidateIndent <= $stepIndent && preg_match('/^\s*-\s+/', $candidate) === 1) {
                    break;
                }
            }

            if (preg_match('/^\s*persist-credentials:\s*false\s*$/', $candidate) === 1) {
                $persistCredentialsDisabled = true;
                break;
            }
        }

        if (!$persistCredentialsDisabled) {
            $failures[] = sprintf(
                '%s:%d actions/checkout must set persist-credentials: false.',
                $relative,
                $index + 1
            );
        }
    }

    if (preg_match('/^\s*pull_request_target\s*:/m', $workflow) === 1) {
        $failures[] = $relative . ' must not use pull_request_target without a separately reviewed security exception.';
    }
}

if (!is_file($architectureWorkflowPath)) {
    $failures[] = 'Architecture Guards workflow is missing.';
} else {
    $architectureWorkflow = file_get_contents($architectureWorkflowPath);

    if (!is_string($architectureWorkflow) || $architectureWorkflow === '') {
        $failures[] = 'Architecture Guards workflow could not be read.';
    } else {
        if (preg_match('/^permissions:\R\s{2}contents:\s*read\s*$/m', $architectureWorkflow) !== 1) {
            $failures[] = 'Architecture Guards must grant contents: read only.';
        }

        if (preg_match('/\bcontents:\s*write\b/', $architectureWorkflow) === 1) {
            $failures[] = 'Architecture Guards must not grant contents: write.';
        }

        if (
            str_contains($architectureWorkflow, 'GH_TOKEN: ${{ github.token }}')
            || str_contains($architectureWorkflow, 'secrets.GITHUB_TOKEN')
        ) {
            $failures[] = 'Architecture Guards must not export a GitHub token for repository writes.';
        }

        if (preg_match('/\bgit\b[^\r\n]*\bpush\b/', $architectureWorkflow) === 1) {
            $failures[] = 'Architecture Guards must not push repository changes.';
        }

        if (substr_count($architectureWorkflow, "- '.github/workflows/**'") < 2) {
            $failures[] = 'Architecture Guards must run for workflow changes on both pull_request and implementation pushes.';
        }
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }
    exit(1);
}

echo sprintf(
    "security-ci-permissions-contract: ok (%d workflows scanned)\n",
    count($workflowPaths)
);
