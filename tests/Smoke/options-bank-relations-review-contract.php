<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string,mixed> */
function relationsReviewJson(string $path): array
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

relationsReviewJson($root . '/config/product/options-bank-review.schema.json');
$review = relationsReviewJson($root . '/config/product/options-bank-reviews/relations-bank-review-v1.json');
if (($review['schema_version'] ?? null) !== 1
    || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 4
    || ($review['surface']['key'] ?? null) !== 'relations'
    || ($review['decision'] ?? null) !== 'BANK_REVIEWED'
    || ($review['record_count'] ?? null) !== 144
    || ($review['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Relations Bank Review certificate identity/decision is invalid.');
}

$records = [];
foreach (glob($root . '/config/product/options-bank/relations*.json') ?: [] as $file) {
    $bank = relationsReviewJson($file);
    if (($bank['surface']['key'] ?? null) !== 'relations') {
        continue;
    }
    foreach (($bank['records'] ?? []) as $record) {
        $id = $record['id'] ?? null;
        if (!is_string($id) || isset($records[$id])) {
            throw new RuntimeException('Relations Bank record IDs must be valid and unique.');
        }
        $records[$id] = $record;
    }
}
if (count($records) !== 144) {
    throw new RuntimeException(sprintf('Relations Bank Review requires exactly 144 local records; found %d.', count($records)));
}

$semantic = relationsReviewJson($root . '/config/product/options-bank-semantic-relations.json');
$relationSemantic = [];
foreach (($semantic['relationships'] ?? []) as $row) {
    if (is_array($row) && ($row['surface'] ?? null) === 'relations') {
        $relationSemantic[] = $row;
    }
}
$aliases = 0;
$effective = 0;
foreach ($relationSemantic as $row) {
    if (($row['relation'] ?? null) === 'ALIAS') {
        ++$aliases;
    } elseif (($row['relation'] ?? null) === 'EFFECTIVE_DERIVATION') {
        ++$effective;
    }
}
$semanticExpectations = $review['semantic_expectations'] ?? [];
if (($semanticExpectations['relationships'] ?? null) !== count($relationSemantic)
    || ($semanticExpectations['aliases'] ?? null) !== $aliases
    || ($semanticExpectations['effective_derivations'] ?? null) !== $effective
    || count($relationSemantic) !== 0) {
    throw new RuntimeException('Relations semantic registry must close with zero duplicate/derived relationships.');
}

$native = relationsReviewJson($root . '/config/product/options-bank-audits/relations-native-wordpress.json');
$market = relationsReviewJson($root . '/config/product/options-bank-audits/relations-market-ecosystem.json');
if (($native['status'] ?? null) !== 'NATIVE_AUDITED' || ($native['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Relations native audit is not a zero-unresolved certified prerequisite.');
}
if (($market['status'] ?? null) !== 'MARKET_AUDITED' || ($market['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Relations market audit is not a zero-unresolved certified prerequisite.');
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
    if ($classification === 'DEFERRED' && !($adoption === 'LATER' && $priority === 'P3_LATER' && $horizon === 'WPE_FUTURE')) {
        $deferredConsistent = false;
    }
    if ($classification === 'WPE_EXCEED' && !($adoption === 'WPE_EXCEED' && $priority === 'P1_EXCEED' && $horizon === 'WPE_FUTURE')) {
        $exceedConsistent = false;
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
    'wpe_exceed_shard_future_only' => $exceedConsistent,
    'record_delta_after_market_audit' => 0,
];
foreach ($expectedGates as $key => $value) {
    if (($gates[$key] ?? null) !== $value) {
        throw new RuntimeException("Relations Bank Review policy gate {$key} disagrees with repository truth.");
    }
}
if ($unreviewed !== 0 || !$rejectedConsistent || !$deferredConsistent || !$exceedConsistent) {
    throw new RuntimeException('Relations Bank Review policy consistency is not closed.');
}

$required = $review['required_artifacts'] ?? [];
$expectedPaths = [
    'semantic_registry' => 'config/product/options-bank-semantic-relations.json',
    'native_audit' => 'config/product/options-bank-audits/relations-native-wordpress.json',
    'market_audit' => 'config/product/options-bank-audits/relations-market-ecosystem.json',
];
$seenArtifacts = [];
foreach ($required as $artifact) {
    if (!is_array($artifact) || !is_string($artifact['kind'] ?? null) || !is_string($artifact['path'] ?? null)) {
        throw new RuntimeException('Relations Bank Review required artifact is malformed.');
    }
    $seenArtifacts[$artifact['kind']] = $artifact['path'];
}
if ($seenArtifacts !== $expectedPaths) {
    throw new RuntimeException('Relations Bank Review prerequisites have drifted.');
}

$progress = relationsReviewJson($root . '/config/product/options-bank-progress.json');
$progressRow = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 4 && ($row['key'] ?? null) === 'relations') {
        $progressRow = $row;
        break;
    }
}
if (!is_array($progressRow)
    || ($progressRow['status'] ?? null) !== 'BANK_REVIEWED'
    || ($progressRow['records'] ?? null) !== count($records)) {
    throw new RuntimeException('Relations Bank Review and canonical progress truth disagree.');
}

printf("Relations Bank Review V1: PASS (144 records, 0 semantic overlaps, native/market 0 unresolved, policy gates closed).\n");
