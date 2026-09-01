<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$reviewPath = $root . '/config/product/options-bank-reviews/dashboard-widgets-bank-review-v1.json';
$reviewSchemaPath = $root . '/config/product/options-bank-review.schema.json';
$semanticPath = $root . '/config/product/options-bank-semantic-relations.json';
$nativeAuditPath = $root . '/config/product/options-bank-audits/dashboard-widgets-native-wordpress.json';
$marketAuditPath = $root . '/config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json';
$progressPath = $root . '/config/product/options-bank-progress.json';
$bankDirectory = $root . '/config/product/options-bank';
$wpeExceedPath = $bankDirectory . '/dashboard-widgets--wpe-exceed.json';
$deferredPath = $bankDirectory . '/dashboard-widgets--future-deferred.json';

/** @return array<string, mixed> */
function readDashboardWidgetsReviewJson(string $path): array
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

readDashboardWidgetsReviewJson($reviewSchemaPath);
$review = readDashboardWidgetsReviewJson($reviewPath);

if (($review['schema_version'] ?? null) !== 1
    || ($review['bank_version'] ?? null) !== 'v1'
    || ($review['surface']['id'] ?? null) !== 10
    || ($review['surface']['key'] ?? null) !== 'dashboard-widgets'
    || ($review['record_count'] ?? null) !== 123) {
    throw new RuntimeException('Dashboard Widgets Bank Review certificate identity/count is invalid.');
}

$decision = $review['decision'] ?? null;
if (!in_array($decision, ['REVIEW_BLOCKED', 'BANK_REVIEWED'], true)) {
    throw new RuntimeException('Dashboard Widgets Bank Review decision must be REVIEW_BLOCKED or BANK_REVIEWED.');
}

/** @var array<string, array<string, mixed>> $records */
$records = [];
$optionPaths = [];
$bankFiles = glob($bankDirectory . '/dashboard-widgets*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Dashboard Widgets Options Bank shards.');
}
sort($bankFiles, SORT_STRING);
foreach ($bankFiles as $file) {
    $bank = readDashboardWidgetsReviewJson($file);
    if (($bank['surface']['id'] ?? null) !== 10 || ($bank['surface']['key'] ?? null) !== 'dashboard-widgets') {
        throw new RuntimeException(sprintf('Invalid Dashboard Widgets Options Bank shard: %s.', $file));
    }
    $bankRecords = $bank['records'] ?? null;
    if (!is_array($bankRecords)) {
        throw new RuntimeException(sprintf('Dashboard Widgets Options Bank shard %s has no records array.', $file));
    }
    foreach ($bankRecords as $record) {
        if (!is_array($record)) {
            throw new RuntimeException(sprintf('Invalid Dashboard Widgets Bank record in %s.', $file));
        }
        $id = $record['id'] ?? null;
        $optionPath = $record['option_path'] ?? null;
        if (!is_string($id) || $id === '' || isset($records[$id])) {
            throw new RuntimeException('Dashboard Widgets Bank record IDs must be valid and unique.');
        }
        if (!is_string($optionPath) || $optionPath === '' || isset($optionPaths[$optionPath])) {
            throw new RuntimeException('Dashboard Widgets option paths must be valid and unique.');
        }
        $records[$id] = $record;
        $optionPaths[$optionPath] = true;
    }
}
if (count($records) !== 123 || count($optionPaths) !== 123) {
    throw new RuntimeException(sprintf(
        'Dashboard Widgets Bank Review requires 123 unique records/paths; found %d records and %d paths.',
        count($records),
        count($optionPaths),
    ));
}

$semantic = readDashboardWidgetsReviewJson($semanticPath);
$relationships = $semantic['relationships'] ?? null;
if (!is_array($relationships)) {
    throw new RuntimeException('Semantic registry is malformed.');
}
$dashboardSemantic = [];
$aliases = 0;
$effectiveDerivations = 0;
foreach ($relationships as $row) {
    if (!is_array($row) || ($row['surface'] ?? null) !== 'dashboard-widgets') {
        continue;
    }
    $dashboardSemantic[] = $row;
    if (($row['relation'] ?? null) === 'ALIAS') {
        ++$aliases;
    } elseif (($row['relation'] ?? null) === 'EFFECTIVE_DERIVATION') {
        ++$effectiveDerivations;
    } else {
        throw new RuntimeException('Dashboard Widgets semantic registry contains an unsupported relationship type.');
    }
}
$semanticExpectations = $review['semantic_expectations'] ?? null;
if (!is_array($semanticExpectations)
    || ($semanticExpectations['relationships'] ?? null) !== count($dashboardSemantic)
    || ($semanticExpectations['aliases'] ?? null) !== $aliases
    || ($semanticExpectations['effective_derivations'] ?? null) !== $effectiveDerivations
    || count($dashboardSemantic) !== 0) {
    throw new RuntimeException('Dashboard Widgets semantic registry must currently close with zero alias/effective-derivation entries.');
}

$native = readDashboardWidgetsReviewJson($nativeAuditPath);
$market = readDashboardWidgetsReviewJson($marketAuditPath);
if (($native['coverage']['unresolved'] ?? null) !== 0 || ($market['coverage']['unresolved'] ?? null) !== 0) {
    throw new RuntimeException('Dashboard Widgets native/market prerequisites must remain zero-unresolved.');
}
if (!in_array($native['status'] ?? null, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException('Dashboard Widgets native audit lifecycle state is invalid.');
}
if (!in_array($market['status'] ?? null, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException('Dashboard Widgets market audit lifecycle state is invalid.');
}

$unreviewed = 0;
$rejectedUnsafe = 0;
$deferred = 0;
$wpeExceed = 0;
$rejectedConsistent = true;
$deferredConsistent = true;
$exceedConsistent = true;
foreach ($records as $id => $record) {
    $classification = $record['classification'] ?? null;
    $adoption = $record['adoption'] ?? null;
    $priority = $record['priority'] ?? null;
    $horizon = $record['horizon'] ?? null;

    if ($adoption === 'UNREVIEWED') {
        ++$unreviewed;
    }

    if ($classification === 'REJECTED_UNSAFE') {
        ++$rejectedUnsafe;
        if ($adoption !== 'REJECT' || $priority !== 'NOT_SCHEDULED') {
            $rejectedConsistent = false;
        }
    }
    if ($adoption === 'REJECT' && $classification !== 'REJECTED_UNSAFE') {
        $rejectedConsistent = false;
    }

    if ($classification === 'DEFERRED') {
        ++$deferred;
        if ($horizon !== 'WPE_FUTURE' || $adoption !== 'LATER' || $priority !== 'P3_LATER') {
            $deferredConsistent = false;
        }
    }
    if ($adoption === 'LATER' && ($classification !== 'DEFERRED' || $horizon !== 'WPE_FUTURE' || $priority !== 'P3_LATER')) {
        $deferredConsistent = false;
    }

    if ($classification === 'WPE_EXCEED') {
        ++$wpeExceed;
        if ($horizon !== 'WPE_FUTURE' || $adoption !== 'WPE_EXCEED' || $priority !== 'P1_EXCEED') {
            $exceedConsistent = false;
        }
    }
    if ($adoption === 'WPE_EXCEED' && ($classification !== 'WPE_EXCEED' || $horizon !== 'WPE_FUTURE' || $priority !== 'P1_EXCEED')) {
        $exceedConsistent = false;
    }

    if (!is_string($classification) || !is_string($adoption) || !is_string($priority) || !is_string($horizon)) {
        throw new RuntimeException(sprintf('Dashboard Widgets record %s is missing review-critical policy metadata.', $id));
    }
}
if ($unreviewed !== 0 || $deferred !== 2 || $wpeExceed !== 12) {
    throw new RuntimeException(sprintf(
        'Dashboard Widgets review expects 0 unreviewed, 2 deferred and 12 WPE-exceed records; found %d/%d/%d.',
        $unreviewed,
        $deferred,
        $wpeExceed,
    ));
}
if (!$rejectedConsistent || !$deferredConsistent || !$exceedConsistent) {
    throw new RuntimeException('Dashboard Widgets policy classifications are internally inconsistent.');
}

$exceedBank = readDashboardWidgetsReviewJson($wpeExceedPath);
$exceedRecords = $exceedBank['records'] ?? null;
if (!is_array($exceedRecords) || count($exceedRecords) !== 12) {
    throw new RuntimeException('Dashboard Widgets WPE-exceed shard must contain exactly 12 records.');
}
$exceedShardFutureOnly = true;
foreach ($exceedRecords as $record) {
    if (!is_array($record)
        || ($record['classification'] ?? null) !== 'WPE_EXCEED'
        || ($record['horizon'] ?? null) !== 'WPE_FUTURE'
        || ($record['adoption'] ?? null) !== 'WPE_EXCEED'
        || ($record['priority'] ?? null) !== 'P1_EXCEED') {
        $exceedShardFutureOnly = false;
        break;
    }
}

$deferredBank = readDashboardWidgetsReviewJson($deferredPath);
$deferredRecords = $deferredBank['records'] ?? null;
if (!is_array($deferredRecords) || count($deferredRecords) !== 2) {
    throw new RuntimeException('Dashboard Widgets future-deferred shard must contain exactly two records.');
}
foreach ($deferredRecords as $record) {
    if (!is_array($record)
        || ($record['classification'] ?? null) !== 'DEFERRED'
        || ($record['horizon'] ?? null) !== 'WPE_FUTURE'
        || ($record['adoption'] ?? null) !== 'LATER'
        || ($record['priority'] ?? null) !== 'P3_LATER') {
        throw new RuntimeException('Dashboard Widgets future-deferred shard contains a non-canonical deferred record.');
    }
}

$gates = $review['policy_gates'] ?? null;
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
if (!is_array($gates)) {
    throw new RuntimeException('Dashboard Widgets Bank Review policy_gates is malformed.');
}
foreach ($expectedGates as $key => $value) {
    if (($gates[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Dashboard Widgets Bank Review policy gate %s disagrees with repository truth.', $key));
    }
}
if (!$exceedShardFutureOnly) {
    throw new RuntimeException('Dashboard Widgets WPE-exceed shard must contain only canonical future WPE_EXCEED records.');
}

$required = $review['required_artifacts'] ?? null;
if (!is_array($required)) {
    throw new RuntimeException('Dashboard Widgets Bank Review required_artifacts is malformed.');
}
$expectedPaths = [
    'semantic_registry' => 'config/product/options-bank-semantic-relations.json',
    'native_audit' => 'config/product/options-bank-audits/dashboard-widgets-native-wordpress.json',
    'market_audit' => 'config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json',
];
$seenArtifacts = [];
foreach ($required as $artifact) {
    if (!is_array($artifact) || !is_string($artifact['kind'] ?? null) || !is_string($artifact['path'] ?? null) || !is_string($artifact['expected_status'] ?? null)) {
        throw new RuntimeException('Dashboard Widgets Bank Review required artifact is malformed.');
    }
    $seenArtifacts[$artifact['kind']] = $artifact['path'];
}
if ($seenArtifacts !== $expectedPaths) {
    throw new RuntimeException('Dashboard Widgets Bank Review prerequisites have drifted.');
}

$progress = readDashboardWidgetsReviewJson($progressPath);
$progressRow = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 10 && ($row['key'] ?? null) === 'dashboard-widgets') {
        $progressRow = $row;
        break;
    }
}
if (!is_array($progressRow)) {
    throw new RuntimeException('Canonical progress is missing Surface 10 / dashboard-widgets.');
}

$reviewUnresolved = $review['unresolved'] ?? null;
if (!is_int($reviewUnresolved) || $reviewUnresolved < 0) {
    throw new RuntimeException('Dashboard Widgets Bank Review unresolved count is invalid.');
}

if ($decision === 'BANK_REVIEWED') {
    if (($native['status'] ?? null) !== 'NATIVE_AUDITED'
        || ($market['status'] ?? null) !== 'MARKET_AUDITED'
        || $reviewUnresolved !== 0
        || ($progressRow['status'] ?? null) !== 'BANK_REVIEWED'
        || ($progressRow['records'] ?? null) !== 123) {
        throw new RuntimeException('Dashboard Widgets BANK_REVIEWED promotion lacks certified native/market/progress prerequisites.');
    }
} else {
    if ($reviewUnresolved === 0) {
        throw new RuntimeException('Dashboard Widgets REVIEW_BLOCKED certificate must identify at least one unresolved integration gate.');
    }
    if (($native['status'] ?? null) === 'NATIVE_AUDITED'
        && ($market['status'] ?? null) === 'MARKET_AUDITED'
        && ($progressRow['status'] ?? null) === 'BANK_REVIEWED'
        && ($progressRow['records'] ?? null) === 123) {
        throw new RuntimeException('Dashboard Widgets Review is blocked despite all canonical promotion prerequisites being complete.');
    }
}

printf(
    "Dashboard Widgets Bank Review V1 contract: PASS (%s; 123 records, 0 semantic overlaps, %d rejected, 2 deferred, 12 WPE-exceed, native/market 0 unresolved).\n",
    $decision,
    $rejectedUnsafe,
);
