<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflowPath = $root . '/.github/workflows/architecture-guards.yml';

if (!is_file($workflowPath)) {
    fwrite(STDERR, "architecture workflow is missing\n");
    exit(1);
}

$workflow = file_get_contents($workflowPath);
if (!is_string($workflow) || $workflow === '') {
    fwrite(STDERR, "architecture workflow could not be read\n");
    exit(1);
}

$failures = [];

if (preg_match('/^permissions:\R\s{2}contents:\s*read\s*$/m', $workflow) !== 1) {
    $failures[] = 'Architecture Guards must grant contents: read only.';
}

if (preg_match('/\bcontents:\s*write\b/', $workflow) === 1) {
    $failures[] = 'Architecture Guards must not grant contents: write.';
}

if (str_contains($workflow, 'GH_TOKEN: ${{ github.token }}')) {
    $failures[] = 'Architecture Guards must not export github.token for repository writes.';
}

if (preg_match('/\bgit\b[^\r\n]*\bpush\b/', $workflow) === 1) {
    $failures[] = 'Architecture Guards must not push repository changes.';
}

if (preg_match('/persist-credentials:\s*false/', $workflow) !== 1) {
    $failures[] = 'Architecture Guards checkout must keep persisted credentials disabled.';
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }
    exit(1);
}

echo "security-ci-permissions-contract: ok\n";
