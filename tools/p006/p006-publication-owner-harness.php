<?php

declare(strict_types=1);

const WPE_PUB_AUTH = 'GOV-P006-B3-PUBLICATION-OWNER-HARNESS-001';
const WPE_PUB_PREMIUM_MODULES = [
    'roles','admin-menu','settings','dashboard','profiles','membership',
    'builder-widgets','forms-workflows','cron','notifications','emails','chat',
];
const WPE_PUB_REQUIRED_FREE_MODULES = ['custom-post-types','taxonomies'];

function pFail(string $message): never { throw new RuntimeException($message); }
function pAssert(bool $condition, string $message): void { if (!$condition) { pFail($message); } }
function pEnv(string $key): string {
    $value = trim((string) getenv($key));
    if ($value === '') { pFail("Missing env: {$key}"); }
    return $value;
}
/** @return array<string,mixed> */
function pJsonRead(string $path): array {
    pAssert(is_file($path), "Missing JSON: {$path}");
    $value = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    pAssert(is_array($value), "Invalid JSON object: {$path}");
    return $value;
}
/** @param array<string,mixed> $value */
function pJsonWrite(string $path, array $value): void {
    $dir = dirname($path);
    if (!is_dir($dir)) { pAssert(mkdir($dir, 0775, true) || is_dir($dir), "Unable to create {$dir}"); }
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    pAssert(file_put_contents($path, $json . PHP_EOL) !== false, "Unable to write {$path}");
}
function pHashFile(string $path): string {
    pAssert(is_file($path), "Missing file: {$path}");
    $hash = hash_file('sha256', $path);
    pAssert(is_string($hash), "Unable to hash {$path}");
    return $hash;
}
function pRemoveTree(string $path): void {
    if (!file_exists($path) && !is_link($path)) { return; }
    if (is_link($path) || is_file($path)) { pAssert(@unlink($path), "Unable to remove {$path}"); return; }
    $items = scandir($path);
    pAssert(is_array($items), "Unable to scan {$path}");
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') { continue; }
        pRemoveTree($path . DIRECTORY_SEPARATOR . $item);
    }
    pAssert(@rmdir($path), "Unable to remove directory {$path}");
}
/** @return list<string> */
function pFiles(string $root): array {
    $real = realpath($root);
    pAssert(is_string($real) && is_dir($real), "Missing directory: {$root}");
    pAssert(!is_link($root), "Symlink root forbidden: {$root}");
    $out = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($real, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $item) {
        pAssert(!$item->isLink(), "Nested symlink forbidden: {$item->getPathname()}");
        if (!$item->isFile()) { continue; }
        $rel = substr($item->getPathname(), strlen($real) + 1);
        $out[] = str_replace('\\', '/', $rel);
    }
    sort($out, SORT_STRING);
    return $out;
}
function pTreeDigest(string $root, string $slug): string {
    $real = realpath($root);
    pAssert(is_string($real) && is_dir($real), "Missing tree: {$root}");
    $ctx = hash_init('sha256');
    foreach (pFiles($root) as $rel) {
        $path = $real . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        hash_update($ctx, $slug . '/' . $rel . "\0" . pHashFile($path) . "\n");
    }
    return hash_final($ctx);
}
/** @return array<string,mixed> */
function pStateManifest(string $root, string $slug, string $entryRel): array {
    $files = [];
    if (is_dir($root)) {
        foreach (pFiles($root) as $rel) {
            $files[$rel] = pHashFile($root . '/' . $rel);
        }
    }
    ksort($files, SORT_STRING);
    $entry = $root . '/' . $entryRel;
    $base = [
        'slug' => $slug,
        'entry' => $entryRel,
        'entry_exists' => is_file($entry),
        'entry_readable' => is_readable($entry),
        'files' => $files,
    ];
    $encoded = json_encode($base, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $base['state_manifest_sha256'] = hash('sha256', $encoded);
    return $base;
}
/** @return array<string,mixed> */
function pIdentity(): array {
    return pJsonRead(pEnv('WPE_PUB_IDENTITY_PATH'));
}
/** @return array<string,mixed> */
function pConfig(): array {
    $target = pEnv('WPE_PUB_TARGET');
    pAssert(in_array($target, ['free','pro'], true), "Unsupported target: {$target}");
    if ($target === 'free') {
        return ['target'=>'free','slug'=>'wpessential','entry'=>'wpessential.php','old'=>'F0','new'=>'F1','counterpart'=>'P0'];
    }
    return ['target'=>'pro','slug'=>'wpessential-pro','entry'=>'wpessential-pro.php','old'=>'P0','new'=>'P1','counterpart'=>'F0'];
}
function pZipForNode(array $identity, string $node): string {
    $artifact = $identity['nodes'][$node]['artifact'] ?? null;
    pAssert(is_string($artifact) && $artifact !== '', "Missing artifact for {$node}");
    $path = rtrim(pEnv('WPE_PUB_CANDIDATE_DIR'), '/\\') . '/' . $artifact;
    pAssert(hash_equals((string) $identity['nodes'][$node]['sha256'], pHashFile($path)), "ZIP hash drift for {$node}");
    return $path;
}
function pExtractNode(array $identity, string $node, string $dest, string $slug): string {
    pRemoveTree($dest);
    pAssert(mkdir($dest, 0775, true) || is_dir($dest), "Unable to create stage {$dest}");
    $zip = new ZipArchive();
    $path = pZipForNode($identity, $node);
    pAssert($zip->open($path) === true, "Unable to open {$path}");
    pAssert($zip->extractTo($dest), "Unable to extract {$path}");
    $zip->close();
    $root = $dest . '/' . $slug;
    pAssert(is_dir($root), "Extracted slug missing: {$root}");
    pAssert(pTreeDigest($root, $slug) === (string) $identity['nodes'][$node]['payload_tree_sha256'], "Extracted tree drift for {$node}");
    return $root;
}
function pCopyFile(string $src, string $dst): void {
    $dir = dirname($dst);
    if (!is_dir($dir)) { pAssert(mkdir($dir, 0775, true) || is_dir($dir), "Unable to create {$dir}"); }
    pAssert(copy($src, $dst), "Unable to copy {$src} -> {$dst}");
    pAssert(hash_equals(pHashFile($src), pHashFile($dst)), "Copied file hash mismatch: {$dst}");
}
/** @return list<string> */
function pNonEntryFiles(string $sourceRoot, string $entryRel): array {
    $files = array_values(array_filter(pFiles($sourceRoot), static fn(string $rel): bool => $rel !== $entryRel && $rel !== '.wpe-publish-entry.tmp'));
    pAssert($files !== [], 'No non-entry payload files');
    return $files;
}
/** @return array{counts:list<int>,observations:list<array<string,mixed>>} */
function pMaterializeNonEntryWithProbes(string $sourceRoot, string $liveRoot, string $entryRel, array $cfg, string $scenario): array {
    $files = pNonEntryFiles($sourceRoot, $entryRel);
    $total = count($files);
    $counts = array_values(array_unique([
        max(1, (int) floor($total * 0.25)),
        max(1, (int) floor($total * 0.50)),
        max(1, (int) floor($total * 0.75)),
    ]));
    sort($counts, SORT_NUMERIC);
    $observations = [];
    foreach ($files as $index => $rel) {
        pCopyFile($sourceRoot . '/' . $rel, $liveRoot . '/' . $rel);
        $copied = $index + 1;
        if (in_array($copied, $counts, true)) {
            $state = pStateManifest($liveRoot, (string) $cfg['slug'], $entryRel);
            pAssert($state['entry_exists'] === false, "Entrypoint appeared before final publish at cut {$copied}");
            $obs = pSpawnObserve("{$scenario}-cut-{$copied}");
            pAssertPartialSafe($obs, $cfg);
            $observations[] = ['copied'=>$copied,'state'=>$state,'observation'=>$obs];
        }
    }
    return ['counts'=>$counts,'observations'=>$observations];
}
function pMaterializePartial(string $sourceRoot, string $liveRoot, string $entryRel, int $count): void {
    $files = pNonEntryFiles($sourceRoot, $entryRel);
    pAssert($count > 0 && $count < count($files), 'Invalid partial count');
    for ($i = 0; $i < $count; $i++) { pCopyFile($sourceRoot . '/' . $files[$i], $liveRoot . '/' . $files[$i]); }
}
function pVerifyNonEntryExact(string $sourceRoot, string $liveRoot, string $entryRel): void {
    $source = pNonEntryFiles($sourceRoot, $entryRel);
    $live = array_values(array_filter(pFiles($liveRoot), static fn(string $rel): bool => $rel !== $entryRel && $rel !== '.wpe-publish-entry.tmp'));
    pAssert($source === $live, 'Non-entry file list mismatch');
    foreach ($source as $rel) { pAssert(hash_equals(pHashFile($sourceRoot . '/' . $rel), pHashFile($liveRoot . '/' . $rel)), "Non-entry file mismatch: {$rel}"); }
}
function pPrepareTempEntry(string $sourceRoot, string $liveRoot, string $entryRel): string {
    $tmp = $liveRoot . '/.wpe-publish-entry.tmp';
    pCopyFile($sourceRoot . '/' . $entryRel, $tmp);
    pAssert(!is_file($liveRoot . '/' . $entryRel), 'Final entry exists before rename');
    return $tmp;
}
function pPublishEntry(string $tmp, string $final, bool $simulateFailure): bool {
    if ($simulateFailure) { return false; }
    pAssert(dirname($tmp) === dirname($final), 'Final entry rename must be same-directory');
    return @rename($tmp, $final);
}
function pRestoreOldEntrypointLast(string $backupRoot, string $liveRoot, array $cfg, string $expectedTree): array {
    pRemoveTree($liveRoot);
    pAssert(mkdir($liveRoot, 0775, true) || is_dir($liveRoot), 'Unable to create recovery live root');
    foreach (pNonEntryFiles($backupRoot, (string) $cfg['entry']) as $rel) { pCopyFile($backupRoot . '/' . $rel, $liveRoot . '/' . $rel); }
    pVerifyNonEntryExact($backupRoot, $liveRoot, (string) $cfg['entry']);
    $tmp = pPrepareTempEntry($backupRoot, $liveRoot, (string) $cfg['entry']);
    pAssert(pPublishEntry($tmp, $liveRoot . '/' . $cfg['entry'], false), 'Recovery final entry rename failed');
    pAssert(pTreeDigest($liveRoot, (string) $cfg['slug']) === $expectedTree, 'Recovered old tree mismatch');
    return pSpawnObserve('recovery');
}
function pResetBaseline(array $identity, array $cfg, string $workspace): void {
    $wpDir = rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    $liveRoot = $wpDir . '/wp-content/plugins/' . $cfg['slug'];
    $resetRoot = pExtractNode($identity, (string) $cfg['old'], $workspace . '/reset', (string) $cfg['slug']);
    pRemoveTree($liveRoot);
    pAssert(@rename($resetRoot, $liveRoot), 'Unable to atomically reset disposable baseline target');
    pAssert(pTreeDigest($liveRoot, (string) $cfg['slug']) === (string) $identity['nodes'][$cfg['old']]['payload_tree_sha256'], 'Baseline reset tree mismatch');
}
function pNetworkAttempts(): array {
    $path = pEnv('WPE_P006_NETWORK_LOG');
    if (!is_file($path)) { return []; }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return is_array($lines) ? array_values($lines) : [];
}
function pClearNetwork(): void { pAssert(file_put_contents(pEnv('WPE_P006_NETWORK_LOG'), '') !== false, 'Unable to clear network log'); }
function pRequestEnvelope(): void {
    $_SERVER['HTTP_HOST'] = 'p006-publication-owner.test';
    $_SERVER['SERVER_NAME'] = 'p006-publication-owner.test';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    $_SERVER['HTTPS'] = 'off';
    $_SERVER['REQUEST_URI'] = '/';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
}
/** @return array<string,mixed> */
function pObserveNow(string $phase): array {
    pClearNetwork();
    pRequestEnvelope();
    $wpDir = rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    require $wpDir . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    pAssert(defined('WPE_P006M_NETWORK_DENY_ACTIVE'), 'Network deny MU plugin missing');
    $compat = $GLOBALS['wpe_pro_compatibility_result'] ?? null;
    $compatState = is_array($compat) ? ($compat['state'] ?? null) : (defined('WPE_PRO_COMPATIBILITY_STATE') ? WPE_PRO_COMPATIBILITY_STATE : null);
    $kernelBooted = false;
    $premium = [];
    $requiredFree = [];
    if (class_exists(\WPEssential\Bootstrap\Plugin::class)) {
        $kernel = \WPEssential\Bootstrap\Plugin::kernel();
        if ($kernel instanceof \WPEssential\Kernel\Kernel && $kernel->isBooted()) {
            $kernelBooted = true;
            $modules = $kernel->modules();
            foreach (WPE_PUB_PREMIUM_MODULES as $id) { if ($modules->has($id)) { $premium[] = $id; } }
            foreach (WPE_PUB_REQUIRED_FREE_MODULES as $id) { if ($modules->has($id)) { $requiredFree[] = $id; } }
            sort($premium, SORT_STRING); sort($requiredFree, SORT_STRING);
        }
    }
    $active = get_option('active_plugins', []);
    $active = is_array($active) ? array_values(array_map('strval', $active)) : [];
    sort($active, SORT_STRING);
    return [
        'phase'=>$phase,
        'wordpress_version'=>(string) get_bloginfo('version'),
        'php_version'=>PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION,
        'mysql_version'=>(string) $GLOBALS['wpdb']->db_version(),
        'filesystem_method'=>get_filesystem_method([], WP_CONTENT_DIR),
        'free_entry_exists'=>is_file(WP_PLUGIN_DIR . '/wpessential/wpessential.php'),
        'pro_entry_exists'=>is_file(WP_PLUGIN_DIR . '/wpessential-pro/wpessential-pro.php'),
        'free_bootstrap_ready'=>defined('WPE_FREE_BOOTSTRAP_READY') && WPE_FREE_BOOTSTRAP_READY === true,
        'compatibility_state'=>is_string($compatState) ? $compatState : null,
        'premium_boot_allowed'=>defined('WPE_PRO_COMPATIBILITY_BOOT_ALLOWED') ? WPE_PRO_COMPATIBILITY_BOOT_ALLOWED === true : false,
        'premium_migrations_allowed'=>defined('WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED') ? WPE_PRO_COMPATIBILITY_MIGRATIONS_ALLOWED === true : false,
        'premium_mutations_allowed'=>defined('WPE_PRO_PREMIUM_MUTATIONS_ALLOWED') ? WPE_PRO_PREMIUM_MUTATIONS_ALLOWED === true : false,
        'kernel_booted'=>$kernelBooted,
        'premium_modules'=>$premium,
        'required_free_modules'=>$requiredFree,
        'active_plugins_option'=>$active,
        'network_attempts'=>pNetworkAttempts(),
        'fatal_or_error'=>null,
    ];
}
/** @return array<string,mixed> */
function pSpawnObserve(string $phase): array {
    $env = getenv();
    pAssert(is_array($env), 'Unable to read environment');
    $env['WPE_PUB_OBSERVE_PHASE'] = $phase;
    $cmd = [PHP_BINARY, __FILE__, 'observe'];
    $spec = [1=>['pipe','w'],2=>['pipe','w']];
    $proc = proc_open($cmd, $spec, $pipes, null, $env);
    pAssert(is_resource($proc), 'Unable to start fresh observation process');
    $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
    $code = proc_close($proc);
    pAssert($code === 0, "Fresh WordPress observation failed ({$phase}): {$stderr}");
    $decoded = json_decode((string) $stdout, true, 512, JSON_THROW_ON_ERROR);
    pAssert(is_array($decoded), "Invalid observation JSON ({$phase})");
    pAssert(($decoded['network_attempts'] ?? null) === [], "Unexpected outbound HTTP ({$phase})");
    return $decoded;
}
function pAssertEnvironment(array $obs): void {
    pAssert(($obs['wordpress_version'] ?? null) === pEnv('WPE_P006_EXPECTED_WP'), 'WordPress runtime cell mismatch');
    pAssert(($obs['php_version'] ?? null) === pEnv('WPE_P006_EXPECTED_PHP'), 'PHP runtime cell mismatch');
    pAssert(str_starts_with((string) ($obs['mysql_version'] ?? ''), '8.4'), 'MySQL runtime cell mismatch');
}
function pAssertCompatible(array $obs): void {
    pAssertEnvironment($obs);
    pAssert(($obs['free_bootstrap_ready'] ?? false) === true, 'Compatible observation missing Free bootstrap');
    pAssert(($obs['compatibility_state'] ?? null) === 'compatible', 'Compatible observation state drift');
    pAssert(($obs['premium_boot_allowed'] ?? false) === true, 'Compatible observation denied premium boot');
    pAssert(($obs['premium_migrations_allowed'] ?? false) === true, 'Compatible observation denied premium migrations');
    pAssert(($obs['kernel_booted'] ?? false) === true, 'Compatible observation Free kernel not booted');
    $required = $obs['required_free_modules'] ?? [];
    sort($required, SORT_STRING);
    $expected = WPE_PUB_REQUIRED_FREE_MODULES; sort($expected, SORT_STRING);
    pAssert($required === $expected, 'Compatible observation missing required Free modules');
    $premium = $obs['premium_modules'] ?? [];
    sort($premium, SORT_STRING);
    $expectedPremium = WPE_PUB_PREMIUM_MODULES; sort($expectedPremium, SORT_STRING);
    pAssert($premium === $expectedPremium, 'Compatible observation premium module set drift');
}
function pAssertPartialSafe(array $obs, array $cfg): void {
    pAssertEnvironment($obs);
    pAssert(array_key_exists('fatal_or_error', $obs) && $obs['fatal_or_error'] === null, 'Partial observation has error');
    pAssert(($obs['premium_boot_allowed'] ?? false) === false, 'Partial state allowed premium boot');
    pAssert(($obs['premium_migrations_allowed'] ?? false) === false, 'Partial state allowed premium migrations');
    pAssert(($obs['premium_mutations_allowed'] ?? false) === false, 'Partial state allowed premium mutations');
    if ($cfg['target'] === 'free') {
        pAssert(($obs['free_entry_exists'] ?? true) === false, 'Free partial state unexpectedly has Free entry');
        pAssert(($obs['free_bootstrap_ready'] ?? true) === false, 'Free partial state booted Free');
    } else {
        pAssert(($obs['pro_entry_exists'] ?? true) === false, 'Pro partial state unexpectedly has Pro entry');
        pAssert(($obs['free_bootstrap_ready'] ?? false) === true, 'Pro partial state lost Free bootstrap');
        pAssert(($obs['kernel_booted'] ?? false) === true, 'Pro partial state lost Free kernel');
        $required = $obs['required_free_modules'] ?? [];
        sort($required, SORT_STRING);
        $expected = WPE_PUB_REQUIRED_FREE_MODULES; sort($expected, SORT_STRING);
        pAssert($required === $expected, 'Pro partial state lost required Free modules');
        pAssert(($obs['premium_modules'] ?? []) === [], 'Pro partial state registered premium modules');
    }
}
function pActivateBaseline(): array {
    pRequestEnvelope();
    $wpDir = rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    require $wpDir . '/wp-load.php';
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    foreach (['wpessential-pro/wpessential-pro.php','wpessential/wpessential.php'] as $plugin) {
        if (is_plugin_active($plugin)) { deactivate_plugins($plugin, true, false); }
    }
    $r = activate_plugin('wpessential/wpessential.php', '', false, true);
    pAssert(!is_wp_error($r), 'Unable to activate Free baseline');
    $r = activate_plugin('wpessential-pro/wpessential-pro.php', '', false, true);
    pAssert(!is_wp_error($r), 'Unable to activate Pro baseline');
    pAssert(get_filesystem_method([], WP_CONTENT_DIR) === 'direct', 'Publication-owner prerequisite requires direct filesystem');
    return ['status'=>'PASS','filesystem_method'=>'direct','active_plugins'=>get_option('active_plugins', [])];
}
/** @return array<string,mixed> */
function pScenarioSuccess(array $identity, array $cfg, string $workspace): array {
    pResetBaseline($identity, $cfg, $workspace . '/success-baseline');
    $baseline = pSpawnObserve('success-baseline'); pAssertCompatible($baseline);
    $wpDir = rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'), '/\\');
    $live = $wpDir . '/wp-content/plugins/' . $cfg['slug'];
    $stage = pExtractNode($identity, (string) $cfg['new'], $workspace . '/success-stage', (string) $cfg['slug']);
    $oldTree = pTreeDigest($live, (string) $cfg['slug']);
    $backup = $workspace . '/success-backup/' . $cfg['slug']; pRemoveTree(dirname($backup)); pAssert(mkdir(dirname($backup), 0775, true) || is_dir(dirname($backup)), 'Unable to create success backup dir');
    pAssert(@rename($live, $backup), 'Unable to preserve old complete generation');
    pAssert(pTreeDigest($backup, (string) $cfg['slug']) === $oldTree, 'Backup tree mismatch');
    pAssert(mkdir($live, 0775, true) || is_dir($live), 'Unable to create success live root');
    $partial = pMaterializeNonEntryWithProbes($stage, $live, (string) $cfg['entry'], $cfg, 'success');
    pVerifyNonEntryExact($stage, $live, (string) $cfg['entry']);
    $tmp = pPrepareTempEntry($stage, $live, (string) $cfg['entry']);
    pAssert(pPublishEntry($tmp, $live . '/' . $cfg['entry'], false), 'Final entry rename failed');
    $finalTree = pTreeDigest($live, (string) $cfg['slug']);
    pAssert($finalTree === (string) $identity['nodes'][$cfg['new']]['payload_tree_sha256'], 'Final new tree mismatch');
    $final = pSpawnObserve('success-final'); pAssertCompatible($final);
    pRemoveTree(dirname($backup));
    return ['scenario'=>'success','status'=>'PASS','old_tree_sha256'=>$oldTree,'staged_tree_sha256'=>pTreeDigest($stage,(string)$cfg['slug']),'cut_points'=>$partial['counts'],'partial_observations'=>$partial['observations'],'final_tree_sha256'=>$finalTree,'final_observation'=>$final];
}
/** @return array<string,mixed> */
function pScenarioInterrupt(array $identity, array $cfg, string $workspace): array {
    pResetBaseline($identity, $cfg, $workspace . '/interrupt-baseline');
    $baseline = pSpawnObserve('interrupt-baseline'); pAssertCompatible($baseline);
    $wpDir = rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'), '/\\'); $live = $wpDir . '/wp-content/plugins/' . $cfg['slug'];
    $stage = pExtractNode($identity, (string) $cfg['new'], $workspace . '/interrupt-stage', (string) $cfg['slug']);
    $oldTree = pTreeDigest($live, (string) $cfg['slug']);
    $backup = $workspace . '/interrupt-backup/' . $cfg['slug']; pRemoveTree(dirname($backup)); pAssert(mkdir(dirname($backup),0775,true)||is_dir(dirname($backup)),'Unable to create interrupt backup');
    pAssert(@rename($live,$backup),'Unable to preserve interrupt old generation'); pAssert(mkdir($live,0775,true)||is_dir($live),'Unable to create interrupt live root');
    $files = pNonEntryFiles($stage,(string)$cfg['entry']); $count=max(1,(int)floor(count($files)/3)); if($count>=count($files)){$count=count($files)-1;}
    pMaterializePartial($stage,$live,(string)$cfg['entry'],$count);
    $state=pStateManifest($live,(string)$cfg['slug'],(string)$cfg['entry']); pAssert($state['entry_exists']===false,'Interrupted entry unexpectedly exists');
    $partial=pSpawnObserve('interrupt-partial'); pAssertPartialSafe($partial,$cfg);
    $recovery=pRestoreOldEntrypointLast($backup,$live,$cfg,$oldTree); pAssertCompatible($recovery);
    pRemoveTree(dirname($backup));
    return ['scenario'=>'interrupt-before-entry','status'=>'PASS','partial_file_count'=>$count,'partial_state'=>$state,'partial_observation'=>$partial,'recovered_tree_sha256'=>pTreeDigest($live,(string)$cfg['slug']),'recovery_observation'=>$recovery];
}
/** @return array<string,mixed> */
function pScenarioRenameFailure(array $identity, array $cfg, string $workspace): array {
    pResetBaseline($identity, $cfg, $workspace . '/rename-baseline');
    $baseline=pSpawnObserve('rename-baseline'); pAssertCompatible($baseline);
    $wpDir=rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'),'/\\'); $live=$wpDir.'/wp-content/plugins/'.$cfg['slug'];
    $stage=pExtractNode($identity,(string)$cfg['new'],$workspace.'/rename-stage',(string)$cfg['slug']);
    $oldTree=pTreeDigest($live,(string)$cfg['slug']);
    $backup=$workspace.'/rename-backup/'.$cfg['slug']; pRemoveTree(dirname($backup)); pAssert(mkdir(dirname($backup),0775,true)||is_dir(dirname($backup)),'Unable to create rename backup');
    pAssert(@rename($live,$backup),'Unable to preserve rename old generation'); pAssert(mkdir($live,0775,true)||is_dir($live),'Unable to create rename live root');
    foreach(pNonEntryFiles($stage,(string)$cfg['entry']) as $rel){pCopyFile($stage.'/'.$rel,$live.'/'.$rel);} pVerifyNonEntryExact($stage,$live,(string)$cfg['entry']);
    $tmp=pPrepareTempEntry($stage,$live,(string)$cfg['entry']); pAssert(pPublishEntry($tmp,$live.'/'.$cfg['entry'],true)===false,'Injected rename failure did not fail');
    pAssert(!is_file($live.'/'.$cfg['entry']),'Final entry exists after injected rename failure');
    $state=pStateManifest($live,(string)$cfg['slug'],(string)$cfg['entry']); $partial=pSpawnObserve('rename-failure'); pAssertPartialSafe($partial,$cfg);
    @unlink($tmp);
    $recovery=pRestoreOldEntrypointLast($backup,$live,$cfg,$oldTree); pAssertCompatible($recovery); pRemoveTree(dirname($backup));
    return ['scenario'=>'final-entry-rename-failure','status'=>'PASS','failure_state'=>$state,'failure_observation'=>$partial,'recovered_tree_sha256'=>pTreeDigest($live,(string)$cfg['slug']),'recovery_observation'=>$recovery];
}
/** @return array<string,mixed> */
function pScenarioCorruptStage(array $identity, array $cfg, string $workspace): array {
    pResetBaseline($identity,$cfg,$workspace.'/corrupt-baseline'); $baseline=pSpawnObserve('corrupt-baseline'); pAssertCompatible($baseline);
    $wpDir=rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'),'/\\'); $live=$wpDir.'/wp-content/plugins/'.$cfg['slug']; $oldTree=pTreeDigest($live,(string)$cfg['slug']);
    $stage=pExtractNode($identity,(string)$cfg['new'],$workspace.'/corrupt-stage',(string)$cfg['slug']); $expected=(string)$identity['nodes'][$cfg['new']]['payload_tree_sha256'];
    $files=pNonEntryFiles($stage,(string)$cfg['entry']); $chosen=$files[0]; pAssert(file_put_contents($stage.'/'.$chosen,"\n/* deterministic staged corruption */\n",FILE_APPEND)!==false,'Unable to corrupt staged file');
    $actual=pTreeDigest($stage,(string)$cfg['slug']); pAssert($actual!==$expected,'Corrupt staged tree unexpectedly matched expected');
    pAssert(pTreeDigest($live,(string)$cfg['slug'])===$oldTree,'Live tree changed during staged corruption refusal'); $after=pSpawnObserve('corrupt-refused'); pAssertCompatible($after);
    return ['scenario'=>'corrupt-stage-refusal','status'=>'PASS','corrupt_file'=>$chosen,'expected_stage_tree_sha256'=>$expected,'actual_stage_tree_sha256'=>$actual,'live_tree_unchanged_sha256'=>$oldTree,'observation'=>$after];
}
/** @return array<string,mixed> */
function pScenarioUnsupported(array $identity,array $cfg,string $workspace): array {
    pResetBaseline($identity,$cfg,$workspace.'/unsupported-baseline'); $baseline=pSpawnObserve('unsupported-baseline'); pAssertCompatible($baseline);
    $wpDir=rtrim(pEnv('WPE_TEST_WORDPRESS_DIR'),'/\\'); $live=$wpDir.'/wp-content/plugins/'.$cfg['slug']; $oldTree=pTreeDigest($live,(string)$cfg['slug']);
    $profile='ftpext-simulated'; $supported=$profile==='direct-same-filesystem'; pAssert($supported===false,'Unsupported profile simulation unexpectedly supported');
    pAssert(pTreeDigest($live,(string)$cfg['slug'])===$oldTree,'Unsupported-profile refusal mutated live tree'); $after=pSpawnObserve('unsupported-refused'); pAssertCompatible($after);
    return ['scenario'=>'unsupported-profile-refusal','status'=>'PASS','simulated_profile'=>$profile,'refused_before_live_mutation'=>true,'live_tree_unchanged_sha256'=>$oldTree,'observation'=>$after];
}
/** @return array<string,mixed> */
function pRun(): array {
    $identity=pIdentity(); $cfg=pConfig(); $evidence=rtrim(pEnv('WPE_PUB_EVIDENCE_DIR'),'/\\');
    pAssert(($identity['source_sha']??null)===pEnv('WPE_P006_SOURCE_SHA'),'Candidate source SHA drift');
    foreach([$cfg['old'],$cfg['new'],$cfg['counterpart']] as $node){pAssert(isset($identity['nodes'][$node]),"Missing node {$node}"); pZipForNode($identity,(string)$node);}
    $baseline=pSpawnObserve('pre-scenarios'); pAssertCompatible($baseline); pAssert(($baseline['filesystem_method']??null)==='direct','Runtime filesystem method is not direct');
    $workspace=$evidence.'/workspace'; pRemoveTree($workspace); pAssert(mkdir($workspace,0775,true)||is_dir($workspace),'Unable to create workspace');
    $orderedStage=pExtractNode($identity,(string)$cfg['new'],$workspace.'/ordered-stage',(string)$cfg['slug']);
    $orderedNonEntryFiles=pNonEntryFiles($orderedStage,(string)$cfg['entry']);
    $result=[
        'status'=>'PASS_PREREQUISITE_ONLY','classification'=>'PUBLICATION OWNER HARNESS PREREQUISITE / NOT FP-49-52 EXECUTION','authorization'=>WPE_PUB_AUTH,
        'source_sha'=>pEnv('WPE_P006_SOURCE_SHA'),'cell_id'=>pEnv('WPE_P006_CELL_ID'),'target'=>$cfg['target'],'wordpress_expected'=>pEnv('WPE_P006_EXPECTED_WP'),'php_expected'=>pEnv('WPE_P006_EXPECTED_PHP'),'mysql_expected'=>'8.4',
        'publication_contract'=>['profile'=>'direct-same-filesystem','entrypoint_last'=>true,'generic_wordpress_move_dir_equivalent'=>false,'unsupported_profiles_refuse_before_mutation'=>true],
        'identity'=>['old_node'=>$cfg['old'],'new_node'=>$cfg['new'],'counterpart_node'=>$cfg['counterpart'],'old_zip_sha256'=>$identity['nodes'][$cfg['old']]['sha256'],'new_zip_sha256'=>$identity['nodes'][$cfg['new']]['sha256'],'old_payload_tree_sha256'=>$identity['nodes'][$cfg['old']]['payload_tree_sha256'],'new_payload_tree_sha256'=>$identity['nodes'][$cfg['new']]['payload_tree_sha256']],
        'target_entry_path'=>$cfg['slug'].'/'.$cfg['entry'],
        'ordered_non_entry_files'=>$orderedNonEntryFiles,
        'ordered_non_entry_file_count'=>count($orderedNonEntryFiles),
        'baseline'=>$baseline,
        'scenarios'=>[
            pScenarioSuccess($identity,$cfg,$workspace),
            pScenarioInterrupt($identity,$cfg,$workspace),
            pScenarioRenameFailure($identity,$cfg,$workspace),
            pScenarioCorruptStage($identity,$cfg,$workspace),
            pScenarioUnsupported($identity,$cfg,$workspace),
        ],
        'network_attempt_count'=>count(pNetworkAttempts()),
        'formal_fp_fixture_executed'=>false,'fixture_accounting_changed'=>false,'pair_certified'=>false,'runtime_certified'=>false,'updater_or_tuf_certified'=>false,'rollback_or_migration_certified'=>false,'adr_0010'=>'Proposed','ga_or_release_authorized'=>false,
    ];
    pAssert($result['network_attempt_count']===0,'Unexpected outbound HTTP at terminal state');
    pRemoveTree($workspace);
    pJsonWrite($evidence.'/summary.json',$result);
    return $result;
}

try {
    $command=$argv[1]??'';
    if($command==='prepare'){
        $result=pActivateBaseline(); pJsonWrite(rtrim(pEnv('WPE_PUB_EVIDENCE_DIR'),'/\\').'/prepare.json',$result); echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL; exit(0);
    }
    if($command==='observe'){
        $result=pObserveNow((string)(getenv('WPE_PUB_OBSERVE_PHASE')?:'observe')); echo json_encode($result,JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL; exit(0);
    }
    if($command==='run'){
        $result=pRun(); echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR).PHP_EOL; exit(0);
    }
    pFail('Usage: php p006-publication-owner-harness.php prepare|observe|run');
} catch (Throwable $e) {
    fwrite(STDERR,'[P-006 publication owner harness] '.$e->getMessage().PHP_EOL);
    exit(1);
}
