<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string, mixed> */
function readTablesReviewJson(string $path): array
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

readTablesReviewJson($root . '/config/product/options-bank-review.schema.json');
$review = readTablesReviewJson($root . '/config/product/options-bank-reviews/tables-bank-review-v1.json');
if (($review['schema_version'] ?? null) !== 1
    || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 7
    || ($review['surface']['key'] ?? null) !== 'tables'
    || ($review['record_count'] ?? null) !== 165) {
    throw new RuntimeException('Surface 7 Bank Review certificate identity/count is invalid.');
}

$records = [];
foreach (glob($root . '/config/product/options-bank/tables--*.json') ?: [] as $file) {
    $bank = readTablesReviewJson($file);
    foreach (($bank['records'] ?? []) as $record) {
        $id = $record['id'] ?? null;
        if (!is_string($id) || isset($records[$id])) {
            throw new RuntimeException('Surface 7 Bank record IDs must be valid and unique.');
        }
        $records[$id] = $record;
    }
}
if (count($records) !== 165) {
    throw new RuntimeException(sprintf('Surface 7 Bank Review requires exactly 165 records; found %d.', count($records)));
}

$semantic = readTablesReviewJson($root . '/config/product/options-bank-semantic-relations.json');
$tableSemantic = [];
foreach (($semantic['relationships'] ?? []) as $row) {
    if (is_array($row) && ($row['surface'] ?? null) === 'tables') {
        $tableSemantic[] = $row;
    }
}
$aliases = 0;
$effective = 0;
foreach ($tableSemantic as $row) {
    if (($row['relation'] ?? null) === 'ALIAS') {
        ++$aliases;
    } elseif (($row['relation'] ?? null) === 'EFFECTIVE_DERIVATION') {
        ++$effective;
    }
}
$semanticExpected = $review['semantic_expectations'] ?? [];
if (($semanticExpected['relationships'] ?? null) !== count($tableSemantic)
    || ($semanticExpected['aliases'] ?? null) !== $aliases
    || ($semanticExpected['effective_derivations'] ?? null) !== $effective
    || count($tableSemantic) !== 0) {
    throw new RuntimeException('Surface 7 semantic review must close with zero aliases/effective derivations in the shared registry.');
}

$native = readTablesReviewJson($root . '/config/product/options-bank-audits/tables-native-wordpress.json');
$market = readTablesReviewJson($root . '/config/product/options-bank-audits/tables-market-ecosystem.json');
if (($native['status'] ?? null) !== 'NATIVE_AUDITED'
    || ($market['status'] ?? null) !== 'MARKET_AUDITED'
    || ($native['coverage']['unresolved'] ?? null) !== 0
    || ($market['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Surface 7 review requires zero-unresolved certified native and market audits.');
}

$unreviewed = 0;
$rejectedConsistent = true;
$deferredConsistent = true;
$exceedConsistent = true;
$exceedFutureOnly = true;
foreach ($records as $record) {
    if (($record['adoption'] ?? null) === 'UNREVIEWED') {
        ++$unreviewed;
    }
    if (($record['classification'] ?? null) === 'REJECTED_UNSAFE'
        && !(($record['adoption'] ?? null) === 'REJECT' && ($record['priority'] ?? null) === 'NOT_SCHEDULED')) {
        $rejectedConsistent = false;
    }
    if (($record['classification'] ?? null) === 'DEFERRED'
        && !(($record['adoption'] ?? null) === 'LATER' && ($record['priority'] ?? null) === 'P3_LATER' && ($record['horizon'] ?? null) === 'WPE_FUTURE')) {
        $deferredConsistent = false;
    }
    if (($record['classification'] ?? null) === 'WPE_EXCEED') {
        if (!(($record['adoption'] ?? null) === 'WPE_EXCEED' && ($record['priority'] ?? null) === 'P1_EXCEED' && ($record['horizon'] ?? null) === 'WPE_FUTURE')) {
            $exceedConsistent = false;
        }
        if (($record['horizon'] ?? null) !== 'WPE_FUTURE') {
            $exceedFutureOnly = false;
        }
    }
}

$gates = $review['policy_gates'] ?? [];
$expectedGates = [
    'unreviewed_records'=>$unreviewed,
    'native_unresolved'=>0,
    'market_unresolved'=>0,
    'semantic_sources_are_noncanonical'=>true,
    'rejected_unsafe_consistent'=>$rejectedConsistent,
    'deferred_consistent'=>$deferredConsistent,
    'wpe_exceed_consistent'=>$exceedConsistent,
    'wpe_exceed_shard_future_only'=>$exceedFutureOnly,
    'record_delta_after_market_audit'=>0,
];
foreach ($expectedGates as $key => $value) {
    if (($gates[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Surface 7 Bank Review policy gate %s disagrees with repository truth.', $key));
    }
}

$expectedPaths = [
    'semantic_registry'=>'config/product/options-bank-semantic-relations.json',
    'native_audit'=>'config/product/options-bank-audits/tables-native-wordpress.json',
    'market_audit'=>'config/product/options-bank-audits/tables-market-ecosystem.json',
];
$seen = [];
foreach (($review['required_artifacts'] ?? []) as $artifact) {
    if (!is_array($artifact) || !is_string($artifact['kind'] ?? null) || !is_string($artifact['path'] ?? null)) {
        throw new RuntimeException('Surface 7 Bank Review required artifact is malformed.');
    }
    $seen[$artifact['kind']] = $artifact['path'];
}
if ($seen !== $expectedPaths) {
    throw new RuntimeException('Surface 7 Bank Review prerequisite paths have drifted.');
}

$progress = readTablesReviewJson($root . '/config/product/options-bank-progress.json');
$progressRow = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 7 && ($row['key'] ?? null) === 'tables') {
        $progressRow = $row;
        break;
    }
}
if (!is_array($progressRow)) {
    throw new RuntimeException('Canonical progress is missing Surface 7 / tables.');
}

$decision = $review['decision'] ?? null;
$unresolved = $review['unresolved'] ?? null;
if (!in_array($decision, ['REVIEW_BLOCKED','BANK_REVIEWED'], true) || !is_int($unresolved) || $unresolved < 0) {
    throw new RuntimeException('Surface 7 review decision/unresolved state is invalid.');
}
if ($decision === 'BANK_REVIEWED') {
    if ($unresolved !== 0 || ($progressRow['status'] ?? null) !== 'BANK_REVIEWED' || ($progressRow['records'] ?? null) !== 165) {
        throw new RuntimeException('Surface 7 BANK_REVIEWED promotion lacks canonical progress prerequisites.');
    }
} else {
    if ($unresolved < 1) {
        throw new RuntimeException('Surface 7 REVIEW_BLOCKED must identify at least one integration blocker.');
    }
    if (($progressRow['status'] ?? null) === 'BANK_REVIEWED' && ($progressRow['records'] ?? null) === 165) {
        throw new RuntimeException('Surface 7 review is stale-blocked despite completed canonical progress promotion.');
    }
}

printf("Surface 7 Bank Review V1 contract: PASS (%s; 165 records, native/market 0 unresolved; canonical progress=%s/%s).\n", $decision, (string) ($progressRow['status'] ?? 'missing'), (string) ($progressRow['records'] ?? 'missing'));
