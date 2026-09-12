<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$contract = json_decode((string) file_get_contents($root . '/config/product/option-contracts/settings.json'), true, 512, JSON_THROW_ON_ERROR);
$ux = (string) file_get_contents($root . '/docs/UI/SETTINGS-PAGES-UX-CONTRACT-V1.md');

if (($contract['surface_id'] ?? null) !== 12 || ($contract['surface_key'] ?? null) !== 'settings') {
    throw new RuntimeException('Settings UX contract identity mismatch.');
}
$allowed = ['OPTION_CONTRACT_COMPLETE', 'UX_CONTRACT_COMPLETE', 'RUNTIME_CERTIFIED', 'PRODUCT_PARITY_CERTIFIED'];
if (!in_array($contract['status'] ?? null, $allowed, true)) {
    throw new RuntimeException('Settings machine contract is below OPTION_CONTRACT_COMPLETE.');
}
$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage) || ($coverage['missing'] ?? null) !== 0 || ($coverage['unclassified'] ?? null) !== 0) {
    throw new RuntimeException('Settings coverage must remain missing=0 and unclassified=0.');
}
$ids = [];
foreach (($contract['feature_groups'] ?? []) as $group) {
    foreach (($group['options'] ?? []) as $option) {
        $id = $option['id'] ?? null;
        if (!is_string($id) || $id === '' || isset($ids[$id])) {
            throw new RuntimeException('Settings Atomic Option IDs are invalid or duplicated.');
        }
        $ids[$id] = true;
    }
}
if (count($ids) !== 17) {
    throw new RuntimeException('Settings UX certification expects exactly 17 current Atomic Options.');
}
$headings = ['## Lifecycle preconditions','## Canonical route and information architecture','## UX state classes','## Atomic Option → UX map','## Interaction and persistence','## Loading, empty, validation, conflict and recovery','## Security and ownership','## Accessibility','## Multisite and scope','## Portability and reference remapping','## Performance and scale','## Degraded/provider states','## UX lifecycle exit criteria','## Non-certifications'];
foreach ($headings as $heading) {
    if (!str_contains($ux, $heading)) {
        throw new RuntimeException("Settings UX contract missing heading: {$heading}");
    }
}
foreach (array_keys($ids) as $id) {
    if (substr_count($ux, '- `' . $id . '` —') !== 1) {
        throw new RuntimeException("Settings UX map must contain exactly one entry for {$id}.");
    }
}
$invariants = [
    'Secret material is never displayed or persisted here.',
    'Fields owns reusable field schema/validation semantics.',
    'Network scope is derived from trusted server context.',
    'Reset and migration are privileged high-impact actions',
    'Runtime implementation is not certified by this document.',
    'Product parity is not certified.',
    'Production deployment or release is not verified.',
];
foreach ($invariants as $needle) {
    if (!str_contains($ux, $needle)) {
        throw new RuntimeException("Settings UX invariant missing: {$needle}");
    }
}
foreach (['Runtime implementation: COMPLETE','Product parity: CERTIFIED','Production deployment: VERIFIED'] as $claim) {
    if (str_contains($ux, $claim)) {
        throw new RuntimeException("Settings UX contract makes premature claim: {$claim}");
    }
}

echo "Settings Pages UX Contract V1: PASS\n";
