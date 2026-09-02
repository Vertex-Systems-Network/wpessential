<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
$root = dirname(__DIR__, 2);
function qread(string $path): array {
    $raw = file_get_contents($path);
    if ($raw === false) { throw new RuntimeException("Unable to read $path"); }
    $v = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($v)) { throw new RuntimeException("Invalid JSON object: $path"); }
    return $v;
}
$ids = [];
foreach (glob($root . '/config/product/options-bank/query*.json') ?: [] as $file) {
    $bank = qread($file);
    if (($bank['surface']['id'] ?? null) !== 6 || ($bank['surface']['key'] ?? null) !== 'query') { continue; }
    foreach (($bank['records'] ?? []) as $record) { $ids[$record['id']] = true; }
}
$audit = qread($root . '/config/product/options-bank-audits/query-native-wordpress.json');
if (($audit['status'] ?? null) !== 'NATIVE_AUDITED') { throw new RuntimeException('Query native audit is not NATIVE_AUDITED.'); }
if (($audit['coverage']['items'] ?? null) !== count($audit['items'] ?? [])) { throw new RuntimeException('Query native item coverage mismatch.'); }
if (($audit['coverage']['unresolved'] ?? null) !== 0) { throw new RuntimeException('Query native audit has unresolved items.'); }
foreach (($audit['items'] ?? []) as $item) {
    if (($item['disposition'] ?? null) === 'UNRESOLVED') { throw new RuntimeException('Query native audit contains UNRESOLVED disposition.'); }
    foreach (($item['bank_record_ids'] ?? []) as $id) {
        if (!isset($ids[$id])) { throw new RuntimeException("Query native audit references missing Bank record $id."); }
    }
}
fwrite(STDOUT, sprintf("Query native audit contract: PASS (%d items, %d Bank records).\n", count($audit['items']), count($ids)));
