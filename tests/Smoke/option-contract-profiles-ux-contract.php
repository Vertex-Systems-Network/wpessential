<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$contract = json_decode((string) file_get_contents($root . '/config/product/option-contracts/profiles.json'), true, 512, JSON_THROW_ON_ERROR);
$ux = (string) file_get_contents($root . '/docs/UI/USER-PROFILE-UX-CONTRACT-V1.md');

if (($contract['surface_id'] ?? null) !== 14 || ($contract['surface_key'] ?? null) !== 'profiles') {
    throw new RuntimeException('User Profiles UX identity mismatch.');
}
$allowed = ['OPTION_CONTRACT_COMPLETE','UX_CONTRACT_COMPLETE','RUNTIME_CERTIFIED','PRODUCT_PARITY_CERTIFIED'];
if (!in_array($contract['status'] ?? null, $allowed, true)) {
    throw new RuntimeException('Profiles machine contract is below OPTION_CONTRACT_COMPLETE.');
}
$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage) || ($coverage['missing'] ?? null) !== 0 || ($coverage['unclassified'] ?? null) !== 0) {
    throw new RuntimeException('Profiles coverage must remain complete.');
}
$ids = [];
foreach (($contract['feature_groups'] ?? []) as $group) {
    foreach (($group['options'] ?? []) as $option) {
        $id = $option['id'] ?? null;
        if (!is_string($id) || $id === '' || isset($ids[$id])) {
            throw new RuntimeException('Profiles Atomic Option ID failure.');
        }
        $ids[$id] = true;
    }
}
if (count($ids) !== 17) {
    throw new RuntimeException('Profiles UX certification expects 17 Atomic Options.');
}
$headings = ['## Lifecycle preconditions','## Canonical route and information architecture','## UX state classes','## Atomic Option → UX map','## Interaction and persistence','## Loading, empty, validation, conflict and recovery','## Security and ownership','## Accessibility','## Multisite and scope','## Portability and reference remapping','## Performance and scale','## Degraded/provider states','## UX lifecycle exit criteria','## Non-certifications'];
foreach ($headings as $heading) {
    if (!str_contains($ux, $heading)) {
        throw new RuntimeException("Profiles UX missing heading: {$heading}");
    }
}
foreach (array_keys($ids) as $id) {
    if (substr_count($ux, '- `' . $id . '` —') !== 1) {
        throw new RuntimeException("Profiles UX map must contain exactly one {$id} entry.");
    }
}
$invariants = [
    'Roles/Policy owns role and capability grants.',
    'Fields owns field schema/value validation.',
    'Self-service requests cannot elevate to admin scope from client payload.',
    'The surface never fabricates a role grant, verification result, media authorization or privacy completion.',
    'Runtime implementation is not certified by this document.',
    'Product parity is not certified.',
    'Production deployment or release is not verified.',
];
foreach ($invariants as $needle) {
    if (!str_contains($ux, $needle)) {
        throw new RuntimeException("Profiles UX invariant missing: {$needle}");
    }
}
foreach (['Runtime implementation: COMPLETE','Product parity: CERTIFIED','Production deployment: VERIFIED'] as $claim) {
    if (str_contains($ux, $claim)) {
        throw new RuntimeException("Profiles UX premature claim: {$claim}");
    }
}

echo "User Profiles UX Contract V1: PASS\n";
