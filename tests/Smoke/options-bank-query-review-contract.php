<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
$root = dirname(__DIR__, 2);
function qrread(string $path): array {
    $raw = file_get_contents($path);
    if ($raw === false) { throw new RuntimeException("Unable to read $path"); }
    $v = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($v)) { throw new RuntimeException("Invalid JSON object: $path"); }
    return $v;
}
$ids = []; $paths = []; $count = 0; $unreviewed = 0;
foreach (glob($root . '/config/product/options-bank/query*.json') ?: [] as $file) {
    $bank = qrread($file);
    if (($bank['surface']['id'] ?? null) !== 6 || ($bank['surface']['key'] ?? null) !== 'query') { continue; }
    foreach (($bank['records'] ?? []) as $record) {
        ++$count;
        $id = $record['id']; $path = $record['option_path'];
        if (isset($ids[$id])) { throw new RuntimeException("Duplicate Query Bank id $id."); }
        if (isset($paths[$path])) { throw new RuntimeException("Duplicate Query option_path $path."); }
        $ids[$id] = true; $paths[$path] = true;
        if (($record['adoption'] ?? null) === 'UNREVIEWED') { ++$unreviewed; }
    }
}
$native = qrread($root . '/config/product/options-bank-audits/query-native-wordpress.json');
$market = qrread($root . '/config/product/options-bank-audits/query-market-ecosystem.json');
$review = qrread($root . '/config/product/options-bank-reviews/query-bank-review-v1.json');
if ($count !== 166 || ($review['record_count'] ?? null) !== 166) { throw new RuntimeException("Query Bank must contain exactly 166 records; found $count."); }
if ($unreviewed !== 0 || ($review['policy_gates']['unreviewed_records'] ?? null) !== 0) { throw new RuntimeException('Query Bank has unreviewed records.'); }
if (($native['status'] ?? null) !== 'NATIVE_AUDITED' || ($native['coverage']['unresolved'] ?? null) !== 0) { throw new RuntimeException('Native prerequisite incomplete.'); }
if (($market['status'] ?? null) !== 'MARKET_AUDITED' || ($market['coverage']['unresolved'] ?? null) !== 0) { throw new RuntimeException('Market prerequisite incomplete.'); }
if (($review['decision'] ?? null) !== 'BANK_REVIEWED' || ($review['unresolved'] ?? null) !== 0) { throw new RuntimeException('Query Bank review is not complete.'); }
$wpe = qrread($root . '/config/product/options-bank/query--wpe-exceed-v1.json');
foreach (($wpe['records'] ?? []) as $record) {
    if (($record['classification'] ?? null) !== 'WPE_EXCEED' || ($record['horizon'] ?? null) !== 'WPE_FUTURE') {
        throw new RuntimeException('Query WPE-exceed shard contains non-future/non-WPE_EXCEED record.');
    }
}
foreach (['raw_sql_rejected','arbitrary_php_rejected','identifier_injection_rejected'] as $key) {
    $id = "query.safety.$key";
    if (($ids[$id] ?? false) !== true) { throw new RuntimeException("Required unsafe rejection record missing: $id"); }
}
fwrite(STDOUT, "Query Bank review contract: PASS (166 records, 0 duplicates, 0 unreviewed, BANK_REVIEWED candidate).\n");
