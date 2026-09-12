<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$contract = json_decode((string) file_get_contents($root . '/config/product/option-contracts/notifications.json'), true, 512, JSON_THROW_ON_ERROR);
$ux = (string) file_get_contents($root . '/docs/UI/NOTIFICATIONS-UX-CONTRACT-V1.md');

if (($contract['surface_id'] ?? null) !== 19 || ($contract['surface_key'] ?? null) !== 'notifications') {
    throw new RuntimeException('Notifications UX identity mismatch.');
}
$allowed = ['OPTION_CONTRACT_COMPLETE','UX_CONTRACT_COMPLETE','RUNTIME_CERTIFIED','PRODUCT_PARITY_CERTIFIED'];
if (!in_array($contract['status'] ?? null, $allowed, true)) {
    throw new RuntimeException('Notifications contract is below OPTION_CONTRACT_COMPLETE.');
}
$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage) || ($coverage['missing'] ?? null) !== 0 || ($coverage['unclassified'] ?? null) !== 0) {
    throw new RuntimeException('Notifications coverage must remain complete.');
}
$ids = [];
foreach (($contract['feature_groups'] ?? []) as $group) {
    foreach (($group['options'] ?? []) as $option) {
        $id = $option['id'] ?? null;
        if (!is_string($id) || $id === '' || isset($ids[$id])) {
            throw new RuntimeException('Notifications Atomic Option ID failure.');
        }
        $ids[$id] = true;
    }
}
if (count($ids) !== 16) {
    throw new RuntimeException('Notifications UX certification expects 16 Atomic Options.');
}
$headings = ['## Lifecycle preconditions','## Canonical route and information architecture','## UX state classes','## Atomic Option → UX map','## Interaction and persistence','## Loading, empty, validation, conflict and recovery','## Security and ownership','## Accessibility','## Multisite and scope','## Portability and reference remapping','## Performance and scale','## Degraded/provider states','## UX lifecycle exit criteria','## Non-certifications'];
foreach ($headings as $heading) {
    if (!str_contains($ux, $heading)) {
        throw new RuntimeException("Notifications UX missing heading: {$heading}");
    }
}
foreach (array_keys($ids) as $id) {
    if (substr_count($ux, '- `' . $id . '` —') !== 1) {
        throw new RuntimeException("Notifications UX map must contain exactly one {$id} entry.");
    }
}
$invariants = [
    'Raw user-authored hooks/callbacks are prohibited.',
    'Delivery evidence is not user receipt evidence',
    'No editor preview dispatches externally.',
    'The surface never fabricates eligible recipients, schedule execution, delivery, receipt or read state.',
    'Runtime implementation is not certified by this document.',
    'Product parity is not certified.',
    'Production deployment or release is not verified.',
];
foreach ($invariants as $needle) {
    if (!str_contains($ux, $needle)) {
        throw new RuntimeException("Notifications UX invariant missing: {$needle}");
    }
}
foreach (['Runtime implementation: COMPLETE','Product parity: CERTIFIED','Production deployment: VERIFIED'] as $claim) {
    if (str_contains($ux, $claim)) {
        throw new RuntimeException("Notifications UX premature claim: {$claim}");
    }
}

echo "Notifications UX Contract V1: PASS\n";
