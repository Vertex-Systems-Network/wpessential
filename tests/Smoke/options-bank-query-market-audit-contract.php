<?php
declare(strict_types=1);
if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
$root = dirname(__DIR__, 2);
function qmread(string $path): array {
    $raw = file_get_contents($path);
    if ($raw === false) { throw new RuntimeException("Unable to read $path"); }
    $v = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($v)) { throw new RuntimeException("Invalid JSON object: $path"); }
    return $v;
}
$ids = [];
foreach (glob($root . '/config/product/options-bank/query*.json') ?: [] as $file) {
    $bank = qmread($file);
    if (($bank['surface']['id'] ?? null) !== 6 || ($bank['surface']['key'] ?? null) !== 'query') { continue; }
    foreach (($bank['records'] ?? []) as $record) { $ids[$record['id']] = true; }
}
$audit = qmread($root . '/config/product/options-bank-audits/query-market-ecosystem.json');
if (($audit['status'] ?? null) !== 'MARKET_AUDITED' || ($audit['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Query market audit is not complete.');
}
$refs = 0;
foreach (($audit['primary_providers'] ?? []) as $provider) {
    foreach (($audit['required_families'] ?? []) as $family) {
        $mapped = $provider['family_map'][$family] ?? null;
        $na = in_array($family, $provider['non_applicable_families'] ?? [], true);
        if ((!is_array($mapped) || $mapped === []) && !$na) {
            throw new RuntimeException("Provider {$provider['id']} does not disposition family $family.");
        }
    }
    foreach (($provider['family_map'] ?? []) as $mapped) {
        foreach ($mapped as $id) { ++$refs; if (!isset($ids[$id])) { throw new RuntimeException("Missing Query Bank record $id."); } }
    }
}
foreach (($audit['specialist_providers'] ?? []) as $provider) {
    foreach (($provider['bank_record_ids'] ?? []) as $id) { ++$refs; if (!isset($ids[$id])) { throw new RuntimeException("Missing Query Bank record $id."); } }
}
if (($audit['coverage']['bank_record_references'] ?? null) !== $refs) { throw new RuntimeException('Query market Bank reference count mismatch.'); }
fwrite(STDOUT, sprintf("Query market audit contract: PASS (%d primary, %d specialist, %d Bank references).\n", count($audit['primary_providers']), count($audit['specialist_providers']), $refs));
