<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$reviewPath = $root . '/config/product/options-bank-reviews/fields-bank-review-v2.json';
$reviewSchemaPath = $root . '/config/product/options-bank-review.schema.json';
$semanticPath = $root . '/config/product/options-bank-semantic-relations.json';
$nativeAuditPath = $root . '/config/product/options-bank-audits/fields-native-wordpress.json';
$marketAuditPath = $root . '/config/product/options-bank-audits/fields-market-ecosystem.json';
$progressPath = $root . '/config/product/options-bank-progress.json';
$bankDirectory = $root . '/config/product/options-bank';
$wpeExceedPath = $bankDirectory . '/fields--wpe-exceed-market-v2.json';

/** @return array<string, mixed> */
function readBankReviewJson(string $path): array
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException(sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()), 0, $exception);
    }

    if (!is_array($decoded)) {
        throw new RuntimeException(sprintf('%s must contain a JSON object.', $path));
    }

    return $decoded;
}

/** @param mixed $value */
function requireBankReviewString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

// Keep the review schema dependency-free but parseable in every smoke environment.
readBankReviewJson($reviewSchemaPath);

$review = readBankReviewJson($reviewPath);
if (($review['schema_version'] ?? null) !== 1 || ($review['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Fields Bank Review V2 has an unsupported version.');
}

$surface = $review['surface'] ?? null;
if (!is_array($surface) || ($surface['id'] ?? null) !== 3 || ($surface['key'] ?? null) !== 'fields') {
    throw new RuntimeException('Fields Bank Review V2 must target canonical Surface 3 / fields.');
}
requireBankReviewString($review['snapshot_date'] ?? null, 'Fields Bank Review V2 is missing snapshot_date.');
if (($review['decision'] ?? null) !== 'BANK_REVIEWED') {
    throw new RuntimeException('Fields Bank Review V2 decision must be BANK_REVIEWED.');
}
if (($review['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('BANK_REVIEWED requires zero unresolved review items.');
}

$requiredArtifacts = $review['required_artifacts'] ?? null;
if (!is_array($requiredArtifacts) || count($requiredArtifacts) !== 3) {
    throw new RuntimeException('Fields Bank Review V2 must bind exactly semantic, native-audit and market-audit artifacts.');
}
$expectedArtifacts = [
    'semantic_registry' => 'config/product/options-bank-semantic-relations.json',
    'native_audit' => 'config/product/options-bank-audits/fields-native-wordpress.json',
    'market_audit' => 'config/product/options-bank-audits/fields-market-ecosystem.json',
];
$seenArtifacts = [];
foreach ($requiredArtifacts as $artifact) {
    if (!is_array($artifact)) {
        throw new RuntimeException('Fields Bank Review V2 contains an invalid required artifact.');
    }
    $kind = requireBankReviewString($artifact['kind'] ?? null, 'Review artifact has no kind.');
    $path = requireBankReviewString($artifact['path'] ?? null, sprintf('Review artifact %s has no path.', $kind));
    requireBankReviewString($artifact['expected_status'] ?? null, sprintf('Review artifact %s has no expected_status.', $kind));
    if (($expectedArtifacts[$kind] ?? null) !== $path || isset($seenArtifacts[$kind])) {
        throw new RuntimeException(sprintf('Review artifact %s does not match the canonical path.', $kind));
    }
    if (!is_file($root . '/' . $path)) {
        throw new RuntimeException(sprintf('Review artifact %s is missing from the repository.', $path));
    }
    $seenArtifacts[$kind] = true;
}
if (count($seenArtifacts) !== count($expectedArtifacts)) {
    throw new RuntimeException('Fields Bank Review V2 is missing a required upstream artifact.');
}

$semantic = readBankReviewJson($semanticPath);
$relationships = $semantic['relationships'] ?? null;
if (!is_array($relationships)) {
    throw new RuntimeException('Semantic registry is malformed.');
}
$aliases = 0;
$effectiveDerivations = 0;
$semanticSources = [];
foreach ($relationships as $relationship) {
    if (!is_array($relationship) || ($relationship['surface'] ?? null) !== 'fields') {
        continue;
    }
    $sourceId = requireBankReviewString($relationship['source_id'] ?? null, 'Fields semantic relationship has no source_id.');
    $targetId = requireBankReviewString($relationship['target_id'] ?? null, 'Fields semantic relationship has no target_id.');
    if ($sourceId === $targetId || isset($semanticSources[$sourceId])) {
        throw new RuntimeException(sprintf('Fields semantic source %s is invalid or duplicated.', $sourceId));
    }
    $semanticSources[$sourceId] = true;
    if (($relationship['relation'] ?? null) === 'ALIAS') {
        ++$aliases;
    } elseif (($relationship['relation'] ?? null) === 'EFFECTIVE_DERIVATION') {
        ++$effectiveDerivations;
    } else {
        throw new RuntimeException(sprintf('Fields semantic relationship %s has an unsupported relation.', $sourceId));
    }
}
$semanticExpectations = $review['semantic_expectations'] ?? null;
if (!is_array($semanticExpectations)
    || ($semanticExpectations['relationships'] ?? null) !== count($semanticSources)
    || ($semanticExpectations['aliases'] ?? null) !== $aliases
    || ($semanticExpectations['effective_derivations'] ?? null) !== $effectiveDerivations
    || count($semanticSources) !== 6
    || $aliases !== 3
    || $effectiveDerivations !== 3
) {
    throw new RuntimeException('Fields semantic review expectations do not match the canonicalized registry.');
}

$nativeAudit = readBankReviewJson($nativeAuditPath);
$nativeCoverage = $nativeAudit['coverage'] ?? null;
if (($nativeAudit['status'] ?? null) !== 'NATIVE_AUDITED'
    || !is_array($nativeCoverage)
    || ($nativeCoverage['unresolved'] ?? null) !== 0
) {
    throw new RuntimeException('Fields Bank Review V2 requires a zero-unresolved NATIVE_AUDITED certificate.');
}

$marketAudit = readBankReviewJson($marketAuditPath);
$marketCoverage = $marketAudit['coverage'] ?? null;
if (($marketAudit['status'] ?? null) !== 'MARKET_AUDITED'
    || !is_array($marketCoverage)
    || ($marketCoverage['unresolved'] ?? null) !== 0
) {
    throw new RuntimeException('Fields Bank Review V2 requires a zero-unresolved MARKET_AUDITED certificate.');
}

$fieldsRecords = 0;
$unreviewed = 0;
$rejectedUnsafe = 0;
$deferred = 0;
$wpeExceed = 0;
$bankFiles = glob($bankDirectory . '/fields*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Fields Bank shards.');
}
sort($bankFiles, SORT_STRING);
foreach ($bankFiles as $file) {
    $bank = readBankReviewJson($file);
    $bankSurface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($bankSurface) || ($bankSurface['key'] ?? null) !== 'fields' || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Fields Bank shard: %s', $file));
    }

    foreach ($records as $record) {
        if (!is_array($record)) {
            throw new RuntimeException(sprintf('Invalid Fields Bank record in %s.', $file));
        }
        $id = requireBankReviewString($record['id'] ?? null, sprintf('Fields Bank record in %s has no id.', $file));
        $classification = requireBankReviewString($record['classification'] ?? null, sprintf('%s has no classification.', $id));
        $horizon = requireBankReviewString($record['horizon'] ?? null, sprintf('%s has no horizon.', $id));
        $adoption = requireBankReviewString($record['adoption'] ?? null, sprintf('%s has no adoption.', $id));
        $priority = requireBankReviewString($record['priority'] ?? null, sprintf('%s has no priority.', $id));
        ++$fieldsRecords;

        if ($adoption === 'UNREVIEWED') {
            ++$unreviewed;
        }

        if ($classification === 'REJECTED_UNSAFE') {
            ++$rejectedUnsafe;
            if ($adoption !== 'REJECT' || $priority !== 'NOT_SCHEDULED') {
                throw new RuntimeException(sprintf('%s REJECTED_UNSAFE policy is inconsistent.', $id));
            }
        }
        if ($adoption === 'REJECT' && $classification !== 'REJECTED_UNSAFE') {
            throw new RuntimeException(sprintf('%s is rejected without REJECTED_UNSAFE classification.', $id));
        }

        if ($classification === 'DEFERRED') {
            ++$deferred;
            if ($horizon !== 'WPE_FUTURE' || $adoption !== 'LATER' || $priority !== 'P3_LATER') {
                throw new RuntimeException(sprintf('%s DEFERRED policy is inconsistent.', $id));
            }
        }
        if ($adoption === 'LATER' && $priority !== 'P3_LATER') {
            throw new RuntimeException(sprintf('%s LATER adoption must use P3_LATER.', $id));
        }

        if ($classification === 'WPE_EXCEED') {
            ++$wpeExceed;
            if ($horizon !== 'WPE_FUTURE' || $adoption !== 'WPE_EXCEED' || $priority !== 'P1_EXCEED') {
                throw new RuntimeException(sprintf('%s WPE_EXCEED policy is inconsistent.', $id));
            }
        }
        if ($adoption === 'WPE_EXCEED' && ($horizon !== 'WPE_FUTURE' || $priority !== 'P1_EXCEED')) {
            throw new RuntimeException(sprintf('%s WPE_EXCEED adoption must remain future/P1-exceed.', $id));
        }
    }
}

if ($fieldsRecords !== 618 || ($review['record_count'] ?? null) !== $fieldsRecords) {
    throw new RuntimeException(sprintf('Fields Bank Review V2 requires exactly 618 reviewed records; found %d.', $fieldsRecords));
}
if ($unreviewed !== 0) {
    throw new RuntimeException(sprintf('Fields Bank Review V2 cannot certify %d unreviewed record(s).', $unreviewed));
}
if ($rejectedUnsafe === 0 || $deferred === 0 || $wpeExceed === 0) {
    throw new RuntimeException('Fields Bank Review V2 expects explicit rejected, deferred and WPE-exceed classifications.');
}

$exceedBank = readBankReviewJson($wpeExceedPath);
$exceedRecords = $exceedBank['records'] ?? null;
if (!is_array($exceedRecords) || $exceedRecords === []) {
    throw new RuntimeException('Fields WPE exceed shard is missing or empty.');
}
foreach ($exceedRecords as $record) {
    if (!is_array($record)
        || ($record['classification'] ?? null) !== 'WPE_EXCEED'
        || ($record['horizon'] ?? null) !== 'WPE_FUTURE'
        || ($record['adoption'] ?? null) !== 'WPE_EXCEED'
        || ($record['priority'] ?? null) !== 'P1_EXCEED'
    ) {
        throw new RuntimeException('Fields WPE exceed shard contains a non-canonical exceed record.');
    }
}

$policyGates = $review['policy_gates'] ?? null;
if (!is_array($policyGates)
    || ($policyGates['unreviewed_records'] ?? null) !== 0
    || ($policyGates['native_unresolved'] ?? null) !== 0
    || ($policyGates['market_unresolved'] ?? null) !== 0
    || ($policyGates['semantic_sources_are_noncanonical'] ?? null) !== true
    || ($policyGates['rejected_unsafe_consistent'] ?? null) !== true
    || ($policyGates['deferred_consistent'] ?? null) !== true
    || ($policyGates['wpe_exceed_consistent'] ?? null) !== true
    || ($policyGates['wpe_exceed_shard_future_only'] ?? null) !== true
    || ($policyGates['record_delta_after_market_audit'] ?? null) !== 0
) {
    throw new RuntimeException('Fields Bank Review V2 policy gate declaration is inconsistent.');
}

$progress = readBankReviewJson($progressPath);
$rows = $progress['surface_status'] ?? null;
if (!is_array($rows)) {
    throw new RuntimeException('Options Bank progress is malformed.');
}
$fieldsProgress = null;
foreach ($rows as $row) {
    if (is_array($row) && ($row['key'] ?? null) === 'fields') {
        $fieldsProgress = $row;
        break;
    }
}
if (!is_array($fieldsProgress)
    || ($fieldsProgress['status'] ?? null) !== 'BANK_REVIEWED'
    || ($fieldsProgress['records'] ?? null) !== 618
) {
    throw new RuntimeException('Fields Bank Review V2 and canonical progress truth disagree.');
}

// Global Bank counts are intentionally not pinned here. They grow as later
// canonical surfaces are seeded/audited/reviewed. The dedicated progress
// contract derives and validates those global counters against all Bank shards.
$truth = $progress['truth'] ?? null;
if (!is_array($truth)) {
    throw new RuntimeException('Options Bank progress is missing global truth counters.');
}

fwrite(
    STDOUT,
    sprintf(
        "Fields Bank Review V2: PASS (%d records, %d semantic relations, %d rejected, %d deferred, %d WPE-exceed, 0 unresolved).\n",
        $fieldsRecords,
        count($semanticSources),
        $rejectedUnsafe,
        $deferred,
        $wpeExceed,
    ),
);
