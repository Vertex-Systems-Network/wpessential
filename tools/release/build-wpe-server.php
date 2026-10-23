<?php

declare(strict_types=1);

/**
 * Owner-only package of WPE Server (server/wpessential-server). It is never part of the Free or
 * Pro distributables; the plugin itself refuses to run outside wpessential.org and
 * vertexsystemsnetwork.com (ADR-0233).
 */

$root   = dirname(__DIR__, 2);
$source = $root . '/server/wpessential-server';
$target = $root . '/artifacts/wpessential-server.zip';

if (!is_file($source . '/wpessential-server.php') || !is_file($source . '/src/HostLock.php')) {
    fwrite(STDERR, "WPE Server source or its host lock is missing.\n");
    exit(1);
}
if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0775, true)) {
    fwrite(STDERR, "Cannot create artifacts/.\n");
    exit(1);
}

$zip = new ZipArchive();
if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Cannot write {$target}.\n");
    exit(1);
}
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS));
$paths = [];
foreach ($files as $file) {
    if ($file->isFile() && !str_ends_with($file->getFilename(), '.dist')) {
        $paths[] = substr($file->getPathname(), strlen($source) + 1);
    }
}
sort($paths, SORT_STRING);
foreach ($paths as $relative) {
    $zip->addFile($source . '/' . $relative, 'wpessential-server/' . str_replace(DIRECTORY_SEPARATOR, '/', $relative));
    $zip->setMtimeName('wpessential-server/' . $relative, 315532800);
}
$zip->close();
fwrite(STDOUT, "WPE Server package: {$target} (" . count($paths) . " files)\n");
