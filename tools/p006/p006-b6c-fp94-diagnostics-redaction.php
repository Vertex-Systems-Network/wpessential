<?php

declare(strict_types=1);

const P006B6C_AUTH = 'GOV-P006-B6C-FP94-DIAGNOSTICS-REDACTION-001';
const P006B6C_SUCCESS_ID = '990.p006_b6c_status_probe';
const P006B6C_MARKER_ID = '991.p006_b6c_marker_probe';
const P006B6C_LICENSE_CANARY = 'P006_B6C_LICENSE_TOKEN_DO_NOT_EMIT';
const P006B6C_VAULT_CANARY = 'P006_B6C_VAULT_PLAINTEXT_DO_NOT_EMIT';
const P006B6C_PRIVATE_CANARY = 'P006_B6C_PRIVATE_USER_DATA_DO_NOT_EMIT';

function b6cFail(string $message): never
{
    throw new RuntimeException($message);
}

function b6cAssert(bool $condition, string $message): void
{
    if (!$condition) {
        b6cFail($message);
    }
}

function b6cEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        b6cFail('Missing env: ' . $key);
    }
    return $value;
}

/** @return array<string,string> */
function b6cCanaries(): array
{
    return [
        'license_like' => P006B6C_LICENSE_CANARY,
        'vault_plaintext' => P006B6C_VAULT_CANARY,
        'private_user_data' => P006B6C_PRIVATE_CANARY,
    ];
}

/** @param array<string,string> $canaries */
function b6cAssertNoCanaries(string $label, string $surface, array $canaries): void
{
    foreach ($canaries as $kind => $canary) {
        b6cAssert(!str_contains($surface, $canary), $label . ' leaked ' . $kind);
    }
}

/** @return array{bytes:int,sha256:string,no_raw_canaries:bool} */
function b6cSurfaceSummary(string $surface): array
{
    b6cAssertNoCanaries('surface', $surface, b6cCanaries());
    return [
        'bytes' => strlen($surface),
        'sha256' => hash('sha256', $surface),
        'no_raw_canaries' => true,
    ];
}

/** @param array<string,mixed> $value */
function b6cWriteEvidence(string $path, array $value): void
{
    if (!is_dir(dirname($path))) {
        b6cAssert(mkdir(dirname($path), 0775, true) || is_dir(dirname($path)), 'Unable to create evidence directory');
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    b6cAssertNoCanaries('evidence JSON', $json, b6cCanaries());
    b6cAssert(file_put_contents($path, $json . PHP_EOL) !== false, 'Unable to write evidence');
}

/** @return array{files:list<string>,files_sha256:string,patterns_absent:bool} */
function b6cAuditMigrationSurfaces(string $repoRoot): array
{
    $roots = [
        $repoRoot . '/frameworks/Platform/Database/Migrations',
        $repoRoot . '/frameworks/Modules/CustomTables/Migration',
    ];
    $patterns = ['error_log(', 'trigger_error(', 'do_action(', 'apply_filters('];
    $files = [];

    foreach ($roots as $root) {
        b6cAssert(is_dir($root), 'Migration audit root missing: ' . $root);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        );
        foreach ($iterator as $item) {
            if (!$item->isFile() || strtolower($item->getExtension()) !== 'php') {
                continue;
            }
            $path = $item->getPathname();
            $relative = str_replace('\\', '/', substr($path, strlen($repoRoot) + 1));
            $content = file_get_contents($path);
            b6cAssert(is_string($content), 'Unable to read migration source: ' . $relative);
            foreach ($patterns as $pattern) {
                b6cAssert(!str_contains($content, $pattern), 'Unexpected migration logging/hook surface in ' . $relative . ': ' . $pattern);
            }
            $files[] = $relative;
        }
    }

    sort($files, SORT_STRING);
    b6cAssert($files !== [], 'Migration surface audit found no PHP files');
    return [
        'files' => $files,
        'files_sha256' => hash('sha256', implode("\n", $files) . "\n"),
        'patterns_absent' => true,
    ];
}

$sourceSha = b6cEnv('WPE_P006_SOURCE_SHA');
b6cAssert(preg_match('/^[0-9a-f]{40}$/', $sourceSha) === 1, 'Invalid source SHA');
$evidenceFile = b6cEnv('WPE_P006_EVIDENCE_FILE');
$errorLog = b6cEnv('WPE_P006_ERROR_LOG');
$stderrCapture = b6cEnv('WPE_P006_STDERR_CAPTURE');
$repoRoot = dirname(__DIR__, 2);

if (!defined('ABSPATH')) {
    define('ABSPATH', $repoRoot . '/');
}

foreach ([
    '/frameworks/Contracts/MigrationInterface.php',
    '/frameworks/Contracts/MigrationStateStoreInterface.php',
    '/frameworks/Platform/Database/DatabaseAdapterInterface.php',
    '/frameworks/Platform/Database/Migrations/InMemoryMigrationStateStore.php',
    '/frameworks/Platform/Database/Migrations/MigrationRegistry.php',
    '/frameworks/Platform/Database/Migrations/MigrationRunner.php',
    '/frameworks/Platform/Database/Migrations/WpdbMigrationStateStore.php',
    '/frameworks/Modules/CustomTables/Migration/Run/Persistence/CreateMigrationRunStoreMigration.php',
    '/frameworks/Platform/Secrets/SensitiveValue.php',
] as $relative) {
    require_once $repoRoot . $relative;
}

final class P006B6CAdapter implements \WPEssential\Platform\Database\DatabaseAdapterInterface
{
    public function __construct(private readonly string $mode)
    {
        b6cAssert(in_array($mode, ['target_create_failure', 'marker_verify_failure'], true), 'Unsupported adapter mode');
    }

    public function networkTablePrefix(): string
    {
        return 'wpep006b6c_';
    }

    public function charsetCollate(): string
    {
        return '';
    }

    public function prepare(string $query, mixed ...$args): string
    {
        foreach ($args as $arg) {
            $replacement = "'" . str_replace("'", "''", (string) $arg) . "'";
            $query = preg_replace('/%s/', $replacement, $query, 1) ?? $query;
        }
        return $query;
    }

    public function getRow(string $query): ?array
    {
        return null;
    }

    public function getResults(string $query): array
    {
        return [];
    }

    public function getVar(string $query): mixed
    {
        return null;
    }

    public function query(string $query): int|bool
    {
        if (
            $this->mode === 'target_create_failure'
            && str_starts_with(ltrim($query), 'CREATE TABLE IF NOT EXISTS')
            && str_contains($query, 'wpep006b6c_wpe_custom_table_migration_runs')
        ) {
            return false;
        }
        return 0;
    }

    public function insert(string $table, array $data, array $formats = []): bool
    {
        return true;
    }

    public function lastError(): string
    {
        return '';
    }

    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}

try {
    b6cAssert(file_put_contents($errorLog, '') !== false, 'Unable to initialize PHP error log');
    ini_set('log_errors', '1');
    ini_set('display_errors', '0');
    ini_set('error_log', $errorLog);
    error_reporting(E_ALL);

    $canaries = b6cCanaries();
    $sourceAudit = b6cAuditMigrationSurfaces($repoRoot);

    $successMigration = new class implements \WPEssential\Contracts\MigrationInterface {
        public function id(): string { return P006B6C_SUCCESS_ID; }
        public function sequence(): int { return 990; }
        public function isDestructive(): bool { return false; }
        public function recoveryPlan(): ?string { return null; }
        public function apply(): void {}
    };
    $successRegistry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
    $successRegistry->register($successMigration);
    $successStore = new \WPEssential\Platform\Database\Migrations\InMemoryMigrationStateStore();
    $successRunner = new \WPEssential\Platform\Database\Migrations\MigrationRunner($successRegistry, $successStore);
    $runnerAppliedIds = $successRunner->runPending();
    b6cAssert($runnerAppliedIds === [P006B6C_SUCCESS_ID], 'Successful runner output drift');

    $targetException = null;
    try {
        $targetAdapter = new P006B6CAdapter('target_create_failure');
        $targetRegistry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
        $targetRegistry->register(
            new \WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration($targetAdapter),
        );
        $targetRunner = new \WPEssential\Platform\Database\Migrations\MigrationRunner(
            $targetRegistry,
            new \WPEssential\Platform\Database\Migrations\InMemoryMigrationStateStore(),
        );
        $targetRunner->runPending();
        b6cFail('Target migration failure probe unexpectedly succeeded');
    } catch (RuntimeException $exception) {
        b6cAssert(
            $exception->getMessage() === 'Unable to initialize Custom Tables Migration Run store.',
            'Target migration exception message drift',
        );
        $targetException = $exception->getMessage();
    }

    $markerException = null;
    try {
        $markerAdapter = new P006B6CAdapter('marker_verify_failure');
        $markerStore = new \WPEssential\Platform\Database\Migrations\WpdbMigrationStateStore($markerAdapter);
        $markerMigration = new class implements \WPEssential\Contracts\MigrationInterface {
            public function id(): string { return P006B6C_MARKER_ID; }
            public function sequence(): int { return 991; }
            public function isDestructive(): bool { return false; }
            public function recoveryPlan(): ?string { return null; }
            public function apply(): void {}
        };
        $markerRegistry = new \WPEssential\Platform\Database\Migrations\MigrationRegistry();
        $markerRegistry->register($markerMigration);
        $markerRunner = new \WPEssential\Platform\Database\Migrations\MigrationRunner($markerRegistry, $markerStore);
        $markerRunner->runPending();
        b6cFail('Marker persistence failure probe unexpectedly succeeded');
    } catch (RuntimeException $exception) {
        b6cAssert(
            $exception->getMessage() === 'Migration state could not be persisted.',
            'Marker-store exception message drift',
        );
        $markerException = $exception->getMessage();
    }

    b6cAssert(is_string($targetException), 'Target exception not captured');
    b6cAssert(is_string($markerException), 'Marker exception not captured');

    $sensitive = new \WPEssential\Platform\Secrets\SensitiveValue(P006B6C_VAULT_CANARY);
    $sensitiveJson = json_encode($sensitive, JSON_THROW_ON_ERROR);
    $sensitiveDebug = print_r($sensitive, true);
    b6cAssert(is_string($sensitiveDebug), 'SensitiveValue debug capture failed');
    b6cAssert(str_contains($sensitiveJson, '[REDACTED]'), 'SensitiveValue JSON redaction missing');
    b6cAssert(str_contains($sensitiveDebug, '[REDACTED]'), 'SensitiveValue debug redaction missing');
    b6cAssert(!str_contains($sensitiveJson, P006B6C_VAULT_CANARY), 'SensitiveValue JSON leaked plaintext');
    b6cAssert(!str_contains($sensitiveDebug, P006B6C_VAULT_CANARY), 'SensitiveValue debug leaked plaintext');

    $phpErrorLog = is_file($errorLog) ? (string) file_get_contents($errorLog) : '';
    $stderr = is_file($stderrCapture) ? (string) file_get_contents($stderrCapture) : '';

    $surfaces = [
        'runner_output' => json_encode($runnerAppliedIds, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        'target_exception' => $targetException,
        'marker_exception' => $markerException,
        'php_error_log' => $phpErrorLog,
        'stderr_capture' => $stderr,
        'sensitive_json' => $sensitiveJson,
        'sensitive_debug' => $sensitiveDebug,
    ];
    $surfaceEvidence = [];
    foreach ($surfaces as $label => $surface) {
        b6cAssertNoCanaries($label, $surface, $canaries);
        $surfaceEvidence[$label] = b6cSurfaceSummary($surface);
    }

    $canaryFingerprints = [];
    foreach ($canaries as $kind => $value) {
        $canaryFingerprints[$kind] = hash('sha256', $value);
    }

    $evidence = [
        'status' => 'PASS',
        'protocol' => 'P-006',
        'prerequisite' => 'B6c-FP94-diagnostics-redaction-capture',
        'authorization' => P006B6C_AUTH,
        'classification' => 'TEST-ONLY DIAGNOSTICS/REDACTION PREREQUISITE / FP-94 NOT FORMALLY EXECUTED / NON-CERTIFYING',
        'source_sha' => $sourceSha,
        'php' => PHP_VERSION,
        'source_surface_audit' => [
            'files_scanned_count' => count($sourceAudit['files']),
            'files_sha256' => $sourceAudit['files_sha256'],
            'dedicated_logging_or_hook_patterns_absent' => $sourceAudit['patterns_absent'],
        ],
        'canary_fingerprints_sha256' => $canaryFingerprints,
        'runner_applied_ids' => $runnerAppliedIds,
        'product_exception_messages' => [
            'target_migration' => $targetException,
            'marker_store' => $markerException,
        ],
        'sensitive_value' => [
            'json_representation' => '[REDACTED]',
            'json_contains_redacted_marker' => true,
            'debug_contains_redacted_marker' => true,
            'debug_sha256' => hash('sha256', $sensitiveDebug),
            'vault_plaintext_absent' => true,
        ],
        'captured_surfaces' => $surfaceEvidence,
        'raw_canaries_absent_from_all_captured_surfaces' => true,
        'raw_canaries_absent_from_evidence_json' => true,
        'formal_fixture_executed' => false,
        'fixture_accounting_changed' => false,
        'product_logging_added_or_changed' => false,
        'product_runtime_source_modified' => false,
        'wordpress_or_mysql_runtime_used' => false,
        'provider_or_network_runtime_used' => false,
        'runtime_grant_created' => false,
        'accounting' => ['documented' => 144, 'executed' => 57, 'pass' => 57, 'fail' => 0, 'inconclusive' => 0],
    ];

    $preflightJson = json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    b6cAssertNoCanaries('pre-write evidence JSON', $preflightJson, $canaries);
    b6cWriteEvidence($evidenceFile, $evidence);

    fwrite(STDOUT, $preflightJson . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 B6c FP-94 prerequisite] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
