<?php

declare(strict_types=1);

function mFail(string $message): never
{
    throw new RuntimeException($message);
}

function mAssert(bool $condition, string $message): void
{
    if (!$condition) {
        mFail($message);
    }
}

function mEnv(string $key): string
{
    $value = trim((string) getenv($key));
    if ($value === '') {
        mFail("Missing env: {$key}");
    }
    return $value;
}

/** @return array<string,mixed> */
function mReadJson(string $path): array
{
    mAssert(is_file($path), "Missing JSON: {$path}");
    $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    mAssert(is_array($decoded), "Invalid JSON object: {$path}");
    return $decoded;
}

/** @param array<string,mixed> $value */
function mWriteJson(string $path, array $value): void
{
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mAssert(mkdir($dir, 0775, true) || is_dir($dir), "Unable to create directory: {$dir}");
    }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    mAssert(file_put_contents($path, $json . PHP_EOL) !== false, "Unable to write JSON: {$path}");
}

function mHashFile(string $path): string
{
    mAssert(is_file($path) && filesize($path) > 0, "Missing artifact: {$path}");
    $hash = hash_file('sha256', $path);
    mAssert(is_string($hash), "Unable to hash artifact: {$path}");
    return $hash;
}

function mTreeDigest(string $root, string $slug): string
{
    $real = realpath($root);
    mAssert(is_string($real) && is_dir($real), "Unable to resolve payload root: {$slug}");
    mAssert(!is_link($root), "Live plugin root must be a real directory: {$root}");

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        mAssert(!$item->isLink(), "Nested symlink in live payload: {$item->getPathname()}");
        if (!$item->isFile()) {
            continue;
        }
        $relative = substr($item->getPathname(), strlen($real) + 1);
        $relative = str_replace('\\', '/', $relative);
        $sha = hash_file('sha256', $item->getPathname());
        mAssert(is_string($sha), "Unable to hash live file: {$item->getPathname()}");
        $files[$slug . '/' . $relative] = $sha;
    }

    ksort($files, SORT_STRING);
    $ctx = hash_init('sha256');
    foreach ($files as $name => $sha) {
        hash_update($ctx, $name . "\0" . $sha . "\n");
    }
    return hash_final($ctx);
}

/** @return list<string> */
function mNetworkAttempts(string $log): array
{
    if (!is_file($log)) {
        return [];
    }
    $lines = file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values(array_map('strval', $lines)) : [];
}

/** @return list<string> */
function mBackupResidue(string $wpDir): array
{
    $root = $wpDir . '/wp-content/upgrade-temp-backup/plugins';
    if (!is_dir($root)) {
        return [];
    }
    $entries = array_values(array_filter(scandir($root) ?: [], static fn(string $v): bool => $v !== '.' && $v !== '..'));
    sort($entries, SORT_STRING);
    return $entries;
}

/** @return array<string,mixed> */
function mInput(): array
{
    $wpDir = rtrim(mEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    $evidenceDir = rtrim(mEnv('WPE_P006_EVIDENCE_DIR'), '/\\');
    $target = mEnv('WPE_P006_MANUAL_TARGET');
    mAssert(in_array($target, ['free', 'pro'], true), 'WPE_P006_MANUAL_TARGET must be free or pro');

    return [
        'wp_dir' => $wpDir,
        'evidence_dir' => $evidenceDir,
        'network_log' => mEnv('WPE_P006_NETWORK_LOG'),
        'identity_path' => mEnv('WPE_P006_IDENTITY_PATH'),
        'f0_zip' => mEnv('WPE_P006_F0_ZIP_PATH'),
        'f1_zip' => mEnv('WPE_P006_F1_ZIP_PATH'),
        'p0_zip' => mEnv('WPE_P006_P0_ZIP_PATH'),
        'p1_zip' => mEnv('WPE_P006_P1_ZIP_PATH'),
        'target' => $target,
        'expected_wp' => mEnv('WPE_P006_EXPECTED_WP'),
        'expected_php' => mEnv('WPE_P006_EXPECTED_PHP'),
        'expected_mysql' => mEnv('WPE_P006_EXPECTED_MYSQL'),
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in */
function mVerifyArtifacts(array $identity, array $in): void
{
    $nodes = $identity['nodes'] ?? null;
    mAssert(is_array($nodes), 'Candidate identity nodes missing');

    $map = [
        'F0' => $in['f0_zip'],
        'F1' => $in['f1_zip'],
        'P0' => $in['p0_zip'],
        'P1' => $in['p1_zip'],
    ];
    foreach ($map as $node => $path) {
        mAssert(isset($nodes[$node]) && is_array($nodes[$node]), "Candidate node missing: {$node}");
        mAssert(hash_equals((string) $nodes[$node]['sha256'], mHashFile((string) $path)), "{$node} ZIP hash drift");
    }
}

/** @param array<string,mixed> $in */
function mBoot(array $in): void
{
    $_SERVER['HTTP_HOST'] = 'p006-manual.test';
    $_SERVER['SERVER_NAME'] = 'p006-manual.test';
    $_SERVER['SERVER_PORT'] = '80';
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = '/wp-admin/plugins.php';
    $_SERVER['SCRIPT_NAME'] = '/wp-admin/plugins.php';
    $_SERVER['PHP_SELF'] = '/wp-admin/plugins.php';

    require $in['wp_dir'] . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function mPrepare(array $identity, array $in): array
{
    mVerifyArtifacts($identity, $in);
    file_put_contents($in['network_log'], '');
    mBoot($in);

    mAssert(is_blog_installed(), 'Disposable WordPress is not installed');

    foreach (['wpessential-pro/wpessential-pro.php', 'wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin, true, false);
        }
    }

    $result = activate_plugin('wpessential/wpessential.php', '', false, true);
    mAssert(!is_wp_error($result) && is_plugin_active('wpessential/wpessential.php'), 'F0 activation failed');

    $result = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    mAssert(!is_wp_error($result) && is_plugin_active('wpessential-pro/wpessential-pro.php'), 'P0 activation failed');

    $nodes = $identity['nodes'];
    $freeRoot = $in['wp_dir'] . '/wp-content/plugins/wpessential';
    $proRoot = $in['wp_dir'] . '/wp-content/plugins/wpessential-pro';
    $freeTree = mTreeDigest($freeRoot, 'wpessential');
    $proTree = mTreeDigest($proRoot, 'wpessential-pro');

    mAssert($freeTree === $nodes['F0']['payload_tree_sha256'], 'Baseline Free tree is not F0');
    mAssert($proTree === $nodes['P0']['payload_tree_sha256'], 'Baseline Pro tree is not P0');

    $attempts = mNetworkAttempts($in['network_log']);
    mAssert($attempts === [], 'Unexpected outbound HTTP during baseline activation');

    return [
        'status' => 'PASS',
        'phase' => 'prepare',
        'target' => $in['target'],
        'environment' => [
            'wordpress' => get_bloginfo('version'),
            'php' => PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
            'mysql' => $GLOBALS['wpdb']->db_version(),
            'filesystem_method_declared' => defined('FS_METHOD') ? FS_METHOD : null,
        ],
        'baseline' => [
            'free_node' => 'F0',
            'pro_node' => 'P0',
            'free_payload_tree_sha256' => $freeTree,
            'pro_payload_tree_sha256' => $proTree,
            'free_active' => is_plugin_active('wpessential/wpessential.php'),
            'pro_active' => is_plugin_active('wpessential-pro/wpessential-pro.php'),
            'free_root_is_symlink' => is_link($freeRoot),
            'pro_root_is_symlink' => is_link($proRoot),
        ],
        'network_attempts' => [],
        'fp58_status' => 'NOT_EXECUTED',
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function mOverwrite(array $identity, array $in): array
{
    mVerifyArtifacts($identity, $in);
    file_put_contents($in['network_log'], '');
    mBoot($in);

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skins.php';
    require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

    mAssert(is_plugin_active('wpessential/wpessential.php'), 'Free is not active before manual overwrite');
    mAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro is not active before manual overwrite');

    $filesystemMethod = get_filesystem_method([], WP_CONTENT_DIR);
    mAssert($filesystemMethod === 'direct', "Expected direct WordPress filesystem method, got {$filesystemMethod}");
    mAssert(WP_Filesystem(), 'WP_Filesystem() failed');
    global $wp_filesystem;
    mAssert(is_object($wp_filesystem), 'WordPress filesystem object missing');

    $zip = $in['target'] === 'free' ? $in['f1_zip'] : $in['p1_zip'];
    $slug = $in['target'] === 'free' ? 'wpessential' : 'wpessential-pro';
    $expectedNode = $in['target'] === 'free' ? 'F1' : 'P1';

    $beforeTree = mTreeDigest(
        $in['wp_dir'] . '/wp-content/plugins/' . $slug,
        $slug,
    );
    $expectedBefore = $identity['nodes'][$in['target'] === 'free' ? 'F0' : 'P0']['payload_tree_sha256'];
    mAssert($beforeTree === $expectedBefore, 'Manual overwrite did not begin from the expected baseline payload');

    $skin = new Automatic_Upgrader_Skin();
    $upgrader = new Plugin_Upgrader($skin);
    $result = $upgrader->install(
        (string) $zip,
        [
            'overwrite_package' => true,
            'clear_update_cache' => false,
        ],
    );

    mAssert($result === true, 'Plugin_Upgrader manual overwrite did not return true');

    $afterTree = mTreeDigest(
        $in['wp_dir'] . '/wp-content/plugins/' . $slug,
        $slug,
    );
    mAssert(
        $afterTree === $identity['nodes'][$expectedNode]['payload_tree_sha256'],
        "Live {$slug} payload does not match {$expectedNode} after WordPress overwrite",
    );

    $attempts = mNetworkAttempts($in['network_log']);
    mAssert($attempts === [], 'Unexpected outbound HTTP during local manual ZIP overwrite');

    return [
        'status' => 'PASS',
        'phase' => 'overwrite',
        'target' => $in['target'],
        'transport' => [
            'owner' => 'WordPress core',
            'api' => 'Plugin_Upgrader::install',
            'local_zip' => true,
            'overwrite_package' => true,
            'filesystem_method' => $filesystemMethod,
            'plugin_root_is_symlink' => is_link($in['wp_dir'] . '/wp-content/plugins/' . $slug),
            'upgrader_result' => true,
        ],
        'payload' => [
            'before_tree_sha256' => $beforeTree,
            'after_tree_sha256' => $afterTree,
            'expected_after_node' => $expectedNode,
            'zip_sha256' => mHashFile((string) $zip),
        ],
        'activation_state_after_transport_same_process' => [
            'free_active' => is_plugin_active('wpessential/wpessential.php'),
            'pro_active' => is_plugin_active('wpessential-pro/wpessential-pro.php'),
        ],
        'temp_backup_residue' => mBackupResidue($in['wp_dir']),
        'network_attempts' => [],
        'fp58_status' => 'NOT_EXECUTED',
    ];
}

/** @param array<string,mixed> $identity @param array<string,mixed> $in @return array<string,mixed> */
function mObserve(array $identity, array $in): array
{
    mVerifyArtifacts($identity, $in);
    file_put_contents($in['network_log'], '');
    mBoot($in);

    $freeNode = $in['target'] === 'free' ? 'F1' : 'F0';
    $proNode = $in['target'] === 'pro' ? 'P1' : 'P0';

    $freeRoot = $in['wp_dir'] . '/wp-content/plugins/wpessential';
    $proRoot = $in['wp_dir'] . '/wp-content/plugins/wpessential-pro';
    $freeTree = mTreeDigest($freeRoot, 'wpessential');
    $proTree = mTreeDigest($proRoot, 'wpessential-pro');

    mAssert($freeTree === $identity['nodes'][$freeNode]['payload_tree_sha256'], 'Observed Free payload tree mismatch');
    mAssert($proTree === $identity['nodes'][$proNode]['payload_tree_sha256'], 'Observed Pro payload tree mismatch');
    mAssert(is_plugin_active('wpessential/wpessential.php'), 'Free plugin did not remain active after manual overwrite');
    mAssert(is_plugin_active('wpessential-pro/wpessential-pro.php'), 'Pro plugin did not remain active after manual overwrite');

    $compatibility = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    mAssert(is_array($compatibility), 'Compatibility result missing after fresh post-overwrite boot');
    mAssert(($compatibility['state'] ?? null) === 'compatible', 'Post-overwrite pair is not compatible');
    mAssert(($compatibility['premium_boot_allowed'] ?? null) === true, 'Post-overwrite premium boot was not admitted');

    $attempts = mNetworkAttempts($in['network_log']);
    mAssert($attempts === [], 'Unexpected outbound HTTP during post-overwrite fresh boot');

    return [
        'status' => 'PASS',
        'phase' => 'observe',
        'target' => $in['target'],
        'logical_pair' => ['free' => $freeNode, 'pro' => $proNode],
        'payload' => [
            'free_payload_tree_sha256' => $freeTree,
            'pro_payload_tree_sha256' => $proTree,
        ],
        'activation_state' => [
            'free_active' => true,
            'pro_active' => true,
        ],
        'compatibility' => $compatibility,
        'temp_backup_residue' => mBackupResidue($in['wp_dir']),
        'network_attempts' => [],
        'fp58_status' => 'NOT_EXECUTED',
    ];
}

/** @param array<string,mixed> $in @return array<string,mixed> */
function mAggregate(array $in): array
{
    $prepare = mReadJson($in['evidence_dir'] . '/prepare.json');
    $overwrite = mReadJson($in['evidence_dir'] . '/overwrite.json');
    $observe = mReadJson($in['evidence_dir'] . '/observe.json');

    foreach ([$prepare, $overwrite, $observe] as $record) {
        mAssert(($record['status'] ?? null) === 'PASS', 'Harness phase is not PASS');
        mAssert(($record['fp58_status'] ?? null) === 'NOT_EXECUTED', 'Harness incorrectly promoted FP-58');
    }

    return [
        'status' => 'PASS',
        'classification' => 'HARNESS PREREQUISITE VALIDATION ONLY',
        'target' => $in['target'],
        'wordpress_owned_manual_overwrite_transport_validated' => true,
        'transport' => $overwrite['transport'],
        'before_after_payload' => [
            'baseline' => $prepare['baseline'],
            'overwrite' => $overwrite['payload'],
            'fresh_boot' => $observe['payload'],
        ],
        'fresh_boot_logical_pair' => $observe['logical_pair'],
        'activation_state' => $observe['activation_state'],
        'compatibility_state' => $observe['compatibility']['state'] ?? null,
        'temp_backup_residue' => $observe['temp_backup_residue'],
        'network_attempt_count' => 0,
        'certification_boundary' => [
            'fp58' => 'NOT_EXECUTED',
            'interrupted_or_corrupt_replacement' => 'NOT_EXECUTED',
            'updater_or_tuf_certified' => false,
            'rollback_or_migration_certified' => false,
            'pair_certified' => false,
            'runtime_certified' => false,
            'adr_0010' => 'Proposed',
            'p006_accounting_changed' => false,
        ],
    ];
}

$mode = trim((string) getenv('WPE_P006_EXECUTION_MODE'));
if ($mode === '') {
    $mode = $argv[1] ?? '';
}

try {
    $in = mInput();
    $identity = mReadJson($in['identity_path']);

    if ($mode === 'prepare') {
        $result = mPrepare($identity, $in);
        mWriteJson($in['evidence_dir'] . '/prepare.json', $result);
    } elseif ($mode === 'overwrite') {
        $result = mOverwrite($identity, $in);
        mWriteJson($in['evidence_dir'] . '/overwrite.json', $result);
    } elseif ($mode === 'observe') {
        $result = mObserve($identity, $in);
        mWriteJson($in['evidence_dir'] . '/observe.json', $result);
    } elseif ($mode === 'aggregate') {
        $result = mAggregate($in);
        mWriteJson($in['evidence_dir'] . '/summary.json', $result);
    } else {
        mFail('Usage: <prepare|overwrite|observe|aggregate>');
    }

    fwrite(STDOUT, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, '[P-006 manual replacement harness] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
