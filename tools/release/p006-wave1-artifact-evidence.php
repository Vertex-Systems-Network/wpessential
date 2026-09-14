<?php

declare(strict_types=1);

const P006_WAVE = 'wave-1a-static-artifact-evidence';
const P006_PROTOCOL = 'P006-FREE-PRO-COMPATIBILITY-EXECUTABLE-EVIDENCE-PROTOCOL';
const P006_AUTHORIZED_FIXTURES = ['FP-01', 'FP-02', 'FP-05', 'FP-07', 'FP-08'];

final class P006EvidenceException extends RuntimeException
{
}

function p006Fail(string $message): never
{
    throw new P006EvidenceException($message);
}

/** @return array<string,mixed> */
function p006ReadJson(string $path): array
{
    if (!is_file($path)) {
        p006Fail("Required JSON file is missing: {$path}");
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        p006Fail("Unable to read JSON file: {$path}");
    }

    $decoded = json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($decoded)) {
        p006Fail("JSON root must be an object: {$path}");
    }

    return $decoded;
}

function p006HashFile(string $path): string
{
    if (!is_file($path)) {
        p006Fail("Required artifact is missing: {$path}");
    }

    $hash = hash_file('sha256', $path);
    if ($hash === false || preg_match('/^[a-f0-9]{64}$/', $hash) !== 1) {
        p006Fail("Unable to produce SHA-256 for artifact: {$path}");
    }

    return $hash;
}

/** @return list<string> */
function p006ZipEntries(string $path): array
{
    if (!class_exists(ZipArchive::class)) {
        p006Fail('ZipArchive is unavailable.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        p006Fail("Unable to open ZIP artifact: {$path}");
    }

    $entries = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = $zip->getNameIndex($index);
        if (!is_string($name) || $name === '') {
            $zip->close();
            p006Fail("ZIP contains an unreadable entry name: {$path}");
        }
        $entries[] = $name;
    }
    $zip->close();

    sort($entries, SORT_STRING);
    return array_values($entries);
}

function p006ZipRead(string $path, string $entry): string
{
    if (!class_exists(ZipArchive::class)) {
        p006Fail('ZipArchive is unavailable.');
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        p006Fail("Unable to open ZIP artifact: {$path}");
    }

    $content = $zip->getFromName($entry);
    $zip->close();

    if (!is_string($content)) {
        p006Fail("ZIP entry is missing: {$entry}");
    }

    return $content;
}

/** @return array<string,string> */
function p006ParseHeaders(string $text): array
{
    $wanted = [
        'Plugin Name',
        'Version',
        'Requires at least',
        'Requires PHP',
        'Requires Plugins',
        'Stable tag',
    ];
    $headers = [];

    foreach (preg_split('/\R/', $text) ?: [] as $line) {
        $line = preg_replace('/^\s*\*\s?/', '', $line) ?? $line;
        foreach ($wanted as $name) {
            if (preg_match('/^' . preg_quote($name, '/') . ':\s*(.+?)\s*$/i', $line, $matches) === 1) {
                $headers[$name] = trim($matches[1]);
            }
        }
    }

    return $headers;
}

/** @return string|int */
function p006ParseDefine(string $php, string $name): string|int
{
    $pattern = '/define\(\s*[\'\"]' . preg_quote($name, '/') . '[\'\"]\s*,\s*(' 
        . '[\'\"][^\'\"]*[\'\"]|[0-9]+' 
        . ')\s*\);/';

    if (preg_match($pattern, $php, $matches) !== 1) {
        p006Fail("Required compatibility constant is missing or not a simple literal: {$name}");
    }

    $literal = trim($matches[1]);
    if (preg_match('/^[0-9]+$/', $literal) === 1) {
        return (int) $literal;
    }

    return substr($literal, 1, -1);
}

function p006VersionTokenIsSafe(string $value): bool
{
    return preg_match('/^[0-9]+(?:\.[0-9A-Za-z-]+)+$/', $value) === 1;
}

function p006VersionFloorFromComposer(string $constraint): ?string
{
    if (preg_match('/^>=\s*([0-9]+\.[0-9]+(?:\.[0-9]+)?)$/', trim($constraint), $matches) !== 1) {
        return null;
    }

    return $matches[1];
}

/**
 * @param array<string,bool> $assertions
 * @param array<string,mixed> $actual
 * @return array<string,mixed>
 */
function p006Fixture(string $id, string $expected, array $assertions, array $actual): array
{
    $failed = [];
    foreach ($assertions as $name => $passed) {
        if ($passed !== true) {
            $failed[] = $name;
        }
    }

    return [
        'fixture_id' => $id,
        'expected' => $expected,
        'actual' => $actual,
        'assertions' => $assertions,
        'failed_assertions' => $failed,
        'status' => $failed === [] ? 'PASS' : 'FAIL',
        'linked_defect' => null,
    ];
}

/** @param array<string,mixed> $evidence */
function p006WriteEvidence(string $path, array $evidence): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006Fail("Unable to create evidence directory: {$directory}");
    }

    $json = json_encode(
        $evidence,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );

    if (file_put_contents($path, $json . PHP_EOL) === false) {
        p006Fail("Unable to write evidence file: {$path}");
    }
}

$root = realpath(__DIR__ . '/../..');
if ($root === false) {
    fwrite(STDERR, "[p006] Unable to resolve repository root.\n");
    exit(2);
}

$artifactDir = $root . '/artifacts';
$evidencePath = $artifactDir . '/p006-wave1-evidence.json';
$sourceSha = trim((string) getenv('WPE_P006_SOURCE_SHA'));

try {
    if (preg_match('/^[a-f0-9]{40}$/', $sourceSha) !== 1) {
        p006Fail('WPE_P006_SOURCE_SHA must be the exact 40-character source commit SHA.');
    }

    $freeZip = $artifactDir . '/wpessential.zip';
    $proZip = $artifactDir . '/wpessential-pro.zip';
    $freeManifest = p006ReadJson($artifactDir . '/wpessential-package.json');
    $proManifest = p006ReadJson($artifactDir . '/wpessential-pro-package.json');
    $determinism = p006ReadJson($artifactDir . '/p006-wave1-determinism.json');

    $freeHash = p006HashFile($freeZip);
    $proHash = p006HashFile($proZip);
    $freeEntries = p006ZipEntries($freeZip);
    $proEntries = p006ZipEntries($proZip);

    $freePhp = p006ZipRead($freeZip, 'wpessential/wpessential.php');
    $proPhp = p006ZipRead($proZip, 'wpessential-pro/wpessential-pro.php');
    $readme = p006ZipRead($freeZip, 'wpessential/readme.txt');
    $composer = p006ReadJson($root . '/composer.json');

    $freeHeaders = p006ParseHeaders($freePhp);
    $proHeaders = p006ParseHeaders($proPhp);
    $readmeHeaders = p006ParseHeaders($readme);

    $freeVersion = p006ParseDefine($freePhp, 'WPE_VERSION');
    $platformApi = p006ParseDefine($freePhp, 'WPE_PLATFORM_API_VERSION');
    $platformSchema = p006ParseDefine($freePhp, 'WPE_PLATFORM_SCHEMA_GENERATION');

    $proVersion = p006ParseDefine($proPhp, 'WPE_PRO_VERSION');
    $proMinFree = p006ParseDefine($proPhp, 'WPE_PRO_MIN_FREE_VERSION');
    $proMaxFree = p006ParseDefine($proPhp, 'WPE_PRO_MAX_FREE_VERSION');
    $proMinApi = p006ParseDefine($proPhp, 'WPE_PRO_MIN_PLATFORM_API_VERSION');
    $proMaxApi = p006ParseDefine($proPhp, 'WPE_PRO_MAX_PLATFORM_API_VERSION');
    $proMinSchema = p006ParseDefine($proPhp, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION');
    $proMaxSchema = p006ParseDefine($proPhp, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION');
    $proSchema = p006ParseDefine($proPhp, 'WPE_PRO_SCHEMA_GENERATION');

    if (!is_string($freeVersion) || !is_string($platformApi) || !is_int($platformSchema)) {
        p006Fail('Free metadata literals have unexpected types.');
    }
    if (
        !is_string($proVersion)
        || !is_string($proMinFree)
        || !is_string($proMaxFree)
        || !is_string($proMinApi)
        || !is_string($proMaxApi)
        || !is_int($proMinSchema)
        || !is_int($proMaxSchema)
        || !is_int($proSchema)
    ) {
        p006Fail('Pro compatibility metadata literals have unexpected types.');
    }

    $composerPhp = (string) (($composer['require']['php'] ?? ''));
    $composerPhpFloor = p006VersionFloorFromComposer($composerPhp);

    $unexpectedFreePayload = [];
    foreach ($freeEntries as $entry) {
        if (!str_starts_with($entry, 'wpessential/')) {
            $unexpectedFreePayload[] = $entry;
            continue;
        }

        if ($entry === 'wpessential/wpessential-pro.php') {
            $unexpectedFreePayload[] = $entry;
            continue;
        }

        if (preg_match('#^wpessential/assets/admin/(fields|query|columns-runtime)\.#', $entry) === 1) {
            $unexpectedFreePayload[] = $entry;
            continue;
        }

        if (str_starts_with($entry, 'wpessential/frameworks/Modules/')) {
            $modulePath = substr($entry, strlen('wpessential/frameworks/Modules/'));
            if (
                !str_starts_with($modulePath, 'CustomPostTypes/')
                && !str_starts_with($modulePath, 'Taxonomies/')
            ) {
                $unexpectedFreePayload[] = $entry;
            }
        }
    }

    $freeFirst = (string) ($determinism['free_first'] ?? '');
    $freeSecond = (string) ($determinism['free_second'] ?? '');
    $proFirst = (string) ($determinism['pro_first'] ?? '');
    $proSecond = (string) ($determinism['pro_second'] ?? '');
    $determinismSource = (string) ($determinism['source_sha'] ?? '');

    $fixtures = [];
    $fixtures['FP-01'] = p006Fixture(
        'FP-01',
        'Free artifact version, Platform API, schema generation and minimum environment metadata are machine-readable and internally consistent.',
        [
            'free_manifest_hash_matches_artifact' => ($freeManifest['sha256'] ?? null) === $freeHash,
            'free_manifest_edition_is_free' => ($freeManifest['edition'] ?? null) === 'free',
            'free_header_version_matches_constant' => ($freeHeaders['Version'] ?? null) === $freeVersion,
            'free_plugin_version_is_machine_readable' => p006VersionTokenIsSafe($freeVersion),
            'platform_api_is_machine_readable' => p006VersionTokenIsSafe($platformApi),
            'platform_schema_is_positive_integer' => $platformSchema > 0,
            'free_wordpress_minimum_is_machine_readable' => isset($freeHeaders['Requires at least'])
                && p006VersionTokenIsSafe((string) $freeHeaders['Requires at least']),
            'free_php_minimum_is_machine_readable' => isset($freeHeaders['Requires PHP'])
                && p006VersionTokenIsSafe((string) $freeHeaders['Requires PHP']),
        ],
        [
            'plugin_version' => $freeVersion,
            'platform_api' => $platformApi,
            'platform_schema_generation' => $platformSchema,
            'requires_wordpress' => $freeHeaders['Requires at least'] ?? null,
            'requires_php' => $freeHeaders['Requires PHP'] ?? null,
            'artifact_sha256' => $freeHash,
        ],
    );

    $fixtures['FP-02'] = p006Fixture(
        'FP-02',
        'Pro artifact version, supported Free/Platform API ranges and Pro schema generation are machine-readable and internally consistent.',
        [
            'pro_manifest_hash_matches_artifact' => ($proManifest['sha256'] ?? null) === $proHash,
            'pro_manifest_edition_is_pro' => ($proManifest['edition'] ?? null) === 'pro',
            'pro_header_version_matches_constant' => ($proHeaders['Version'] ?? null) === $proVersion,
            'pro_plugin_version_is_machine_readable' => p006VersionTokenIsSafe($proVersion),
            'pro_declares_free_dependency' => ($proHeaders['Requires Plugins'] ?? null) === 'wpessential',
            'free_version_range_is_machine_readable' => p006VersionTokenIsSafe($proMinFree)
                && p006VersionTokenIsSafe($proMaxFree),
            'free_version_range_is_ordered' => version_compare($proMinFree, $proMaxFree, '<='),
            'platform_api_range_is_machine_readable' => p006VersionTokenIsSafe($proMinApi)
                && p006VersionTokenIsSafe($proMaxApi),
            'platform_api_range_is_ordered' => version_compare($proMinApi, $proMaxApi, '<='),
            'platform_schema_range_is_ordered' => $proMinSchema > 0 && $proMinSchema <= $proMaxSchema,
            'pro_schema_is_positive_integer' => $proSchema > 0,
        ],
        [
            'plugin_version' => $proVersion,
            'supported_free' => ['min' => $proMinFree, 'max' => $proMaxFree],
            'supported_platform_api' => ['min' => $proMinApi, 'max' => $proMaxApi],
            'supported_platform_schema' => ['min' => $proMinSchema, 'max' => $proMaxSchema],
            'pro_schema_generation' => $proSchema,
            'requires_wordpress' => $proHeaders['Requires at least'] ?? null,
            'requires_php' => $proHeaders['Requires PHP'] ?? null,
            'requires_plugins' => $proHeaders['Requires Plugins'] ?? null,
            'artifact_sha256' => $proHash,
        ],
    );

    $fixtures['FP-05'] = p006Fixture(
        'FP-05',
        'Free artifact contains no prohibited Pro implementation, Pro-owned admin assets or Pro bootstrap.',
        [
            'all_free_entries_are_under_free_root' => array_reduce(
                $freeEntries,
                static fn (bool $carry, string $entry): bool => $carry && str_starts_with($entry, 'wpessential/'),
                true,
            ),
            'no_pro_payload_detected' => $unexpectedFreePayload === [],
            'free_bootstrap_present' => in_array('wpessential/wpessential.php', $freeEntries, true),
            'pro_bootstrap_absent' => !in_array('wpessential/wpessential-pro.php', $freeEntries, true),
        ],
        [
            'free_file_count' => count($freeEntries),
            'unexpected_payload' => $unexpectedFreePayload,
        ],
    );

    $freeWpMin = (string) ($freeHeaders['Requires at least'] ?? '');
    $proWpMin = (string) ($proHeaders['Requires at least'] ?? '');
    $readmeWpMin = (string) ($readmeHeaders['Requires at least'] ?? '');
    $freePhpMin = (string) ($freeHeaders['Requires PHP'] ?? '');
    $proPhpMin = (string) ($proHeaders['Requires PHP'] ?? '');
    $readmePhpMin = (string) ($readmeHeaders['Requires PHP'] ?? '');

    $fixtures['FP-07'] = p006Fixture(
        'FP-07',
        'Package headers, readme and Composer metadata agree on represented minimum PHP/WordPress requirements.',
        [
            'wordpress_minima_are_nonempty' => $freeWpMin !== '' && $proWpMin !== '' && $readmeWpMin !== '',
            'wordpress_minima_agree' => $freeWpMin === $proWpMin && $freeWpMin === $readmeWpMin,
            'php_minima_are_nonempty' => $freePhpMin !== '' && $proPhpMin !== '' && $readmePhpMin !== '',
            'php_header_and_readme_minima_agree' => $freePhpMin === $proPhpMin && $freePhpMin === $readmePhpMin,
            'composer_php_floor_is_parseable' => $composerPhpFloor !== null,
            'composer_php_floor_agrees' => $composerPhpFloor !== null && $freePhpMin === $composerPhpFloor,
        ],
        [
            'wordpress' => [
                'free_header' => $freeWpMin,
                'pro_header' => $proWpMin,
                'readme' => $readmeWpMin,
            ],
            'php' => [
                'free_header' => $freePhpMin,
                'pro_header' => $proPhpMin,
                'readme' => $readmePhpMin,
                'composer_constraint' => $composerPhp,
                'composer_floor' => $composerPhpFloor,
            ],
        ],
    );

    $pairId = hash('sha256', "free:{$freeHash}\npro:{$proHash}\n");
    $fixtures['FP-08'] = p006Fixture(
        'FP-08',
        'Exact deterministic SHA-256 hashes uniquely identify the immutable Free and Pro candidate binaries under test.',
        [
            'determinism_source_matches_tested_source' => $determinismSource === $sourceSha,
            'free_hash_is_valid_sha256' => preg_match('/^[a-f0-9]{64}$/', $freeHash) === 1,
            'pro_hash_is_valid_sha256' => preg_match('/^[a-f0-9]{64}$/', $proHash) === 1,
            'free_repeated_builds_match' => $freeFirst !== '' && $freeFirst === $freeSecond && $freeFirst === $freeHash,
            'pro_repeated_builds_match' => $proFirst !== '' && $proFirst === $proSecond && $proFirst === $proHash,
            'free_manifest_hash_matches' => ($freeManifest['sha256'] ?? null) === $freeHash,
            'pro_manifest_hash_matches' => ($proManifest['sha256'] ?? null) === $proHash,
            'free_and_pro_hashes_are_distinct' => $freeHash !== $proHash,
        ],
        [
            'source_sha' => $sourceSha,
            'free_sha256' => $freeHash,
            'pro_sha256' => $proHash,
            'pair_id_sha256' => $pairId,
            'free_first_build_sha256' => $freeFirst,
            'free_second_build_sha256' => $freeSecond,
            'pro_first_build_sha256' => $proFirst,
            'pro_second_build_sha256' => $proSecond,
        ],
    );

    $failedFixtures = [];
    foreach ($fixtures as $id => $fixture) {
        if (($fixture['status'] ?? null) !== 'PASS') {
            $failedFixtures[] = $id;
        }
    }

    $evidence = [
        'protocol' => P006_PROTOCOL,
        'wave' => P006_WAVE,
        'authorization' => 'GOV-OWNER-CONSENT-P006-001',
        'parent_issue' => 939,
        'execution_issue' => 945,
        'source_sha' => $sourceSha,
        'runner' => [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'os' => PHP_OS_FAMILY,
        ],
        'truth_boundary' => [
            'binary_package_artifact_identity_only' => true,
            'wordpress_runtime_executed' => false,
            'database_or_migrations_executed' => false,
            'remote_provider_or_license_calls_executed' => false,
            'production_side_effects' => false,
        ],
        'artifacts' => [
            'free' => [
                'path' => 'artifacts/wpessential.zip',
                'sha256' => $freeHash,
                'manifest' => $freeManifest,
            ],
            'pro' => [
                'path' => 'artifacts/wpessential-pro.zip',
                'sha256' => $proHash,
                'manifest' => $proManifest,
            ],
            'pair_id_sha256' => $pairId,
        ],
        'authorized_fixtures' => P006_AUTHORIZED_FIXTURES,
        'fixtures' => $fixtures,
        'summary' => [
            'executed' => count($fixtures),
            'passed' => count($fixtures) - count($failedFixtures),
            'failed' => count($failedFixtures),
            'failed_fixtures' => $failedFixtures,
            'p006_runtime_certified' => false,
            'free_pro_pair_certified' => false,
            'adjacent_certifications_promoted' => [],
        ],
        'redaction' => [
            'license_tokens_included' => false,
            'oauth_tokens_included' => false,
            'provider_credentials_included' => false,
            'signed_private_artifacts_included' => false,
        ],
    ];

    p006WriteEvidence($evidencePath, $evidence);
    fwrite(STDOUT, json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

    exit($failedFixtures === [] ? 0 : 1);
} catch (Throwable $exception) {
    $fixtures = [];
    foreach (P006_AUTHORIZED_FIXTURES as $id) {
        $fixtures[$id] = [
            'fixture_id' => $id,
            'status' => 'NOT EXECUTED',
            'reason' => 'harness_error_before_fixture_result',
        ];
    }

    $evidence = [
        'protocol' => P006_PROTOCOL,
        'wave' => P006_WAVE,
        'authorization' => 'GOV-OWNER-CONSENT-P006-001',
        'parent_issue' => 939,
        'execution_issue' => 945,
        'source_sha' => $sourceSha,
        'execution_state' => 'HARNESS_ERROR',
        'error_class' => $exception::class,
        'error' => $exception->getMessage(),
        'authorized_fixtures' => P006_AUTHORIZED_FIXTURES,
        'fixtures' => $fixtures,
        'summary' => [
            'executed' => 0,
            'passed' => 0,
            'failed' => 0,
            'p006_runtime_certified' => false,
            'free_pro_pair_certified' => false,
        ],
    ];

    try {
        p006WriteEvidence($evidencePath, $evidence);
    } catch (Throwable) {
        // Preserve the original failure as the primary stderr signal.
    }

    fwrite(STDERR, '[p006] ' . $exception->getMessage() . PHP_EOL);
    exit(2);
}
