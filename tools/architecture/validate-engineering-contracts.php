<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$errors = [];
$directAccessPattern = "/if\\s*\\(\\s*!\\s*defined\\s*\\(\\s*['\"]ABSPATH['\"]\\s*\\)\\s*\\)\\s*\\{\\s*exit\\s*;\\s*\\}/s";

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
    if (($composer['license'] ?? null) !== 'GPL-3.0-or-later') {
        $errors[] = 'Composer license must match repository GPL v3 metadata: GPL-3.0-or-later.';
    }
}

if (!is_dir($root . '/frameworks')) {
    $errors[] = 'frameworks/ source root is missing.';
}
if (is_dir($root . '/src')) {
    $errors[] = 'Legacy src/ source root must not coexist with the canonical frameworks/ root.';
}

$entrypointPath = $root . '/wpessential.php';
$entrypoint = (string) @file_get_contents($entrypointPath);
foreach ([
    'Plugin URI: https://wpessential.org',
    'Description: Modular WordPress application platform for structured data, automation, integrations, admin tooling, workflows, and AI-ready operations.',
    'Version: 0.1.0-dev',
    'Requires at least: 6.9',
    'Requires PHP: 8.2',
    'Author: VSN Team',
    'Author URI: https://wpessential.org',
    'License: GPL-3.0-or-later',
    "define('WPE_VERSION', '0.1.0-dev')",
    "define('WPE_AJAX_ACTION', 'wpessential_dispatch')",
    "define('WPE_NONCE_ACTION', 'wpessential_request')",
] as $requiredMarker) {
    if (!str_contains($entrypoint, $requiredMarker)) {
        $errors[] = 'Missing required entrypoint metadata/constant marker: ' . $requiredMarker;
    }
}
if (preg_match($directAccessPattern, $entrypoint) !== 1) {
    $errors[] = 'Main plugin entrypoint must fail closed when ABSPATH is undefined.';
}

$readmePath = $root . '/readme.txt';
$wordpressReadme = (string) @file_get_contents($readmePath);
foreach ([
    '=== WPEssential ===',
    'Requires at least: 6.9',
    'Tested up to: 7.1',
    'Requires PHP: 8.2',
    'Stable tag: 0.1.0-dev',
    'License: GPLv3 or later',
    'https://wpessential.org',
] as $requiredMarker) {
    if (!str_contains($wordpressReadme, $requiredMarker)) {
        $errors[] = 'WordPress.org readme missing required marker: ' . $requiredMarker;
    }
}
if (!is_file($root . '/CONTRIBUTING.md')) {
    $errors[] = 'CONTRIBUTING.md is required for contribution/release governance.';
} else {
    $contributing = (string) file_get_contents($root . '/CONTRIBUTING.md');
    foreach (['wpesential/apply_*', 'wpessential/hook_*', 'ABSPATH', 'WordPress.org release checklist'] as $marker) {
        if (!str_contains($contributing, $marker)) {
            $errors[] = 'CONTRIBUTING.md missing engineering/release marker: ' . $marker;
        }
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
    'frameworks/Platform/WordPress/Registrations/AtomicCompiledRegistrationStore.php',
    'frameworks/Platform/WordPress/Registrations/WpdbCompiledRegistrationPersistenceGateway.php',
    'frameworks/Platform/WordPress/Registrations/Migrations/CreateCompiledRegistrationTablesMigration.php',
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
    if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
        continue;
    }

    $relative = substr($file->getPathname(), strlen($root) + 1);
    $code = (string) file_get_contents($file->getPathname());
    if (preg_match($directAccessPattern, $code) !== 1) {
        $errors[] = 'Production PHP file must contain the canonical ABSPATH direct-access guard: ' . $relative;
    }

    if ($file->getPathname() === $functionsPath) {
        continue;
    }

    if (preg_match('/\bnamespace\s+WPEssential(?:\\\\[A-Za-z0-9_\\\\]+)?\s*;/', $code) !== 1) {
        $errors[] = 'Production class file must declare WPEssential namespace: ' . $relative;
    }

    if (!str_ends_with($file->getPathname(), 'WordPressAjaxGateway.php') && str_contains($code, "'wp_ajax_")) {
        $errors[] = 'Only WordPressAjaxGateway may register wp_ajax_* hooks: ' . $relative;
    }
}

foreach ([$root . '/tests/Smoke', $root . '/tests/Integration'] as $testDir) {
    if (!is_dir($testDir)) {
        continue;
    }
    foreach (glob($testDir . '/*.php') ?: [] as $file) {
        $code = (string) file_get_contents($file);
        if (str_contains($code, "'/src/'") || str_contains($code, '"/src/"')) {
            $errors[] = 'Test still references legacy src/: ' . basename($file);
        }
        if (preg_match("/define\\s*\\(\\s*['\"]ABSPATH['\"]/", $code) !== 1) {
            $errors[] = 'Smoke/integration entrypoint must define ABSPATH before loading guarded production source: ' . basename($file);
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
fwrite(STDOUT, " - canonical WordPress.org metadata + contribution contract\n");
fwrite(STDOUT, " - ABSPATH direct-access guards on all production PHP source\n");
fwrite(STDOUT, " - centralized AJAX + nonce contracts\n");
fwrite(STDOUT, " - atomic compiled registration persistence + observability foundations\n");
