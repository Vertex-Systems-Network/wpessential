<?php

declare(strict_types=1);

const WPE_PACKAGE_SLUG = 'wpessential';
const WPE_PACKAGE_MTIME = 946684800; // 2000-01-01T00:00:00Z, valid for ZIP and intentionally fixed.

function fail(string $message): never
{
    fwrite(STDERR, "[package] {$message}\n");
    exit(1);
}

function ensureDirectory(string $path): void
{
    if (is_dir($path)) {
        return;
    }

    if (!mkdir($path, 0775, true) && !is_dir($path)) {
        fail("Unable to create directory: {$path}");
    }
}

function removeTree(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    if (is_file($path) || is_link($path)) {
        @unlink($path);
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $itemPath = $item->getPathname();
        if ($item->isDir() && !$item->isLink()) {
            @rmdir($itemPath);
        } else {
            @unlink($itemPath);
        }
    }

    @rmdir($path);
}

/** @return list<string> */
function relativeFiles(string $root): array
{
    if (!is_dir($root)) {
        fail("Expected directory is missing: {$root}");
    }

    $root = rtrim($root, '/\\');
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if ($item->isLink()) {
            fail('Symlinks are not permitted in the distributable payload: ' . $item->getPathname());
        }
        if (!$item->isFile()) {
            continue;
        }

        $relative = substr($item->getPathname(), strlen($root) + 1);
        $relative = str_replace('\\', '/', $relative);
        if ($relative === '' || str_contains($relative, '../')) {
            fail('Unsafe package path detected: ' . $relative);
        }
        $files[] = $relative;
    }

    sort($files, SORT_STRING);
    return array_values($files);
}

function copyFileNormalized(string $source, string $destination): void
{
    if (!is_file($source) || is_link($source)) {
        fail("Expected regular source file is missing: {$source}");
    }

    ensureDirectory(dirname($destination));
    if (!copy($source, $destination)) {
        fail("Unable to copy {$source} to {$destination}");
    }
    @chmod($destination, 0644);
    @touch($destination, WPE_PACKAGE_MTIME);
}

function copyTreeNormalized(string $sourceRoot, string $destinationRoot): void
{
    foreach (relativeFiles($sourceRoot) as $relative) {
        copyFileNormalized(
            rtrim($sourceRoot, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative),
            rtrim($destinationRoot, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );
    }
}

function runCommand(string $command): void
{
    fwrite(STDOUT, "[package] {$command}\n");
    passthru($command, $exitCode);
    if ($exitCode !== 0) {
        fail("Command failed with exit code {$exitCode}");
    }
}

function assertProjectMetadata(string $root): void
{
    $plugin = file_get_contents($root . '/wpessential.php');
    $readme = file_get_contents($root . '/readme.txt');
    $license = file_get_contents($root . '/LICENSE');
    $composerRaw = file_get_contents($root . '/composer.json');
    $lockRaw = file_get_contents($root . '/composer.lock');

    if ($plugin === false || $readme === false || $license === false || $composerRaw === false || $lockRaw === false) {
        fail('Unable to read required package metadata.');
    }

    $requiredPluginMetadata = [
        'Plugin Name: WPEssential',
        'Version: 0.1.0-dev',
        'Requires at least: 6.9',
        'Requires PHP: 8.2',
        'License: GPL-3.0-or-later',
    ];
    foreach ($requiredPluginMetadata as $needle) {
        if (!str_contains($plugin, $needle)) {
            fail("Plugin metadata is missing required value: {$needle}");
        }
    }

    $requiredReadmeMetadata = [
        'Requires at least: 6.9',
        'Requires PHP: 8.2',
        'Stable tag: 0.1.0-dev',
        'License: GPLv3 or later',
    ];
    foreach ($requiredReadmeMetadata as $needle) {
        if (!str_contains($readme, $needle)) {
            fail("readme.txt is missing required value: {$needle}");
        }
    }

    if (!str_contains($license, 'GNU GENERAL PUBLIC LICENSE') || !str_contains($license, 'Version 3, 29 June 2007')) {
        fail('LICENSE is not the expected GNU GPL v3 license text.');
    }

    $composer = json_decode($composerRaw, true, flags: JSON_THROW_ON_ERROR);
    if (($composer['license'] ?? null) !== 'GPL-3.0-or-later') {
        fail('composer.json license must remain GPL-3.0-or-later.');
    }

    $lock = json_decode($lockRaw, true, flags: JSON_THROW_ON_ERROR);
    $runtimePackages = $lock['packages'] ?? null;
    if (!is_array($runtimePackages)) {
        fail('composer.lock does not expose a runtime package list.');
    }
    if ($runtimePackages !== []) {
        fail('Runtime Composer dependencies now exist. Add an explicit distribution-license review before packaging them.');
    }
}

/** @return list<string> */
function requiredAdminBuildArtifacts(): array
{
    return [
        'assets/admin/main.js',
        'assets/admin/main.css',
        'assets/admin/main.asset.php',
        'assets/admin/taxonomy.js',
        'assets/admin/taxonomy.css',
        'assets/admin/taxonomy.asset.php',
        'assets/admin/import-export.js',
        'assets/admin/import-export.css',
        'assets/admin/import-export.asset.php',
    ];
}

function validateStagedPayload(string $stageRoot): void
{
    $required = [
        'wpessential.php',
        'readme.txt',
        'LICENSE',
        ...requiredAdminBuildArtifacts(),
        'vendor/autoload.php',
        'frameworks/Bootstrap/Plugin.php',
    ];

    foreach ($required as $relative) {
        $path = $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path) || filesize($path) === 0) {
            fail("Required distributable file is missing or empty: {$relative}");
        }
    }

    $forbiddenRoots = ['.git', '.github', 'admin-ui', 'config', 'docs', 'node_modules', 'tests', 'tools'];
    $forbiddenFiles = [
        '.gitignore',
        'AGENTS.md',
        'CHECKPOINT.md',
        'CONTRIBUTING.md',
        'DEVELOPMENT-CONSENT.md',
        'composer.json',
        'composer.lock',
        'package.json',
        'package-lock.json',
        'phpcs.xml.dist',
        'phpstan.neon.dist',
        'phpunit.xml.dist',
        'tsconfig.json',
    ];

    foreach (relativeFiles($stageRoot) as $relative) {
        $firstSegment = explode('/', $relative, 2)[0];
        if (in_array($firstSegment, $forbiddenRoots, true) || in_array($relative, $forbiddenFiles, true)) {
            fail("Development-only path leaked into distributable payload: {$relative}");
        }
    }
}

function buildArchive(string $stageRoot, string $archivePath): array
{
    if (!class_exists(ZipArchive::class)) {
        fail('PHP Zip extension is required to build the deterministic distributable.');
    }

    @unlink($archivePath);
    $zip = new ZipArchive();
    $openResult = $zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($openResult !== true) {
        fail("Unable to open ZIP archive {$archivePath}; ZipArchive code {$openResult}");
    }

    $files = relativeFiles($stageRoot);
    foreach ($files as $relative) {
        $source = $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        $entry = WPE_PACKAGE_SLUG . '/' . $relative;

        if (!$zip->addFile($source, $entry)) {
            $zip->close();
            fail("Unable to add archive entry: {$entry}");
        }
        if (!$zip->setMtimeName($entry, WPE_PACKAGE_MTIME)) {
            $zip->close();
            fail("Unable to normalize archive timestamp: {$entry}");
        }
        if (!$zip->setExternalAttributesName($entry, ZipArchive::OPSYS_UNIX, 0100644 << 16)) {
            $zip->close();
            fail("Unable to normalize archive permissions: {$entry}");
        }
        if (!$zip->setCompressionName($entry, ZipArchive::CM_DEFLATE, 9)) {
            $zip->close();
            fail("Unable to normalize archive compression: {$entry}");
        }
    }

    if (!$zip->close()) {
        fail("Unable to finalize ZIP archive: {$archivePath}");
    }

    $hash = hash_file('sha256', $archivePath);
    if ($hash === false) {
        fail("Unable to hash ZIP archive: {$archivePath}");
    }

    return ['sha256' => $hash, 'files' => count($files), 'bytes' => filesize($archivePath) ?: 0];
}

try {
    $root = realpath(__DIR__ . '/../..');
    if ($root === false) {
        fail('Unable to resolve repository root.');
    }

    assertProjectMetadata($root);

    $requiredBuildArtifacts = requiredAdminBuildArtifacts();
    foreach ($requiredBuildArtifacts as $relative) {
        $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path) || filesize($path) === 0) {
            fail("Build the admin application before packaging; missing {$relative}");
        }
    }

    $tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wpessential-dist-' . bin2hex(random_bytes(8));
    $stageRoot = $tempRoot . DIRECTORY_SEPARATOR . WPE_PACKAGE_SLUG;
    register_shutdown_function(static function () use ($tempRoot): void {
        removeTree($tempRoot);
    });
    ensureDirectory($stageRoot);

    foreach (['wpessential.php', 'readme.txt', 'LICENSE', 'composer.json', 'composer.lock'] as $relative) {
        copyFileNormalized($root . DIRECTORY_SEPARATOR . $relative, $stageRoot . DIRECTORY_SEPARATOR . $relative);
    }
    copyTreeNormalized($root . '/frameworks', $stageRoot . '/frameworks');
    foreach ($requiredBuildArtifacts as $relative) {
        copyFileNormalized(
            $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative),
            $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );
    }

    $composerBinary = getenv('COMPOSER_BINARY');
    if ($composerBinary === false || trim($composerBinary) === '') {
        $composerBinary = 'composer';
    }
    $composerCommand = escapeshellcmd($composerBinary)
        . ' install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-plugins --classmap-authoritative --working-dir='
        . escapeshellarg($stageRoot);
    runCommand($composerCommand);

    @unlink($stageRoot . '/composer.json');
    @unlink($stageRoot . '/composer.lock');
    validateStagedPayload($stageRoot);

    if (!defined('ABSPATH')) {
        define('ABSPATH', $stageRoot);
    }
    require_once $stageRoot . '/vendor/autoload.php';
    if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) {
        fail('Production Composer autoload probe could not resolve WPEssential\\Bootstrap\\Plugin.');
    }

    $artifactDir = $root . '/artifacts';
    ensureDirectory($artifactDir);
    $candidateA = $artifactDir . '/wpessential-a.zip';
    $candidateB = $artifactDir . '/wpessential-b.zip';
    $final = $artifactDir . '/wpessential.zip';

    $first = buildArchive($stageRoot, $candidateA);
    $second = buildArchive($stageRoot, $candidateB);
    if ($first['sha256'] !== $second['sha256']) {
        fail('Determinism check failed: repeated ZIP builds produced different SHA-256 values.');
    }

    @unlink($final);
    if (!rename($candidateA, $final)) {
        fail('Unable to promote deterministic candidate to final artifact.');
    }
    @unlink($candidateB);

    $result = [
        'artifact' => 'artifacts/wpessential.zip',
        'sha256' => $first['sha256'],
        'files' => $first['files'],
        'bytes' => $first['bytes'],
        'root' => WPE_PACKAGE_SLUG . '/',
        'fixed_mtime' => gmdate('c', WPE_PACKAGE_MTIME),
        'runtime_composer_packages' => 0,
    ];

    file_put_contents(
        $artifactDir . '/wpessential-package.json',
        json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL
    );

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (JsonException $exception) {
    fail('Invalid JSON metadata: ' . $exception->getMessage());
} catch (Throwable $exception) {
    fail($exception::class . ': ' . $exception->getMessage());
}
