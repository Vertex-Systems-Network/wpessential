<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$contractPath = $root . '/config/product/option-contracts/admin-menu.json';
$uxPath = $root . '/docs/UI/ADMIN-MENU-UX-CONTRACT-V1.md';

$contract = json_decode((string) file_get_contents($contractPath), true, 512, JSON_THROW_ON_ERROR);
$ux = (string) file_get_contents($uxPath);

if (($contract['surface_id'] ?? null) !== 11 || ($contract['surface_key'] ?? null) !== 'admin-menu') {
    throw new RuntimeException('Admin Menu UX contract identity mismatch.');
}

$allowedStatuses = ['OPTION_CONTRACT_COMPLETE', 'UX_CONTRACT_COMPLETE', 'RUNTIME_CERTIFIED', 'PRODUCT_PARITY_CERTIFIED'];
if (!in_array($contract['status'] ?? null, $allowedStatuses, true)) {
    throw new RuntimeException('Admin Menu machine contract is below OPTION_CONTRACT_COMPLETE.');
}

$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage) || ($coverage['missing'] ?? null) !== 0 || ($coverage['unclassified'] ?? null) !== 0) {
    throw new RuntimeException('Admin Menu machine coverage must remain missing=0 and unclassified=0.');
}

$ids = [];
foreach (($contract['feature_groups'] ?? []) as $group) {
    foreach (($group['options'] ?? []) as $option) {
        $id = $option['id'] ?? null;
        if (!is_string($id) || $id === '' || isset($ids[$id])) {
            throw new RuntimeException('Admin Menu Atomic Option IDs are invalid or duplicated.');
        }
        $ids[$id] = true;
    }
}
if (count($ids) !== 16) {
    throw new RuntimeException('Admin Menu UX certification expects exactly 16 current Atomic Options.');
}

$requiredHeadings = [
    '## Lifecycle preconditions',
    '## Canonical route and information architecture',
    '## UX state classes',
    '## Atomic Option → UX map',
    '## Interaction and persistence',
    '## Loading, empty, validation, conflict and recovery',
    '## Security and ownership',
    '## Accessibility',
    '## Multisite and scope',
    '## Portability and reference remapping',
    '## Performance and scale',
    '## Degraded/provider states',
    '## UX lifecycle exit criteria',
    '## Non-certifications',
];
foreach ($requiredHeadings as $heading) {
    if (!str_contains($ux, $heading)) {
        throw new RuntimeException("Admin Menu UX contract missing heading: {$heading}");
    }
}

foreach (array_keys($ids) as $id) {
    $marker = '- `' . $id . '` —';
    if (substr_count($ux, $marker) !== 1) {
        throw new RuntimeException("Admin Menu UX map must contain exactly one entry for {$id}.");
    }
}

$requiredSafety = [
    'Visibility is presentation behavior, never authorization.',
    'unsafe URL schemes are prohibited',
    'Preview must not impersonate',
    'Network scope is derived from trusted server context',
    'Runtime implementation is not certified by this document.',
    'Product parity is not certified.',
    'Production deployment or release is not verified.',
];
foreach ($requiredSafety as $needle) {
    if (!str_contains($ux, $needle)) {
        throw new RuntimeException("Admin Menu UX safety invariant missing: {$needle}");
    }
}

$forbiddenClaims = [
    'Runtime implementation: COMPLETE',
    'Product parity: CERTIFIED',
    'Production deployment: VERIFIED',
];
foreach ($forbiddenClaims as $claim) {
    if (str_contains($ux, $claim)) {
        throw new RuntimeException("Admin Menu UX contract makes premature certification claim: {$claim}");
    }
}

echo "Admin Menu UX Contract V1: PASS\n";
