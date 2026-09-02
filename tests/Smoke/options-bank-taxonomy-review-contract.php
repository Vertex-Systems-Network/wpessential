<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function taxonomyReviewContractJson(string $path): array
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

taxonomyReviewContractJson($root . '/config/product/options-bank-native-audit.schema.json');
taxonomyReviewContractJson($root . '/config/product/options-bank-market-audit.schema.json');
taxonomyReviewContractJson($root . '/config/product/options-bank-review.schema.json');

$registry = taxonomyReviewContractJson($root . '/config/product/competitor-parity-surfaces.json');
$canonical = [];
foreach (($registry['surfaces'] ?? []) as $row) {
    if (is_array($row) && is_int($row['id'] ?? null) && is_string($row['key'] ?? null)) {
        $canonical[$row['key']] = $row['id'];
    }
}
if (($canonical['taxonomy'] ?? null) !== 2) {
    throw new RuntimeException('Canonical Surface 2 must remain taxonomy.');
}

$records = [];
foreach (glob($root . '/config/product/options-bank/taxonomy*.json') ?: [] as $file) {
    $bank = taxonomyReviewContractJson($file);
    if (($bank['surface']['key'] ?? null) !== 'taxonomy') {
        throw new RuntimeException("Invalid Taxonomy Bank shard: {$file}.");
    }
    foreach (($bank['records'] ?? []) as $record) {
        $id = $record['id'] ?? null;
        if (!is_string($id) || isset($records[$id])) {
            throw new RuntimeException('Taxonomy Bank record IDs must be valid and unique.');
        }
        $records[$id] = $record;
    }
}
if (count($records) !== 71) {
    throw new RuntimeException(sprintf('Taxonomy review requires the unchanged 71-record Bank; found %d.', count($records)));
}

$native = taxonomyReviewContractJson($root . '/config/product/options-bank-audits/taxonomy-native-wordpress.json');
if (($native['schema_version'] ?? null) !== 1
    || ($native['bank_version'] ?? null) !== 'v1'
    || ($native['surface']['id'] ?? null) !== 2
    || ($native['surface']['key'] ?? null) !== 'taxonomy'
    || ($native['snapshot']['wordpress_target'] ?? null) !== '7.1'
    || ($native['status'] ?? null) !== 'NATIVE_AUDITED') {
    throw new RuntimeException('Taxonomy native certificate identity/status is invalid.');
}
foreach (($native['snapshot']['sources'] ?? []) as $source) {
    $url = is_array($source) ? ($source['url'] ?? null) : null;
    if (!is_string($url) || !str_starts_with($url, 'https://developer.wordpress.org/')) {
        throw new RuntimeException('Taxonomy native evidence must use Developer.WordPress.org.');
    }
}

$allowedNative = ['BANK_RECORD','PROVIDER_MAPPING','SYSTEM_RUNTIME','OUT_OF_SURFACE','LEGACY_COMPATIBILITY','CORE_INTERNAL','UNRESOLVED'];
$nativeCounts = array_fill_keys($allowedNative, 0);
$nativeSeen = [];
foreach (($native['items'] ?? []) as $item) {
    if (!is_array($item) || !is_string($item['id'] ?? null) || isset($nativeSeen[$item['id']])) {
        throw new RuntimeException('Invalid or duplicate Taxonomy native audit item.');
    }
    $id = $item['id'];
    if (!str_starts_with($id, 'taxonomy.native.')) {
        throw new RuntimeException("Native item {$id} is outside Taxonomy.");
    }
    $nativeSeen[$id] = true;
    $disposition = $item['disposition'] ?? null;
    if (!is_string($disposition) || !in_array($disposition, $allowedNative, true)) {
        throw new RuntimeException("Invalid native disposition for {$id}.");
    }
    ++$nativeCounts[$disposition];
    if (!is_string($item['evidence_url'] ?? null) || !str_starts_with($item['evidence_url'], 'https://developer.wordpress.org/')) {
        throw new RuntimeException("{$id} lacks primary WordPress evidence.");
    }
    $bankIds = $item['bank_record_ids'] ?? [];
    if (!is_array($bankIds)) {
        throw new RuntimeException("{$id} has invalid Bank references.");
    }
    foreach ($bankIds as $recordId) {
        if (!is_string($recordId) || !isset($records[$recordId])) {
            throw new RuntimeException("{$id} references missing Taxonomy Bank record.");
        }
    }
    if ($disposition === 'BANK_RECORD' && $bankIds === []) {
        throw new RuntimeException("{$id} must map to Bank coverage.");
    }
    if ($disposition === 'OUT_OF_SURFACE') {
        $owner = $item['owner_surface'] ?? null;
        if (!is_string($owner) || !isset($canonical[$owner]) || $owner === 'taxonomy') {
            throw new RuntimeException("{$id} must name another canonical owner.");
        }
    }
}

foreach ([
    'taxonomy.native.register.identity','taxonomy.native.register.labels','taxonomy.native.register.capabilities',
    'taxonomy.native.register.visibility','taxonomy.native.register.rest','taxonomy.native.register.metabox',
    'taxonomy.native.register.rewrite','taxonomy.native.register.count_callback','taxonomy.native.register.default_term',
    'taxonomy.native.register.sort','taxonomy.native.register.object_term_args','taxonomy.native.validation.key_constraints',
    'taxonomy.native.validation.reserved_terms','taxonomy.native.terms.delete','taxonomy.native.rest.permissions',
    'taxonomy.native.multisite.site_scoped_terms','taxonomy.native.rewrite.conflicts','taxonomy.native.boundary.term_meta',
    'taxonomy.native.boundary.relationships','taxonomy.native.boundary.query','taxonomy.native.boundary.roles',
    'taxonomy.native.boundary.admin_columns','taxonomy.native.boundary.persistent_order','taxonomy.native.boundary.search_index',
] as $required) {
    if (!isset($nativeSeen[$required])) {
        throw new RuntimeException("Taxonomy native audit is missing {$required}.");
    }
}
$nativeCoverage = $native['coverage'] ?? [];
$nativeExpected = [
    'items'=>count($nativeSeen),'bank_record'=>$nativeCounts['BANK_RECORD'],'provider_mapping'=>$nativeCounts['PROVIDER_MAPPING'],
    'system_runtime'=>$nativeCounts['SYSTEM_RUNTIME'],'out_of_surface'=>$nativeCounts['OUT_OF_SURFACE'],
    'legacy_compatibility'=>$nativeCounts['LEGACY_COMPATIBILITY'],'core_internal'=>$nativeCounts['CORE_INTERNAL'],
    'unresolved'=>$nativeCounts['UNRESOLVED'],
];
foreach ($nativeExpected as $key=>$value) {
    if (($nativeCoverage[$key] ?? null) !== $value) {
        throw new RuntimeException("Taxonomy native coverage {$key} drifted.");
    }
}
if (count($nativeSeen) !== 35 || $nativeCounts['BANK_RECORD'] !== 12 || $nativeCounts['SYSTEM_RUNTIME'] !== 16
    || $nativeCounts['OUT_OF_SURFACE'] !== 7 || $nativeCounts['UNRESOLVED'] !== 0) {
    throw new RuntimeException('Taxonomy native certified counts have drifted.');
}

$market = taxonomyReviewContractJson($root . '/config/product/options-bank-audits/taxonomy-market-ecosystem.json');
$families = ['definition_association','labels_admin','visibility_rewrite','rest_api','capabilities_security','term_behavior','portability_multisite','integration_boundaries'];
if (($market['schema_version'] ?? null) !== 1 || ($market['bank_version'] ?? null) !== 'v1'
    || ($market['surface']['id'] ?? null) !== 2 || ($market['surface']['key'] ?? null) !== 'taxonomy'
    || ($market['status'] ?? null) !== 'MARKET_AUDITED' || ($market['required_families'] ?? null) !== $families) {
    throw new RuntimeException('Taxonomy market certificate identity/families are invalid.');
}
$familySet = array_fill_keys($families, true);
$providerIds = [];
$familyMappings = 0;
$naCells = 0;
$bankRefs = 0;
foreach (($market['primary_providers'] ?? []) as $provider) {
    if (!is_array($provider) || !is_string($provider['id'] ?? null) || isset($providerIds[$provider['id']])) {
        throw new RuntimeException('Invalid or duplicate Taxonomy market provider.');
    }
    $pid = $provider['id'];
    $providerIds[$pid] = true;
    foreach (($provider['evidence'] ?? []) as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException("Provider {$pid} has invalid evidence.");
        }
    }
    $mapped = [];
    foreach (($provider['family_map'] ?? []) as $family=>$ids) {
        if (!isset($familySet[$family]) || !is_array($ids) || $ids === []) {
            throw new RuntimeException("Provider {$pid} has invalid family mapping.");
        }
        $mapped[$family] = true;
        ++$familyMappings;
        foreach ($ids as $recordId) {
            if (!is_string($recordId) || !isset($records[$recordId])) {
                throw new RuntimeException("Provider {$pid} references missing Bank record.");
            }
            ++$bankRefs;
        }
    }
    $na = [];
    foreach (($provider['non_applicable_families'] ?? []) as $family) {
        if (!is_string($family) || !isset($familySet[$family]) || isset($mapped[$family]) || isset($na[$family])) {
            throw new RuntimeException("Provider {$pid} has invalid non-applicable family.");
        }
        $na[$family] = true;
        ++$naCells;
    }
    if (count($mapped) + count($na) !== 8) {
        throw new RuntimeException("Provider {$pid} must disposition all eight families.");
    }
}
$providerRoster = array_keys($providerIds);
sort($providerRoster);
if ($providerRoster !== ['acf','cptui','jetengine','meta_box','pods','scf','toolset']) {
    throw new RuntimeException('Taxonomy market provider roster drifted.');
}
$specialists = $market['specialist_providers'] ?? [];
if (!is_array($specialists) || count($specialists) !== 1 || ($specialists[0]['id'] ?? null) !== 'taxopress') {
    throw new RuntimeException('Taxonomy specialist roster must contain TaxoPress exactly once.');
}
foreach (($specialists[0]['bank_record_ids'] ?? []) as $recordId) {
    if (!is_string($recordId) || !isset($records[$recordId])) {
        throw new RuntimeException('TaxoPress references missing Taxonomy Bank coverage.');
    }
    ++$bankRefs;
}

$requiredExtras = [
    'acf.definition_active_toggle'=>'IMPLEMENTATION_PATTERN','cptui.taxonomy_key_migration'=>'IMPLEMENTATION_PATTERN',
    'cptui.network_wide_definition'=>'IMPLEMENTATION_PATTERN','acf.taxonomy_field_engine'=>'OUT_OF_SURFACE',
    'meta_box.taxonomy_field_engine'=>'OUT_OF_SURFACE','jetengine.term_meta_fields'=>'OUT_OF_SURFACE',
    'taxopress.term_order_pro'=>'OUT_OF_SURFACE','taxopress.auto_terms'=>'OUT_OF_SURFACE',
    'taxopress.synonyms_search'=>'OUT_OF_SURFACE','toolset.archive_rendering'=>'OUT_OF_SURFACE',
];
$extraSeen = [];
$marketUnresolved = 0;
foreach (($market['extra_dispositions'] ?? []) as $extra) {
    if (!is_array($extra) || !is_string($extra['id'] ?? null)) {
        throw new RuntimeException('Invalid Taxonomy market disposition.');
    }
    $id = $extra['id'];
    if (isset($extraSeen[$id]) || ($requiredExtras[$id] ?? null) !== ($extra['disposition'] ?? null)) {
        throw new RuntimeException("Unexpected Taxonomy market disposition {$id}.");
    }
    $extraSeen[$id] = true;
    if (!is_string($extra['evidence_url'] ?? null) || !str_starts_with($extra['evidence_url'], 'https://')) {
        throw new RuntimeException("{$id} has invalid market evidence.");
    }
    if (($extra['disposition'] ?? null) === 'OUT_OF_SURFACE') {
        $owner = $extra['owner_surface'] ?? null;
        if (!is_string($owner) || !isset($canonical[$owner]) || $owner === 'taxonomy') {
            throw new RuntimeException("{$id} must name another canonical owner.");
        }
    }
    if (($extra['disposition'] ?? null) === 'UNRESOLVED') {
        ++$marketUnresolved;
    }
}
if (count($extraSeen) !== 10 || $marketUnresolved !== 0) {
    throw new RuntimeException('Taxonomy market audit must close all 10 semantic resolutions with zero unresolved.');
}
$marketCoverage = $market['coverage'] ?? [];
$marketExpected = [
    'primary_providers'=>7,'specialist_providers'=>1,'family_mappings'=>$familyMappings,
    'non_applicable_family_cells'=>$naCells,'bank_record_references'=>$bankRefs,
    'extra_dispositions'=>10,'unresolved'=>0,
];
foreach ($marketExpected as $key=>$value) {
    if (($marketCoverage[$key] ?? null) !== $value) {
        throw new RuntimeException("Taxonomy market coverage {$key} drifted.");
    }
}
if ($familyMappings !== 40 || $naCells !== 16 || $bankRefs !== 106) {
    throw new RuntimeException('Taxonomy market certified counts have drifted.');
}

$review = taxonomyReviewContractJson($root . '/config/product/options-bank-reviews/taxonomy-bank-review-v1.json');
if (($review['schema_version'] ?? null) !== 1 || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 2 || ($review['surface']['key'] ?? null) !== 'taxonomy'
    || ($review['decision'] ?? null) !== 'BANK_REVIEWED' || ($review['record_count'] ?? null) !== 71
    || ($review['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Taxonomy Bank Review certificate is invalid.');
}

$semantic = taxonomyReviewContractJson($root . '/config/product/options-bank-semantic-relations.json');
$taxonomySemantic = [];
foreach (($semantic['relationships'] ?? []) as $row) {
    if (is_array($row) && ($row['surface'] ?? null) === 'taxonomy') {
        $taxonomySemantic[] = $row;
    }
}
if ($taxonomySemantic !== [] || ($review['semantic_expectations'] ?? null) !== ['relationships'=>0,'aliases'=>0,'effective_derivations'=>0]) {
    throw new RuntimeException('Taxonomy semantic review must close at zero duplicate/derived authored controls.');
}

$unreviewed = 0;
$rejected = true;
$deferred = true;
$exceed = true;
foreach ($records as $record) {
    $classification = $record['classification'] ?? null;
    $adoption = $record['adoption'] ?? null;
    $priority = $record['priority'] ?? null;
    $horizon = $record['horizon'] ?? null;
    if ($adoption === 'UNREVIEWED') ++$unreviewed;
    if ($classification === 'REJECTED_UNSAFE' && !($adoption === 'REJECT' && $priority === 'NOT_SCHEDULED')) $rejected = false;
    if ($adoption === 'REJECT' && $classification !== 'REJECTED_UNSAFE') $rejected = false;
    if ($classification === 'DEFERRED' && !($horizon === 'WPE_FUTURE' && $adoption === 'LATER' && $priority === 'P3_LATER')) $deferred = false;
    if ($classification === 'WPE_EXCEED' && !($horizon === 'WPE_FUTURE' && $adoption === 'WPE_EXCEED' && $priority === 'P1_EXCEED')) $exceed = false;
    if ($adoption === 'WPE_EXCEED' && !($horizon === 'WPE_FUTURE' && $priority === 'P1_EXCEED')) $exceed = false;
}
$expectedGates = [
    'unreviewed_records'=>$unreviewed,'native_unresolved'=>0,'market_unresolved'=>0,
    'semantic_sources_are_noncanonical'=>true,'rejected_unsafe_consistent'=>$rejected,
    'deferred_consistent'=>$deferred,'wpe_exceed_consistent'=>$exceed,
    'wpe_exceed_shard_future_only'=>$exceed,'record_delta_after_market_audit'=>0,
];
if (($review['policy_gates'] ?? null) !== $expectedGates || $unreviewed !== 0 || !$rejected || !$deferred || !$exceed) {
    throw new RuntimeException('Taxonomy Bank Review policy gates are not closed.');
}

$expectedArtifacts = [
    'semantic_registry'=>'config/product/options-bank-semantic-relations.json',
    'native_audit'=>'config/product/options-bank-audits/taxonomy-native-wordpress.json',
    'market_audit'=>'config/product/options-bank-audits/taxonomy-market-ecosystem.json',
];
$seenArtifacts = [];
foreach (($review['required_artifacts'] ?? []) as $artifact) {
    if (!is_array($artifact) || !is_string($artifact['kind'] ?? null) || !is_string($artifact['path'] ?? null)) {
        throw new RuntimeException('Malformed Taxonomy review artifact.');
    }
    $kind = $artifact['kind'];
    if (($expectedArtifacts[$kind] ?? null) !== $artifact['path'] || isset($seenArtifacts[$kind]) || !is_file($root . '/' . $artifact['path'])) {
        throw new RuntimeException("Taxonomy review artifact {$kind} drifted or is missing.");
    }
    $seenArtifacts[$kind] = $artifact['path'];
}
if ($seenArtifacts !== $expectedArtifacts) {
    throw new RuntimeException('Taxonomy review prerequisites are incomplete.');
}

$handoff = file_get_contents($root . '/docs/RESEARCH/TAXONOMY-BANK-REVIEW-2026-09.md');
if ($handoff === false || !str_contains($handoff, '## Integration Requirements')
    || !str_contains($handoff, 'options-bank-progress.json') || !str_contains($handoff, 'record count at `71`')) {
    throw new RuntimeException('Taxonomy integration handoff is incomplete.');
}

printf(
    "Taxonomy Bank Review V1: PASS (71 records; native %d dispositions/0 unresolved; market 7+1 providers/%d resolutions/0 unresolved; 0 semantic overlaps).\n",
    count($nativeSeen), count($extraSeen),
);
