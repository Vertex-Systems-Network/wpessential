<?php

declare(strict_types=1);

const P006J_GRANT = 'GOV-P001-CF-TEMP-008';
const P006J_FIXTURES = ['FP-61','FP-62','FP-63','FP-64','FP-65','FP-66','FP-67','FP-68','FP-76'];

function jFail(string $message): never
{
    throw new RuntimeException($message);
}

function jAssert(bool $condition, string $message): void
{
    if (!$condition) {
        jFail($message);
    }
}

function jEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        jFail("Missing env: {$key}");
    }

    return $value;
}

function jHash(string $path): string
{
    jAssert(is_file($path) && filesize($path) > 0, "Missing file: {$path}");
    $hash = hash_file('sha256', $path);
    jAssert(is_string($hash), "Hash failed: {$path}");

    return $hash;
}

function jPair(string $freeHash, string $proHash): string
{
    return hash('sha256', $freeHash . ':' . $proHash);
}

/** @param array<string,mixed> $value */
function jWrite(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }

    $encoded = json_encode(
        $value,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
    );

    jAssert(file_put_contents($path, $encoded . PHP_EOL) !== false, "Write failed: {$path}");
}

/** @return array<string,mixed> */
function jRead(string $path): array
{
    jAssert(is_file($path), "Missing JSON: {$path}");
    $decoded = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    jAssert(is_array($decoded), "Invalid JSON: {$path}");

    return $decoded;
}

/** @return array<string,mixed> */
function jIdentity(): array
{
    $sourceSha = jEnv('WPE_P006_SOURCE_SHA');
    jAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');

    $freeHash = jHash(jEnv('WPE_P006_FREE_ZIP_PATH'));
    $proHash = jHash(jEnv('WPE_P006_PRO_ZIP_PATH'));
    $sourcePath = jEnv('WPE_P006_PREFLIGHT_SOURCE_PATH');
    $preflightHash = jHash($sourcePath);

    return [
        'protocol' => 'P-006',
        'wave' => '1J-lane-b1-pure-range-semantics',
        'source_sha' => $sourceSha,
        'temporary_approval_id' => P006J_GRANT,
        'classification' => 'NON-RELEASE / TEST-ONLY EVIDENCE',
        'candidate_identity' => [
            'free_sha256' => $freeHash,
            'pro_sha256' => $proHash,
            'pair_id_sha256' => jPair($freeHash, $proHash),
            'local_compatibility_preflight_sha256' => $preflightHash,
        ],
        'authorized_fixtures' => P006J_FIXTURES,
        'authorized_php_cells' => ['8.2', '8.5'],
        'execution_boundary' => [
            'wordpress_boot' => false,
            'database' => false,
            'filesystem_replacement' => false,
            'provider_or_license_calls' => false,
            'network_service' => false,
            'destructive_or_live_action' => false,
        ],
        'certification_boundary' => [
            'permanent_p001_cf_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];
}

/** @return array<string,mixed> */
function jLoadIdentity(): array
{
    $identity = jRead(jEnv('WPE_P006_IDENTITY_PATH'));
    jAssert(($identity['temporary_approval_id'] ?? null) === P006J_GRANT, 'Grant mismatch');
    jAssert(($identity['source_sha'] ?? null) === jEnv('WPE_P006_SOURCE_SHA'), 'Source SHA mismatch');

    $candidate = $identity['candidate_identity'] ?? null;
    jAssert(is_array($candidate), 'Candidate identity absent');

    $freeHash = jHash(jEnv('WPE_P006_FREE_ZIP_PATH'));
    $proHash = jHash(jEnv('WPE_P006_PRO_ZIP_PATH'));
    $preflightHash = jHash(jEnv('WPE_P006_PREFLIGHT_SOURCE_PATH'));

    jAssert(($candidate['free_sha256'] ?? null) === $freeHash, 'Free artifact hash drift; STOP');
    jAssert(($candidate['pro_sha256'] ?? null) === $proHash, 'Pro artifact hash drift; STOP');
    jAssert(($candidate['pair_id_sha256'] ?? null) === jPair($freeHash, $proHash), 'Canonical pair identity drift; STOP');
    jAssert(($candidate['local_compatibility_preflight_sha256'] ?? null) === $preflightHash, 'Compatibility source hash drift; STOP');

    return $identity;
}

/** @return array<string,mixed> */
function jBaseFree(): array
{
    return [
        'present' => true,
        'bootstrap_complete' => true,
        'version' => '1.5.0',
        'platform_api' => '1.5.0',
        'platform_schema' => 5,
    ];
}

/** @return array<string,mixed> */
function jBasePro(): array
{
    return [
        'package_complete' => true,
        'version' => '2.0.0',
        'min_free_version' => '1.0.0',
        'max_free_version' => '2.0.0',
        'min_platform_api' => '1.0.0',
        'max_platform_api' => '2.0.0',
        'min_platform_schema' => 1,
        'max_platform_schema' => 10,
        'schema' => 2,
    ];
}

/** @param array<string,mixed> $decision @param array<string,mixed> $expected */
function jAssertDecision(array $decision, array $expected, string $label): void
{
    foreach (['state','dimension','reason','remediation','premium_boot_allowed','premium_migrations_allowed'] as $key) {
        jAssert(
            array_key_exists($key, $decision) && ($decision[$key] === ($expected[$key] ?? null)),
            "{$label}: unexpected {$key}; got " . json_encode($decision[$key] ?? null) . ', expected ' . json_encode($expected[$key] ?? null),
        );
    }
}

/** @return array<string,mixed> */
function jExpectedCompatible(): array
{
    return [
        'state' => 'compatible',
        'dimension' => 'pair',
        'reason' => 'compatible_local_pair',
        'remediation' => 'none',
        'premium_boot_allowed' => true,
        'premium_migrations_allowed' => true,
    ];
}

/** @return array<string,mixed> */
function jExpectedRejected(string $state, string $dimension, string $reason, string $remediation): array
{
    return [
        'state' => $state,
        'dimension' => $dimension,
        'reason' => $reason,
        'remediation' => $remediation,
        'premium_boot_allowed' => false,
        'premium_migrations_allowed' => false,
    ];
}

/** @return array<string,mixed> */
function jEval(array $free, array $pro): array
{
    return \WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::evaluate($free, $pro);
}

/** @return array<string,mixed> */
function jFp61(): array
{
    $free = jBaseFree();
    $pro = jBasePro();
    $pro['min_platform_api'] = '1.5.0';
    $pro['max_platform_api'] = '1.5.0';

    $decision = jEval($free, $pro);
    jAssertDecision($decision, jExpectedCompatible(), 'FP-61');

    return ['status'=>'PASS','fixture_id'=>'FP-61','case'=>'exact_match','decision'=>$decision];
}

/** @return array<string,mixed> */
function jFp62(): array
{
    $free = jBaseFree();
    $pro = jBasePro();
    $free['platform_api'] = '1.0.0';

    $decision = jEval($free, $pro);
    jAssertDecision($decision, jExpectedCompatible(), 'FP-62');

    return ['status'=>'PASS','fixture_id'=>'FP-62','case'=>'inclusive_minimum','decision'=>$decision];
}

/** @return array<string,mixed> */
function jFp63(): array
{
    $free = jBaseFree();
    $pro = jBasePro();
    $free['platform_api'] = '2.0.0';

    $decision = jEval($free, $pro);
    jAssertDecision($decision, jExpectedCompatible(), 'FP-63');

    return ['status'=>'PASS','fixture_id'=>'FP-63','case'=>'inclusive_maximum','decision'=>$decision];
}

/** @return array<string,mixed> */
function jFp64(): array
{
    $free = jBaseFree();
    $pro = jBasePro();
    $free['platform_api'] = '0.9.9';

    $expected = jExpectedRejected('platform_api_too_old','platform_api','platform_api_below_supported_minimum','update_free');
    $decision = jEval($free, $pro);
    jAssertDecision($decision, $expected, 'FP-64');

    return ['status'=>'PASS','fixture_id'=>'FP-64','case'=>'below_minimum','decision'=>$decision];
}

/** @return array<string,mixed> */
function jFp65(): array
{
    $free = jBaseFree();
    $pro = jBasePro();
    $free['platform_api'] = '2.0.1';

    $expected = jExpectedRejected('platform_api_too_new','platform_api','platform_api_above_supported_maximum','update_pro');
    $decision = jEval($free, $pro);
    jAssertDecision($decision, $expected, 'FP-65');

    return ['status'=>'PASS','fixture_id'=>'FP-65','case'=>'above_maximum','decision'=>$decision];
}

/** @return array<string,mixed> */
function jFp66(): array
{
    $invalidPro = jExpectedRejected(
        'pro_metadata_invalid',
        'pro_metadata',
        'pro_metadata_missing_malformed_or_contradictory',
        'reinstall_pro',
    );
    $invalidFree = jExpectedRejected(
        'platform_api_invalid',
        'platform_api',
        'platform_api_malformed',
        'repair_free',
    );

    $cases = [];

    $definitions = [
        'malformed_min' => static function (array $free, array $pro): array {
            $pro['min_platform_api'] = '1.x.0';
            return [$free, $pro, jExpectedRejected('pro_metadata_invalid','pro_metadata','pro_metadata_missing_malformed_or_contradictory','reinstall_pro')];
        },
        'empty_max' => static function (array $free, array $pro): array {
            $pro['max_platform_api'] = '';
            return [$free, $pro, jExpectedRejected('pro_metadata_invalid','pro_metadata','pro_metadata_missing_malformed_or_contradictory','reinstall_pro')];
        },
        'non_string_min' => static function (array $free, array $pro): array {
            $pro['min_platform_api'] = 1.0;
            return [$free, $pro, jExpectedRejected('pro_metadata_invalid','pro_metadata','pro_metadata_missing_malformed_or_contradictory','reinstall_pro')];
        },
        'contradictory_range' => static function (array $free, array $pro): array {
            $pro['min_platform_api'] = '2.0.0';
            $pro['max_platform_api'] = '1.0.0';
            return [$free, $pro, jExpectedRejected('pro_metadata_invalid','pro_metadata','pro_metadata_missing_malformed_or_contradictory','reinstall_pro')];
        },
        'malformed_installed_api' => static function (array $free, array $pro): array {
            $free['platform_api'] = '1.5';
            return [$free, $pro, jExpectedRejected('platform_api_invalid','platform_api','platform_api_malformed','repair_free')];
        },
        'prerelease_installed_api' => static function (array $free, array $pro): array {
            $free['platform_api'] = '1.5.0-beta';
            return [$free, $pro, jExpectedRejected('platform_api_invalid','platform_api','platform_api_malformed','repair_free')];
        },
    ];

    foreach ($definitions as $id => $mutate) {
        [$free, $pro, $expected] = $mutate(jBaseFree(), jBasePro());
        $decision = jEval($free, $pro);
        jAssertDecision($decision, $expected, "FP-66/{$id}");
        $cases[$id] = $decision;
    }

    jAssert(count($cases) === 6, 'FP-66 coverage table drift');

    return ['status'=>'PASS','fixture_id'=>'FP-66','cases'=>$cases];
}

/** @return array<string,mixed> */
function jFp67(): array
{
    $free = jBaseFree();
    $pro = jBasePro();
    $free['platform_api'] = '2.0.0';
    $pro['min_platform_api'] = '1.0.0';
    $pro['max_platform_api'] = '1.9.9';

    $expected = jExpectedRejected('platform_api_too_new','platform_api','platform_api_above_supported_maximum','update_pro');
    $decision = jEval($free, $pro);
    jAssertDecision($decision, $expected, 'FP-67');

    return ['status'=>'PASS','fixture_id'=>'FP-67','case'=>'unknown_next_major_outside_range','decision'=>$decision];
}

/** @return array<string,mixed> */
function jFp68(): array
{
    $pro = jBasePro();

    $baseline = jBaseFree();
    $baseline['version'] = '1.0.0';
    $baseline['platform_api'] = '1.5.0';

    $changed = jBaseFree();
    $changed['version'] = '1.8.7';
    $changed['platform_api'] = '1.5.0';

    $before = jEval($baseline, $pro);
    $after = jEval($changed, $pro);

    jAssertDecision($before, jExpectedCompatible(), 'FP-68/baseline');
    jAssertDecision($after, jExpectedCompatible(), 'FP-68/changed_marketing_version');
    jAssert($before['platform_api'] === $after['platform_api'], 'FP-68 Platform API must remain unchanged');
    jAssert($before['free_version'] !== $after['free_version'], 'FP-68 marketing version must actually change');

    return [
        'status'=>'PASS',
        'fixture_id'=>'FP-68',
        'case'=>'marketing_version_changes_inside_declared_range',
        'baseline_decision'=>$before,
        'changed_decision'=>$after,
        'platform_api_unchanged'=>true,
        'marketing_version_equality_shortcut_observed'=>false,
    ];
}

/** @return list<string> */
function jForbiddenRemoteSourceMatches(string $source): array
{
    $patterns = [
        '/\\bwp_(?:safe_)?remote_[a-z_]+\\s*\\(/i',
        '/\\bcurl_(?:init|exec|multi_exec)\\s*\\(/i',
        '/\\bfsockopen\\s*\\(/i',
        '/\\bstream_socket_client\\s*\\(/i',
        '/file_get_contents\\s*\\(\\s*[\'\"]https?:\\/\\//i',
        '/\\bRequests::(?:request|get|post)\\s*\\(/i',
        '/\\bGuzzleHttp\\\\/i',
        '/\\bWPEssential\\\\[^;\\n]*(?:License|Entitlement|Provider)[^;\\n]*/i',
    ];

    $matches = [];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source, $m) === 1) {
            $matches[] = $m[0];
        }
    }

    return $matches;
}

/** @return array<string,mixed> */
function jFp76(string $sourcePath): array
{
    $source = (string) file_get_contents($sourcePath);
    jAssert($source !== '', 'FP-76 compatibility source unreadable');

    $remoteMatches = jForbiddenRemoteSourceMatches($source);
    jAssert($remoteMatches === [], 'FP-76 remote/provider dependency detected: ' . implode(', ', $remoteMatches));

    $vectors = [
        'exact' => [jBaseFree(), jBasePro(), jExpectedCompatible()],
        'below' => [(static function (): array {$v=jBaseFree();$v['platform_api']='0.9.9';return $v;})(), jBasePro(), jExpectedRejected('platform_api_too_old','platform_api','platform_api_below_supported_minimum','update_free')],
        'above' => [(static function (): array {$v=jBaseFree();$v['platform_api']='2.0.1';return $v;})(), jBasePro(), jExpectedRejected('platform_api_too_new','platform_api','platform_api_above_supported_maximum','update_pro')],
    ];

    $decisions = [];
    foreach ($vectors as $id => [$free, $pro, $expected]) {
        $decision = jEval($free, $pro);
        jAssertDecision($decision, $expected, "FP-76/{$id}");
        $decisions[$id] = $decision;
    }

    $included = array_map(static fn (string $p): string => str_replace('\\\\','/',$p), get_included_files());
    foreach ($included as $path) {
        jAssert(!str_ends_with($path, '/wp-load.php') && !str_ends_with($path, '/wp-settings.php'), 'FP-76 unexpectedly booted WordPress');
    }

    return [
        'status'=>'PASS',
        'fixture_id'=>'FP-76',
        'pure_php_cli'=>PHP_SAPI === 'cli',
        'wordpress_booted'=>false,
        'database_used'=>false,
        'remote_or_provider_source_matches'=>[],
        'range_vectors'=>$decisions,
        'included_file_count'=>count($included),
    ];
}

/** @return array<string,array<string,mixed>> */
function jRunFixtureTable(string $sourcePath): array
{
    return [
        'FP-61' => jFp61(),
        'FP-62' => jFp62(),
        'FP-63' => jFp63(),
        'FP-64' => jFp64(),
        'FP-65' => jFp65(),
        'FP-66' => jFp66(),
        'FP-67' => jFp67(),
        'FP-68' => jFp68(),
        'FP-76' => jFp76($sourcePath),
    ];
}

/** @return array<string,mixed> */
function jExecute(): array
{
    $identity = jLoadIdentity();

    $expectedPhp = jEnv('WPE_P006_EXPECTED_PHP');
    $actualPhp = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
    jAssert(in_array($expectedPhp, ['8.2','8.5'], true), 'PHP cell outside grant -008');
    jAssert($actualPhp === $expectedPhp, "PHP cell mismatch: {$actualPhp} != {$expectedPhp}");
    jAssert(PHP_SAPI === 'cli', 'Wave 1J requires pure PHP CLI');

    jAssert(!defined('WPINC'), 'WordPress must not be booted for Wave 1J B1');
    jAssert(!class_exists('wpdb', false), 'Database/WordPress class unexpectedly loaded');

    $sourcePath = jEnv('WPE_P006_PREFLIGHT_SOURCE_PATH');
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__DIR__, 2) . '/');
    }
    require_once $sourcePath;

    jAssert(class_exists(\WPEssential\Modules\Compatibility\LocalCompatibilityPreflight::class), 'Compatibility evaluator unavailable');

    $first = jRunFixtureTable($sourcePath);
    $second = jRunFixtureTable($sourcePath);

    $firstJson = json_encode($first, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $secondJson = json_encode($second, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    jAssert(hash_equals(hash('sha256', $firstJson), hash('sha256', $secondJson)), 'Fixture decisions are not deterministic');

    foreach (P006J_FIXTURES as $fixtureId) {
        jAssert(($first[$fixtureId]['status'] ?? null) === 'PASS', "{$fixtureId} did not PASS");
    }

    return [
        'status' => 'PASS',
        'protocol' => 'P-006',
        'wave' => '1J-lane-b1-pure-range-semantics',
        'cell' => ['php_minor'=>$actualPhp,'php_version'=>PHP_VERSION,'sapi'=>PHP_SAPI],
        'source_sha' => jEnv('WPE_P006_SOURCE_SHA'),
        'temporary_approval_id' => P006J_GRANT,
        'candidate_identity' => $identity['candidate_identity'],
        'fixture_results' => $first,
        'fixture_count' => count($first),
        'determinism' => [
            'executions' => 2,
            'normalized_decision_sha256' => hash('sha256', $firstJson),
            'identical' => true,
        ],
        'execution_boundary' => [
            'wordpress_boot' => false,
            'database' => false,
            'filesystem_replacement' => false,
            'remote_or_provider_calls' => false,
            'destructive_or_live_action' => false,
        ],
        'certification_boundary' => [
            'permanent_p001_cf_certified' => false,
            'free_pro_pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'ga_or_release_authorized' => false,
        ],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    if ($mode === 'identity') {
        $identity = jIdentity();
        jWrite(jEnv('WPE_P006_IDENTITY_PATH'), $identity);
        fwrite(STDOUT, json_encode($identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
        exit(0);
    }

    jAssert($mode === 'execute', 'Usage: <identity|execute>');
    $result = jExecute();
    $out = rtrim(jEnv('WPE_P006_EVIDENCE_DIR'), '/\\') . '/summary.json';
    jWrite($out, $result);
    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 Wave 1J] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
