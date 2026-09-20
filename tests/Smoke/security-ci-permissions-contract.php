<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$workflowDir = $root . '/.github/workflows';
$gatewayPath = $root . '/frameworks/Platform/WordPress/Ajax/WordPressAjaxGateway.php';
$securityWorkflowPath = $workflowDir . '/security-audit.yml';
$composerLockPath = $root . '/composer.lock';

$failures = [];
$workflowPaths = glob($workflowDir . '/*.yml') ?: [];
sort($workflowPaths, SORT_STRING);

if ($workflowPaths === []) {
    $failures[] = 'No GitHub Actions workflows were found.';
}

foreach ($workflowPaths as $workflowPath) {
    $workflow = file_get_contents($workflowPath);
    $name = basename($workflowPath);
    if (!is_string($workflow) || $workflow === '') {
        $failures[] = sprintf('%s could not be read.', $name);
        continue;
    }

    if (str_contains($workflow, 'pull_request_target')) {
        $failures[] = sprintf('%s must not use pull_request_target.', $name);
    }
    if (preg_match('/\\bcontents:\\s*write\\b/', $workflow) === 1) {
        $failures[] = sprintf('%s must not grant contents: write.', $name);
    }
    if (preg_match('/^permissions:\\R(?:(?:  [^\\r\\n]+)\\R)*  contents:\\s*read\\s*$/m', $workflow) !== 1) {
        $failures[] = sprintf('%s must declare top-level contents: read.', $name);
    }

    $lines = preg_split('/\\R/', $workflow) ?: [];
    foreach ($lines as $index => $line) {
        if (preg_match('/^\\s*uses:\\s*([^\\s#]+)\\s*$/', $line, $match) === 1) {
            $uses = $match[1];
            if (!str_starts_with($uses, './') && preg_match('/^[^@\\s]+@[0-9a-f]{40}$/', $uses) !== 1) {
                $failures[] = sprintf('%s contains a mutable/unpinned action reference: %s', $name, $uses);
            }
        }

        if (preg_match('/^\\s*uses:\\s*actions\\/checkout@[0-9a-f]{40}\\s*$/', $line) !== 1) {
            continue;
        }

        $persistDisabled = false;
        $lineCount = count($lines);
        for ($cursor = $index + 1; $cursor < $lineCount; $cursor++) {
            $next = $lines[$cursor];
            if (preg_match('/^\\s{6}-\\s+(?:name|uses|run):/', $next) === 1) {
                break;
            }
            if (preg_match('/^\\s*persist-credentials:\\s*false\\s*$/', $next) === 1) {
                $persistDisabled = true;
                break;
            }
        }

        if (!$persistDisabled) {
            $failures[] = sprintf('%s checkout must set persist-credentials: false.', $name);
        }
    }
}

$gateway = is_file($gatewayPath) ? file_get_contents($gatewayPath) : false;
if (!is_string($gateway) || $gateway === '') {
    $failures[] = 'WordPress AJAX gateway could not be read.';
} elseif (str_contains($gateway, "wp_ajax_nopriv_")) {
    $failures[] = 'Authenticated-only AJAX gateway must not register a nopriv hook.';
}

$securityWorkflow = is_file($securityWorkflowPath) ? file_get_contents($securityWorkflowPath) : false;
if (!is_string($securityWorkflow) || $securityWorkflow === '') {
    $failures[] = 'Dedicated security audit workflow is missing.';
} else {
    foreach (['composer audit --locked --no-interaction', 'npm audit --audit-level=high'] as $required) {
        if (!str_contains($securityWorkflow, $required)) {
            $failures[] = sprintf('Security workflow must execute: %s', $required);
        }
    }
}

$composerLock = is_file($composerLockPath) ? file_get_contents($composerLockPath) : false;
if (!is_string($composerLock) || $composerLock === '') {
    $failures[] = 'composer.lock is missing.';
} else {
    try {
        $lock = json_decode($composerLock, true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        $failures[] = 'composer.lock is invalid JSON: ' . $exception->getMessage();
        $lock = [];
    }

    $packages = array_merge(
        is_array($lock['packages'] ?? null) ? $lock['packages'] : [],
        is_array($lock['packages-dev'] ?? null) ? $lock['packages-dev'] : [],
    );
    $phpunitVersion = null;
    foreach ($packages as $package) {
        if (is_array($package) && ($package['name'] ?? null) === 'phpunit/phpunit') {
            $phpunitVersion = ltrim((string) ($package['version'] ?? ''), 'v');
            break;
        }
    }
    if ($phpunitVersion === null || version_compare($phpunitVersion, '11.5.50', '<')) {
        $failures[] = 'Locked PHPUnit must be >= 11.5.50 to exclude CVE-2026-24765 affected releases.';
    }
}

if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }
    exit(1);
}

printf(
    "security-ci-permissions-contract: ok (%d workflows checked)\n",
    count($workflowPaths),
);
