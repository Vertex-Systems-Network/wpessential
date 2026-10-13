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


/**
 * Inspect an exact Git diff without changing either checkout.
 * @return array<string,array<int,bool>>
 */
function modifiedLines(string $baseSha, string $headSha): array
{
    foreach ([$baseSha, $headSha] as $sha) {
        if (!preg_match('/^[a-f0-9]{40}$/D', $sha)) {
            fail('Changed-line mode requires two immutable full commit SHAs.');
        }
    }
    // Resolve the explicitly selected checkout, never assume the script resides in it.
    // This also permits disposable Git repositories in the independent smoke tests.
    $repository = getcwd();
    if (!is_string($repository) || !file_exists($repository . '/.git')) {
        fail('Run changed-line evidence from the selected Git checkout root.');
    }
    foreach ([$baseSha, $headSha] as $sha) {
        $verify = proc_open(
            ['git', '-C', $repository, 'cat-file', '-t', $sha],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $verifyPipes
        );
        if (!is_resource($verify)) {
            fail('Unable to verify Git commit identity.');
        }
        $kind = stream_get_contents($verifyPipes[1]);
        stream_get_contents($verifyPipes[2]);
        fclose($verifyPipes[1]);
        fclose($verifyPipes[2]);
        if (proc_close($verify) !== 0 || trim((string) $kind) !== 'commit') {
            fail('Changed-line evidence requires existing commit objects, not blobs or missing refs.');
        }
    }
    $command = [
        'git', '-C', $repository, '-c', 'core.quotePath=false',
        'diff', '--no-ext-diff', '--no-textconv', '--unified=0',
        '--find-renames', $baseSha, $headSha, '--', 'wpessential.php', 'frameworks',
    ];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        fail('Unable to launch read-only Git diff.');
    }
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0 || !is_string($output)) {
        fail('Git diff failed; refusing unverified line evidence: ' . trim((string) $error));
    }
    $changed = [];
    $currentPath = null;
    $inHunk = false;
    foreach (explode("\n", $output) as $line) {
        if (str_starts_with($line, 'diff --git ')) {
            $currentPath = null;
            $inHunk = false;
            continue;
        }
        if (!$inHunk && str_starts_with($line, '+++ ')) {
            $currentPath = str_starts_with($line, '+++ b/') ? substr($line, 6) : null;
            if ($currentPath !== null) {
                $currentPath = relativePath($currentPath, null);
            }
            continue;
        }
        if (str_starts_with($line, '@@ ')) {
            if (!preg_match('/^@@ -[0-9]+(?:,[0-9]+)? \+([0-9]+)(?:,([0-9]+))? @@/', $line, $parts)) {
                fail('Unrecognized Git hunk; cannot verify modified lines.');
            }
            $inHunk = true;
            if ($currentPath === null) {
                continue;
            }
            $start = (int) $parts[1];
            $count = isset($parts[2]) ? (int) $parts[2] : 1;
            for ($n = $start; $n < $start + $count; ++$n) {
                $changed[$currentPath][$n] = true;
            }
        }
    }
    return $changed;
}

/**
 * Flag candidate PHPCS diagnostics on added/modified source lines.
 * @param array<string,array<int,bool>> $changed
 * @return list<array{path:string,line:int,type:string,source:string,message:string}>
 */
function changedLineDiagnostics(string $filename, ?string $root, array $changed): array
{
    try {
        $data = json_decode((string) file_get_contents($filename), true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        fail('Invalid changed-line PHPCS JSON: ' . $e->getMessage());
    }
    if (!is_array($data) || !isset($data['files']) || !is_array($data['files'])) {
        fail('Changed-line PHPCS report files are missing.');
    }
    $findings = [];
    foreach ($data['files'] as $file => $entry) {
        $path = relativePath((string) $file, $root);
        foreach ($entry['messages'] as $diagnostic) {
            $line = $diagnostic['line'] ?? null;
            if (!is_int($line) || $line < 1) {
                fail('Candidate PHPCS message is missing a valid line number: ' . $path);
            }
            if (isset($changed[$path][$line])) {
                $findings[] = [
                    'path' => $path,
                    'line' => $line,
                    'type' => $diagnostic['type'],
                    'source' => $diagnostic['source'],
                    'message' => $diagnostic['message'],
                ];
            }
        }
    }
    usort($findings, static fn (array $a, array $b): int =>
        [$a['path'], $a['line'], $a['type'], $a['source'], $a['message']]
        <=> [$b['path'], $b['line'], $b['type'], $b['source'], $b['message']]
    );
    return $findings;
}

$arguments = array_slice($argv, 1);
$changedLineMode = false;
if (count($arguments) >= 5 && $arguments[count($arguments) - 3] === '--changed-lines') {
    $changedLineMode = true;
    $baseSha = $arguments[count($arguments) - 2];
    $headSha = $arguments[count($arguments) - 1];
    $arguments = array_slice($arguments, 0, -3);
}
if (!in_array(count($arguments), [2, 4], true)) {
    fail('Usage: php compare-phpcs-reports.php BASELINE.json CANDIDATE.json [BASELINE_ROOT CANDIDATE_ROOT] [--changed-lines BASE_SHA HEAD_SHA]');
}

$baseline = reportSignatures($arguments[0], $arguments[2] ?? null);
$candidate = reportSignatures($arguments[1], $arguments[3] ?? null);
$changedLineFindings = $changedLineMode
    ? changedLineDiagnostics($arguments[1], $arguments[3] ?? null, modifiedLines($baseSha, $headSha))
    : [];
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
    'classification' => $regressions !== [] ? 'REGRESSIONS_DETECTED'
        : ($changedLineFindings !== [] ? 'CHANGED_LINE_VIOLATIONS' : 'NO_SIGNATURE_COUNT_INCREASE'),
    'baseline_messages' => array_sum(array_column($baseline, 'count')),
    'candidate_messages' => array_sum(array_column($candidate, 'count')),
    'added_count' => array_sum(array_column($regressions, 'added')),
    'removed_count' => array_sum(array_column($resolutions, 'removed')),
    'regressions' => $regressions,
    'resolutions' => $resolutions,
    'note' => 'Diagnostic-only evidence; full-tree PHPCS remains mandatory. Input reports and their provenance are not authenticated.',
];
if ($changedLineMode) {
    $result['changed_line_violations_count'] = count($changedLineFindings);
    $result['changed_line_violations'] = $changedLineFindings;
    $result['base_sha'] = $baseSha;
    $result['head_sha'] = $headSha;
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
exit($regressions === [] && $changedLineFindings === [] ? 0 : 1);
