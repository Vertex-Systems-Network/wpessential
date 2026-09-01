<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/dashboard-widgets-market-ecosystem.json';
$schemaPath = $root . '/config/product/options-bank-market-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readDashboardWidgetsMarketJson(string $path): array
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
function requireDashboardWidgetsMarketString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value
 * @return array<mixed>
 */
function requireDashboardWidgetsMarketArray($value, string $message): array
{
    if (!is_array($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param array<mixed> $urls */
function validateDashboardWidgetsMarketEvidence(array $urls, string $context): void
{
    if ($urls === []) {
        throw new RuntimeException(sprintf('%s must provide at least one evidence URL.', $context));
    }

    foreach ($urls as $url) {
        if (!is_string($url) || !str_starts_with($url, 'https://')) {
            throw new RuntimeException(sprintf('%s contains an invalid evidence URL.', $context));
        }
    }
}

// Dependency-free smoke contract: the shared schema must remain parseable JSON.
readDashboardWidgetsMarketJson($schemaPath);

$registry = readDashboardWidgetsMarketJson($surfaceRegistryPath);
$surfaces = $registry['surfaces'] ?? null;
if (!is_array($surfaces)) {
    throw new RuntimeException('Canonical surface registry is malformed.');
}

/** @var array<string, int> $surfaceIds */
$surfaceIds = [];
foreach ($surfaces as $surface) {
    if (!is_array($surface) || !is_int($surface['id'] ?? null) || !is_string($surface['key'] ?? null)) {
        throw new RuntimeException('Canonical surface registry contains an invalid row.');
    }
    $surfaceIds[$surface['key']] = $surface['id'];
}
if (($surfaceIds['dashboard-widgets'] ?? null) !== 10) {
    throw new RuntimeException('Canonical Surface 10 must remain dashboard-widgets.');
}

/** @var array<string, true> $bankRecordIds */
$bankRecordIds = [];
$bankFiles = glob($bankDirectory . '/dashboard-widgets*.json');
if ($bankFiles === false || $bankFiles === []) {
    throw new RuntimeException('Unable to enumerate Dashboard Widgets Options Bank shards.');
}
foreach ($bankFiles as $file) {
    $bank = readDashboardWidgetsMarketJson($file);
    $surface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($surface) || ($surface['id'] ?? null) !== 10 || ($surface['key'] ?? null) !== 'dashboard-widgets' || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Dashboard Widgets Options Bank shard: %s', $file));
    }
    foreach ($records as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Dashboard Widgets Bank record in %s.', $file));
        }
        $bankRecordIds[$record['id']] = true;
    }
}
if (count($bankRecordIds) !== 123) {
    throw new RuntimeException(sprintf('Dashboard Widgets market audit expects 123 unique Bank records; found %d.', count($bankRecordIds)));
}

$audit = readDashboardWidgetsMarketJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Dashboard Widgets market audit has an unsupported version.');
}

$surface = $audit['surface'] ?? null;
if (!is_array($surface) || ($surface['id'] ?? null) !== 10 || ($surface['key'] ?? null) !== 'dashboard-widgets') {
    throw new RuntimeException('Dashboard Widgets market audit must target canonical Surface 10 / dashboard-widgets.');
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Dashboard Widgets market audit is missing snapshot metadata.');
}
requireDashboardWidgetsMarketString($snapshot['date'] ?? null, 'Dashboard Widgets market audit is missing snapshot.date.');
requireDashboardWidgetsMarketString($snapshot['notes'] ?? null, 'Dashboard Widgets market audit is missing snapshot.notes.');

$status = requireDashboardWidgetsMarketString($audit['status'] ?? null, 'Dashboard Widgets market audit has no status.');
if (!in_array($status, ['MARKET_AUDIT_IN_PROGRESS', 'MARKET_AUDITED'], true)) {
    throw new RuntimeException(sprintf('Dashboard Widgets market audit has invalid status %s.', $status));
}

$requiredFamilies = requireDashboardWidgetsMarketArray(
    $audit['required_families'] ?? null,
    'Dashboard Widgets market audit is missing required_families.',
);
$expectedFamilies = [
    'widget_definition_content',
    'inventory_cleanup',
    'visibility_targeting',
    'placement_order',
    'remote_content',
    'multisite_presets',
    'portability',
    'welcome_onboarding',
];
if ($requiredFamilies !== $expectedFamilies) {
    throw new RuntimeException('Dashboard Widgets market audit required_families must match the canonical eight-family matrix.');
}
$familyLookup = array_fill_keys($requiredFamilies, true);

$requiredPrimary = ['ultimate_dashboard', 'wp_adminify', 'white_label_cms'];
$requiredSpecialists = ['dashboard_widgets_suite', 'dashboard_welcome_elementor'];

$primaryProviders = requireDashboardWidgetsMarketArray(
    $audit['primary_providers'] ?? null,
    'Dashboard Widgets market audit has no primary_providers.',
);
$specialistProviders = requireDashboardWidgetsMarketArray(
    $audit['specialist_providers'] ?? null,
    'Dashboard Widgets market audit has no specialist_providers.',
);

/** @var array<string, true> $providerIds */
$providerIds = [];
$familyMappings = 0;
$nonApplicableFamilyCells = 0;
$bankRecordReferences = 0;

foreach ($primaryProviders as $index => $provider) {
    if (!is_array($provider)) {
        throw new RuntimeException(sprintf('Dashboard Widgets primary provider %d is invalid.', $index));
    }

    $id = requireDashboardWidgetsMarketString($provider['id'] ?? null, sprintf('Dashboard Widgets primary provider %d has no id.', $index));
    requireDashboardWidgetsMarketString($provider['name'] ?? null, sprintf('Dashboard Widgets primary provider %s has no name.', $id));
    if (isset($providerIds[$id])) {
        throw new RuntimeException(sprintf('Dashboard Widgets market audit duplicates provider id %s.', $id));
    }
    $providerIds[$id] = true;

    $evidence = requireDashboardWidgetsMarketArray($provider['evidence'] ?? null, sprintf('Dashboard Widgets primary provider %s has no evidence.', $id));
    validateDashboardWidgetsMarketEvidence($evidence, sprintf('Dashboard Widgets primary provider %s', $id));

    $familyMap = requireDashboardWidgetsMarketArray($provider['family_map'] ?? null, sprintf('Dashboard Widgets primary provider %s has no family_map.', $id));
    $nonApplicable = requireDashboardWidgetsMarketArray(
        $provider['non_applicable_families'] ?? null,
        sprintf('Dashboard Widgets primary provider %s has no non_applicable_families.', $id),
    );

    $mapped = [];
    foreach ($familyMap as $family => $recordIds) {
        if (!is_string($family) || !isset($familyLookup[$family])) {
            throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s maps unknown family %s.', $id, (string) $family));
        }
        if (!is_array($recordIds) || $recordIds === []) {
            throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s family %s must map to Bank records.', $id, $family));
        }
        if (isset($mapped[$family])) {
            throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s duplicates family %s.', $id, $family));
        }
        $mapped[$family] = true;
        ++$familyMappings;

        foreach ($recordIds as $recordId) {
            if (!is_string($recordId) || !isset($bankRecordIds[$recordId])) {
                throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s family %s references missing Bank record %s.', $id, $family, (string) $recordId));
            }
            ++$bankRecordReferences;
        }
    }

    $notApplicable = [];
    foreach ($nonApplicable as $family) {
        if (!is_string($family) || !isset($familyLookup[$family])) {
            throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s has unknown non-applicable family %s.', $id, (string) $family));
        }
        if (isset($mapped[$family]) || isset($notApplicable[$family])) {
            throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s duplicates family disposition %s.', $id, $family));
        }
        $notApplicable[$family] = true;
        ++$nonApplicableFamilyCells;
    }

    if (count($mapped) + count($notApplicable) !== count($requiredFamilies)) {
        throw new RuntimeException(sprintf('Dashboard Widgets primary provider %s must disposition every required capability family.', $id));
    }
}

$primaryIds = array_keys($providerIds);
sort($primaryIds);
$expectedPrimary = $requiredPrimary;
sort($expectedPrimary);
if ($primaryIds !== $expectedPrimary) {
    throw new RuntimeException('Dashboard Widgets market audit primary provider roster does not match the required benchmark set.');
}

/** @var array<string, true> $specialistSeen */
$specialistSeen = [];
foreach ($specialistProviders as $index => $provider) {
    if (!is_array($provider)) {
        throw new RuntimeException(sprintf('Dashboard Widgets specialist provider %d is invalid.', $index));
    }

    $id = requireDashboardWidgetsMarketString($provider['id'] ?? null, sprintf('Dashboard Widgets specialist provider %d has no id.', $index));
    requireDashboardWidgetsMarketString($provider['name'] ?? null, sprintf('Dashboard Widgets specialist provider %s has no name.', $id));
    if (isset($providerIds[$id]) || isset($specialistSeen[$id])) {
        throw new RuntimeException(sprintf('Dashboard Widgets market audit duplicates specialist provider id %s.', $id));
    }
    $specialistSeen[$id] = true;

    $evidence = requireDashboardWidgetsMarketArray($provider['evidence'] ?? null, sprintf('Dashboard Widgets specialist provider %s has no evidence.', $id));
    validateDashboardWidgetsMarketEvidence($evidence, sprintf('Dashboard Widgets specialist provider %s', $id));

    $recordIds = requireDashboardWidgetsMarketArray(
        $provider['bank_record_ids'] ?? null,
        sprintf('Dashboard Widgets specialist provider %s has no Bank coverage.', $id),
    );
    if ($recordIds === []) {
        throw new RuntimeException(sprintf('Dashboard Widgets specialist provider %s must map to at least one Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($bankRecordIds[$recordId])) {
            throw new RuntimeException(sprintf('Dashboard Widgets specialist provider %s references missing Bank record %s.', $id, (string) $recordId));
        }
        ++$bankRecordReferences;
    }
}

$specialistIds = array_keys($specialistSeen);
sort($specialistIds);
$expectedSpecialists = $requiredSpecialists;
sort($expectedSpecialists);
if ($specialistIds !== $expectedSpecialists) {
    throw new RuntimeException('Dashboard Widgets market audit specialist provider roster does not match the required benchmark set.');
}

$extraDispositions = requireDashboardWidgetsMarketArray(
    $audit['extra_dispositions'] ?? null,
    'Dashboard Widgets market audit is missing extra_dispositions.',
);
$allowedExtraDispositions = [
    'OUT_OF_SURFACE',
    'IMPLEMENTATION_PATTERN',
    'DEFERRED_WITH_REASON',
    'REJECTED_UNSAFE',
    'UNRESOLVED',
];
$extraSeen = [];
$unresolved = 0;
foreach ($extraDispositions as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Dashboard Widgets extra market disposition %d is invalid.', $index));
    }

    $id = requireDashboardWidgetsMarketString($item['id'] ?? null, sprintf('Dashboard Widgets extra market disposition %d has no id.', $index));
    $provider = requireDashboardWidgetsMarketString($item['provider'] ?? null, sprintf('Dashboard Widgets extra market disposition %s has no provider.', $id));
    $disposition = requireDashboardWidgetsMarketString($item['disposition'] ?? null, sprintf('Dashboard Widgets extra market disposition %s has no disposition.', $id));
    $evidenceUrl = requireDashboardWidgetsMarketString($item['evidence_url'] ?? null, sprintf('Dashboard Widgets extra market disposition %s has no evidence_url.', $id));
    requireDashboardWidgetsMarketString($item['notes'] ?? null, sprintf('Dashboard Widgets extra market disposition %s has no notes.', $id));

    if (isset($extraSeen[$id])) {
        throw new RuntimeException(sprintf('Dashboard Widgets market audit duplicates extra disposition id %s.', $id));
    }
    $extraSeen[$id] = true;

    if ($provider !== 'ecosystem' && !isset($providerIds[$provider]) && !isset($specialistSeen[$provider])) {
        throw new RuntimeException(sprintf('Dashboard Widgets extra market disposition %s references unknown provider %s.', $id, $provider));
    }
    if (!in_array($disposition, $allowedExtraDispositions, true)) {
        throw new RuntimeException(sprintf('Dashboard Widgets extra market disposition %s has invalid disposition %s.', $id, $disposition));
    }
    if (!str_starts_with($evidenceUrl, 'https://')) {
        throw new RuntimeException(sprintf('Dashboard Widgets extra market disposition %s has an invalid evidence URL.', $id));
    }

    if ($disposition === 'OUT_OF_SURFACE') {
        $ownerSurface = $item['owner_surface'] ?? null;
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'dashboard-widgets') {
            throw new RuntimeException(sprintf('Dashboard Widgets extra market disposition %s must name a different canonical owner_surface.', $id));
        }
    }

    if ($disposition === 'UNRESOLVED') {
        ++$unresolved;
    }
}

$requiredExtraIds = [
    'wp_adminify.raw_script_widget',
    'market.arbitrary_php_widget',
    'ultimate_dashboard.global_admin_branding',
    'white_label_cms.admin_menu_restrictions',
];
$actualExtraIds = array_keys($extraSeen);
sort($actualExtraIds);
sort($requiredExtraIds);
if ($actualExtraIds !== $requiredExtraIds) {
    throw new RuntimeException('Dashboard Widgets market audit extra dispositions must preserve the reviewed safety/ownership boundary set.');
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Dashboard Widgets market audit is missing coverage counters.');
}
$expectedCoverage = [
    'primary_providers' => count($primaryProviders),
    'specialist_providers' => count($specialistProviders),
    'family_mappings' => $familyMappings,
    'non_applicable_family_cells' => $nonApplicableFamilyCells,
    'bank_record_references' => $bankRecordReferences,
    'extra_dispositions' => count($extraDispositions),
    'unresolved' => $unresolved,
];
foreach ($expectedCoverage as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Dashboard Widgets market audit coverage.%s must be %d.', $key, $value));
    }
}

if ($unresolved !== 0) {
    throw new RuntimeException('Dashboard Widgets market audit candidate must have zero unresolved dispositions.');
}

fwrite(
    STDOUT,
    sprintf(
        "Dashboard Widgets market audit contract: PASS (%s; %d primary, %d specialist, %d family mappings, %d Bank refs, 0 unresolved).\n",
        $status,
        count($primaryProviders),
        count($specialistProviders),
        $familyMappings,
        $bankRecordReferences,
    ),
);
