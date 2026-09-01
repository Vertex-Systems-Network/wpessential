<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);

/** @return array<string, mixed> */
function readStatusMarketAuditJson(string $path): array
{
    $raw = file_get_contents($path);
    if ($raw === false) {
        throw new RuntimeException(sprintf('Unable to read %s.', $path));
    }

    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException(sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()), 0, $exception);
    }

    if (!is_array($data)) {
        throw new RuntimeException(sprintf('%s must contain a JSON object.', $path));
    }

    return $data;
}

$schema = readStatusMarketAuditJson($root . '/config/product/options-bank-market-audit.schema.json');
$surfaceSchema = $schema['properties']['surface']['properties'] ?? null;
if (!is_array($surfaceSchema)
    || ($surfaceSchema['id']['minimum'] ?? null) !== 1
    || ($surfaceSchema['id']['maximum'] ?? null) !== 56
    || array_key_exists('const', $surfaceSchema['id'])
    || ($surfaceSchema['key']['pattern'] ?? null) !== '^[a-z0-9][a-z0-9-]*$'
    || array_key_exists('const', $surfaceSchema['key'])) {
    throw new RuntimeException('Market-audit schema must remain generic across canonical surfaces 1-56.');
}

$registry = readStatusMarketAuditJson($root . '/config/product/competitor-parity-surfaces.json');
$canonicalKeys = [];
foreach (($registry['surfaces'] ?? []) as $row) {
    if (is_array($row) && is_int($row['id'] ?? null) && is_string($row['key'] ?? null)) {
        $canonicalKeys[$row['key']] = $row['id'];
    }
}
if (($canonicalKeys['status'] ?? null) !== 5) {
    throw new RuntimeException('Canonical Surface 5 must remain status.');
}

$statusRecords = [];
$optionPaths = [];
foreach (glob($root . '/config/product/options-bank/status*.json') ?: [] as $file) {
    $bank = readStatusMarketAuditJson($file);
    if (($bank['surface']['id'] ?? null) !== 5
        || ($bank['surface']['key'] ?? null) !== 'status'
        || ($bank['status'] ?? null) !== 'BANK_SURFACE_SEEDED') {
        throw new RuntimeException(sprintf('Invalid Status Bank shard: %s', $file));
    }

    foreach (($bank['records'] ?? []) as $record) {
        if (!is_array($record)
            || !is_string($record['id'] ?? null)
            || !is_string($record['option_path'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Status Bank record in %s.', $file));
        }

        $id = $record['id'];
        $optionPath = $record['option_path'];
        if (isset($statusRecords[$id]) || isset($optionPaths[$optionPath])) {
            throw new RuntimeException(sprintf('Status Bank record IDs and option paths must remain unique: %s.', $id));
        }

        $statusRecords[$id] = $record;
        $optionPaths[$optionPath] = true;
    }
}
if (count($statusRecords) !== 129) {
    throw new RuntimeException(sprintf('Status market audit candidate requires exactly 129 local Bank records; found %d.', count($statusRecords)));
}

$audit = readStatusMarketAuditJson($root . '/config/product/options-bank-audits/status-market-ecosystem.json');
if (($audit['schema_version'] ?? null) !== 1
    || ($audit['bank_version'] ?? null) !== 'v1'
    || ($audit['surface']['id'] ?? null) !== 5
    || ($audit['surface']['key'] ?? null) !== 'status') {
    throw new RuntimeException('Status market audit identity is invalid.');
}

$auditStatus = $audit['status'] ?? null;
if (!in_array($auditStatus, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException('Status market audit lifecycle state is invalid.');
}

$families = [
    'applicability_editor',
    'definition_labels_presentation',
    'events_automation',
    'lifecycle_portability_audit',
    'native_visibility_registration',
    'permissions_policy',
    'query_rest_api',
    'scheduling_expiration',
    'transitions_state_machine',
];
if (($audit['required_families'] ?? null) !== $families) {
    throw new RuntimeException('Status market audit must use the canonical nine-family matrix.');
}
$familySet = array_fill_keys($families, true);

$requiredPrimary = ['edit_flow', 'extended_post_status', 'publishpress_statuses'];
$providerIds = [];
$providerMaps = [];
$familyMappings = 0;
$nonApplicable = 0;
$bankRefs = 0;

foreach (($audit['primary_providers'] ?? []) as $provider) {
    if (!is_array($provider) || !is_string($provider['id'] ?? null)) {
        throw new RuntimeException('Invalid Status primary provider.');
    }

    $id = $provider['id'];
    if (isset($providerIds[$id])) {
        throw new RuntimeException(sprintf('Duplicate Status primary provider %s.', $id));
    }
    $providerIds[$id] = true;

    $evidence = $provider['evidence'] ?? null;
    if (!is_array($evidence) || $evidence === []) {
        throw new RuntimeException(sprintf('Status primary provider %s must cite evidence.', $id));
    }
    foreach ($evidence as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException(sprintf('Status primary provider %s has invalid evidence.', $id));
        }
    }

    $mapped = [];
    foreach (($provider['family_map'] ?? []) as $family => $recordIds) {
        if (!isset($familySet[$family]) || !is_array($recordIds) || $recordIds === []) {
            throw new RuntimeException(sprintf('Status provider %s has invalid family map.', $id));
        }
        if (isset($mapped[$family])) {
            throw new RuntimeException(sprintf('Status provider %s duplicates family %s.', $id, $family));
        }

        $mapped[$family] = true;
        ++$familyMappings;
        foreach ($recordIds as $recordId) {
            if (!is_string($recordId) || !isset($statusRecords[$recordId])) {
                throw new RuntimeException(sprintf('Status provider %s references missing Bank record.', $id));
            }
            $providerMaps[$id][$family][] = $recordId;
            ++$bankRefs;
        }
    }

    $na = [];
    foreach (($provider['non_applicable_families'] ?? []) as $family) {
        if (!is_string($family) || !isset($familySet[$family]) || isset($mapped[$family]) || isset($na[$family])) {
            throw new RuntimeException(sprintf('Status provider %s has invalid non-applicable family.', $id));
        }
        $na[$family] = true;
        ++$nonApplicable;
    }

    if (count($mapped) + count($na) !== count($families)) {
        throw new RuntimeException(sprintf('Status provider %s must disposition all nine families.', $id));
    }
}

$ids = array_keys($providerIds);
sort($ids);
if ($ids !== $requiredPrimary) {
    throw new RuntimeException('Status market primary roster must be Edit Flow, Extended Post Status and PublishPress Statuses.');
}

$specialists = $audit['specialist_providers'] ?? null;
if (!is_array($specialists)) {
    throw new RuntimeException('Status market audit specialist roster is invalid.');
}
$requiredSpecialists = [
    'jetengine_status_consumers',
    'oasis_workflow',
    'publishpress_future',
    'woocommerce_order_status_manager',
];
$specialistIds = [];
foreach ($specialists as $specialist) {
    if (!is_array($specialist) || !is_string($specialist['id'] ?? null)) {
        throw new RuntimeException('Invalid Status specialist provider.');
    }
    $id = $specialist['id'];
    if (isset($specialistIds[$id])) {
        throw new RuntimeException(sprintf('Duplicate Status specialist provider %s.', $id));
    }
    $specialistIds[$id] = true;

    $evidence = $specialist['evidence'] ?? null;
    if (!is_array($evidence) || $evidence === []) {
        throw new RuntimeException(sprintf('Status specialist %s must cite evidence.', $id));
    }
    foreach ($evidence as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException(sprintf('Status specialist %s has invalid evidence.', $id));
        }
    }

    $recordIds = $specialist['bank_record_ids'] ?? null;
    if (!is_array($recordIds) || $recordIds === []) {
        throw new RuntimeException(sprintf('Status specialist %s must reference Bank coverage.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($statusRecords[$recordId])) {
            throw new RuntimeException(sprintf('Status specialist %s references missing Bank record.', $id));
        }
        ++$bankRefs;
    }
}
$ids = array_keys($specialistIds);
sort($ids);
if ($ids !== $requiredSpecialists) {
    throw new RuntimeException('Status specialist roster must contain JetEngine consumers, Oasis Workflow, PublishPress Future and WooCommerce Order Status Manager.');
}

foreach ([
    ['publishpress_statuses', 'transitions_state_machine', 'status.transition.machine_enabled'],
    ['publishpress_statuses', 'permissions_policy', 'status.transition.capability'],
    ['publishpress_statuses', 'native_visibility_registration', 'status.visibility.public'],
    ['extended_post_status', 'applicability_editor', 'status.compat.extended_post_status_import'],
    ['edit_flow', 'transitions_state_machine', 'status.transition.machine_enabled'],
] as [$provider, $family, $recordId]) {
    if (!in_array($recordId, $providerMaps[$provider][$family] ?? [], true)) {
        throw new RuntimeException(sprintf('Required Status market mapping %s is not present under %s/%s.', $recordId, $provider, $family));
    }
}

$extras = $audit['extra_dispositions'] ?? null;
if (!is_array($extras)) {
    throw new RuntimeException('Status market audit extra dispositions are invalid.');
}

$expectedExtras = [
    'status.market.publishpress.workflow_routing' => ['OUT_OF_SURFACE', 'forms-workflows'],
    'status.market.publishpress.notifications' => ['OUT_OF_SURFACE', 'notifications'],
    'status.market.oasis.routing_inbox' => ['OUT_OF_SURFACE', 'forms-workflows'],
    'status.market.oasis.process_history' => ['IMPLEMENTATION_PATTERN', 'ledger'],
    'status.market.woocommerce.email_triggers' => ['OUT_OF_SURFACE', 'emails'],
    'status.market.woocommerce.reporting' => ['OUT_OF_SURFACE', 'analytics'],
    'status.market.woocommerce.payment_semantics' => ['IMPLEMENTATION_PATTERN', null],
    'status.market.jetengine.visibility' => ['OUT_OF_SURFACE', 'builder-widgets'],
    'status.market.jetengine.query' => ['OUT_OF_SURFACE', 'query'],
    'status.market.jetengine.expiration_execution' => ['IMPLEMENTATION_PATTERN', null],
];

$seenExtras = [];
$unresolved = 0;
foreach ($extras as $extra) {
    if (!is_array($extra) || !is_string($extra['id'] ?? null)) {
        throw new RuntimeException('Invalid Status extra market disposition.');
    }

    $id = $extra['id'];
    if (isset($seenExtras[$id])) {
        throw new RuntimeException(sprintf('Duplicate Status extra disposition %s.', $id));
    }
    $seenExtras[$id] = true;

    $disposition = $extra['disposition'] ?? null;
    $url = $extra['evidence_url'] ?? null;
    if (!is_string($url) || !str_starts_with($url, 'https://')) {
        throw new RuntimeException(sprintf('Status extra disposition %s has invalid evidence.', $id));
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $owner = $extra['owner_surface'] ?? null;
        if (!is_string($owner) || !isset($canonicalKeys[$owner]) || $owner === 'status') {
            throw new RuntimeException(sprintf('Status OUT_OF_SURFACE disposition %s must name another canonical owner.', $id));
        }
    } elseif (isset($extra['owner_surface'])) {
        $owner = $extra['owner_surface'];
        if (!is_string($owner) || !isset($canonicalKeys[$owner]) || $owner === 'status') {
            throw new RuntimeException(sprintf('Status extra disposition %s names an invalid owner_surface.', $id));
        }
    }

    if ($disposition === 'UNRESOLVED') {
        ++$unresolved;
    }

    $expected = $expectedExtras[$id] ?? null;
    if (!is_array($expected)) {
        throw new RuntimeException(sprintf('Unexpected Status extra disposition %s.', $id));
    }
    $actualOwner = $extra['owner_surface'] ?? null;
    if ($disposition !== $expected[0] || $actualOwner !== $expected[1]) {
        throw new RuntimeException(sprintf('Status extra disposition %s has drifted from canonical ownership.', $id));
    }
}
foreach ($expectedExtras as $id => $_expected) {
    if (!isset($seenExtras[$id])) {
        throw new RuntimeException(sprintf('Status market audit is missing mandatory extra disposition %s.', $id));
    }
}
if (count($extras) !== count($expectedExtras)) {
    throw new RuntimeException(sprintf('Status market audit must contain exactly %d extra dispositions.', count($expectedExtras)));
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Status market audit is missing coverage counters.');
}
$expectedCoverage = [
    'primary_providers' => count($requiredPrimary),
    'specialist_providers' => count($requiredSpecialists),
    'family_mappings' => $familyMappings,
    'non_applicable_family_cells' => $nonApplicable,
    'bank_record_references' => $bankRefs,
    'extra_dispositions' => count($extras),
    'unresolved' => $unresolved,
];
foreach ($expectedCoverage as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Status market coverage.%s must be %d.', $key, $value));
    }
}
if ($unresolved !== 0) {
    throw new RuntimeException('Status market audit requires zero unresolved items before certification.');
}

$expectedSafety = [
    'status.security.reject_builtin_override' => ['REJECTED_UNSAFE', 'REJECT', 'NOT_SCHEDULED'],
    'status.security.reject_direct_sql_state_write' => ['REJECTED_UNSAFE', 'REJECT', 'NOT_SCHEDULED'],
    'status.security.reject_workflow_private_write' => ['REJECTED_UNSAFE', 'REJECT', 'NOT_SCHEDULED'],
    'status.security.reject_arbitrary_transition_code' => ['REJECTED_UNSAFE', 'REJECT', 'NOT_SCHEDULED'],
];
foreach ($expectedSafety as $recordId => $expected) {
    $record = $statusRecords[$recordId] ?? null;
    $actual = is_array($record) ? [
        $record['classification'] ?? null,
        $record['adoption'] ?? null,
        $record['priority'] ?? null,
    ] : [];
    if ($actual !== $expected) {
        throw new RuntimeException(sprintf('Status safety record %s has drifted from its rejection contract.', $recordId));
    }
}

$progress = readStatusMarketAuditJson($root . '/config/product/options-bank-progress.json');
$statusProgress = null;
foreach (($progress['surface_status'] ?? []) as $row) {
    if (is_array($row) && ($row['id'] ?? null) === 5 && ($row['key'] ?? null) === 'status') {
        $statusProgress = $row;
        break;
    }
}
if (!is_array($statusProgress)) {
    throw new RuntimeException('Options Bank progress is missing canonical Surface 5 / status.');
}

$nativeAudit = readStatusMarketAuditJson($root . '/config/product/options-bank-audits/status-native-wordpress.json');
$nativeStatus = $nativeAudit['status'] ?? null;

if ($auditStatus === 'MARKET_AUDITED') {
    if ($nativeStatus !== 'NATIVE_AUDITED') {
        throw new RuntimeException('Status market certification must not outrun native certification.');
    }
    if (!in_array($statusProgress['status'] ?? null, ['MARKET_AUDITED', 'BANK_REVIEWED'], true)
        || ($statusProgress['records'] ?? null) !== count($statusRecords)) {
        throw new RuntimeException('Certified Status market audit and canonical progress truth disagree.');
    }
} elseif (!in_array($statusProgress['status'] ?? null, ['UNSEEDED', 'BANK_SURFACE_SEEDED', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException('Status progress must not outrun an in-progress market audit.');
}

printf(
    "Status market audit contract: PASS (%s; %d primary, %d specialist, %d family mappings, %d Bank references, %d extras, 0 unresolved; %d current Status records).\n",
    $auditStatus,
    count($requiredPrimary),
    count($requiredSpecialists),
    $familyMappings,
    $bankRefs,
    count($extras),
    count($statusRecords),
);
