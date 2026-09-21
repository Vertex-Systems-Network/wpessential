<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$failures = [];

$limits = [
    '.ai/state/CURRENT-STATE.yaml' => 12 * 1024,
    '.ai/state/LAST-CHECKPOINT.md' => 16 * 1024,
    '.ai/state/EXECUTION-JOURNAL.md' => 32 * 1024,
];

$contents = [];

foreach ($limits as $relative => $maxBytes) {
    $path = $root . '/' . $relative;
    if (!is_file($path)) {
        $failures[] = $relative . ' is missing.';
        continue;
    }

    $size = filesize($path);
    if (!is_int($size) || $size < 1) {
        $failures[] = $relative . ' is empty or unreadable.';
        continue;
    }
    if ($size > $maxBytes) {
        $failures[] = sprintf('%s exceeds compact-state limit (%d > %d bytes).', $relative, $size, $maxBytes);
    }

    $content = file_get_contents($path);
    if (!is_string($content) || $content === '') {
        $failures[] = $relative . ' could not be read.';
        continue;
    }

    $contents[$relative] = $content;
}

$current = $contents['.ai/state/CURRENT-STATE.yaml'] ?? '';

$requiredCurrentPatterns = [
    '/^schema_version:\s*1\s*$/m',
    '/^repository:\s*Vertex-Systems-Network\/wpessential\s*$/m',
    '/^state_policy:\s*GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001\s*$/m',
    '/^observed_main_sha:\s*[0-9a-f]{40}\s*$/m',
    '/^active_issue:\s*(?:null|"#\d+")\s*$/m',
    '/^active_pr:\s*(?:null|"#\d+")\s*$/m',
    '/^active_branch:\s*\S+\s*$/m',
    '/^current_milestone:\s*".+"\s*$/m',
    '/^milestone_status:\s*[A-Z_]+\s*$/m',
    '/^last_completed_milestone:\s*".+"\s*$/m',
    '/^next_safe_action:\s*".+"\s*$/m',
    '/^\s{2}one_logical_milestone_per_user_turn:\s*true\s*$/m',
    '/^\s{2}max_consolidated_ci_status_refreshes_per_milestone:\s*1\s*$/m',
    '/^\s{2}tight_polling_loops_forbidden:\s*true\s*$/m',
    '/^\s{2}durable_state_write_before_completion_report:\s*true\s*$/m',
    '/^\s{2}historical_checkpoint_full_read_default:\s*false\s*$/m',
];

foreach ($requiredCurrentPatterns as $pattern) {
    if (preg_match($pattern, $current) !== 1) {
        $failures[] = 'CURRENT-STATE.yaml is missing a mandatory timeout-resilience contract field: ' . $pattern;
    }
}

$requiredReferences = [
    'AGENTS.md' => [
        '.ai/state/CURRENT-STATE.yaml',
        'docs/AI/TIMEOUT-RESILIENT-EXECUTION-POLICY.md',
    ],
    'AUTO-AGENT.md' => [
        '.ai/state/CURRENT-STATE.yaml',
        'docs/AI/TIMEOUT-RESILIENT-EXECUTION-POLICY.md',
    ],
    'docs/ENGINEERING-EXECUTION-GOVERNANCE.md' => [
        'GOV-AI-NATIVE-TIMEOUT-RESILIENCE-001',
    ],
    'docs/AI/RUNNER-BENCHMARK-EXECUTION-POLICY.md' => [
        'tight CI/status polling',
    ],
];

foreach ($requiredReferences as $relative => $needles) {
    $path = $root . '/' . $relative;
    $content = is_file($path) ? file_get_contents($path) : false;
    if (!is_string($content)) {
        $failures[] = $relative . ' is missing or unreadable.';
        continue;
    }

    foreach ($needles as $needle) {
        if (!str_contains($content, $needle)) {
            $failures[] = sprintf('%s must reference "%s".', $relative, $needle);
        }
    }
}

$queuePath = $root . '/config/coordination/agent-work-queue.json';
$queue = json_decode((string) file_get_contents($queuePath), true);
if (!is_array($queue) || !isset($queue['slots']) || !is_array($queue['slots'])) {
    $failures[] = 'agent-work-queue.json is invalid or missing slots.';
} else {
    $expectedCompleted = [
        'p006-b6a-fp89-platform-api-candidate-v1',
        'p006-b6b-fp86-fail-once-marker-v1',
        'p006-b6c-fp94-diagnostics-redaction-v1',
    ];
    $states = [];
    foreach ($queue['slots'] as $slot) {
        if (is_array($slot) && isset($slot['slot_id'], $slot['status'])) {
            $states[(string) $slot['slot_id']] = (string) $slot['status'];
        }
    }
    foreach ($expectedCompleted as $slotId) {
        if (($states[$slotId] ?? null) !== 'COMPLETED') {
            $failures[] = $slotId . ' must be reconciled to COMPLETED after its merged prerequisite PR.';
        }
    }
}

$benchmarkPath = $root . '/config/coordination/runner-benchmark.json';
$benchmark = json_decode((string) file_get_contents($benchmarkPath), true);
if (!is_array($benchmark) || !isset($benchmark['tasks']) || !is_array($benchmark['tasks'])) {
    $failures[] = 'runner-benchmark.json is invalid or missing tasks.';
} else {
    $byId = [];
    foreach ($benchmark['tasks'] as $task) {
        if (is_array($task) && isset($task['id'])) {
            $byId[(string) $task['id']] = $task;
        }
    }

    if (($byId['RB-0004']['status'] ?? null) !== 'PASS') {
        $failures[] = 'RB-0004 must be PASS after Security PR #1111 merge.';
    }
    $evidence = $byId['RB-0004']['evidence'] ?? [];
    if (!is_array($evidence) || !array_filter($evidence, static fn ($item): bool => is_string($item) && str_contains($item, 'PR #1111 merged'))) {
        $failures[] = 'RB-0004 must record PR #1111 merged evidence.';
    }
    if (!isset($byId['RB-0006'])) {
        $failures[] = 'RB-0006 timeout-resilient AI-state exact-head validation entry is missing.';
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . PHP_EOL);
    }
    exit(1);
}

echo "ai-timeout-resilient-state-contract: ok" . PHP_EOL;
