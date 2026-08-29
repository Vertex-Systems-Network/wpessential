<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];

$composerPath = $root . '/composer.json';
$composer = is_file($composerPath) ? json_decode((string) file_get_contents($composerPath), true) : null;
if (!is_array($composer)) {
    $errors[] = 'composer.json must be readable JSON.';
} else {
    if (($composer['autoload']['psr-4']['WPEssential\\'] ?? null) !== 'frameworks/') {
        $errors[] = 'WPEssential PSR-4 root must be frameworks/.';
    }
    if (!in_array('frameworks/functions.php', $composer['autoload']['files'] ?? [], true)) {
        $errors[] = 'frameworks/functions.php must be Composer-autoloaded.';
    }
}

if (!is_dir($root . '/frameworks')) {
    $errors[] = 'frameworks/ source root is missing.';
}
if (is_dir($root . '/src')) {
    $errors[] = 'Legacy src/ source root must not coexist with the canonical frameworks/ root.';
}

$entrypoint = (string) @file_get_contents($root . '/wpessential.php');
foreach ([
    "define('WPE_VERSION', '0.1.0-dev')",
    "define('WPE_AJAX_ACTION', 'wpessential_dispatch')",
    "define('WPE_NONCE_ACTION', 'wpessential_request')",
] as $requiredConstant) {
    if (!str_contains($entrypoint, $requiredConstant)) {
        $errors[] = 'Missing required entrypoint constant declaration: ' . $requiredConstant;
    }
}

$hookNames = (string) @file_get_contents($root . '/frameworks/Platform/WordPress/Hooks/HookNames.php');
if (!str_contains($hookNames, "FILTER_PREFIX = 'wpesential/apply_';")) {
    $errors[] = 'Filter prefix must remain exactly wpesential/apply_*.';
}
if (!str_contains($hookNames, "ACTION_PREFIX = 'wpessential/hook_';")) {
    $errors[] = 'Action prefix must remain exactly wpessential/hook_*.';
}

$functionsPath = $root . '/frameworks/functions.php';
$functions = (string) @file_get_contents($functionsPath);
if (preg_match_all('/\bfunction\s+([A-Za-z_][A-Za-z0-9_]*)\s*\(/', $functions, $matches)) {
    foreach ($matches[1] as $name) {
        if (!str_starts_with($name, 'wpessential_')) {
            $errors[] = 'Global function must use wpessential_* prefix: ' . $name;
        }
    }
}

$requiredFiles = [
    'frameworks/Platform/WordPress/Ajax/WordPressAjaxGateway.php',
    'frameworks/Platform/WordPress/Ajax/AjaxDispatcher.php',
    'frameworks/Platform/WordPress/Security/NonceManager.php',
    'frameworks/Platform/WordPress/Security/NonceOperation.php',
    'frameworks/Platform/WordPress/Registrations/RegistrationCompiler.php',
    'frameworks/Platform/WordPress/Registrations/RegistrationRuntimeLoader.php',
    'frameworks/Platform/Observability/FlowTrace.php',
    'frameworks/Platform/Observability/TraceMetadataSanitizer.php',
];
foreach ($requiredFiles as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing engineering-contract source: ' . $file;
    }
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/frameworks', FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php' || $file->getPathname() === $functionsPath) {
        continue;
    }
    $code = (string) file_get_contents($file->getPathname());
    if (preg_match('/\bnamespace\s+WPEssential(?:\\\\[A-Za-z0-9_\\\\]+)?\s*;/', $code) !== 1) {
        $errors[] = 'Production class file must declare WPEssential namespace: ' . substr($file->getPathname(), strlen($root) + 1);
    }

    if (!str_ends_with($file->getPathname(), 'WordPressAjaxGateway.php') && str_contains($code, "'wp_ajax_")) {
        $errors[] = 'Only WordPressAjaxGateway may register wp_ajax_* hooks: ' . substr($file->getPathname(), strlen($root) + 1);
    }
}

$smokeDir = $root . '/tests/Smoke';
if (is_dir($smokeDir)) {
    foreach (glob($smokeDir . '/*.php') ?: [] as $file) {
        $code = (string) file_get_contents($file);
        if (str_contains($code, "'/src/'") || str_contains($code, '"/src/"')) {
            $errors[] = 'Smoke test still references legacy src/: ' . basename($file);
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "WPEssential engineering contract guard FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, " - {$error}\n");
    }
    exit(1);
}

fwrite(STDOUT, "WPEssential engineering contract guard PASS\n");
fwrite(STDOUT, " - WPEssential\\ => frameworks/\n");
fwrite(STDOUT, " - exact custom hook/filter prefixes\n");
fwrite(STDOUT, " - WPE_* bootstrap constants\n");
fwrite(STDOUT, " - centralized AJAX + nonce contracts\n");
fwrite(STDOUT, " - compiled registration + observability foundations\n");
