<?php

declare(strict_types=1);

/**
 * Fail-closed identity preflight for the one-time Wave 1U minimum-cell grant.
 * This preflight never records a formal fixture result or changes fixture counts.
 */

const P006_WAVE1U_GRANT = 'GOV-P001-CF-TEMP-019';
const P006_WAVE1U_SNAPSHOT = '24178a1289fb5929eda850f488d932c94bbbddc5936a82cc26cb83979823b058';

function wave1uRequire(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function wave1uEnv(string $name): string
{
    $value = trim((string) getenv($name));
    wave1uRequire($value !== '', 'Missing ' . $name);
    return $value;
}

/** @return array<string,mixed> */
function wave1uJson(string $path): array
{
    wave1uRequire(is_file($path), 'Missing identity file');
    $raw = file_get_contents($path);
    wave1uRequire(is_string($raw) && $raw !== '', 'Unreadable identity file');
    $value = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    wave1uRequire(is_array($value), 'Invalid identity object');
    return $value;
}

try {
    $source = wave1uEnv('WPE_P006_SOURCE_SHA');
    wave1uRequire(preg_match('/^[0-9a-f]{40}$/', $source) === 1, 'Invalid source SHA');
    wave1uRequire(wave1uEnv('WPE_P006_AUTHORIZATION') === P006_WAVE1U_GRANT, 'Grant mismatch');
    wave1uRequire(wave1uEnv('WPE_P006_CELL') === 'minimum', 'Only the minimum cell is authorized');
    $candidate = wave1uJson(wave1uEnv('WPE_P006_CANDIDATE_IDENTITY'));
    wave1uRequire(($candidate['source_sha'] ?? null) === $source, 'Candidate source drift');
    wave1uRequire(($candidate['variant_boundary']['P_API_changed_entries'] ?? null) === ['wpessential-pro/wpessential-pro.php'], 'P-API changed-entry drift');
    wave1uRequire(($candidate['pairs']['F0_P0']['preflight']['state'] ?? null) === 'compatible', 'Canonical pair mismatch');
    wave1uRequire(($candidate['pairs']['F0_P_API']['preflight']['state'] ?? null) === 'platform_api_too_old', 'P-API decision mismatch');
    wave1uRequire(($candidate['pairs']['F0_P_API']['preflight']['premium_boot_allowed'] ?? null) === false, 'Incompatible premium boot allowed');
    wave1uRequire(($candidate['pairs']['F0_P_API']['preflight']['premium_migrations_allowed'] ?? null) === false, 'Incompatible premium migrations allowed');

    $dir = rtrim(wave1uEnv('WPE_P006_CANDIDATE_DIR'), '/\\');
    $nodes = ['F0' => 'f0.zip', 'P0' => 'p0.zip', 'P_API' => 'p-api.zip'];
    $hashes = [];
    foreach ($nodes as $name => $file) {
        $expected = $candidate['nodes'][$name]['sha256'] ?? null;
        wave1uRequire(is_string($expected) && preg_match('/^[0-9a-f]{64}$/', $expected) === 1, 'Missing candidate hash');
        $actual = hash_file('sha256', $dir . '/' . $file);
        wave1uRequire(is_string($actual) && hash_equals($expected, $actual), 'Candidate ZIP identity drift');
        $hashes[$name] = $actual;
    }
    wave1uRequire($hashes['F0'] !== $hashes['P0'] && $hashes['P0'] !== $hashes['P_API'], 'Candidate nodes must differ');
    foreach (['F0_P0' => 'P0', 'F0_P_API' => 'P_API'] as $pair => $pro) {
        $expected = hash('sha256', $hashes['F0'] . ':' . $hashes[$pro]);
        wave1uRequire(hash_equals($expected, (string) ($candidate['pairs'][$pair]['pair_id_sha256'] ?? '')), 'Pair identity drift');
    }

    $record = [
        'status' => 'READY_FOR_BOUNDED_RUNTIME',
        'protocol' => 'P-006',
        'wave' => '1U',
        'authorization' => P006_WAVE1U_GRANT,
        'source_sha' => $source,
        'cell' => ['id' => 'minimum', 'wordpress' => '6.9', 'php' => '8.2', 'mysql' => '8.4'],
        'authorized_fixtures' => ['FP-86', 'FP-89', 'FP-90', 'FP-94'],
        'candidate_sha256' => $hashes,
        'pair_id_sha256' => [
            'F0_P0' => $candidate['pairs']['F0_P0']['pair_id_sha256'],
            'F0_P_API' => $candidate['pairs']['F0_P_API']['pair_id_sha256'],
        ],
        'required_older_snapshot_sha256' => P006_WAVE1U_SNAPSHOT,
        'formal_fixture_executed' => false,
        'accounting_changed' => false,
    ];
    $output = wave1uEnv('WPE_P006_IDENTITY_OUTPUT');
    wave1uRequire(is_dir(dirname($output)) || mkdir(dirname($output), 0775, true), 'Unable to create output directory');
    $encoded = json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    wave1uRequire(file_put_contents($output, $encoded) !== false, 'Unable to write identity record');
    fwrite(STDOUT, $encoded);
} catch (Throwable $error) {
    fwrite(STDERR, '[P-006 Wave 1U identity] ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
