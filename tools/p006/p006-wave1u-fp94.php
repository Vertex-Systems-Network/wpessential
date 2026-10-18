<?php

declare(strict_types=1);

/** Real FP-86 migration diagnostics capture, scoped to the TEMP-019 minimum cell. */
function fp94Require(bool $ok, string $message): void
{
    if (!$ok) {
        throw new RuntimeException($message);
    }
}

function fp94Env(string $key): string
{
    $value = trim((string) getenv($key));
    fp94Require($value !== '', 'Missing ' . $key);
    return $value;
}

try {
    $source = fp94Env('WPE_P006_SOURCE_SHA');
    fp94Require(preg_match('/^[a-f0-9]{40}$/', $source) === 1, 'Invalid source identity');
    fp94Require(fp94Env('WPE_P006_AUTHORIZATION') === 'GOV-P001-CF-TEMP-019', 'Grant drift');
    fp94Require(fp94Env('WPE_P006_CELL') === 'minimum', 'Unauthorized cell');
    foreach (['WP' => '6.9', 'PHP' => '8.2', 'MYSQL' => '8.4'] as $key => $value) {
        fp94Require(fp94Env('WPE_P006_EXPECTED_' . $key) === $value, 'Runtime cell drift');
    }
    $canaries = [
        'license_like' => 'P006_WAVE1U_LICENSE_TOKEN_DO_NOT_EMIT',
        'vault_plaintext' => 'P006_WAVE1U_VAULT_PLAINTEXT_DO_NOT_EMIT',
        'private_user_data' => 'P006_WAVE1U_PRIVATE_USER_DATA_DO_NOT_EMIT',
    ];
    $fp86Raw = file_get_contents(fp94Env('WPE_P006_FP86_EVIDENCE'));
    fp94Require(is_string($fp86Raw) && $fp86Raw !== '', 'Missing FP-86 runtime record');
    $fp86 = json_decode($fp86Raw, true, 512, JSON_THROW_ON_ERROR);
    fp94Require(is_array($fp86), 'Invalid FP-86 runtime record');
    fp94Require(($fp86['status'] ?? null) === 'PASS' && ($fp86['fixture_id'] ?? null) === 'FP-86', 'FP-86 did not pass');
    fp94Require(($fp86['source_sha'] ?? null) === $source, 'Source head mismatch');
    fp94Require(($fp86['authorization'] ?? null) === 'GOV-P001-CF-TEMP-019', 'FP-86 grant mismatch');
    fp94Require(($fp86['environment']['wordpress'] ?? null) === '6.9', 'Actual WP drift');
    fp94Require(str_starts_with((string) ($fp86['environment']['php'] ?? ''), '8.2.'), 'Actual PHP drift');
    fp94Require(str_starts_with((string) ($fp86['environment']['mysql'] ?? ''), '8.4.'), 'Actual MySQL drift');
    fp94Require(($fp86['first_run']['exception']['message'] ?? null) === 'P006_B6B_FAIL_ONCE_MARKER_WRITE', 'Controlled failure missing');
    fp94Require(($fp86['second_fresh_run']['applied_ids'] ?? null) === ['220.custom_tables_migration_runs_v1'], 'Actual migration status missing');
    fp94Require(($fp86['third_fresh_run']['applied_ids'] ?? null) === [], 'Settled migration status drift');

    $surfaces = ['fp86_evidence' => $fp86Raw];
    foreach ([
        'runtime_stdout' => 'WPE_P006_FP86_STDOUT',
        'runtime_stderr' => 'WPE_P006_FP86_STDERR',
        'php_error_log' => 'WPE_P006_FP86_ERROR_LOG',
    ] as $label => $key) {
        $path = fp94Env($key);
        fp94Require(is_file($path), 'Missing captured surface: ' . $label);
        $bytes = file_get_contents($path);
        fp94Require(is_string($bytes), 'Unreadable captured surface: ' . $label);
        $surfaces[$label] = $bytes;
    }
    $secretClass = dirname(__DIR__, 2) . '/frameworks/Platform/Secrets/SensitiveValue.php';
    require_once $secretClass;
    $secret = new \WPEssential\Platform\Secrets\SensitiveValue($canaries['vault_plaintext']);
    $json = json_encode($secret, JSON_THROW_ON_ERROR);
    $debug = print_r($secret, true);
    fp94Require(str_contains($json, '[REDACTED]') && str_contains($debug, '[REDACTED]'), 'SensitiveValue redaction missing');
    $surfaces['sensitive_json'] = $json;
    $surfaces['sensitive_debug'] = $debug;
    $summaries = [];
    foreach ($surfaces as $label => $bytes) {
        foreach ($canaries as $kind => $value) {
            fp94Require(!str_contains($bytes, $value), $label . ' leaked ' . $kind);
        }
        $summaries[$label] = ['bytes' => strlen($bytes), 'sha256' => hash('sha256', $bytes), 'raw_canaries_absent' => true];
    }
    $fingerprints = [];
    foreach ($canaries as $kind => $value) {
        $fingerprints[$kind] = hash('sha256', $value);
    }
    $result = [
        'status' => 'PASS',
        'protocol' => 'P-006',
        'fixture_id' => 'FP-94',
        'wave' => '1U',
        'authorization' => 'GOV-P001-CF-TEMP-019',
        'classification' => 'FORMAL FP-94 MINIMUM-CELL MIGRATION DIAGNOSTICS / NON-CERTIFYING',
        'source_sha' => $source,
        'cell' => ['wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
        'actual_environment' => $fp86['environment'],
        'real_migration' => '220.custom_tables_migration_runs_v1',
        'controlled_failure_observed' => true,
        'canary_fingerprints_sha256' => $fingerprints,
        'captured_surfaces' => $summaries,
        'sensitive_value_redacted' => true,
        'formal_fixture_executed' => true,
        'fixture_accounting_changed' => false,
        'pair_or_runtime_certified' => false,
    ];
    $encoded = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    foreach ($canaries as $kind => $value) {
        fp94Require(!str_contains($encoded, $value), 'Formal evidence leaked ' . $kind);
    }
    $path = fp94Env('WPE_P006_FP94_EVIDENCE');
    fp94Require(is_dir(dirname($path)) || mkdir(dirname($path), 0775, true), 'Evidence directory unavailable');
    fp94Require(file_put_contents($path, $encoded) !== false, 'Cannot write formal evidence');
    fwrite(STDOUT, $encoded);
} catch (Throwable $error) {
    fwrite(STDERR, '[P-006 Wave 1U FP-94] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
