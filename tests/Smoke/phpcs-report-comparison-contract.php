<?php

declare(strict_types=1);

$script = dirname(__DIR__, 2) . '/tools/quality/compare-phpcs-reports.php';
$tmp = sys_get_temp_dir() . '/wpe-phpcs-contract-' . bin2hex(random_bytes(8));
if (!mkdir($tmp, 0700) && !is_dir($tmp)) {
    throw new RuntimeException('Unable to make fixture directory.');
}

function runComparison(string $script, array $args): array
{
    $command = array_merge([PHP_BINARY, $script], $args);
    $pipes = [];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to execute comparator.');
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), (string) $stdout, (string) $stderr];
}

try {
    $error = ['type' => 'ERROR', 'source' => 'WordPress.WhiteSpace.Test', 'message' => 'Spacing missing', 'line' => 4, 'column' => 1];
    $warning = ['type' => 'WARNING', 'source' => 'WordPress.Security.Test', 'message' => 'Check escaped output', 'line' => 5, 'column' => 1];
    $base = ['files' => ['/tmp/wpe-before/frameworks/X.php' => ['messages' => [$error, $warning]]]];
    $same = ['files' => ['/tmp/wpe-after/frameworks/X.php' => ['messages' => [$error, $warning]]]];
    $new = ['files' => ['/tmp/wpe-after/frameworks/X.php' => ['messages' => [$error, $warning, $error]]]];
    $before = $tmp . '/before.json';
    $after = $tmp . '/after.json';
    file_put_contents($before, json_encode($base, JSON_THROW_ON_ERROR));
    file_put_contents($after, json_encode($new, JSON_THROW_ON_ERROR));

    [$status, $out] = runComparison($script, [$before, $after, '/tmp/wpe-before', '/tmp/wpe-after']);
    $data = json_decode($out, true, 512, JSON_THROW_ON_ERROR);
    if ($status !== 1 || $data['added_count'] !== 1 || $data['classification'] !== 'REGRESSIONS_DETECTED') {
        throw new RuntimeException('New PHPCS error did not fail closed.');
    }

    file_put_contents($after, json_encode($same, JSON_THROW_ON_ERROR));
    [$status, $out] = runComparison($script, [$before, $after, '/tmp/wpe-before', '/tmp/wpe-after']);
    $data = json_decode($out, true, 512, JSON_THROW_ON_ERROR);
    if ($status !== 0 || $data['added_count'] !== 0 || $data['baseline_messages'] !== 2) {
        throw new RuntimeException('Equivalent PHPCS reports did not pass.');
    }

    [$status] = runComparison($script, [$before, $after]);
    if ($status !== 2) {
        throw new RuntimeException('Unanchored absolute paths were accepted.');
    }

    file_put_contents($after, '{bad json');
    [$status] = runComparison($script, [$before, $after, '/tmp/wpe-before', '/tmp/wpe-after']);
    if ($status !== 2) {
        throw new RuntimeException('Malformed JSON was accepted.');
    }

    echo "PASS: PHPCS comparator error, unchanged, absolute-root and malformed-JSON contracts\n";
} finally {
    foreach (glob($tmp . '/*') ?: [] as $path) {
        unlink($path);
    }
    rmdir($tmp);
}
