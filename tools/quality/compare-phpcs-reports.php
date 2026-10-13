<?php

declare(strict_types=1);

/**
 * Diagnostic-only comparison of two PHPCS JSON reports.
 * This tool does not change, replace, or weaken the full-tree PHPCS CI gate.
 */
function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(2);
}

function relativePath(string $path, ?string $root): string
{
    $path = str_replace('\\', '/', $path);
    if (str_contains($path, "\0")) {
        fail('PHPCS report contains a NUL path.');
    }

    if ($root !== null) {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        if ($root === '') {
            fail('A non-empty root directory is required.');
        }
        if (str_starts_with($path, $root . '/')) {
            $path = substr($path, strlen($root) + 1);
        }
    }

    if (str_starts_with($path, './')) {
        $path = substr($path, 2);
    }
    if ($path === '' || str_starts_with($path, '/') || preg_match('~^[a-z]:/~i', $path)
        || preg_match('~(^|/)\.\.(/|$)~', $path)) {
        fail('Unanchored or unsafe PHPCS report path: ' . $path);
    }
    return $path;
}

/** @return array<string,array{path:string,type:string,source:string,message:string,count:int}> */
function reportSignatures(string $filename, ?string $root): array
{
    if (!is_file($filename) || !is_readable($filename)) {
        fail('Missing or unreadable PHPCS report: ' . $filename);
    }
    try {
        $data = json_decode((string) file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        fail('Invalid PHPCS JSON: ' . $e->getMessage());
    }
    if (!is_array($data) || !isset($data['files']) || !is_array($data['files'])) {
        fail('PHPCS JSON must contain a files object.');
    }

    $counts = [];
    foreach ($data['files'] as $file => $entry) {
        if (!is_string($file) || !is_array($entry) || !isset($entry['messages']) || !is_array($entry['messages']) || !array_is_list($entry['messages'])) {
            fail('Malformed PHPCS file entry.');
        }
        $path = relativePath($file, $root);
        foreach ($entry['messages'] as $message) {
            if (!is_array($message) || !isset($message['type'], $message['source'], $message['message'])
                || !is_string($message['type']) || !in_array($message['type'], ['ERROR', 'WARNING'], true)
                || !is_string($message['source']) || $message['source'] === ''
                || !is_string($message['message']) || $message['message'] === '') {
                fail('Malformed PHPCS message in ' . $path);
            }
            $key = json_encode([$path, $message['type'], $message['source'], $message['message']], JSON_THROW_ON_ERROR);
            if (!isset($counts[$key])) {
                $counts[$key] = ['path' => $path, 'type' => $message['type'], 'source' => $message['source'], 'message' => $message['message'], 'count' => 0];
            }
            ++$counts[$key]['count'];
        }
    }
    ksort($counts, SORT_STRING);
    return $counts;
}

if (!in_array(count($argv), [3, 5], true)) {
    fail('Usage: php compare-phpcs-reports.php BASELINE.json CANDIDATE.json [BASELINE_ROOT CANDIDATE_ROOT]');
}

$baseline = reportSignatures($argv[1], $argv[3] ?? null);
$candidate = reportSignatures($argv[2], $argv[4] ?? null);
$regressions = [];
$resolutions = [];
foreach (array_unique(array_merge(array_keys($baseline), array_keys($candidate))) as $key) {
    $before = $baseline[$key]['count'] ?? 0;
    $after = $candidate[$key]['count'] ?? 0;
    if ($after > $before) {
        $regressions[] = [...$candidate[$key], 'baseline' => $before, 'added' => $after - $before];
    } elseif ($before > $after) {
        $resolutions[] = [...$baseline[$key], 'candidate' => $after, 'removed' => $before - $after];
    }
}

$result = [
    'classification' => $regressions === [] ? 'NO_SIGNATURE_COUNT_INCREASE' : 'REGRESSIONS_DETECTED',
    'baseline_messages' => array_sum(array_column($baseline, 'count')),
    'candidate_messages' => array_sum(array_column($candidate, 'count')),
    'added_count' => array_sum(array_column($regressions, 'added')),
    'removed_count' => array_sum(array_column($resolutions, 'removed')),
    'regressions' => $regressions,
    'resolutions' => $resolutions,
    'note' => 'Diagnostic-only signature counts, not line-level/new-code proof; full-tree PHPCS remains mandatory.',
];
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit($regressions === [] ? 0 : 1);
