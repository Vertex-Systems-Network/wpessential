<?php

declare(strict_types=1);

const P006_WAVE1H_EVIDENCE_PATH = __DIR__ . '/../../artifacts/p006-wave1h-static-harness-evidence.json';
const P006_WAVE1H_GRANT = 'GOV-P001-CF-TEMP-006';

function p006hFail(string $message): never
{
    fwrite(STDERR, "[p006-wave1h] {$message}\n");
    exit(1);
}

function p006hReadFile(string $path): string
{
    $content = file_get_contents($path);
    if ($content === false) {
        p006hFail("Unable to read required file: {$path}");
    }

    return $content;
}

/** @return array<string,mixed> */
function p006hReadJson(string $path): array
{
    try {
        $decoded = json_decode(p006hReadFile($path), true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        p006hFail("Invalid JSON at {$path}: {$exception->getMessage()}");
    }

    if (!is_array($decoded)) {
        p006hFail("Expected JSON object at {$path}");
    }

    return $decoded;
}

function p006hHashFile(string $path): string
{
    $hash = hash_file('sha256', $path);
    if ($hash === false) {
        p006hFail("Unable to hash file: {$path}");
    }

    return $hash;
}

function p006hZipEntry(ZipArchive $zip, string $entry): string
{
    $content = $zip->getFromName($entry);
    if ($content === false) {
        p006hFail("Required ZIP entry is missing: {$entry}");
    }

    return $content;
}

/** @return string|int */
function p006hParseDefine(string $php, string $name): string|int
{
    $pattern = '/define\(\s*[\'\"]' . preg_quote($name, '/') . '[\'\"]\s*,\s*('
        . '[\'\"][^\'\"]*[\'\"]|[0-9]+'
        . ')\s*\);/';

    if (preg_match($pattern, $php, $matches) !== 1) {
        p006hFail("Required literal compatibility constant is missing: {$name}");
    }

    $literal = trim($matches[1]);
    if (preg_match('/^[0-9]+$/', $literal) === 1) {
        return (int) $literal;
    }

    return substr($literal, 1, -1);
}

/**
 * @param array<string,mixed> $result
 * @return array<string,mixed>
 */
function p006hDecision(array $result): array
{
    return [
        'state' => $result['state'] ?? null,
        'dimension' => $result['dimension'] ?? null,
        'reason' => $result['reason'] ?? null,
        'remediation' => $result['remediation'] ?? null,
        'free_version' => $result['free_version'] ?? null,
        'pro_version' => $result['pro_version'] ?? null,
        'platform_api' => $result['platform_api'] ?? null,
        'platform_schema' => $result['platform_schema'] ?? null,
        'pro_schema' => $result['pro_schema'] ?? null,
        'premium_boot_allowed' => $result['premium_boot_allowed'] ?? null,
        'premium_migrations_allowed' => $result['premium_migrations_allowed'] ?? null,
    ];
}

/**
 * @param array<string,bool> $assertions
 * @param array<string,mixed> $actual
 * @return array<string,mixed>
 */
function p006hFixture(
    string $id,
    string $expected,
    array $assertions,
    array $actual,
    ?string $terminalOverride = null,
): array {
    $failed = [];
    foreach ($assertions as $name => $passed) {
        if ($passed !== true) {
            $failed[] = $name;
        }
    }

    $status = $terminalOverride ?? ($failed === [] ? 'PASS' : 'FAIL');
    if (!in_array($status, ['PASS', 'FAIL', 'INCONCLUSIVE'], true)) {
        p006hFail("Invalid terminal status for {$id}: {$status}");
    }

    return [
        'fixture_id' => $id,
        'expected' => $expected,
        'actual' => $actual,
        'assertions' => $assertions,
        'failed_assertions' => $failed,
        'status' => $status,
        'linked_defect' => null,
    ];
}

/** @return list<array{archive:string,entry:string,marker:string}> */
function p006hLifecycleOwnerMarkers(ZipArchive $zip, string $archive): array
{
    $markers = [];
    $patterns = [
        'activation_hook' => '/\bregister_activation_hook\s*\(/i',
        'deactivation_hook' => '/\bregister_deactivation_hook\s*\(/i',
        'uninstall_hook' => '/\bregister_uninstall_hook\s*\(/i',
        'migration_owner_type' => '/\b(?:class|interface|trait)\s+[A-Za-z_][A-Za-z0-9_]*(?:Migration|Migrator|Installer|Activator)\b/i',
    ];

    for ($index = 0; $index < $zip->numFiles; $index++) {
        $entry = $zip->getNameIndex($index);
        if (!is_string($entry) || !str_ends_with(strtolower($entry), '.php')) {
            continue;
        }

        $firstParty = $archive === 'free'
            ? ($entry === 'wpessential/wpessential.php' || str_starts_with($entry, 'wpessential/frameworks/'))
            : ($entry === 'wpessential-pro/wpessential-pro.php' || str_starts_with($entry, 'wpessential-pro/frameworks/'));
        if (!$firstParty) {
            continue;
        }

        $content = $zip->getFromIndex($index);
        if (!is_string($content)) {
            p006hFail("Unable to inspect PHP ZIP entry: {$entry}");
        }

        foreach ($patterns as $marker => $pattern) {
            if (preg_match($pattern, $content) === 1) {
                $markers[] = ['archive' => $archive, 'entry' => $entry, 'marker' => $marker];
            }
        }
    }

    return $markers;
}

/** @param array<string,mixed> $evidence */
function p006hWriteEvidence(array $evidence): void
{
    $directory = dirname(P006_WAVE1H_EVIDENCE_PATH);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006hFail("Unable to create evidence directory: {$directory}");
    }

    try {
        $json = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    } catch (JsonException $exception) {
        p006hFail('Unable to encode evidence JSON: ' . $exception->getMessage());
    }

    if (file_put_contents(P006_WAVE1H_EVIDENCE_PATH, $json) === false) {
        p006hFail('Unable to write P-006 Wave 1H evidence JSON.');
    }
}

$root = realpath(__DIR__ . '/../..');
if ($root === false) {
    p006hFail('Unable to resolve repository root.');
}

$sourceSha = trim((string) getenv('WPE_P006_SOURCE_SHA'));
if ($sourceSha === '' || preg_match('/^[0-9a-f]{40}$/', $sourceSha) !== 1) {
    p006hFail('WPE_P006_SOURCE_SHA must contain the exact 40-character source head SHA.');
}

$freeZipPath = $root . '/artifacts/wpessential.zip';
$proZipPath = $root . '/artifacts/wpessential-pro.zip';
$freeManifestPath = $root . '/artifacts/wpessential-package.json';
$proManifestPath = $root . '/artifacts/wpessential-pro-package.json';

foreach ([$freeZipPath, $proZipPath, $freeManifestPath, $proManifestPath] as $requiredPath) {
    if (!is_file($requiredPath) || filesize($requiredPath) === 0) {
        p006hFail("Required immutable candidate artifact is missing or empty: {$requiredPath}");
    }
}

if (!class_exists(ZipArchive::class)) {
    p006hFail('PHP Zip extension is required for P-006 Wave 1H evidence.');
}

$freeHash = p006hHashFile($freeZipPath);
$proHash = p006hHashFile($proZipPath);
$pairId = hash('sha256', $freeHash . ':' . $proHash);
$freeManifest = p006hReadJson($freeManifestPath);
$proManifest = p006hReadJson($proManifestPath);

if (($freeManifest['sha256'] ?? null) !== $freeHash) {
    p006hFail('Free artifact SHA-256 does not match its package manifest.');
}
if (($proManifest['sha256'] ?? null) !== $proHash) {
    p006hFail('Pro artifact SHA-256 does not match its package manifest.');
}

$freeZip = new ZipArchive();
if ($freeZip->open($freeZipPath) !== true) {
    p006hFail('Unable to open immutable Free candidate ZIP.');
}
$proZip = new ZipArchive();
if ($proZip->open($proZipPath) !== true) {
    $freeZip->close();
    p006hFail('Unable to open immutable Pro candidate ZIP.');
}

$freeBootstrap = p006hZipEntry($freeZip, 'wpessential/wpessential.php');
$proBootstrap = p006hZipEntry($proZip, 'wpessential-pro/wpessential-pro.php');
$compatibilityEntry = 'wpessential-pro/frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php';
$packagedCompatibilitySource = p006hZipEntry($proZip, $compatibilityEntry);
$sourceCompatibilityPath = $root . '/frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php';
$sourceCompatibilityHash = p006hHashFile($sourceCompatibilityPath);
$packagedCompatibilityHash = hash('sha256', $packagedCompatibilitySource);
if (!hash_equals($sourceCompatibilityHash, $packagedCompatibilityHash)) {
    $freeZip->close();
    $proZip->close();
    p006hFail('Packaged compatibility implementation differs from exact source implementation.');
}

$lifecycleOwnerMarkers = array_merge(
    p006hLifecycleOwnerMarkers($freeZip, 'free'),
    p006hLifecycleOwnerMarkers($proZip, 'pro'),
);
$freeZip->close();
$proZip->close();

$tempCompatibilityPath = sys_get_temp_dir() . '/p006-wave1h-compat-' . bin2hex(random_bytes(8)) . '.php';
if (file_put_contents($tempCompatibilityPath, $packagedCompatibilitySource) === false) {
    p006hFail('Unable to stage packaged compatibility implementation for static harness evaluation.');
}
register_shutdown_function(static function () use ($tempCompatibilityPath): void {
    @unlink($tempCompatibilityPath);
});

if (!defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}
require_once $tempCompatibilityPath;

$class = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::class;
if (!class_exists($class)) {
    p006hFail('Packaged compatibility implementation did not define the expected class.');
}

$baseFree = [
    'present' => true,
    'bootstrap_complete' => true,
    'version' => p006hParseDefine($freeBootstrap, 'WPE_VERSION'),
    'platform_api' => p006hParseDefine($freeBootstrap, 'WPE_PLATFORM_API_VERSION'),
    'platform_schema' => p006hParseDefine($freeBootstrap, 'WPE_PLATFORM_SCHEMA_GENERATION'),
];
$basePro = [
    'package_complete' => true,
    'version' => p006hParseDefine($proBootstrap, 'WPE_PRO_VERSION'),
    'min_free_version' => p006hParseDefine($proBootstrap, 'WPE_PRO_MIN_FREE_VERSION'),
    'max_free_version' => p006hParseDefine($proBootstrap, 'WPE_PRO_MAX_FREE_VERSION'),
    'min_platform_api' => p006hParseDefine($proBootstrap, 'WPE_PRO_MIN_PLATFORM_API_VERSION'),
    'max_platform_api' => p006hParseDefine($proBootstrap, 'WPE_PRO_MAX_PLATFORM_API_VERSION'),
    'min_platform_schema' => p006hParseDefine($proBootstrap, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION'),
    'max_platform_schema' => p006hParseDefine($proBootstrap, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION'),
    'schema' => p006hParseDefine($proBootstrap, 'WPE_PRO_SCHEMA_GENERATION'),
];

$baseDecision = p006hDecision($class::evaluate($baseFree, $basePro));
if (($baseDecision['state'] ?? null) !== 'compatible') {
    p006hFail('Exact immutable candidate pair is not locally compatible; Wave 1H cannot evaluate its bounded fixtures.');
}

$preflightEvaluationPos = strpos($proBootstrap, '$compatibility = $preflightClass::evaluateRuntime($packageComplete);');
$firstFreeRuntimeClassPos = strpos($proBootstrap, '\\WPEssential\\Bootstrap\\Plugin::class');
$setActivationPolicyPos = strpos($proBootstrap, '\\WPEssential\\Bootstrap\\Plugin::setModuleActivationPolicy(');
$fp29 = p006hFixture(
    'FP-29',
    'Pro evaluates local compatibility before dereferencing the Free runtime API profile or invoking Free bootstrap methods.',
    [
        'preflight_evaluation_present' => $preflightEvaluationPos !== false,
        'free_runtime_profile_reference_present' => $firstFreeRuntimeClassPos !== false,
        'activation_policy_call_present' => $setActivationPolicyPos !== false,
        'preflight_precedes_free_runtime_profile' => is_int($preflightEvaluationPos) && is_int($firstFreeRuntimeClassPos) && $preflightEvaluationPos < $firstFreeRuntimeClassPos,
        'preflight_precedes_free_bootstrap_method_call' => is_int($preflightEvaluationPos) && is_int($setActivationPolicyPos) && $preflightEvaluationPos < $setActivationPolicyPos,
    ],
    [
        'preflight_evaluation_offset' => $preflightEvaluationPos,
        'first_free_runtime_class_offset' => $firstFreeRuntimeClassPos,
        'activation_policy_call_offset' => $setActivationPolicyPos,
    ],
);

$freeProMarkers = [
    'pro_constant' => preg_match('/\bWPE_PRO_[A-Z0-9_]+\b/', $freeBootstrap) === 1,
    'pro_namespace' => str_contains($freeBootstrap, 'WPEssential\\Modules\\'),
    'pro_bootstrap_filename' => str_contains($freeBootstrap, 'wpessential-pro.php'),
    'pro_module_path' => str_contains($freeBootstrap, 'frameworks/Modules/'),
];
$fp30 = p006hFixture(
    'FP-30',
    'The packaged Free bootstrap does not eagerly reference Pro-only constants, namespaces, bootstrap files, or module paths.',
    [
        'no_pro_constant_reference' => $freeProMarkers['pro_constant'] === false,
        'no_pro_namespace_reference' => $freeProMarkers['pro_namespace'] === false,
        'no_pro_bootstrap_filename_reference' => $freeProMarkers['pro_bootstrap_filename'] === false,
        'no_pro_module_path_reference' => $freeProMarkers['pro_module_path'] === false,
    ],
    ['markers' => $freeProMarkers],
);

$compatibilityDependencies = [
    'free_bootstrap_namespace' => str_contains($packagedCompatibilitySource, 'WPEssential\\Bootstrap\\'),
    'platform_namespace' => str_contains($packagedCompatibilitySource, 'WPEssential\\Platform\\'),
    'container_literal' => preg_match('/\b(?:Container|ServiceContainer)\b/', $packagedCompatibilitySource) === 1,
    'module_interface_literal' => str_contains($packagedCompatibilitySource, 'ModuleInterface'),
    'object_construction' => preg_match('/\bnew\s+[A-Za-z_\\\\]/', $packagedCompatibilitySource) === 1,
];
$compatibilityPrefixPos = strpos($proBootstrap, "WPEssential\\\\Modules\\\\Compatibility\\\\");
$autoloadGatePos = strpos($proBootstrap, '!str_starts_with($class, $compatibilityPrefix)');
$fp31 = p006hFixture(
    'FP-31',
    'The minimal Pro compatibility implementation is self-contained and explicitly exempted from the premium implementation autoload gate.',
    [
        'compatibility_prefix_exemption_declared' => $compatibilityPrefixPos !== false,
        'autoload_gate_uses_compatibility_exemption' => $autoloadGatePos !== false,
        'no_free_bootstrap_namespace_dependency' => $compatibilityDependencies['free_bootstrap_namespace'] === false,
        'no_platform_namespace_dependency' => $compatibilityDependencies['platform_namespace'] === false,
        'no_container_dependency' => $compatibilityDependencies['container_literal'] === false,
        'no_module_interface_dependency' => $compatibilityDependencies['module_interface_literal'] === false,
        'no_object_construction_dependency' => $compatibilityDependencies['object_construction'] === false,
    ],
    ['compatibility_source_sha256' => $packagedCompatibilityHash, 'dependency_markers' => $compatibilityDependencies],
);

$incompatibleFreeForLifecycle = array_replace($baseFree, ['version' => '9.9.9']);
$incompatibleLifecycleDecision = $class::evaluate($incompatibleFreeForLifecycle, $basePro);
$fp33 = p006hFixture(
    'FP-33',
    'No packaged activation/deactivation/uninstall hook or migration-owner type exists that could perform lifecycle work before compatibility is known; discovery is a STOP/REVIEW condition.',
    [
        'no_activation_or_migration_owner_marker' => $lifecycleOwnerMarkers === [],
        'incompatible_decision_denies_boot' => ($incompatibleLifecycleDecision['premium_boot_allowed'] ?? null) === false,
        'incompatible_decision_denies_migrations' => ($incompatibleLifecycleDecision['premium_migrations_allowed'] ?? null) === false,
    ],
    ['lifecycle_owner_markers' => $lifecycleOwnerMarkers],
    $lifecycleOwnerMarkers === [] ? null : 'INCONCLUSIVE',
);

$changedFree = $baseFree;
$changedFree['version'] = '9.9.9';
$compatibleFirst = p006hDecision($class::evaluate($baseFree, $basePro));
$changedDecision = p006hDecision($class::evaluate($changedFree, $basePro));
$compatibleAgain = p006hDecision($class::evaluate($baseFree, $basePro));
$fp40 = p006hFixture(
    'FP-40',
    'Changing current candidate metadata causes a fresh incompatible decision and restoring the original metadata recomputes the original compatible decision.',
    [
        'initial_pair_compatible' => ($compatibleFirst['state'] ?? null) === 'compatible',
        'changed_artifact_recomputed_incompatible' => ($changedDecision['state'] ?? null) === 'free_version_too_new',
        'changed_artifact_denies_boot' => ($changedDecision['premium_boot_allowed'] ?? null) === false,
        'restored_artifact_recomputed_compatible' => $compatibleAgain === $compatibleFirst,
    ],
    ['initial' => $compatibleFirst, 'changed' => $changedDecision, 'restored' => $compatibleAgain],
);

$persistentApiPattern = '/\b(?:get_transient|set_transient|delete_transient|wp_cache_get|wp_cache_set|get_option|update_option)\s*\(/';
$persistentApiInCompatibility = preg_match($persistentApiPattern, $packagedCompatibilitySource) === 1;
$cleanIncompatible = p006hDecision($class::evaluate($changedFree, $basePro));
$hostileDecoratedFree = $changedFree + [
    'transient_compatibility' => 'compatible',
    'object_cache_compatibility' => 'compatible',
    'cached_premium_boot_allowed' => true,
];
$hostileDecoratedPro = $basePro + [
    'transient_compatibility' => 'compatible',
    'object_cache_compatibility' => 'compatible',
    'cached_premium_boot_allowed' => true,
];
$hostileDecision = p006hDecision($class::evaluate($hostileDecoratedFree, $hostileDecoratedPro));
$fp41 = p006hFixture(
    'FP-41',
    'Stale or hostile cache/transient-shaped values cannot enter the local compatibility decision or authorize an otherwise incompatible Pro load.',
    [
        'compatibility_source_has_no_persistent_cache_api_reads' => $persistentApiInCompatibility === false,
        'hostile_extra_fields_do_not_change_decision' => $hostileDecision === $cleanIncompatible,
        'hostile_decision_remains_incompatible' => ($hostileDecision['state'] ?? null) === 'free_version_too_new',
        'hostile_decision_denies_boot' => ($hostileDecision['premium_boot_allowed'] ?? null) === false,
        'hostile_decision_denies_migrations' => ($hostileDecision['premium_migrations_allowed'] ?? null) === false,
    ],
    [
        'persistent_cache_api_reference_found' => $persistentApiInCompatibility,
        'clean_incompatible' => $cleanIncompatible,
        'hostile_decorated' => $hostileDecision,
    ],
);

$originalTimezone = date_default_timezone_get();
$timezoneDecisions = [];
foreach (['UTC', 'Pacific/Kiritimati', 'America/Los_Angeles', 'Asia/Karachi'] as $timezone) {
    if (!date_default_timezone_set($timezone)) {
        p006hFail("Unable to set deterministic test timezone: {$timezone}");
    }
    $timezoneDecisions[$timezone] = p006hDecision($class::evaluate($baseFree, $basePro));
}
date_default_timezone_set($originalTimezone);
$firstTimezoneDecision = reset($timezoneDecisions);
$allTimezoneDecisionsIdentical = is_array($firstTimezoneDecision);
foreach ($timezoneDecisions as $decision) {
    $allTimezoneDecisionsIdentical = $allTimezoneDecisionsIdentical && $decision === $firstTimezoneDecision;
}
$timeApiPattern = '/\b(?:time|microtime|date|gmdate|strtotime|current_time|wp_date)\s*\(/';
$timeApiInCompatibility = preg_match($timeApiPattern, $packagedCompatibilitySource) === 1;
$fp42 = p006hFixture(
    'FP-42',
    'Identical immutable artifact metadata yields the same binary compatibility decision across local timezone changes and the compatibility layer has no clock dependency.',
    [
        'compatibility_source_has_no_clock_api_calls' => $timeApiInCompatibility === false,
        'all_timezone_decisions_identical' => $allTimezoneDecisionsIdentical,
        'all_timezone_decisions_compatible' => array_reduce(
            $timezoneDecisions,
            static fn (bool $carry, array $decision): bool => $carry
                && ($decision['state'] ?? null) === 'compatible'
                && ($decision['premium_boot_allowed'] ?? null) === true,
            true,
        ),
    ],
    ['clock_api_reference_found' => $timeApiInCompatibility, 'timezone_decisions' => $timezoneDecisions],
);

$fixtures = [$fp29, $fp30, $fp31, $fp33, $fp40, $fp41, $fp42];
$summary = [
    'documented_scope' => 7,
    'executed' => count($fixtures),
    'passed' => count(array_filter($fixtures, static fn (array $fixture): bool => $fixture['status'] === 'PASS')),
    'failed' => count(array_filter($fixtures, static fn (array $fixture): bool => $fixture['status'] === 'FAIL')),
    'inconclusive' => count(array_filter($fixtures, static fn (array $fixture): bool => $fixture['status'] === 'INCONCLUSIVE')),
    'certified_pair' => false,
    'runtime_certification' => false,
];

$evidence = [
    'schema_version' => 1,
    'wave' => 'P-006 Wave 1H',
    'grant' => P006_WAVE1H_GRANT,
    'source_sha' => $sourceSha,
    'execution_mode' => 'STATIC_HARNESS_ONLY_NO_WORDPRESS_RUNTIME',
    'candidate_identity' => [
        'free_sha256' => $freeHash,
        'pro_sha256' => $proHash,
        'pair_id' => $pairId,
        'free_manifest_sha256' => $freeManifest['sha256'] ?? null,
        'pro_manifest_sha256' => $proManifest['sha256'] ?? null,
        'compatibility_source_sha256' => $sourceCompatibilityHash,
        'compatibility_packaged_sha256' => $packagedCompatibilityHash,
    ],
    'boundaries' => [
        'real_wordpress_runtime_executed' => false,
        'provider_or_license_calls_executed' => false,
        'production_credentials_or_live_sites_used' => false,
        'destructive_or_irreversible_operations_executed' => false,
        'deployment_or_release_executed' => false,
        'permanent_p001_cf_promoted' => false,
        'pair_certification_promoted' => false,
        'runtime_certification_promoted' => false,
        'adr_0010_promoted' => false,
    ],
    'summary' => $summary,
    'fixtures' => $fixtures,
];

p006hWriteEvidence($evidence);

printf(
    "[p006-wave1h] source=%s free=%s pro=%s pair=%s pass=%d fail=%d inconclusive=%d\n",
    $sourceSha,
    $freeHash,
    $proHash,
    $pairId,
    $summary['passed'],
    $summary['failed'],
    $summary['inconclusive'],
);

if ($summary['failed'] > 0 || $summary['inconclusive'] > 0) {
    exit(2);
}
