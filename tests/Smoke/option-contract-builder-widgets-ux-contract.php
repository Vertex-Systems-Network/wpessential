<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$contract = json_decode((string) file_get_contents($root . '/config/product/option-contracts/builder-widgets.json'), true, 512, JSON_THROW_ON_ERROR);
$ux = (string) file_get_contents($root . '/docs/UI/BUILDER-WIDGETS-UX-CONTRACT-V1.md');

if (($contract['surface_id'] ?? null) !== 16 || ($contract['surface_key'] ?? null) !== 'builder-widgets') {
    throw new RuntimeException('Builder Widgets UX identity mismatch.');
}
$allowed = ['OPTION_CONTRACT_COMPLETE','UX_CONTRACT_COMPLETE','RUNTIME_CERTIFIED','PRODUCT_PARITY_CERTIFIED'];
if (!in_array($contract['status'] ?? null, $allowed, true)) {
    throw new RuntimeException('Builder Widgets contract is below OPTION_CONTRACT_COMPLETE.');
}
$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage) || ($coverage['missing'] ?? null) !== 0 || ($coverage['unclassified'] ?? null) !== 0) {
    throw new RuntimeException('Builder Widgets coverage must remain complete.');
}
$ids = [];
foreach (($contract['feature_groups'] ?? []) as $group) {
    foreach (($group['options'] ?? []) as $option) {
        $id = $option['id'] ?? null;
        if (!is_string($id) || $id === '' || isset($ids[$id])) {
            throw new RuntimeException('Builder Widgets Atomic Option ID failure.');
        }
        $ids[$id] = true;
    }
}
if (count($ids) !== 18) {
    throw new RuntimeException('Builder Widgets UX certification expects 18 Atomic Options.');
}
$headings = ['## Lifecycle preconditions','## Canonical route and information architecture','## UX state classes','## Atomic Option → UX map','## Interaction and persistence','## Loading, empty, validation, conflict and recovery','## Security and ownership','## Accessibility','## Multisite and scope','## Portability and reference remapping','## Performance and scale','## Degraded/provider states','## UX lifecycle exit criteria','## Non-certifications'];
foreach ($headings as $heading) {
    if (!str_contains($ux, $heading)) {
        throw new RuntimeException("Builder Widgets UX missing heading: {$heading}");
    }
}
foreach (array_keys($ids) as $id) {
    if (substr_count($ux, '- `' . $id . '` —') !== 1) {
        throw new RuntimeException("Builder Widgets UX map must contain exactly one {$id} entry.");
    }
}
$invariants = [
    'Fields owns reusable controls/schema;',
    'Arbitrary executable callbacks are prohibited.',
    'Client preview state never authorizes a server action.',
    'The surface never fabricates data, authorization, renderer success or adapter parity.',
    'Runtime implementation is not certified by this document.',
    'Product parity is not certified.',
    'Production deployment or release is not verified.',
];
foreach ($invariants as $needle) {
    if (!str_contains($ux, $needle)) {
        throw new RuntimeException("Builder Widgets UX invariant missing: {$needle}");
    }
}
foreach (['Runtime implementation: COMPLETE','Product parity: CERTIFIED','Production deployment: VERIFIED'] as $claim) {
    if (str_contains($ux, $claim)) {
        throw new RuntimeException("Builder Widgets UX premature claim: {$claim}");
    }
}

echo "Builder Widgets UX Contract V1: PASS\n";
