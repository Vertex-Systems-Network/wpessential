<?php

declare(strict_types=1);

const WPE_FREE_PACKAGE_SLUG = 'wpessential';
const WPE_PRO_PACKAGE_SLUG = 'wpessential-pro';
const WPE_PACKAGE_MTIME = 946684800; // 2000-01-01T00:00:00Z.

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

/**
 * @param null|callable(string):bool $include
 */
function copyTreeNormalized(string $sourceRoot, string $destinationRoot, ?callable $include = null): void
{
    foreach (relativeFiles($sourceRoot) as $relative) {
        if ($include !== null && !$include($relative)) {
            continue;
        }

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

function artifactExistsAndIsNonEmpty(string $root, string $relative): bool
{
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    return is_file($path) && filesize($path) > 0;
}

/** @return list<string> */
function adminArtifactTriplet(string $entry): array
{
    return [
        "assets/admin/{$entry}.js",
        "assets/admin/{$entry}.css",
        "assets/admin/{$entry}.asset.php",
    ];
}

/** @return list<string> */
function requiredAdminArtifactTriplet(string $root, string $entry): array
{
    $triplet = adminArtifactTriplet($entry);
    foreach ($triplet as $relative) {
        if (!artifactExistsAndIsNonEmpty($root, $relative)) {
            fail("Build the admin application before packaging; missing {$relative}");
        }
    }
    return $triplet;
}

/** @return list<string> */
function optionalAdminArtifactTriplet(string $root, string $entry): array
{
    $triplet = adminArtifactTriplet($entry);
    $present = array_values(array_filter(
        $triplet,
        static fn (string $relative): bool => artifactExistsAndIsNonEmpty($root, $relative)
    ));

    if ($present === []) {
        return [];
    }

    if (count($present) !== count($triplet)) {
        fail("Optional admin entry {$entry} is only partially built; refusing to package an incomplete asset triplet.");
    }

    return $triplet;
}

function assertProjectMetadata(string $root): void
{
    $plugin = file_get_contents($root . '/wpessential.php');
    $proPlugin = file_get_contents($root . '/wpessential-pro.php');
    $readme = file_get_contents($root . '/readme.txt');
    $license = file_get_contents($root . '/LICENSE');
    $composerRaw = file_get_contents($root . '/composer.json');
    $lockRaw = file_get_contents($root . '/composer.lock');

    if (
        $plugin === false
        || $proPlugin === false
        || $readme === false
        || $license === false
        || $composerRaw === false
        || $lockRaw === false
    ) {
        fail('Unable to read required package metadata.');
    }

    foreach ([
        'Plugin Name: WPEssential',
        'Version: 0.1.0-dev',
        'Requires at least: 6.9',
        'Requires PHP: 8.2',
        'License: GPL-3.0-or-later',
    ] as $needle) {
        if (!str_contains($plugin, $needle)) {
            fail("Free plugin metadata is missing required value: {$needle}");
        }
    }

    foreach ([
        'Plugin Name: WPEssential Pro',
        'Version: 0.1.0-dev',
        'Requires at least: 6.9',
        'Requires PHP: 8.2',
        'Requires Plugins: wpessential',
        'License: GPL-3.0-or-later',
    ] as $needle) {
        if (!str_contains($proPlugin, $needle)) {
            fail("Pro plugin metadata is missing required value: {$needle}");
        }
    }

    foreach ([
        'Requires at least: 6.9',
        'Requires PHP: 8.2',
        'Stable tag: 0.1.0-dev',
        'License: GPLv3 or later',
    ] as $needle) {
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

function isFreeFrameworkFile(string $relative): bool
{
    if (!str_starts_with($relative, 'Modules/')) {
        return true;
    }

    return str_starts_with($relative, 'Modules/CustomPostTypes/')
        || str_starts_with($relative, 'Modules/Taxonomies/');
}

function isProModuleFile(string $relative): bool
{
    return !str_starts_with($relative, 'CustomPostTypes/')
        && !str_starts_with($relative, 'Taxonomies/');
}

/** @param list<string> $freeAssets */
function stageFreePackage(string $root, string $stageRoot, array $freeAssets): void
{
    ensureDirectory($stageRoot);

    foreach (['wpessential.php', 'readme.txt', 'LICENSE', 'composer.json', 'composer.lock'] as $relative) {
        copyFileNormalized($root . DIRECTORY_SEPARATOR . $relative, $stageRoot . DIRECTORY_SEPARATOR . $relative);
    }

    copyTreeNormalized($root . '/frameworks', $stageRoot . '/frameworks', 'isFreeFrameworkFile');
    foreach ($freeAssets as $relative) {
        copyFileNormalized(
            $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative),
            $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );
    }

    $composerBinary = getenv('COMPOSER_BINARY');
    if ($composerBinary === false || trim($composerBinary) === '') {
        $composerBinary = 'composer';
    }

    runCommand(
        escapeshellcmd($composerBinary)
        . ' install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-plugins --classmap-authoritative --working-dir='
        . escapeshellarg($stageRoot)
    );

    @unlink($stageRoot . '/composer.json');
    @unlink($stageRoot . '/composer.lock');
}

/** @param list<string> $proAssets */
function stageProPackage(string $root, string $stageRoot, array $proAssets): void
{
    ensureDirectory($stageRoot);
    copyFileNormalized($root . '/wpessential-pro.php', $stageRoot . '/wpessential-pro.php');
    copyFileNormalized($root . '/LICENSE', $stageRoot . '/LICENSE');
    copyTreeNormalized($root . '/frameworks/Modules', $stageRoot . '/frameworks/Modules', 'isProModuleFile');

    foreach ($proAssets as $relative) {
        copyFileNormalized(
            $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative),
            $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)
        );
    }
}

/** @param list<string> $freeAssets */
function validateFreeStage(string $stageRoot, array $freeAssets): void
{
    foreach ([
        'wpessential.php',
        'readme.txt',
        'LICENSE',
        'vendor/autoload.php',
        'frameworks/Bootstrap/Plugin.php',
        'frameworks/Modules/CustomPostTypes/CustomPostTypeModule.php',
        'frameworks/Modules/Taxonomies/TaxonomyModule.php',
        ...$freeAssets,
    ] as $relative) {
        $path = $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path) || filesize($path) === 0) {
            fail("Required Free distributable file is missing or empty: {$relative}");
        }
    }

    foreach (relativeFiles($stageRoot) as $relative) {
        if (str_starts_with($relative, 'frameworks/Modules/')) {
            $moduleRelative = substr($relative, strlen('frameworks/Modules/'));
            if (!isProModuleFile($moduleRelative)) {
                continue;
            }
            fail("Pro module implementation leaked into Free artifact: {$relative}");
        }

        if (
            str_starts_with($relative, 'assets/admin/fields.')
            || str_starts_with($relative, 'assets/admin/query.')
            || str_starts_with($relative, 'assets/admin/columns-runtime.')
            || $relative === 'wpessential-pro.php'
        ) {
            fail("Pro-owned payload leaked into Free artifact: {$relative}");
        }
    }
}

/** @param list<string> $proAssets */
function validateProStage(string $stageRoot, array $proAssets): void
{
    foreach ([
        'wpessential-pro.php',
        'LICENSE',
        'frameworks/Modules/Roles/RolesModule.php',
        'frameworks/Modules/AdminMenu/AdminMenuModule.php',
        'frameworks/Modules/Settings/SettingsModule.php',
        'frameworks/Modules/Dashboard/DashboardModule.php',
        'frameworks/Modules/Profiles/ProfilesModule.php',
        'frameworks/Modules/Membership/MembershipModule.php',
        'frameworks/Modules/BuilderWidgets/BuilderWidgetsModule.php',
        'frameworks/Modules/FormsWorkflows/FormsWorkflowsModule.php',
        'frameworks/Modules/Cron/CronModule.php',
        'frameworks/Modules/Notifications/NotificationsModule.php',
        'frameworks/Modules/Emails/EmailsModule.php',
        'frameworks/Modules/Chat/ChatModule.php',
        ...$proAssets,
    ] as $relative) {
        $path = $stageRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path) || filesize($path) === 0) {
            fail("Required Pro distributable file is missing or empty: {$relative}");
        }
    }

    foreach (relativeFiles($stageRoot) as $relative) {
        if (
            str_starts_with($relative, 'frameworks/Bootstrap/')
            || str_starts_with($relative, 'frameworks/Contracts/')
            || str_starts_with($relative, 'frameworks/Kernel/')
            || str_starts_with($relative, 'frameworks/Platform/')
            || str_starts_with($relative, 'frameworks/Modules/CustomPostTypes/')
            || str_starts_with($relative, 'frameworks/Modules/Taxonomies/')
            || $relative === 'wpessential.php'
        ) {
            fail("Free Platform/module payload leaked into Pro artifact: {$relative}");
        }
    }
}

/** @return array{sha256:string,files:int,bytes:int} */
function buildArchive(string $stageRoot, string $archivePath, string $packageSlug): array
{
    if (!class_exists(ZipArchive::class)) {
        fail('PHP Zip extension is required to build deterministic distributables.');
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
        $entry = $packageSlug . '/' . $relative;

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

/**
 * @param array{sha256:string,files:int,bytes:int} $result
 */
function writePackageManifest(string $path, string $artifact, string $root, string $edition, array $result): void
{
    file_put_contents(
        $path,
        json_encode([
            'artifact' => $artifact,
            'edition' => $edition,
            'sha256' => $result['sha256'],
            'files' => $result['files'],
            'bytes' => $result['bytes'],
            'root' => $root . '/',
            'fixed_mtime' => gmdate('c', WPE_PACKAGE_MTIME),
            'runtime_composer_packages' => $edition === 'free' ? 0 : null,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL
    );
}

try {
    $root = realpath(__DIR__ . '/../..');
    if ($root === false) {
        fail('Unable to resolve repository root.');
    }

    assertProjectMetadata($root);

    $freeAssets = [
        ...requiredAdminArtifactTriplet($root, 'main'),
        ...requiredAdminArtifactTriplet($root, 'taxonomy'),
    ];
    $proAssets = [
        ...requiredAdminArtifactTriplet($root, 'fields'),
        ...optionalAdminArtifactTriplet($root, 'query'),
        ...requiredAdminArtifactTriplet($root, 'columns-runtime'),
    ];

    $tempRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wpessential-dist-' . bin2hex(random_bytes(8));
    $freeStage = $tempRoot . '/free/' . WPE_FREE_PACKAGE_SLUG;
    $proStage = $tempRoot . '/pro/' . WPE_PRO_PACKAGE_SLUG;
    register_shutdown_function(static function () use ($tempRoot): void {
        removeTree($tempRoot);
    });

    stageFreePackage($root, $freeStage, $freeAssets);
    stageProPackage($root, $proStage, $proAssets);
    validateFreeStage($freeStage, $freeAssets);
    validateProStage($proStage, $proAssets);

    $artifactDir = $root . '/artifacts';
    ensureDirectory($artifactDir);

    $freeA = $artifactDir . '/wpessential-a.zip';
    $freeB = $artifactDir . '/wpessential-b.zip';
    $freeFinal = $artifactDir . '/wpessential.zip';
    $freeFirst = buildArchive($freeStage, $freeA, WPE_FREE_PACKAGE_SLUG);
    $freeSecond = buildArchive($freeStage, $freeB, WPE_FREE_PACKAGE_SLUG);
    if ($freeFirst['sha256'] !== $freeSecond['sha256']) {
        fail('Free determinism check failed: repeated ZIP builds produced different SHA-256 values.');
    }
    @unlink($freeFinal);
    if (!rename($freeA, $freeFinal)) {
        fail('Unable to promote deterministic Free candidate to final artifact.');
    }
    @unlink($freeB);

    $proA = $artifactDir . '/wpessential-pro-a.zip';
    $proB = $artifactDir . '/wpessential-pro-b.zip';
    $proFinal = $artifactDir . '/wpessential-pro.zip';
    $proFirst = buildArchive($proStage, $proA, WPE_PRO_PACKAGE_SLUG);
    $proSecond = buildArchive($proStage, $proB, WPE_PRO_PACKAGE_SLUG);
    if ($proFirst['sha256'] !== $proSecond['sha256']) {
        fail('Pro determinism check failed: repeated ZIP builds produced different SHA-256 values.');
    }
    @unlink($proFinal);
    if (!rename($proA, $proFinal)) {
        fail('Unable to promote deterministic Pro candidate to final artifact.');
    }
    @unlink($proB);

    writePackageManifest(
        $artifactDir . '/wpessential-package.json',
        'artifacts/wpessential.zip',
        WPE_FREE_PACKAGE_SLUG,
        'free',
        $freeFirst,
    );
    writePackageManifest(
        $artifactDir . '/wpessential-pro-package.json',
        'artifacts/wpessential-pro.zip',
        WPE_PRO_PACKAGE_SLUG,
        'pro',
        $proFirst,
    );

    fwrite(STDOUT, json_encode([
        'free' => $freeFirst + ['artifact' => 'artifacts/wpessential.zip'],
        'pro' => $proFirst + ['artifact' => 'artifacts/wpessential-pro.zip'],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (JsonException $exception) {
    fail('Invalid JSON metadata: ' . $exception->getMessage());
} catch (Throwable $exception) {
    fail($exception::class . ': ' . $exception->getMessage());
}
