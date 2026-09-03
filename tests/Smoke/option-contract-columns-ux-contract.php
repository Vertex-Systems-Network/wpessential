<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$uxPath = $root . '/docs/PRODUCT/ADMIN-COLUMNS-UX-CONTRACT-V1.md';
$contractPath = $root . '/config/product/option-contracts/columns.json';

if (!is_file($uxPath)) {
    throw new RuntimeException('Admin Columns UX contract is missing.');
}

$ux = file_get_contents($uxPath);
if ($ux === false || trim($ux) === '') {
    throw new RuntimeException('Admin Columns UX contract is unreadable or empty.');
}

if (!is_file($contractPath)) {
    throw new RuntimeException('Admin Columns Atomic Option contract is missing.');
}

$contractContents = file_get_contents($contractPath);
if ($contractContents === false) {
    throw new RuntimeException('Unable to read Admin Columns Atomic Option contract.');
}

try {
    $contract = json_decode($contractContents, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    throw new RuntimeException('Admin Columns Atomic Option contract JSON is invalid.', 0, $exception);
}

if (!is_array($contract)
    || ($contract['surface_id'] ?? null) !== 8
    || ($contract['surface_key'] ?? null) !== 'columns'
) {
    throw new RuntimeException('Admin Columns Atomic Option contract identity is invalid.');
}

$status = $contract['status'] ?? null;
if (!in_array($status, ['OPTION_CONTRACT_COMPLETE', 'UX_CONTRACT_COMPLETE'], true)) {
    throw new RuntimeException('Admin Columns UX contract requires OPTION_CONTRACT_COMPLETE or later machine truth.');
}

$coverage = $contract['coverage_summary'] ?? null;
if (!is_array($coverage)
    || ($coverage['missing'] ?? null) !== 0
    || ($coverage['unclassified'] ?? null) !== 0
) {
    throw new RuntimeException('Admin Columns UX contract requires zero missing/unclassified Atomic semantics.');
}

$projection = $contract['source_projection'] ?? null;
if (!is_array($projection)
    || ($projection['source_record_count'] ?? null) !== 214
    || !is_array($projection['entries'] ?? null)
    || count($projection['entries']) !== 214
) {
    throw new RuntimeException('Admin Columns UX contract requires complete 214-record source projection.');
}

$requiredHeadings = [
    '# WPEssential — Admin Columns UX Contract V1',
    '## 2. Canonical route and navigation',
    '## 3. UX state classes',
    '### 3.1 Authored definition',
    '### 3.2 Personal preference',
    '### 3.3 Effective/runtime state',
    '### 3.4 Diagnostic state',
    '### 3.5 Deferred / prohibited state',
    '## 4. Column Sets collection',
    '## 5. View / Column Set editor',
    '## 6. Column editor',
    '## 7. Sorting, filtering and search',
    '## 8. Editing and actions',
    '## 9. Conditional formatting and visibility',
    '## 10. Export UX',
    '## 11. Performance UX',
    '## 12. Adapters and degraded states',
    '## 13. Portability and dependencies',
    '## 14. Multisite and scope',
    '## 15. Accessibility contract',
    '## 16. Loading, empty, error and recovery states',
    '## 17. Security UX invariants',
    '## 18. Performance and scale acceptance targets',
    '## 19. UX lifecycle exit criteria',
    '## 20. Non-certifications',
];

foreach ($requiredHeadings as $heading) {
    if (!str_contains($ux, $heading)) {
        throw new RuntimeException(sprintf('Admin Columns UX contract is missing required heading: %s', $heading));
    }
}

$requiredPhrases = [
    'Data & Intelligence',
    'Admin Columns',
    'Column Sets',
    'Segments',
    'Adapters',
    'Diagnostics',
    'User-scoped state that MUST NOT mutate the shared View definition',
    'Effective state is never presented as an editable setting',
    'Arbitrary executable PHP/JavaScript source remains **Prohibited**',
    'Surface 8 MUST NOT become a private query engine',
    'all rows matching the current canonical backend query',
    'color MUST NOT be the only carrier of meaning',
    'Visibility is presentation behavior only',
    'hiding a Column, row action, View or Segment be treated as revoking access',
    'formula-injection mitigation',
    'no-N+1 evidence',
    'WooCommerce order data MUST use supported WooCommerce/storage adapters',
    'DataViews compatibility',
    'Site/network/user scope is derived from trusted runtime context',
    'accessible reorder alternative to pointer-only drag/drop',
    'failed save with retained user input',
    'client-provided site/network scope become authority',
    'runtime implementation',
    'production deployment/release',
];

foreach ($requiredPhrases as $phrase) {
    if (!str_contains($ux, $phrase)) {
        throw new RuntimeException(sprintf('Admin Columns UX contract is missing required invariant: %s', $phrase));
    }
}

$forbiddenClaims = [
    'Status: **UX_CONTRACT_COMPLETE**',
    'Runtime implementation: **COMPLETE**',
    'product parity certified',
    'production verified',
];
foreach ($forbiddenClaims as $claim) {
    if (str_contains($ux, $claim)) {
        throw new RuntimeException(sprintf('Admin Columns UX contract makes a premature certification claim: %s', $claim));
    }
}

$stateClassOrder = [
    '### 3.1 Authored definition',
    '### 3.2 Personal preference',
    '### 3.3 Effective/runtime state',
    '### 3.4 Diagnostic state',
    '### 3.5 Deferred / prohibited state',
];
$lastPosition = -1;
foreach ($stateClassOrder as $heading) {
    $position = strpos($ux, $heading);
    if ($position === false || $position <= $lastPosition) {
        throw new RuntimeException('Admin Columns UX state classes are missing or out of canonical order.');
    }
    $lastPosition = $position;
}

fwrite(
    STDOUT,
    "Admin Columns UX contract: PASS (IA/state/safety/accessibility/portability/degraded-state contract present; runtime unclaimed).\n",
);
