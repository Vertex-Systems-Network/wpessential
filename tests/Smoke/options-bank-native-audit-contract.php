<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$auditPath = $root . '/config/product/options-bank-audits/fields-native-wordpress.json';
$auditSchemaPath = $root . '/config/product/options-bank-native-audit.schema.json';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$bankDirectory = $root . '/config/product/options-bank';

/** @return array<string, mixed> */
function readNativeAuditJson(string $path): array
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
function requireNativeAuditString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

// Schemas are kept dependency-free in smoke tests, but they must remain parseable JSON.
readNativeAuditJson($auditSchemaPath);

$registry = readNativeAuditJson($surfaceRegistryPath);
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

/** @var array<string, array<string, true>> $bankRecordIds */
$bankRecordIds = [];
$bankFiles = glob($bankDirectory . '/*.json');
if ($bankFiles === false) {
    throw new RuntimeException('Unable to enumerate Options Bank shards.');
}
foreach ($bankFiles as $file) {
    $bank = readNativeAuditJson($file);
    $surface = $bank['surface'] ?? null;
    $records = $bank['records'] ?? null;
    if (!is_array($surface) || !is_string($surface['key'] ?? null) || !is_array($records)) {
        throw new RuntimeException(sprintf('Invalid Options Bank shard: %s', $file));
    }
    $surfaceKey = $surface['key'];
    foreach ($records as $record) {
        if (!is_array($record) || !is_string($record['id'] ?? null)) {
            throw new RuntimeException(sprintf('Invalid Options Bank record in %s.', $file));
        }
        $bankRecordIds[$surfaceKey][$record['id']] = true;
    }
}

$audit = readNativeAuditJson($auditPath);
if (($audit['schema_version'] ?? null) !== 1 || ($audit['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Fields native audit has an unsupported version.');
}

$surface = $audit['surface'] ?? null;
if (!is_array($surface) || ($surface['id'] ?? null) !== 3 || ($surface['key'] ?? null) !== 'fields') {
    throw new RuntimeException('Fields native audit must target canonical Surface 3 / fields.');
}

$snapshot = $audit['snapshot'] ?? null;
if (!is_array($snapshot)) {
    throw new RuntimeException('Fields native audit is missing snapshot metadata.');
}
requireNativeAuditString($snapshot['date'] ?? null, 'Fields native audit is missing snapshot.date.');
requireNativeAuditString($snapshot['wordpress_target'] ?? null, 'Fields native audit is missing snapshot.wordpress_target.');
$sources = $snapshot['sources'] ?? null;
if (!is_array($sources) || $sources === []) {
    throw new RuntimeException('Fields native audit must identify primary sources.');
}

$status = requireNativeAuditString($audit['status'] ?? null, 'Fields native audit has no status.');
if (!in_array($status, ['NATIVE_AUDIT_IN_PROGRESS', 'NATIVE_AUDITED'], true)) {
    throw new RuntimeException(sprintf('Fields native audit has invalid status %s.', $status));
}

$allowedDispositions = [
    'BANK_RECORD', 'PROVIDER_MAPPING', 'SYSTEM_RUNTIME', 'OUT_OF_SURFACE',
    'LEGACY_COMPATIBILITY', 'CORE_INTERNAL', 'UNRESOLVED',
];
$items = $audit['items'] ?? null;
if (!is_array($items) || $items === []) {
    throw new RuntimeException('Fields native audit has no disposition items.');
}

$seenIds = [];
$counts = array_fill_keys($allowedDispositions, 0);
foreach ($items as $index => $item) {
    if (!is_array($item)) {
        throw new RuntimeException(sprintf('Native audit item %d is invalid.', $index));
    }

    $id = requireNativeAuditString($item['id'] ?? null, sprintf('Native audit item %d has no id.', $index));
    requireNativeAuditString($item['api'] ?? null, sprintf('Native audit item %s has no api.', $id));
    requireNativeAuditString($item['member'] ?? null, sprintf('Native audit item %s has no member.', $id));
    $disposition = requireNativeAuditString($item['disposition'] ?? null, sprintf('Native audit item %s has no disposition.', $id));
    $evidenceUrl = requireNativeAuditString($item['evidence_url'] ?? null, sprintf('Native audit item %s has no evidence_url.', $id));
    requireNativeAuditString($item['notes'] ?? null, sprintf('Native audit item %s has no notes.', $id));

    if (!str_starts_with($id, 'fields.native.')) {
        throw new RuntimeException(sprintf('Native audit item %s must use fields.native.* id namespace.', $id));
    }
    if (isset($seenIds[$id])) {
        throw new RuntimeException(sprintf('Fields native audit duplicates item id %s.', $id));
    }
    $seenIds[$id] = true;

    if (!in_array($disposition, $allowedDispositions, true)) {
        throw new RuntimeException(sprintf('Native audit item %s has invalid disposition %s.', $id, $disposition));
    }
    ++$counts[$disposition];

    if (!str_starts_with($evidenceUrl, 'https://developer.wordpress.org/')) {
        throw new RuntimeException(sprintf('Native audit item %s must cite a primary Developer.WordPress.org URL.', $id));
    }

    $recordIds = $item['bank_record_ids'] ?? [];
    if (!is_array($recordIds)) {
        throw new RuntimeException(sprintf('Native audit item %s has invalid bank_record_ids.', $id));
    }
    if (in_array($disposition, ['BANK_RECORD', 'PROVIDER_MAPPING'], true) && $recordIds === []) {
        throw new RuntimeException(sprintf('Native audit item %s must map to at least one Bank record.', $id));
    }
    foreach ($recordIds as $recordId) {
        if (!is_string($recordId) || !isset($bankRecordIds['fields'][$recordId])) {
            throw new RuntimeException(sprintf('Native audit item %s references missing Fields Bank record %s.', $id, (string) $recordId));
        }
    }

    $ownerSurface = $item['owner_surface'] ?? null;
    if (in_array($disposition, ['OUT_OF_SURFACE', 'CORE_INTERNAL'], true)) {
        if (!is_string($ownerSurface) || !isset($surfaceIds[$ownerSurface]) || $ownerSurface === 'fields') {
            throw new RuntimeException(sprintf('Native audit item %s must name a different canonical owner_surface.', $id));
        }
    }
}

$requiredItems = [
    'fields.native.register_meta.object_type',
    'fields.native.register_meta.meta_key',
    'fields.native.register_meta.object_subtype',
    'fields.native.register_meta.type',
    'fields.native.register_meta.label',
    'fields.native.register_meta.description',
    'fields.native.register_meta.single',
    'fields.native.register_meta.default',
    'fields.native.register_meta.sanitize_callback',
    'fields.native.register_meta.auth_callback',
    'fields.native.register_meta.show_in_rest',
    'fields.native.register_meta.rest_schema',
    'fields.native.register_meta.rest_schema_items',
    'fields.native.register_meta.rest_prepare',
    'fields.native.register_meta.revisions_enabled',
    'fields.native.register_meta.deprecated_callback_form',
    'fields.native.register_meta.subtype_precedence',
    'fields.native.register_meta.default_schema_validation',
    'fields.native.register_meta.revisions_object_type',
    'fields.native.register_meta.revisions_subtype',
    'fields.native.register_meta.auth_fallback',
    'fields.native.register_meta.args_filter',
    'fields.native.meta.is_protected_meta',
    'fields.native.meta.get_registered_keys',
    'fields.native.meta.unregister',
    'fields.native.bindings.server.name',
    'fields.native.bindings.server.label',
    'fields.native.bindings.server.get_value',
    'fields.native.bindings.server.uses_context',
    'fields.native.bindings.server.value_filter',
    'fields.native.bindings.editor.name',
    'fields.native.bindings.editor.label',
    'fields.native.bindings.editor.get_values',
    'fields.native.bindings.editor.set_values',
    'fields.native.bindings.editor.can_edit',
    'fields.native.bindings.editor.fields_list',
    'fields.native.bindings.supported_attributes.helper',
    'fields.native.bindings.core.post_meta',
    'fields.native.bindings.core.post_data',
    'fields.native.bindings.core.term_data',
    'fields.native.bindings.core.pattern_overrides',
];
foreach ($requiredItems as $requiredId) {
    if (!isset($seenIds[$requiredId])) {
        throw new RuntimeException(sprintf('Fields native audit is missing mandatory disposition %s.', $requiredId));
    }
}

$coverage = $audit['coverage'] ?? null;
if (!is_array($coverage)) {
    throw new RuntimeException('Fields native audit is missing coverage counters.');
}
$expectedCoverage = [
    'items' => count($items),
    'bank_record' => $counts['BANK_RECORD'],
    'provider_mapping' => $counts['PROVIDER_MAPPING'],
    'system_runtime' => $counts['SYSTEM_RUNTIME'],
    'out_of_surface' => $counts['OUT_OF_SURFACE'],
    'legacy_compatibility' => $counts['LEGACY_COMPATIBILITY'],
    'core_internal' => $counts['CORE_INTERNAL'],
    'unresolved' => $counts['UNRESOLVED'],
];
foreach ($expectedCoverage as $key => $value) {
    if (($coverage[$key] ?? null) !== $value) {
        throw new RuntimeException(sprintf('Fields native audit coverage.%s must be %d.', $key, $value));
    }
}

if ($status === 'NATIVE_AUDITED' && $counts['UNRESOLVED'] !== 0) {
    throw new RuntimeException('NATIVE_AUDITED requires zero unresolved native disposition items.');
}

fwrite(
    STDOUT,
    sprintf(
        "Fields native audit contract: PASS (%d dispositions, %d Bank, %d provider, %d runtime/out-of-surface/internal/legacy, 0 unresolved).\n",
        count($items),
        $counts['BANK_RECORD'],
        $counts['PROVIDER_MAPPING'],
        $counts['SYSTEM_RUNTIME'] + $counts['OUT_OF_SURFACE'] + $counts['CORE_INTERNAL'] + $counts['LEGACY_COMPATIBILITY'],
    ),
);
