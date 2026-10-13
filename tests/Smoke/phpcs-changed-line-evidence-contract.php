<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

// Independent changed-line diagnostic contract. No enforcement gate is replaced.
$tool = dirname(__DIR__, 2) . '/tools/quality/compare-phpcs-reports.php';
$root = sys_get_temp_dir() . '/wpe-phpcs-lines-' . bin2hex(random_bytes(8));
$repository = $root . '/repo';
if (!mkdir($repository, 0700, true)) {
    throw new RuntimeException('Unable to create disposable repository.');
}

/** @param list<string> $command @return array{int,string,string} */
function evidenceCommand(array $command, string $cwd): array
{
    $pipes = [];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $cwd);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start fixture command.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), (string) $stdout, (string) $stderr];
}

/** @param list<string> $command */
function checkedEvidenceCommand(array $command, string $cwd): string
{
    [$code, $stdout, $stderr] = evidenceCommand($command, $cwd);
    if ($code !== 0) {
        throw new RuntimeException('Fixture Git command failed: ' . $stderr);
    }
    return trim($stdout);
}

try {
    checkedEvidenceCommand(['git', 'init', '-q'], $repository);
    mkdir($repository . '/frameworks');
    $file = $repository . '/frameworks/Example.php';
    file_put_contents($file, "<?php\n\$value = true;\n\$other = false;\n");
    checkedEvidenceCommand(['git', 'add', 'frameworks/Example.php'], $repository);
    checkedEvidenceCommand([
        'git', '-c', 'user.name=Test', '-c', 'user.email=test@example.test',
        'commit', '-qm', 'baseline',
    ], $repository);
    $baselineSha = checkedEvidenceCommand(['git', 'rev-parse', 'HEAD'], $repository);

    file_put_contents($file, "<?php\n\$value = true;\n\$other = true;\n");
    checkedEvidenceCommand(['git', 'add', 'frameworks/Example.php'], $repository);
    checkedEvidenceCommand([
        'git', '-c', 'user.name=Test', '-c', 'user.email=test@example.test',
        'commit', '-qm', 'candidate',
    ], $repository);
    $candidateSha = checkedEvidenceCommand(['git', 'rev-parse', 'HEAD'], $repository);

    $message = [
        'type' => 'ERROR',
        'source' => 'WordPress.WhiteSpace.Mock',
        'message' => 'Same error on a rewritten source line',
        'line' => 3,
        'column' => 1,
    ];
    $report = static fn (array $messages): string =>
        json_encode(['files' => ['frameworks/Example.php' => ['messages' => $messages]]], JSON_THROW_ON_ERROR);

    $before = $root . '/before.json';
    $after = $root . '/after.json';
    file_put_contents($before, $report([$message]));
    file_put_contents($after, $report([$message]));

    // The old comparator cannot distinguish unchanged signature counts,
    // but the optional diff-aware evidence must detect the touched line.
    [$code, $stdout] = evidenceCommand(
        [PHP_BINARY, $tool, $before, $after], $repository
    );
    $legacy = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
    if ($code !== 0 || $legacy['classification'] !== 'NO_SIGNATURE_COUNT_INCREASE') {
        throw new RuntimeException('Existing comparator compatibility regressed.');
    }

    [$code, $stdout] = evidenceCommand(
        [PHP_BINARY, $tool, $before, $after, '--changed-lines', $baselineSha, $candidateSha],
        $repository
    );
    $strict = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
    if ($code !== 1 || $strict['classification'] !== 'CHANGED_LINE_VIOLATIONS'
        || $strict['changed_line_violations_count'] !== 1
        || $strict['changed_line_violations'][0]['line'] !== 3) {
        throw new RuntimeException('Violation on rewritten line did not fail closed.');
    }

    // Unchanged legacy violations on untouched lines are not fabricated as new.
    $message['line'] = 2;
    file_put_contents($after, $report([$message]));
    [$code, $stdout] = evidenceCommand(
        [PHP_BINARY, $tool, $before, $after, '--changed-lines', $baselineSha, $candidateSha],
        $repository
    );
    $untouched = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
    if ($code !== 0 || $untouched['changed_line_violations_count'] !== 0) {
        throw new RuntimeException('Unchanged legacy line incorrectly marked newly edited.');
    }

    // Missing provenance or line metadata cannot yield a green differential.
    [$code] = evidenceCommand(
        [PHP_BINARY, $tool, $before, $after, '--changed-lines', 'bad-sha', $candidateSha],
        $repository
    );
    if ($code !== 2) {
        throw new RuntimeException('Non-immutable baseline identifier was accepted.');
    }

    $missingLine = $message;
    unset($missingLine['line']);
    file_put_contents($after, $report([$missingLine]));
    [$code] = evidenceCommand(
        [PHP_BINARY, $tool, $before, $after, '--changed-lines', $baselineSha, $candidateSha],
        $repository
    );
    if ($code !== 2) {
        throw new RuntimeException('Diagnostic with missing line metadata was accepted.');
    }

    echo "PASS: optional changed-line PHPCS evidence, unchanged legacy, invalid SHA and invalid metadata\n";
} finally {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $path) {
        if ($path->isDir() && !$path->isLink()) {
            rmdir($path->getPathname());
        } else {
            unlink($path->getPathname());
        }
    }
    rmdir($root);
}
