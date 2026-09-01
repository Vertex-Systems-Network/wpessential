<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$reviewPath = $root . '/config/product/options-bank-reviews/status-bank-review-v1.json';
$reviewSchemaPath = $root . '/config/product/options-bank-review.schema.json';
$semanticPath = $root . '/config/product/options-bank-semantic-relations.json';
$nativeAuditPath = $root . '/config/product/options-bank-audits/status-native-wordpress.json';
$marketAuditPath = $root . '/config/product/options-bank-audits/status-market-ecosystem.json';
$progressPath = $root . '/config/product/options-bank-progress.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readStatusReviewJson(string $path): array
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

$schema = readStatusReviewJson($reviewSchemaPath);
$surfaceSchema = $schema['properties']['surface']['properties'] ?? null;
if (!is_array($surfaceSchema)
    || ($surfaceSchema['id']['minimum'] ?? null) !== 1
    || ($surfaceSchema['id']['maximum'] ?? null) !== 56
    || array_key_exists('const', $surfaceSchema['id'])
    || ($surfaceSchema['key']['pattern'] ?? null) !== '^[a-z0-9][a-z0-9-]*$'
    || array_key_exists('const', $surfaceSchema['key'])) {
    throw new RuntimeException('Options Bank review schema must remain generic across canonical surfaces 1-56.');
}

$review = readStatusReviewJson($reviewPath);
if (($review['schema_version'] ?? null) !== 1
    || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 5
    || ($review['surface']['key'] ?? null) !== 'status'
    || ($review['record_count'] ?? null) !== 129) {
    throw new RuntimeException('Status Bank Review certificate identity/count is invalid.');
}

$decision = $review['decision'] ?? null;
if (!in_array($decision, ['REVIEW_BLOCKED', 'BANK_REVIEWED'], true)) {
    throw new RuntimeException('Status Bank Review decision must be REVIEW_BLOCKED or BANK_REVIEWED.');
}

/** @var array<string, array<string, mixed>> $records */
$records = [];
$optionPaths = [];
$bankFiles = glob($bankDirectory . '/status*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Status Options Bank shards.');
}
sort($bankFiles, SORT_STRING);
foreach ($bankFiles as $file) {
    $bank = readStatusReviewJson($file);
    if (($bank['surface']['id'] ?? null) !== 5
        || ($bank['surface']['key'] ?? null) !== 'status'
        || ($bank['status'] ?? null) !== 'BANK_SURFACE_SEEDED') {
        throw new RuntimeException(sprintf('Invalid Status Options Bank shard: %s.', $file));
    }

    $bankRecords = $bank['records'] ?? null;
    if (!is_array($bankRecords)) {
        throw new RuntimeException(sprintf('Status Options Bank shard %s has no records array.', $file));
    }

    foreach ($bankRecords as $record) {
        if (!is_array($record)) {
            throw new RuntimeException(sprintf('Invalid Status Bank record in %s.', $file));
        }

        $id = $record['id'] ?? null;
        $optionPath = $record['option_path'] ?? null;
        if (!is_string($id) || $id === '' || isset($records[$id])) {
            throw new RuntimeException('Status Bank record IDs must be valid and unique.');
        }
        if (!is_string($optionPath) || $optionPath === '' || isset($optionPaths[$optionPath])) {
            throw new RuntimeException('Status option paths must be valid and unique.');
        }

        $records[$id] = $record;
        $optionPaths[$optionPath] = true;
    }
}
if (count($records) !== 129 || count($optionPaths) !== 129) {
    throw new RuntimeException(sprintf(
        'Status Bank Review requires 129 unique records/paths; found %d records and %d paths.',
        count($records),
        count($optionPaths),
    ));
}

$semantic = readStatusReviewJson($semanticPath);
$relationships = $semantic['relationships'] ?? null;
if (!is_array($relationships)) {
    throw new RuntimeException('Semantic registry is malformed.');
}
$statusSemantic = [];
$aliases = 0;
$effectiveDerivations = 0;
foreach ($relationships as $row) {
    if (!is_array($row) || ($row['surface'] ?? null) !== 'status') {
        continue;
    }

    $statusSemantic[] = $row;
    if (($row['relation'] ?? null) === 'ALIAS') {
        ++$aliases;
    } elseif (($row['relation'] ?? null) === 'EFFECTIVE_DERIVATION') {
        ++$effectiveDerivations;
    } else {
        throw new RuntimeException('Status semantic registry contains an unsupported relationship type.');
    }
}

$semanticExpectations = $review['semantic_expectations'] ?? null;
if (!is_array($semanticExpectations)
    || ($semanticExpectations['relationships'] ?? null) !== count($statusSemantic)
    || ($semanticExpectations['aliases'] ?? null) !== $aliases
    || ($semanticExpectations['effective_derivations'] ?? null) !== $effectiveDerivations
    || count($statusSemantic) !== 0) {
    throw new RuntimeException('Status semantic registry must currently close with zero alias/effective-derivation entries.');
}

$native = readStatusReviewJson($nativeAuditPath);
$market = readStatusReviewJson($marketAuditPath);
if (($native['surface']['id'] ?? null) !== 5 || ($native['surface']['key'] ?? null) !== 'status') {
    throw new RuntimeException('Status native audit identity is invalid.');
}
if (($market['surface']['id'] ?? null) !== 5 || ($market['surface']['key'] ?? null) !== 'status') {
    throw new RuntimeException('Status market audit identity is invalid.');
}
if (($native['coverage']['unresolved'] ?? null) !== 0 || ($market['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Status native/market prerequisites must remain zero-unresolved.');
}
if (count($native['items'] ?? []) !== 35 || count($market['required_families'] ?? []) !== 9) {
    throw new RuntimeException('Status Bank Review requires the complete 35-item native and nine-family market evidence sets.');
}
if (!in_array($native['status'] ?? null, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException('Status native audit lifecycle state is invalid.');
}
if (!in_array($market['status'] ?? null, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException('Status market audit lifecycle state is invalid.');
}
if (($market['status'] ?? null) === 'MARKET_AUDITED' && ($native['status'] ?? null) !== 'NATIVE_AUDITED') {
    throw new RuntimeException('Status market certification must not outrun native certification.');
}

$unreviewed = 0;
$rejectedUnsafe = 0;
$deferred = 0;
$wpeExceed = 0;
$rejectedConsistent = true;
$deferredConsistent = true;
$exceedConsistent = true;
$exceedFutureOnly = true;

foreach ($records as $id => $record) {
    $classification = $record['classification'] ?? null;
    $adoption = $record['adoption'] ?? null;
    $priority = $record['priority'] ?? null;
    $horizon = $record['horizon'] ?? null;

    if (!is_string($classification) || !is_string($adoption) || !is_string($priority) || !is_string($horizon)) {
        throw new RuntimeException(sprintf('Status record %s is missing review-critical policy metadata.', $id));
    }

    if ($adoption === 'UNREVIEWED') {
        ++$unreviewed;
    }

    if ($classification === 'REJECTED_UNSAFE' || $adoption === 'REJECT') {
        ++$rejectedUnsafe;
        if ($classification !== 'REJECTED_UNSAFE' || $adoption !== 'REJECT' || $priority !== 'NOT_SCHEDULED') {
            $rejectedConsistent = false;
        }
    }

    if ($classification === 'DEFERRED' || $adoption === 'LATER') {
        ++$deferred;
        if ($classification !== 'DEFERRED'
            || $adoption !== 'LATER'
            || $priority !== 'P3_LATER'
            || $horizon !== 'WPE_FUTURE') {
            $deferredConsistent = false;
        }
    }

    if ($classification === 'WPE_EXCEED' || $adoption === 'WPE_EXCEED') {
        ++$wpeExceed;
        if ($adoption !== 'WPE_EXCEED' || $priority !== 'P1_EXCEED' || $horizon !== 'WPE_FUTURE') {
            $exceedConsistent = false;
            $exceedFutureOnly = false;
        }
        if ($classification === 'WPE_EXCEED' && $adoption !== 'WPE_EXCEED') {
            $exceedConsistent = false;
        }
    }
}

$requiredRejected = [
    'status.security.reject_builtin_override',
    'status.security.reject_direct_sql_state_write',
    'status.security.reject_workflow_private_write',
    'status.security.reject_arbitrary_transition_code',
];
foreach ($requiredRejected as $recordId) {
    $record = $records[$recordId] ?? null;
    if (!is_array($record)
        || ($record['classification'] ?? null) !== 'REJECTED_UNSAFE'
        || ($record['adoption'] ?? null) !== 'REJECT'
        || ($record['priority'] ?? null) !== 'NOT_SCHEDULED') {
        throw new RuntimeException(sprintf('Status safety record %s has drifted from its rejection contract.', $recordId));
    }
}
if ($rejectedUnsafe !== 4) {
    throw new RuntimeException(sprintf('Status Bank Review expects exactly four rejected-unsafe records; found %d.', $rejectedUnsafe));
}
if ($deferred !== 0) {
    throw new RuntimeException(sprintf('Status Bank Review currently expects zero deferred records; found %d.', $deferred));
}
if ($wpeExceed === 0) {
    throw new RuntimeException('Status Bank Review expects at least one future WPE_EXCEED record.');
}
if ($unreviewed !== 0 || !$rejectedConsistent || !$deferredConsistent || !$exceedConsistent || !$exceedFutureOnly) {
    throw new RuntimeException('Status Bank Review policy consistency is not closed.');
}

$gates = $review['policy_gates'] ?? null;
if (!is_array($gates)) {
    throw new RuntimeException('Status Bank Review policy_gates is malformed.');
}
$expectedGates = [
    'unreviewed_records' => $unreviewed,
    'native_unresolved' => 0,
    'market_unresolved' => 0,
    'semantic_sources_are_noncanonical' => true,
    'rejected_unsafe_consistent' => $rejectedConsistent,
    'deferred_consistent' => $deferredConsistent,
    'wpe_exceed_consistent' => $exceedConsistent,
    'wpe_exceed_shard_future_only' => $exceedFutureOnly,
    'record_delta_after_market_audit' => 0,
];
foreach ($expectedGates as $key => $value) {
    if (($gates[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Status Bank Review policy gate %s disagrees with repository truth.', $key));
    }
}

$required = $review['required_artifacts'] ?? null;
if (!is_array($required)) {
    throw new RuntimeException('Status Bank Review required_artifacts is malformed.');
}
$expectedPaths = [
    'semantic_registry' => 'config/product/options-bank-semantic-relations.json',
    'native_audit' => 'config/product/options-bank-audits/status-native-wordpress.json',
    'market_audit' => 'config/product/options-bank-audits/status-market-ecosystem.json',
];
$seenArtifacts = [];
foreach ($required as $artifact) {
    if (!is_array($artifact)
        || !is_string($artifact['kind'] ?? null)
        || !is_string($artifact['path'] ?? null)
        || !is_string($artifact['expected_status'] ?? null)
        || trim($artifact['expected_status']) === '') {
        throw new RuntimeException('Status Bank Review required artifact is malformed.');
    }
    $seenArtifacts[$artifact['kind']] = $artifact['path'];
}
if ($seenArtifacts !== $expectedPaths) {
    throw new RuntimeException('Status Bank Review prerequisites have drifted.');
}

$progress = readStatusReviewJson($progressPath);
$progressRow = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 5 && ($row['key'] ?? null) === 'status') {
        $progressRow = $row;
        break;
    }
}
if (!is_array($progressRow)) {
    throw new RuntimeException('Canonical progress is missing Surface 5 / status.');
}

$progressStatus = $progressRow['status'] ?? null;
$progressRecords = $progressRow['records'] ?? null;
$rank = [
    'UNSEEDED' => 0,
    'BANK_SURFACE_SEEDED' => 1,
    'NATIVE_AUDITED' => 2,
    'MARKET_AUDITED' => 3,
    'BANK_REVIEWED' => 4,
];
if (!is_string($progressStatus) || !isset($rank[$progressStatus]) || !is_int($progressRecords)) {
    throw new RuntimeException('Status canonical progress row is malformed.');
}
if ($progressStatus === 'UNSEEDED') {
    if ($progressRecords !== 0) {
        throw new RuntimeException('UNSEEDED Status progress must declare zero records.');
    }
} elseif ($progressRecords !== 129) {
    throw new RuntimeException('Seeded-or-later Status progress must declare exactly 129 records.');
}
if ($rank[$progressStatus] >= $rank['NATIVE_AUDITED'] && ($native['status'] ?? null) !== 'NATIVE_AUDITED') {
    throw new RuntimeException('Status canonical progress must not outrun native certification.');
}
if ($rank[$progressStatus] >= $rank['MARKET_AUDITED'] && ($market['status'] ?? null) !== 'MARKET_AUDITED') {
    throw new RuntimeException('Status canonical progress must not outrun market certification.');
}
if ($progressStatus === 'BANK_REVIEWED' && $decision !== 'BANK_REVIEWED') {
    throw new RuntimeException('Status canonical progress must not claim BANK_REVIEWED while the review certificate is blocked.');
}

$reviewUnresolved = $review['unresolved'] ?? null;
if (!is_int($reviewUnresolved) || $reviewUnresolved < 0) {
    throw new RuntimeException('Status Bank Review unresolved count is invalid.');
}

if ($decision === 'BANK_REVIEWED') {
    if (($native['status'] ?? null) !== 'NATIVE_AUDITED'
        || ($market['status'] ?? null) !== 'MARKET_AUDITED'
        || $reviewUnresolved !== 0
        || $progressStatus !== 'BANK_REVIEWED'
        || $progressRecords !== 129) {
        throw new RuntimeException('Status BANK_REVIEWED promotion lacks certified native/market/progress prerequisites.');
    }
} else {
    if ($reviewUnresolved === 0) {
        throw new RuntimeException('Status REVIEW_BLOCKED certificate must identify at least one unresolved certification gate.');
    }
    if (($native['status'] ?? null) === 'NATIVE_AUDITED'
        && ($market['status'] ?? null) === 'MARKET_AUDITED'
        && $progressStatus === 'BANK_REVIEWED'
        && $progressRecords === 129) {
        throw new RuntimeException('Status Review is blocked despite all canonical promotion prerequisites being complete.');
    }
}

printf(
    "Status Bank Review V1 contract: PASS (%s; 129 records/paths, 0 semantic overlaps, 4 rejected, 0 deferred, %d future/exceed records, native/market 0 unresolved).\n",
    $decision,
    $wpeExceed,
);
