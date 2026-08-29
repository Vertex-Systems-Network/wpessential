<?php

declare(strict_types=1);

/**
 * WPEssential machine-enforced architecture guard.
 *
 * Usage: php tools/architecture/validate.php
 */

$root = dirname(__DIR__, 2);
$errors = [];

/** @return array<string,mixed> */
function loadJson(string $path, array &$errors): array
{
    if (!is_file($path)) {
        $errors[] = "Missing manifest: {$path}";
        return [];
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        $errors[] = "Unreadable manifest: {$path}";
        return [];
    }

    try {
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        $errors[] = "Invalid JSON {$path}: {$exception->getMessage()}";
        return [];
    }

    if (!is_array($decoded)) {
        $errors[] = "Manifest root must be an object: {$path}";
        return [];
    }

    return $decoded;
}

/** @param array<int|string,mixed> $values */
function assertUnique(array $values, string $label, array &$errors): void
{
    $normalized = array_map(static fn($value): string => (string) $value, $values);
    if (count($normalized) !== count(array_unique($normalized))) {
        $errors[] = "Duplicate {$label} detected.";
    }
}

/** @param array<int,int> $surfaceIds */
function assertSurfaceRefs(array $refs, array $surfaceIds, string $context, array &$errors): void
{
    foreach ($refs as $ref) {
        if (!is_int($ref) || !in_array($ref, $surfaceIds, true)) {
            $errors[] = "{$context} references unknown surface: " . json_encode($ref);
        }
    }
}

$surfacesManifest = loadJson($root . '/config/architecture/surfaces.json', $errors);
$ownership = loadJson($root . '/config/architecture/ownership-contracts.json', $errors);
$patternsManifest = loadJson($root . '/config/architecture/system-patterns.json', $errors);
$operations = loadJson($root . '/config/architecture/operation-guards.json', $errors);

$surfaces = $surfacesManifest['surfaces'] ?? [];
$expectedSurfaceCount = $surfacesManifest['expected_surface_count'] ?? null;

if (!is_array($surfaces) || $expectedSurfaceCount !== 56 || count($surfaces) !== 56) {
    $errors[] = 'Surface manifest must contain exactly 56 canonical surfaces.';
}

$surfaceIds = [];
$surfaceKeys = [];
$surfaceRoutes = [];
$validSuites = [
    'solutions',
    'content-schema',
    'data-intelligence',
    'experience-presentation',
    'identity-access',
    'automation-communication',
    'integrations-data',
    'operations-security',
    'developer-ai',
    'platform-support',
];

foreach ($surfaces as $surface) {
    if (!is_array($surface)) {
        $errors[] = 'Every surface entry must be an object.';
        continue;
    }

    $id = $surface['id'] ?? null;
    $key = $surface['key'] ?? null;
    $suite = $surface['suite'] ?? null;
    $route = $surface['route'] ?? null;

    if (!is_int($id) || $id < 1 || $id > 56) {
        $errors[] = 'Surface id must be an integer from 1..56.';
    } else {
        $surfaceIds[] = $id;
    }

    if (!is_string($key) || !preg_match('/^[a-z0-9][a-z0-9-]*$/', $key)) {
        $errors[] = "Invalid surface key for id {$id}.";
    } else {
        $surfaceKeys[] = $key;
    }

    if (!is_string($suite) || !in_array($suite, $validSuites, true)) {
        $errors[] = "Unknown suite for surface {$id}: " . json_encode($suite);
    }

    if (!is_string($route) || !str_starts_with($route, '/')) {
        $errors[] = "Invalid canonical route for surface {$id}.";
    } else {
        $surfaceRoutes[] = $route;
    }
}

sort($surfaceIds);
if ($surfaceIds !== range(1, 56)) {
    $errors[] = 'Surface IDs must be exactly the contiguous canonical range 1..56.';
}
assertUnique($surfaceKeys, 'surface key', $errors);
assertUnique($surfaceRoutes, 'canonical admin route', $errors);

$domains = $ownership['semantic_domains'] ?? [];
$domainNames = [];
foreach ($domains as $domain) {
    if (!is_array($domain)) {
        $errors[] = 'Semantic domain entry must be an object.';
        continue;
    }
    $name = $domain['domain'] ?? null;
    $owner = $domain['owner'] ?? null;
    $delegates = $domain['delegates'] ?? [];
    if (!is_string($name) || $name === '') {
        $errors[] = 'Semantic domain requires a stable name.';
    } else {
        $domainNames[] = $name;
    }
    assertSurfaceRefs([$owner], $surfaceIds, "semantic domain {$name} owner", $errors);
    if (!is_array($delegates)) {
        $errors[] = "Semantic domain {$name} delegates must be an array.";
    } else {
        assertSurfaceRefs($delegates, $surfaceIds, "semantic domain {$name}", $errors);
    }
}
assertUnique($domainNames, 'semantic domain', $errors);

$overlayRules = $ownership['overlay_rules'] ?? [];
foreach ([
    'may_register_canonical_surface',
    'may_register_parallel_kernel',
    'may_register_parallel_admin_app',
    'may_bypass_owner_ability',
] as $forbiddenOverlayRule) {
    if (($overlayRules[$forbiddenOverlayRule] ?? null) !== false) {
        $errors[] = "Competitive overlays must set {$forbiddenOverlayRule}=false.";
    }
}

$overlays = $ownership['competitive_overlays'] ?? [];
foreach ($overlays as $overlay => $refs) {
    if (!is_string($overlay) || !is_array($refs) || $refs === []) {
        $errors[] = 'Every competitive overlay must map to one or more canonical surface references.';
        continue;
    }
    assertSurfaceRefs($refs, $surfaceIds, "overlay {$overlay}", $errors);
}

$patterns = $patternsManifest['patterns'] ?? [];
if (($patternsManifest['expected_pattern_count'] ?? null) !== 40 || !is_array($patterns) || count($patterns) !== 40) {
    $errors[] = 'System pattern manifest must contain exactly 40 patterns.';
}
$patternIds = [];
foreach ($patterns as $pattern) {
    if (!is_array($pattern)) {
        $errors[] = 'Pattern entry must be an object.';
        continue;
    }
    $id = $pattern['id'] ?? null;
    if (!is_string($id) || !preg_match('/^P(?:0[1-9]|[12][0-9]|3[0-9]|40)$/', $id)) {
        $errors[] = 'Invalid pattern ID: ' . json_encode($id);
        continue;
    }
    $patternIds[] = $id;
    $required = $pattern['required'] ?? [];
    $optional = $pattern['optional'] ?? [];
    if (!is_array($required) || $required === []) {
        $errors[] = "Pattern {$id} must have at least one required canonical owner.";
    } else {
        assertSurfaceRefs($required, $surfaceIds, "pattern {$id} required", $errors);
    }
    if (!is_array($optional)) {
        $errors[] = "Pattern {$id} optional must be an array.";
    } else {
        assertSurfaceRefs($optional, $surfaceIds, "pattern {$id} optional", $errors);
    }
}
assertUnique($patternIds, 'system pattern ID', $errors);
$expectedPatterns = array_map(static fn(int $i): string => sprintf('P%02d', $i), range(1, 40));
sort($patternIds);
sort($expectedPatterns);
if ($patternIds !== $expectedPatterns) {
    $errors[] = 'Pattern IDs must be exactly P01..P40.';
}

$abilityRules = $operations['ability_rules'] ?? [];
foreach ([
    'mutation_must_resolve_canonical_owner',
    'registration_does_not_imply_channel_exposure',
    'resource_mutation_requires_policy',
] as $requiredAbilityRule) {
    if (($abilityRules[$requiredAbilityRule] ?? null) !== true) {
        $errors[] = "Ability guard {$requiredAbilityRule} must be true.";
    }
}
if (($abilityRules['destructive_ai_default'] ?? null) !== 'disabled') {
    $errors[] = 'Destructive AI default must be disabled.';
}

foreach (($operations['storage_owners'] ?? []) as $storage) {
    if (!is_array($storage)) {
        $errors[] = 'Storage owner entry must be an object.';
        continue;
    }
    if (isset($storage['surface'])) {
        assertSurfaceRefs([$storage['surface']], $surfaceIds, 'storage owner', $errors);
    } elseif (($storage['owner'] ?? null) !== 'platform') {
        $errors[] = 'Storage owner must resolve to a canonical surface or platform.';
    }
}

$multisite = $operations['multisite'] ?? [];
$requiredFalse = [
    'request_site_id_grants_authority',
    'default_cross_site_relations',
    'site_clone_preserves_environment_identity',
    'network_template_implies_shared_runtime_data',
];
foreach ($requiredFalse as $rule) {
    if (($multisite[$rule] ?? null) !== false) {
        $errors[] = "Multisite guard {$rule} must be false.";
    }
}
if (($multisite['cross_site_access_requires_network_policy'] ?? null) !== true) {
    $errors[] = 'Cross-site access must require explicit network policy.';
}

foreach (($operations['invalidation_owners'] ?? []) as $entry) {
    if (!is_array($entry)) {
        $errors[] = 'Invalidation owner entry must be an object.';
        continue;
    }
    assertSurfaceRefs([$entry['surface'] ?? null], $surfaceIds, 'invalidation owner', $errors);
    if (($entry['source_change_revalidation'] ?? null) !== true) {
        $errors[] = 'Derived caches/indexes must declare source-change revalidation.';
    }
}

foreach (($operations['providers'] ?? []) as $provider) {
    if (!is_array($provider) || ($provider['external_authority'] ?? null) !== true) {
        $errors[] = 'Provider contract must preserve external authority.';
        continue;
    }
    if (($provider['unknown_outcome_first_class'] ?? null) !== true) {
        $errors[] = 'Provider contract must model unknown outcome explicitly.';
    }
    if (isset($provider['surface'])) {
        assertSurfaceRefs([$provider['surface']], $surfaceIds, 'provider surface', $errors);
    }
    if (isset($provider['transport_owner']) && $provider['transport_owner'] !== 23) {
        $errors[] = 'Generic external transport owner must be Surface 23 Connections.';
    }
}

foreach (($operations['destructive_operations'] ?? []) as $operation) {
    if (!is_array($operation) || ($operation['impact_preview'] ?? null) !== true || empty($operation['recovery'])) {
        $errors[] = 'Every destructive operation requires impact preview and recovery contract.';
        continue;
    }
    if (isset($operation['surface'])) {
        assertSurfaceRefs([$operation['surface']], $surfaceIds, 'destructive operation owner', $errors);
    }
}

$ai = $operations['ai'] ?? [];
foreach (['raw_php_sql_shell', 'vault_secret_prompt_context', 'hidden_privileged_ability_path', 'model_args_trusted'] as $mustBeFalse) {
    if (($ai[$mustBeFalse] ?? null) !== false) {
        $errors[] = "AI guard {$mustBeFalse} must be false.";
    }
}
foreach (['same_principal_policy_required', 'mutation_requires_explicit_ability_allowlist'] as $mustBeTrue) {
    if (($ai[$mustBeTrue] ?? null) !== true) {
        $errors[] = "AI guard {$mustBeTrue} must be true.";
    }
}
assertSurfaceRefs([$ai['surface'] ?? null], $surfaceIds, 'AI owner', $errors);

if ($errors !== []) {
    fwrite(STDERR, "WPEssential architecture guard FAILED\n");
    foreach ($errors as $error) {
        fwrite(STDERR, " - {$error}\n");
    }
    exit(1);
}

fwrite(STDOUT, "WPEssential architecture guard PASS\n");
fwrite(STDOUT, " - 56/56 canonical surfaces\n");
fwrite(STDOUT, " - unique keys/routes/suite ownership\n");
fwrite(STDOUT, " - semantic owners + competitive overlay no-bypass rules\n");
fwrite(STDOUT, " - P01..P40 canonical system pattern routing\n");
fwrite(STDOUT, " - Ability/storage/Multisite/invalidation/provider/destructive/AI guards\n");
