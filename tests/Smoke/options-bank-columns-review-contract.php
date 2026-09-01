<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string, mixed> */
function readColumnsReviewJson(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        throw new RuntimeException(sprintf('%s must contain a JSON object.', $path));
    }
    return $data;
}

readColumnsReviewJson($root . '/config/product/options-bank-review.schema.json');
$review = readColumnsReviewJson($root . '/config/product/options-bank-reviews/columns-bank-review-v1.json');
if (($review['schema_version'] ?? null) !== 1
    || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 8
    || ($review['surface']['key'] ?? null) !== 'columns'
    || ($review['record_count'] ?? null) !== 214) {
    throw new RuntimeException('Admin Columns Bank Review certificate identity/count is invalid.');
}

$decision = $review['decision'] ?? null;
if (!in_array($decision, ['REVIEW_BLOCKED', 'BANK_REVIEWED'], true)) {
    throw new RuntimeException('Admin Columns Bank Review decision must be REVIEW_BLOCKED or BANK_REVIEWED.');
}

$records = [];
foreach (glob($root . '/config/product/options-bank/columns*.json') ?: [] as $file) {
    $bank = readColumnsReviewJson($file);
    if (($bank['surface']['id'] ?? null) !== 8 || ($bank['surface']['key'] ?? null) !== 'columns') {
        throw new RuntimeException(sprintf('Invalid Admin Columns Options Bank shard: %s.', $file));
    }
    foreach (($bank['records'] ?? []) as $record) {
        $id = $record['id'] ?? null;
        if (!is_string($id) || isset($records[$id])) {
            throw new RuntimeException('Admin Columns Bank record IDs must be valid and unique.');
        }
        $records[$id] = $record;
    }
}
if (count($records) !== 214) {
    throw new RuntimeException(sprintf('Admin Columns Bank Review requires exactly 214 local records; found %d.', count($records)));
}

$semantic = readColumnsReviewJson($root . '/config/product/options-bank-semantic-relations.json');
$columnsSemantic = [];
foreach (($semantic['relationships'] ?? []) as $row) {
    if (is_array($row) && ($row['surface'] ?? null) === 'columns') {
        $columnsSemantic[] = $row;
    }
}
$aliases = 0;
$effective = 0;
foreach ($columnsSemantic as $row) {
    if (($row['relation'] ?? null) === 'ALIAS') {
        ++$aliases;
    } elseif (($row['relation'] ?? null) === 'EFFECTIVE_DERIVATION') {
        ++$effective;
    }
}
$semanticExpectations = $review['semantic_expectations'] ?? [];
if (($semanticExpectations['relationships'] ?? null) !== count($columnsSemantic)
    || ($semanticExpectations['aliases'] ?? null) !== $aliases
    || ($semanticExpectations['effective_derivations'] ?? null) !== $effective
    || count($columnsSemantic) !== 0) {
    throw new RuntimeException('Admin Columns semantic registry must currently close with zero duplicate/derived relationships.');
}

$native = readColumnsReviewJson($root . '/config/product/options-bank-audits/columns-native-wordpress.json');
$market = readColumnsReviewJson($root . '/config/product/options-bank-audits/columns-market-ecosystem.json');
if (($native['coverage']['unresolved'] ?? null) !== 0 || ($market['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Admin Columns native/market prerequisites must remain zero-unresolved.');
}
if (!in_array($native['status'] ?? null, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException('Admin Columns native audit lifecycle state is invalid.');
}
if (!in_array($market['status'] ?? null, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException('Admin Columns market audit lifecycle state is invalid.');
}

$unreviewed = 0;
$rejectedConsistent = true;
$deferredConsistent = true;
$exceedConsistent = true;
$exceedShardFutureOnly = true;
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
    if ($classification === 'DEFERRED' && !($adoption === 'LATER' && $priority === 'P3_LATER' && $horizon === 'WPE_FUTURE')) {
        $deferredConsistent = false;
    }
    if ($classification === 'WPE_EXCEED' && !($adoption === 'WPE_EXCEED' && $priority === 'P1_EXCEED' && $horizon === 'WPE_FUTURE')) {
        $exceedConsistent = false;
    }
}

$exceedShard = readColumnsReviewJson($root . '/config/product/options-bank/columns--wpe-exceed-market-v1.json');
foreach (($exceedShard['records'] ?? []) as $record) {
    if (($record['classification'] ?? null) !== 'WPE_EXCEED'
        || ($record['adoption'] ?? null) !== 'WPE_EXCEED'
        || ($record['priority'] ?? null) !== 'P1_EXCEED'
        || ($record['horizon'] ?? null) !== 'WPE_FUTURE') {
        $exceedShardFutureOnly = false;
        break;
    }
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
    'wpe_exceed_shard_future_only' => $exceedShardFutureOnly,
    'record_delta_after_market_audit' => 0,
];
foreach ($expectedGates as $key => $value) {
    if (($gates[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Admin Columns Bank Review policy gate %s disagrees with repository truth.', $key));
    }
}
if ($unreviewed !== 0 || !$rejectedConsistent || !$deferredConsistent || !$exceedConsistent || !$exceedShardFutureOnly) {
    throw new RuntimeException('Admin Columns Bank Review policy consistency is not closed.');
}

$required = $review['required_artifacts'] ?? [];
$expectedPaths = [
    'semantic_registry' => 'config/product/options-bank-semantic-relations.json',
    'native_audit' => 'config/product/options-bank-audits/columns-native-wordpress.json',
    'market_audit' => 'config/product/options-bank-audits/columns-market-ecosystem.json',
];
$seenArtifacts = [];
foreach ($required as $artifact) {
    if (!is_array($artifact) || !is_string($artifact['kind'] ?? null) || !is_string($artifact['path'] ?? null)) {
        throw new RuntimeException('Admin Columns Bank Review required artifact is malformed.');
    }
    $seenArtifacts[$artifact['kind']] = $artifact['path'];
}
if ($seenArtifacts !== $expectedPaths) {
    throw new RuntimeException('Admin Columns Bank Review prerequisites have drifted.');
}

$progress = readColumnsReviewJson($root . '/config/product/options-bank-progress.json');
$progressRow = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 8 && ($row['key'] ?? null) === 'columns') {
        $progressRow = $row;
        break;
    }
}
if (!is_array($progressRow)) {
    throw new RuntimeException('Canonical progress is missing Surface 8 / columns.');
}

$reviewUnresolved = $review['unresolved'] ?? null;
if (!is_int($reviewUnresolved) || $reviewUnresolved < 0) {
    throw new RuntimeException('Admin Columns Bank Review unresolved count is invalid.');
}

if ($decision === 'BANK_REVIEWED') {
    if (($native['status'] ?? null) !== 'NATIVE_AUDITED'
        || ($market['status'] ?? null) !== 'MARKET_AUDITED'
        || $reviewUnresolved !== 0
        || ($progressRow['status'] ?? null) !== 'BANK_REVIEWED'
        || ($progressRow['records'] ?? null) !== 214) {
        throw new RuntimeException('Admin Columns BANK_REVIEWED promotion lacks certified native/market/progress prerequisites.');
    }
} else {
    if ($reviewUnresolved === 0) {
        throw new RuntimeException('Admin Columns REVIEW_BLOCKED certificate must identify at least one unresolved integration gate.');
    }
    if (($native['status'] ?? null) === 'NATIVE_AUDITED'
        && ($market['status'] ?? null) === 'MARKET_AUDITED'
        && ($progressRow['status'] ?? null) === 'BANK_REVIEWED'
        && ($progressRow['records'] ?? null) === 214) {
        throw new RuntimeException('Admin Columns Review is blocked despite all canonical promotion prerequisites being complete.');
    }
}

printf(
    "Admin Columns Bank Review V1 contract: PASS (%s; 214 records, 0 semantic overlaps, native/market 0 unresolved).\n",
    $decision,
);
