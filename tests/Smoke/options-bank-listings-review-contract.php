<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function listingsReviewContractJson(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException("Unable to read {$path}.");
    }

    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException("Invalid JSON object: {$path}.");
    }

    return $data;
}

listingsReviewContractJson($root . '/config/product/options-bank-native-audit.schema.json');
listingsReviewContractJson($root . '/config/product/options-bank-market-audit.schema.json');
listingsReviewContractJson($root . '/config/product/options-bank-review.schema.json');

$registry = listingsReviewContractJson($root . '/config/product/competitor-parity-surfaces.json');
$canonical = [];
foreach (($registry['surfaces'] ?? []) as $row) {
    if (is_array($row) && is_int($row['id'] ?? null) && is_string($row['key'] ?? null)) {
        $canonical[$row['key']] = $row['id'];
    }
}
if (($canonical['listings'] ?? null) !== 9) {
    throw new RuntimeException('Canonical Surface 9 must remain listings.');
}

$expectedShardCounts = [
    'listings.json' => 28,
    'listings--templates-rendering.json' => 30,
    'listings--layout-interactions.json' => 32,
    'listings--security-performance.json' => 30,
    'listings--portability-diagnostics.json' => 30,
];

$records = [];
$seenShards = [];
foreach (glob($root . '/config/product/options-bank/listings*.json') ?: [] as $file) {
    $name = basename($file);
    if (!isset($expectedShardCounts[$name])) {
        throw new RuntimeException("Unexpected Listings Bank shard: {$name}.");
    }

    $bank = listingsReviewContractJson($file);
    if (($bank['schema_version'] ?? null) !== 1
        || ($bank['bank_version'] ?? null) !== 'v1'
        || ($bank['surface']['id'] ?? null) !== 9
        || ($bank['surface']['key'] ?? null) !== 'listings'
        || ($bank['status'] ?? null) !== 'BANK_REVIEWED') {
        throw new RuntimeException("Invalid Listings Bank shard identity/status: {$name}.");
    }

    $shardRecords = $bank['records'] ?? null;
    if (!is_array($shardRecords) || count($shardRecords) !== $expectedShardCounts[$name]) {
        throw new RuntimeException("Listings shard {$name} record count drifted.");
    }
    if (($bank['coverage']['records'] ?? null) !== count($shardRecords)
        || ($bank['coverage']['unreviewed'] ?? null) !== 0
        || ($bank['coverage']['adopted_or_classified'] ?? null) !== count($shardRecords)) {
        throw new RuntimeException("Listings shard {$name} coverage drifted.");
    }

    foreach ($shardRecords as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null)) {
            throw new RuntimeException("Invalid Listings record in {$name}.");
        }
        $id = $record['id'];
        if (!str_starts_with($id, 'listings.') || isset($records[$id])) {
            throw new RuntimeException("Listings Bank record IDs must be surface-local and unique: {$id}.");
        }
        $records[$id] = $record;
    }

    $seenShards[$name] = true;
}

if (array_keys($seenShards) !== array_keys($expectedShardCounts)) {
    $seen = array_keys($seenShards);
    $expected = array_keys($expectedShardCounts);
    sort($seen);
    sort($expected);
    if ($seen !== $expected) {
        throw new RuntimeException('Listings Bank shard roster drifted.');
    }
}
if (count($records) !== 150) {
    throw new RuntimeException(sprintf('Listings review requires exactly 150 Bank records; found %d.', count($records)));
}

$unreviewed = 0;
$rejectedConsistent = true;
$deferredConsistent = true;
$exceedConsistent = true;
foreach ($records as $record) {
    $classification = $record['classification'] ?? null;
    $adoption = $record['adoption'] ?? null;
    $priority = $record['priority'] ?? null;
    $horizon = $record['horizon'] ?? null;

    if ($adoption === 'UNREVIEWED') {
        ++$unreviewed;
    }
    if ($classification === 'REJECTED_UNSAFE' && !($adoption === 'REJECT' && $priority === 'NOT_SCHEDULED')) {
        $rejectedConsistent = false;
    }
    if ($adoption === 'REJECT' && $classification !== 'REJECTED_UNSAFE') {
        $rejectedConsistent = false;
    }
    if ($classification === 'DEFERRED' && !($horizon === 'WPE_FUTURE' && $adoption === 'LATER' && $priority === 'P3_LATER')) {
        $deferredConsistent = false;
    }
    if ($classification === 'WPE_EXCEED' && !($horizon === 'WPE_FUTURE' && $adoption === 'WPE_EXCEED' && $priority === 'P1_EXCEED')) {
        $exceedConsistent = false;
    }
    if ($adoption === 'WPE_EXCEED' && !($horizon === 'WPE_FUTURE' && $priority === 'P1_EXCEED')) {
        $exceedConsistent = false;
    }
}

$native = listingsReviewContractJson($root . '/config/product/options-bank-audits/listings-native-wordpress.json');
if (($native['schema_version'] ?? null) !== 1
    || ($native['bank_version'] ?? null) !== 'v1'
    || ($native['surface']['id'] ?? null) !== 9
    || ($native['surface']['key'] ?? null) !== 'listings'
    || !str_contains((string) ($native['snapshot']['wordpress_target'] ?? ''), '7.1')
    || ($native['status'] ?? null) !== 'NATIVE_AUDITED') {
    throw new RuntimeException('Listings native certificate identity/status is invalid.');
}

$allowedNative = ['BANK_RECORD','PROVIDER_MAPPING','SYSTEM_RUNTIME','OUT_OF_SURFACE','LEGACY_COMPATIBILITY','CORE_INTERNAL','UNRESOLVED'];
$nativeCounts = array_fill_keys($allowedNative, 0);
$nativeSeen = [];
foreach (($native['items'] ?? []) as $item) {
    if (!is_array($item) || !is_string($item['id'] ?? null) || isset($nativeSeen[$item['id']])) {
        throw new RuntimeException('Invalid or duplicate Listings native audit item.');
    }
    $id = $item['id'];
    $nativeSeen[$id] = $item;

    $disposition = $item['disposition'] ?? null;
    if (!is_string($disposition) || !in_array($disposition, $allowedNative, true)) {
        throw new RuntimeException("Invalid Listings native disposition for {$id}.");
    }
    ++$nativeCounts[$disposition];

    $url = $item['evidence_url'] ?? null;
    if (!is_string($url) || !str_starts_with($url, 'https://')) {
        throw new RuntimeException("{$id} lacks primary HTTPS evidence.");
    }

    $bankIds = $item['bank_record_ids'] ?? [];
    if (!is_array($bankIds)) {
        throw new RuntimeException("{$id} has invalid Bank references.");
    }
    foreach ($bankIds as $recordId) {
        if (!is_string($recordId) || !isset($records[$recordId])) {
            throw new RuntimeException("{$id} references a missing Listings Bank record.");
        }
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $owner = $item['owner_surface'] ?? null;
        if (!is_string($owner) || !isset($canonical[$owner]) || $owner === 'listings') {
            throw new RuntimeException("{$id} must name another canonical owner.");
        }
    }
}

$nativeCoverage = $native['coverage'] ?? [];
$nativeExpected = [
    'items' => count($nativeSeen),
    'bank_record' => $nativeCounts['BANK_RECORD'],
    'provider_mapping' => $nativeCounts['PROVIDER_MAPPING'],
    'system_runtime' => $nativeCounts['SYSTEM_RUNTIME'],
    'out_of_surface' => $nativeCounts['OUT_OF_SURFACE'],
    'legacy_compatibility' => $nativeCounts['LEGACY_COMPATIBILITY'],
    'core_internal' => $nativeCounts['CORE_INTERNAL'],
    'unresolved' => $nativeCounts['UNRESOLVED'],
];
foreach ($nativeExpected as $key => $value) {
    if (($nativeCoverage[$key] ?? null) !== $value) {
        throw new RuntimeException("Listings native coverage {$key} drifted.");
    }
}
if (count($nativeSeen) !== 14
    || $nativeCounts['BANK_RECORD'] !== 8
    || $nativeCounts['SYSTEM_RUNTIME'] !== 3
    || $nativeCounts['OUT_OF_SURFACE'] !== 3
    || $nativeCounts['UNRESOLVED'] !== 0) {
    throw new RuntimeException('Listings native certified counts have drifted.');
}
foreach (['wp.wp-query', 'wp.wp-user-query', 'wp.wp-term-query'] as $queryBoundary) {
    if (($nativeSeen[$queryBoundary]['disposition'] ?? null) !== 'OUT_OF_SURFACE'
        || ($nativeSeen[$queryBoundary]['owner_surface'] ?? null) !== 'query') {
        throw new RuntimeException("{$queryBoundary} must remain owned by Query.");
    }
}
if (($nativeSeen['wp.interactivity-api']['disposition'] ?? null) !== 'SYSTEM_RUNTIME') {
    throw new RuntimeException('WordPress Interactivity API must remain a runtime mechanism, not authored Listings semantics.');
}

$market = listingsReviewContractJson($root . '/config/product/options-bank-audits/listings-market-ecosystem.json');
$families = ['listing_templates','source_binding','layout','pagination','filters_search','dynamic_values','responsive_states','portability_diagnostics'];
if (($market['schema_version'] ?? null) !== 1
    || ($market['bank_version'] ?? null) !== 'v1'
    || ($market['surface']['id'] ?? null) !== 9
    || ($market['surface']['key'] ?? null) !== 'listings'
    || ($market['status'] ?? null) !== 'MARKET_AUDITED'
    || ($market['required_families'] ?? null) !== $families) {
    throw new RuntimeException('Listings market certificate identity/families are invalid.');
}

$familySet = array_fill_keys($families, true);
$providerIds = [];
$familyMappings = 0;
$naCells = 0;
foreach (($market['primary_providers'] ?? []) as $provider) {
    if (!is_array($provider) || !is_string($provider['id'] ?? null) || isset($providerIds[$provider['id']])) {
        throw new RuntimeException('Invalid or duplicate Listings primary provider.');
    }
    $pid = $provider['id'];
    $providerIds[$pid] = true;

    foreach (($provider['evidence'] ?? []) as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException("Provider {$pid} has invalid evidence.");
        }
    }

    $mapped = [];
    foreach (($provider['family_map'] ?? []) as $family => $values) {
        if (!isset($familySet[$family]) || !is_array($values) || $values === []) {
            throw new RuntimeException("Provider {$pid} has invalid family mapping.");
        }
        $mapped[$family] = true;
        ++$familyMappings;
    }

    $na = [];
    foreach (($provider['non_applicable_families'] ?? []) as $family) {
        if (!is_string($family) || !isset($familySet[$family]) || isset($mapped[$family]) || isset($na[$family])) {
            throw new RuntimeException("Provider {$pid} has invalid non-applicable family.");
        }
        $na[$family] = true;
        ++$naCells;
    }
    if (count($mapped) + count($na) !== count($families)) {
        throw new RuntimeException("Provider {$pid} must disposition all Listings market families.");
    }
}

$providerRoster = array_keys($providerIds);
sort($providerRoster);
if ($providerRoster !== ['bricks_query_loop','elementor_loop_grid','jetengine','wp_grid_builder']) {
    throw new RuntimeException('Listings primary provider roster drifted.');
}

$specialists = $market['specialist_providers'] ?? null;
if (!is_array($specialists) || count($specialists) !== 2) {
    throw new RuntimeException('Listings specialist provider roster must contain exactly two providers.');
}
$specialistRoster = [];
$bankRefs = 0;
foreach ($specialists as $specialist) {
    if (!is_array($specialist) || !is_string($specialist['id'] ?? null)) {
        throw new RuntimeException('Invalid Listings specialist provider.');
    }
    $specialistRoster[] = $specialist['id'];
    foreach (($specialist['bank_record_ids'] ?? []) as $recordId) {
        if (!is_string($recordId) || !isset($records[$recordId])) {
            throw new RuntimeException('Listings specialist references missing Bank coverage.');
        }
        ++$bankRefs;
    }
}
sort($specialistRoster);
if ($specialistRoster !== ['facetwp','meta_box_views']) {
    throw new RuntimeException('Listings specialist provider roster drifted.');
}

$requiredExtras = [
    'market.jetengine.private-query-ui' => ['OUT_OF_SURFACE', 'query'],
    'market.wpgridbuilder.facet-index' => ['OUT_OF_SURFACE', 'search'],
    'market.metabox.executable-template-code' => ['REJECTED_UNSAFE', null],
    'market.metabox.php-location-rules' => ['REJECTED_UNSAFE', null],
];
$extraSeen = [];
foreach (($market['extra_dispositions'] ?? []) as $extra) {
    if (!is_array($extra) || !is_string($extra['id'] ?? null)) {
        throw new RuntimeException('Invalid Listings market disposition.');
    }
    $id = $extra['id'];
    if (isset($extraSeen[$id]) || !isset($requiredExtras[$id])) {
        throw new RuntimeException("Unexpected Listings market disposition {$id}.");
    }
    [$expectedDisposition, $expectedOwner] = $requiredExtras[$id];
    if (($extra['disposition'] ?? null) !== $expectedDisposition) {
        throw new RuntimeException("Listings market disposition {$id} drifted.");
    }
    if ($expectedOwner !== null && ($extra['owner_surface'] ?? null) !== $expectedOwner) {
        throw new RuntimeException("Listings market disposition {$id} owner drifted.");
    }
    $url = $extra['evidence_url'] ?? null;
    if (!is_string($url) || !str_starts_with($url, 'https://')) {
        throw new RuntimeException("{$id} has invalid market evidence.");
    }
    $extraSeen[$id] = true;
}
if (count($extraSeen) !== 4) {
    throw new RuntimeException('Listings market review must close all four explicit semantic dispositions.');
}

$marketCoverage = $market['coverage'] ?? [];
$marketExpected = [
    'primary_providers' => 4,
    'specialist_providers' => 2,
    'family_mappings' => $familyMappings,
    'non_applicable_family_cells' => $naCells,
    'bank_record_references' => $bankRefs,
    'extra_dispositions' => 4,
    'unresolved' => 0,
];
foreach ($marketExpected as $key => $value) {
    if (($marketCoverage[$key] ?? null) !== $value) {
        throw new RuntimeException("Listings market coverage {$key} drifted.");
    }
}
if ($familyMappings !== 32 || $naCells !== 0 || $bankRefs !== 8) {
    throw new RuntimeException('Listings market certified counts have drifted.');
}

$review = listingsReviewContractJson($root . '/config/product/options-bank-reviews/listings-bank-review-v1.json');
if (($review['schema_version'] ?? null) !== 1
    || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 9
    || ($review['surface']['key'] ?? null) !== 'listings'
    || ($review['decision'] ?? null) !== 'BANK_REVIEWED'
    || ($review['record_count'] ?? null) !== 150
    || ($review['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Listings Bank Review certificate is invalid.');
}

$semantic = listingsReviewContractJson($root . '/config/product/options-bank-semantic-relations.json');
$listingsSemantic = [];
foreach (($semantic['relationships'] ?? []) as $row) {
    if (is_array($row) && ($row['surface'] ?? null) === 'listings') {
        $listingsSemantic[] = $row;
    }
}
if ($listingsSemantic !== [] || ($review['semantic_expectations'] ?? null) !== ['relationships' => 0, 'aliases' => 0, 'effective_derivations' => 0]) {
    throw new RuntimeException('Listings semantic review must close at zero duplicate/derived authored controls.');
}

$gates = $review['policy_gates'] ?? [];
$expectedGates = [
    'unreviewed_records' => $unreviewed,
    'native_unresolved' => 0,
    'market_unresolved' => 0,
    'semantic_sources_are_noncanonical' => true,
    'rejected_unsafe_consistent' => $rejectedConsistent,
    'deferred_consistent' => $deferredConsistent,
    'wpe_exceed_consistent' => $exceedConsistent,
    'wpe_exceed_shard_future_only' => true,
    'record_delta_after_market_audit' => 0,
];
foreach ($expectedGates as $key => $value) {
    if (($gates[$key] ?? null) !== $value) {
        throw new RuntimeException("Listings review gate {$key} drifted.");
    }
}
if ($unreviewed !== 0 || !$rejectedConsistent || !$deferredConsistent || !$exceedConsistent) {
    throw new RuntimeException('Listings Bank policy classification is inconsistent.');
}

$planning = file_get_contents($root . '/docs/IMPLEMENTATION/DYNAMIC-LISTINGS-PLANNING-CONTRACT.md');
$ux = file_get_contents($root . '/docs/UI/DYNAMIC-LISTINGS-UX-CONTRACT.md');
$research = file_get_contents($root . '/docs/RESEARCH/LISTINGS-NATIVE-MARKET-AUDIT-2026-09.md');
if (!is_string($planning) || !str_contains($planning, 'runtime') || !str_contains($planning, 'BLOCKED BY DEPENDENCY GATES')) {
    throw new RuntimeException('Listings planning contract must preserve the runtime dependency block.');
}
if (!is_string($ux) || !str_contains($ux, 'Visibility is presentation, not authorization.')) {
    throw new RuntimeException('Listings UX contract must preserve the no-authorization-by-visibility rule.');
}
if (!is_string($research) || !str_contains($research, '150 unique semantic records')) {
    throw new RuntimeException('Listings research must preserve the reviewed 150-record result.');
}

echo "Listings Options Bank review contract PASS\n";
