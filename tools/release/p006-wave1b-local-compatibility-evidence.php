<?php

declare(strict_types=1);

const P006_WAVE1B_EVIDENCE_PATH = __DIR__ . '/../../artifacts/p006-wave1b-local-compatibility-evidence.json';

function p006bFail(string $message): never
{
    fwrite(STDERR, "[p006-wave1b] {$message}\n");
    exit(1);
}

function p006bReadFile(string $path): string
{
    $content = file_get_contents($path);
    if ($content === false) {
        p006bFail("Unable to read required file: {$path}");
    }

    return $content;
}

/** @return array<string,mixed> */
function p006bReadJson(string $path): array
{
    try {
        $decoded = json_decode(p006bReadFile($path), true, flags: JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        p006bFail("Invalid JSON at {$path}: {$exception->getMessage()}");
    }

    if (!is_array($decoded)) {
        p006bFail("Expected JSON object at {$path}");
    }

    return $decoded;
}

function p006bHashFile(string $path): string
{
    $hash = hash_file('sha256', $path);
    if ($hash === false) {
        p006bFail("Unable to hash file: {$path}");
    }

    return $hash;
}

function p006bZipEntry(ZipArchive $zip, string $entry): string
{
    $content = $zip->getFromName($entry);
    if ($content === false) {
        p006bFail("Required ZIP entry is missing: {$entry}");
    }

    return $content;
}

/** @return string|int */
function p006bParseDefine(string $php, string $name): string|int
{
    $pattern = '/define\(\s*[\'\"]' . preg_quote($name, '/') . '[\'\"]\s*,\s*('
        . '[\'\"][^\'\"]*[\'\"]|[0-9]+'
        . ')\s*\);/';

    if (preg_match($pattern, $php, $matches) !== 1) {
        p006bFail("Required literal compatibility constant is missing: {$name}");
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
function p006bDecision(array $result): array
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
function p006bFixture(string $id, string $expected, array $assertions, array $actual): array
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

/**
 * @param array<string,mixed> $free
 * @param array<string,mixed> $pro
 * @param array{state:string,dimension:string,reason:string,remediation:string} $expected
 * @return array{input:array<string,mixed>,expected:array<string,string>,actual:array<string,mixed>,pass:bool}
 */
function p006bFailClosedVector(array $free, array $pro, array $expected): array
{
    $class = \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::class;
    $actual = p006bDecision($class::evaluate($free, $pro));
    $pass = ($actual['state'] ?? null) === $expected['state']
        && ($actual['dimension'] ?? null) === $expected['dimension']
        && ($actual['reason'] ?? null) === $expected['reason']
        && ($actual['remediation'] ?? null) === $expected['remediation']
        && ($actual['premium_boot_allowed'] ?? null) === false
        && ($actual['premium_migrations_allowed'] ?? null) === false;

    return [
        'input' => ['free' => $free, 'pro' => $pro],
        'expected' => $expected,
        'actual' => $actual,
        'pass' => $pass,
    ];
}

/** @param array<string,mixed> $evidence */
function p006bWriteEvidence(array $evidence): void
{
    $directory = dirname(P006_WAVE1B_EVIDENCE_PATH);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        p006bFail("Unable to create evidence directory: {$directory}");
    }

    try {
        $json = json_encode(
            $evidence,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ) . PHP_EOL;
    } catch (JsonException $exception) {
        p006bFail('Unable to encode evidence JSON: ' . $exception->getMessage());
    }

    if (file_put_contents(P006_WAVE1B_EVIDENCE_PATH, $json) === false) {
        p006bFail('Unable to write P-006 Wave 1B evidence JSON.');
    }
}

$root = realpath(__DIR__ . '/../..');
if ($root === false) {
    p006bFail('Unable to resolve repository root.');
}

$sourceSha = trim((string) getenv('WPE_P006_SOURCE_SHA'));
if ($sourceSha === '' || preg_match('/^[0-9a-f]{40}$/', $sourceSha) !== 1) {
    p006bFail('WPE_P006_SOURCE_SHA must contain the exact 40-character PR head SHA.');
}

$freeZipPath = $root . '/artifacts/wpessential.zip';
$proZipPath = $root . '/artifacts/wpessential-pro.zip';
$freeManifestPath = $root . '/artifacts/wpessential-package.json';
$proManifestPath = $root . '/artifacts/wpessential-pro-package.json';

foreach ([$freeZipPath, $proZipPath, $freeManifestPath, $proManifestPath] as $requiredPath) {
    if (!is_file($requiredPath) || filesize($requiredPath) === 0) {
        p006bFail("Required immutable base artifact is missing or empty: {$requiredPath}");
    }
}

if (!class_exists(ZipArchive::class)) {
    p006bFail('PHP Zip extension is required for P-006 Wave 1B evidence.');
}

$freeHash = p006bHashFile($freeZipPath);
$proHash = p006bHashFile($proZipPath);
$freeManifest = p006bReadJson($freeManifestPath);
$proManifest = p006bReadJson($proManifestPath);

if (($freeManifest['sha256'] ?? null) !== $freeHash) {
    p006bFail('Free artifact SHA-256 does not match its package manifest.');
}
if (($proManifest['sha256'] ?? null) !== $proHash) {
    p006bFail('Pro artifact SHA-256 does not match its package manifest.');
}

$freeZip = new ZipArchive();
if ($freeZip->open($freeZipPath) !== true) {
    p006bFail('Unable to open immutable Free candidate ZIP.');
}
$proZip = new ZipArchive();
if ($proZip->open($proZipPath) !== true) {
    $freeZip->close();
    p006bFail('Unable to open immutable Pro candidate ZIP.');
}

$freeBootstrap = p006bZipEntry($freeZip, 'wpessential/wpessential.php');
$proBootstrap = p006bZipEntry($proZip, 'wpessential-pro/wpessential-pro.php');
$compatibilityEntry = 'wpessential-pro/frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php';
$packagedCompatibilitySource = p006bZipEntry($proZip, $compatibilityEntry);
$freeZip->close();
$proZip->close();

$sourceCompatibilityPath = $root . '/frameworks/Modules/Compatibility/LocalCompatibilityPreflight.php';
$sourceCompatibilityHash = p006bHashFile($sourceCompatibilityPath);
$packagedCompatibilityHash = hash('sha256', $packagedCompatibilitySource);
if (!hash_equals($sourceCompatibilityHash, $packagedCompatibilityHash)) {
    p006bFail('Packaged compatibility implementation differs from the exact source implementation.');
}

$tempCompatibilityPath = sys_get_temp_dir() . '/p006-wave1b-compat-' . bin2hex(random_bytes(8)) . '.php';
if (file_put_contents($tempCompatibilityPath, $packagedCompatibilitySource) === false) {
    p006bFail('Unable to stage packaged compatibility implementation for harness evaluation.');
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
    p006bFail('Packaged compatibility implementation did not define the expected class.');
}

$baseFree = [
    'present' => true,
    'bootstrap_complete' => true,
    'version' => p006bParseDefine($freeBootstrap, 'WPE_VERSION'),
    'platform_api' => p006bParseDefine($freeBootstrap, 'WPE_PLATFORM_API_VERSION'),
    'platform_schema' => p006bParseDefine($freeBootstrap, 'WPE_PLATFORM_SCHEMA_GENERATION'),
];
$basePro = [
    'package_complete' => true,
    'version' => p006bParseDefine($proBootstrap, 'WPE_PRO_VERSION'),
    'min_free_version' => p006bParseDefine($proBootstrap, 'WPE_PRO_MIN_FREE_VERSION'),
    'max_free_version' => p006bParseDefine($proBootstrap, 'WPE_PRO_MAX_FREE_VERSION'),
    'min_platform_api' => p006bParseDefine($proBootstrap, 'WPE_PRO_MIN_PLATFORM_API_VERSION'),
    'max_platform_api' => p006bParseDefine($proBootstrap, 'WPE_PRO_MAX_PLATFORM_API_VERSION'),
    'min_platform_schema' => p006bParseDefine($proBootstrap, 'WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION'),
    'max_platform_schema' => p006bParseDefine($proBootstrap, 'WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION'),
    'schema' => p006bParseDefine($proBootstrap, 'WPE_PRO_SCHEMA_GENERATION'),
];

$baseDecision = p006bDecision($class::evaluate($baseFree, $basePro));
if (($baseDecision['state'] ?? null) !== 'compatible') {
    p006bFail('Immutable base pair is not locally compatible; Wave 1B synthetic derivation cannot proceed.');
}

// FP-03: marketing-version changes inside an explicitly synthetic supported range
// must not require exact marketing-version equality when API/schema stay unchanged.
$fp03Pro = $basePro;
$fp03Pro['min_free_version'] = '0.1.0-dev';
$fp03Pro['max_free_version'] = '0.1.9';
$fp03Vectors = [];
foreach (['0.1.1', '0.1.8'] as $syntheticVersion) {
    $free = $baseFree;
    $free['version'] = $syntheticVersion;
    $decision = p006bDecision($class::evaluate($free, $fp03Pro));
    $pass = $syntheticVersion !== (string) $basePro['version']
        && $free['platform_api'] === $baseFree['platform_api']
        && $free['platform_schema'] === $baseFree['platform_schema']
        && ($decision['state'] ?? null) === 'compatible'
        && ($decision['dimension'] ?? null) === 'pair'
        && ($decision['reason'] ?? null) === 'compatible_local_pair'
        && ($decision['remediation'] ?? null) === 'none'
        && ($decision['premium_boot_allowed'] ?? null) === true
        && ($decision['premium_migrations_allowed'] ?? null) === true;

    $fp03Vectors[] = [
        'synthetic' => true,
        'free_version' => $syntheticVersion,
        'supported_free_range' => [$fp03Pro['min_free_version'], $fp03Pro['max_free_version']],
        'platform_api' => $free['platform_api'],
        'platform_schema' => $free['platform_schema'],
        'actual' => $decision,
        'pass' => $pass,
    ];
}
$fp03AllPass = array_reduce(
    $fp03Vectors,
    static fn (bool $carry, array $vector): bool => $carry && ($vector['pass'] ?? false) === true,
    true,
);
$fp03 = p006bFixture(
    'FP-03',
    'Marketing-version changes inside a declared synthetic supported range remain compatible when Platform API/schema are unchanged; exact marketing-version equality is not required.',
    [
        'all_synthetic_versions_differ_from_pro_marketing_version' => array_reduce(
            $fp03Vectors,
            static fn (bool $carry, array $vector): bool => $carry
                && ($vector['free_version'] ?? null) !== $basePro['version'],
            true,
        ),
        'platform_api_and_schema_unchanged' => array_reduce(
            $fp03Vectors,
            static fn (bool $carry, array $vector): bool => $carry
                && ($vector['platform_api'] ?? null) === $baseFree['platform_api']
                && ($vector['platform_schema'] ?? null) === $baseFree['platform_schema'],
            true,
        ),
        'all_vectors_compatible_and_admitted' => $fp03AllPass,
    ],
    ['vectors' => $fp03Vectors],
);

// FP-10: missing/malformed Free/API/schema metadata and contradictory Pro ranges
// must fail closed and deny both premium boot and premium migration admission.
$fp10Cases = [];
$caseFree = $baseFree;
unset($caseFree['version']);
$fp10Cases['free_version_missing'] = p006bFailClosedVector($caseFree, $basePro, [
    'state' => 'free_metadata_missing',
    'dimension' => 'free_version',
    'reason' => 'free_version_missing',
    'remediation' => 'repair_free',
]);
$caseFree = $baseFree;
$caseFree['version'] = 'not-a-version';
$fp10Cases['free_version_malformed'] = p006bFailClosedVector($caseFree, $basePro, [
    'state' => 'free_version_invalid',
    'dimension' => 'free_version',
    'reason' => 'free_version_malformed',
    'remediation' => 'repair_free',
]);
$caseFree = $baseFree;
unset($caseFree['platform_api']);
$fp10Cases['platform_api_missing'] = p006bFailClosedVector($caseFree, $basePro, [
    'state' => 'platform_api_missing',
    'dimension' => 'platform_api',
    'reason' => 'platform_api_missing',
    'remediation' => 'update_free',
]);
$caseFree = $baseFree;
$caseFree['platform_api'] = '0.1';
$fp10Cases['platform_api_malformed'] = p006bFailClosedVector($caseFree, $basePro, [
    'state' => 'platform_api_invalid',
    'dimension' => 'platform_api',
    'reason' => 'platform_api_malformed',
    'remediation' => 'repair_free',
]);
$caseFree = $baseFree;
unset($caseFree['platform_schema']);
$fp10Cases['platform_schema_missing'] = p006bFailClosedVector($caseFree, $basePro, [
    'state' => 'platform_schema_missing',
    'dimension' => 'platform_schema',
    'reason' => 'platform_schema_missing',
    'remediation' => 'update_free',
]);
$caseFree = $baseFree;
$caseFree['platform_schema'] = '1';
$fp10Cases['platform_schema_malformed'] = p006bFailClosedVector($caseFree, $basePro, [
    'state' => 'platform_schema_invalid',
    'dimension' => 'platform_schema',
    'reason' => 'platform_schema_malformed',
    'remediation' => 'repair_free',
]);
$casePro = $basePro;
$casePro['min_free_version'] = '0.2.0';
$casePro['max_free_version'] = '0.1.0';
$fp10Cases['contradictory_free_range'] = p006bFailClosedVector($baseFree, $casePro, [
    'state' => 'pro_metadata_invalid',
    'dimension' => 'pro_metadata',
    'reason' => 'pro_metadata_missing_malformed_or_contradictory',
    'remediation' => 'reinstall_pro',
]);
$casePro = $basePro;
$casePro['min_platform_api'] = '0.2.0';
$casePro['max_platform_api'] = '0.1.0';
$fp10Cases['contradictory_api_range'] = p006bFailClosedVector($baseFree, $casePro, [
    'state' => 'pro_metadata_invalid',
    'dimension' => 'pro_metadata',
    'reason' => 'pro_metadata_missing_malformed_or_contradictory',
    'remediation' => 'reinstall_pro',
]);
$casePro = $basePro;
$casePro['min_platform_schema'] = 2;
$casePro['max_platform_schema'] = 1;
$fp10Cases['contradictory_schema_range'] = p006bFailClosedVector($baseFree, $casePro, [
    'state' => 'pro_metadata_invalid',
    'dimension' => 'pro_metadata',
    'reason' => 'pro_metadata_missing_malformed_or_contradictory',
    'remediation' => 'reinstall_pro',
]);
$fp10AllPass = array_reduce(
    $fp10Cases,
    static fn (bool $carry, array $case): bool => $carry && ($case['pass'] ?? false) === true,
    true,
);
$fp10 = p006bFixture(
    'FP-10',
    'Missing/malformed Free/API/schema metadata and contradictory Pro ranges fail closed, remain non-fatal in the local harness, and deny premium boot/migration admission.',
    [
        'nine_fail_closed_vectors_executed' => count($fp10Cases) === 9,
        'all_expected_fail_closed_results_match' => $fp10AllPass,
        'all_vectors_deny_premium_boot_and_migrations' => array_reduce(
            $fp10Cases,
            static fn (bool $carry, array $case): bool => $carry
                && (($case['actual']['premium_boot_allowed'] ?? null) === false)
                && (($case['actual']['premium_migrations_allowed'] ?? null) === false),
            true,
        ),
    ],
    ['cases' => $fp10Cases],
);

// FP-11: unknown future fields must not alter the known-field decision.
$futureFree = $baseFree + [
    'metadata_schema_version' => 999,
    'future_capability' => 'ignored-by-v1',
    'future_nested' => ['opaque' => true, 'version' => '99.0.0'],
];
$futurePro = $basePro + [
    'metadata_schema_version' => 999,
    'future_required_capabilities' => ['imaginary-capability'],
    'future_policy' => ['mode' => 'unknown', 'strict' => true],
];
$futureDecision = p006bDecision($class::evaluate($futureFree, $futurePro));
$fp11 = p006bFixture(
    'FP-11',
    'Unknown future metadata fields on otherwise valid local metadata do not change or permissively broaden the explicit known-field compatibility decision.',
    [
        'baseline_pair_is_compatible' => ($baseDecision['state'] ?? null) === 'compatible',
        'unknown_fields_preserve_exact_known_field_decision' => $futureDecision === $baseDecision,
        'unknown_fields_do_not_change_boot_admission' => ($futureDecision['premium_boot_allowed'] ?? null) === true,
        'unknown_fields_do_not_change_migration_admission' => ($futureDecision['premium_migrations_allowed'] ?? null) === true,
    ],
    [
        'baseline' => $baseDecision,
        'unknown_field_input' => ['free' => $futureFree, 'pro' => $futurePro],
        'unknown_field_result' => $futureDecision,
    ],
);

$fixtures = [$fp03, $fp10, $fp11];
$passed = count(array_filter($fixtures, static fn (array $fixture): bool => ($fixture['status'] ?? null) === 'PASS'));
$failed = count($fixtures) - $passed;
$pairId = hash('sha256', "free:{$freeHash}\npro:{$proHash}\n");

$evidence = [
    'protocol' => 'P-006',
    'wave' => '1B-local-compatibility-variants',
    'source_sha' => $sourceSha,
    'php_version' => PHP_VERSION,
    'authorized_fixtures' => ['FP-03', 'FP-10', 'FP-11'],
    'base_artifacts' => [
        'free' => [
            'path' => 'artifacts/wpessential.zip',
            'sha256' => $freeHash,
            'manifest_sha256' => $freeManifest['sha256'] ?? null,
        ],
        'pro' => [
            'path' => 'artifacts/wpessential-pro.zip',
            'sha256' => $proHash,
            'manifest_sha256' => $proManifest['sha256'] ?? null,
        ],
        'pair_id_sha256' => $pairId,
    ],
    'compatibility_implementation' => [
        'zip_entry' => $compatibilityEntry,
        'packaged_sha256' => $packagedCompatibilityHash,
        'source_sha256' => $sourceCompatibilityHash,
        'byte_identical_to_exact_source' => hash_equals($sourceCompatibilityHash, $packagedCompatibilityHash),
        'loaded_for_evaluation_from_packaged_candidate_bytes' => true,
    ],
    'base_metadata' => [
        'free' => $baseFree,
        'pro' => $basePro,
        'decision' => $baseDecision,
    ],
    'side_effect_profile' => [
        'wordpress_boot' => false,
        'database' => false,
        'network' => false,
        'provider_or_billing' => false,
        'entitlement' => false,
        'membership' => false,
        'production_credentials_or_data' => false,
        'destructive_or_irreversible_operations' => false,
    ],
    'fixtures' => $fixtures,
    'summary' => [
        'executed' => count($fixtures),
        'passed' => $passed,
        'failed' => $failed,
        'all_authorized_fixtures_terminal' => count($fixtures) === 3,
    ],
    'certification_boundary' => [
        'free_pro_pair_certified' => false,
        'runtime_certified' => false,
        'adr_0010_status' => 'Proposed',
        'adjacent_certifications_promoted' => false,
        'synthetic_variants_are_release_candidates' => false,
    ],
];

p006bWriteEvidence($evidence);
fwrite(STDOUT, json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

if ($failed > 0) {
    fwrite(STDERR, "[p006-wave1b] {$failed} authorized fixture(s) failed.\n");
    exit(1);
}

fwrite(STDOUT, "[p006-wave1b] FP-03, FP-10 and FP-11 PASS. No certification promoted.\n");
