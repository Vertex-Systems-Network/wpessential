<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$guard = "if (!defined('ABSPATH')) {\n    exit;\n}";
$testBootstrap = "if (!defined('ABSPATH')) {\n    define('ABSPATH', dirname(__DIR__, 2) . '/');\n}";
$guardPattern = "/if\\s*\\(\\s*!\\s*defined\\s*\\(\\s*['\"]ABSPATH['\"]\\s*\\)\\s*\\)\\s*\\{\\s*exit\\s*;\\s*\\}/s";
$testPattern = "/define\\s*\\(\\s*['\"]ABSPATH['\"]/";
$changed = [];

/**
 * @param string $content
 */
function insertAfterDeclareOrOpenTag(string $content, string $snippet): string
{
    if (preg_match('/declare\\s*\\(\\s*strict_types\\s*=\\s*1\\s*\\)\\s*;\\s*/', $content, $match, PREG_OFFSET_CAPTURE) === 1) {
        $matched = $match[0][0];
        $offset = $match[0][1] + strlen($matched);
        return substr($content, 0, $offset) . "\n" . $snippet . "\n\n" . ltrim(substr($content, $offset), "\r\n");
    }

    $open = strpos($content, '<?php');
    if ($open === false) {
        throw new RuntimeException('PHP source does not contain an opening tag.');
    }
    $offset = $open + strlen('<?php');
    return substr($content, 0, $offset) . "\n\n" . $snippet . "\n" . substr($content, $offset);
}

/**
 * @param string $path
 * @param string $snippet
 * @param bool $production
 */
function ensureGuard(string $path, string $snippet, bool $production, string $guardPattern, string $testPattern, array &$changed): void
{
    $content = (string) file_get_contents($path);
    if ($production ? preg_match($guardPattern, $content) === 1 : preg_match($testPattern, $content) === 1) {
        return;
    }

    if ($production && preg_match('/namespace\\s+[^;]+;\\s*/', $content, $match, PREG_OFFSET_CAPTURE) === 1) {
        $matched = $match[0][0];
        $offset = $match[0][1] + strlen($matched);
        $updated = substr($content, 0, $offset) . "\n" . $snippet . "\n\n" . ltrim(substr($content, $offset), "\r\n");
    } else {
        $updated = insertAfterDeclareOrOpenTag($content, $snippet);
    }

    if (file_put_contents($path, $updated) === false) {
        throw new RuntimeException('Unable to write ' . $path);
    }
    $changed[] = $path;
}

$frameworkRoot = $root . '/frameworks';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($frameworkRoot, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'php') {
        ensureGuard($file->getPathname(), $guard, true, $guardPattern, $testPattern, $changed);
    }
}

foreach ([$root . '/tests/Smoke', $root . '/tests/Integration'] as $testDir) {
    if (!is_dir($testDir)) {
        continue;
    }
    foreach (glob($testDir . '/*.php') ?: [] as $testFile) {
        ensureGuard($testFile, $testBootstrap, false, $guardPattern, $testPattern, $changed);
    }
}

sort($changed, SORT_STRING);
fwrite(STDOUT, sprintf("Direct-access guard transformer updated %d files.\n", count($changed)));
foreach ($changed as $path) {
    fwrite(STDOUT, ' - ' . substr($path, strlen($root) + 1) . "\n");
}
