<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$bankDirectory = $root . '/config/product/options-bank';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$schemaPath = $root . '/config/product/options-bank.schema.json';

/** @return array<string, mixed> */
function readJsonObject(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException(sprintf('Required JSON file is missing: %s', $path));
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        throw new RuntimeException(sprintf('Unable to read JSON file: %s', $path));
    }

    try {
        $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        throw new RuntimeException(
            sprintf('Invalid JSON in %s: %s', $path, $exception->getMessage()),
            0,
            $exception,
        );
    }

    if (!is_array($decoded)) {
        throw new RuntimeException(sprintf('JSON root must be an object: %s', $path));
    }

    return $decoded;
}

/** @param mixed $value */
function requireString($value, string $message): string
{
    if (!is_string($value) || trim($value) === '') {
        throw new RuntimeException($message);
    }

    return $value;
}

/** @param mixed $value */
function requireInteger($value, string $message): int
{
    if (!is_int($value)) {
        throw new RuntimeException($message);
    }

    return $value;
}

$allowedClassifications = [
    'NATIVE_HARD',
    'WPE_HARD',
    'SOFT_NATIVE',
    'PROVIDER_SOFT',
    'COMPATIBILITY',
    'EXPERT',
    'EXPERIMENTAL',
    'DEFERRED',
    'REJECTED_UNSAFE',
    'WPE_EXCEED',
];
$allowedHardSoft = ['HARD', 'SOFT', 'HYBRID'];
$allowedHorizons = ['CURRENT_NATIVE', 'CURRENT_MARKET', 'WPE_FUTURE'];
$allowedAdoption = [
    'UNREVIEWED',
    'MUST_HAVE',
    'PARITY',
    'COMPETITIVE',
    'WPE_EXCEED',
    'PROVIDER',
    'EXPERT_ONLY',
    'LATER',
    'REJECT',
];
$allowedPriorities = [
    'P0_NATIVE',
    'P0_PARITY',
    'P1_CORE',
    'P1_EXCEED',
    'P2_COMPETITIVE',
    'P3_LATER',
    'NOT_SCHEDULED',
];

// The schema itself must always remain parseable even though this smoke test
// intentionally validates critical invariants without adding a JSON-Schema dependency.
readJsonObject($schemaPath);

$registry = readJsonObject($surfaceRegistryPath);
$registeredSurfaces = $registry['surfaces'] ?? null;
if (!is_array($registeredSurfaces)) {
    throw new RuntimeException('Competitor-parity surface registry is missing surfaces.');
}

/** @var array<int, string> $keysById */
$keysById = [];
/** @var array<string, int> $idsByKey */
$idsByKey = [];
foreach ($registeredSurfaces as $surface) {
    if (!is_array($surface)) {
        throw new RuntimeException('Surface registry contains an invalid row.');
    }

    $id = requireInteger($surface['id'] ?? null, 'Surface registry row is missing an integer id.');
    $key = requireString($surface['key'] ?? null, 'Surface registry row is missing a key.');
    $keysById[$id] = $key;
    $idsByKey[$key] = $id;
}

$files = glob($bankDirectory . '/*.json');
if ($files === false || $files === []) {
    throw new RuntimeException('Master Options Bank has no seeded surface files.');
}

sort($files, SORT_STRING);
$totalRecords = 0;
$validatedSurfaces = [];

foreach ($files as $file) {
    $bank = readJsonObject($file);

    if (($bank['schema_version'] ?? null) !== 1 || ($bank['bank_version'] ?? null) !== 'v1') {
        throw new RuntimeException(sprintf('%s has an unsupported Bank version.', $file));
    }

    $surface = $bank['surface'] ?? null;
    if (!is_array($surface)) {
        throw new RuntimeException(sprintf('%s is missing the surface object.', $file));
    }

    $surfaceId = requireInteger($surface['id'] ?? null, sprintf('%s has no integer surface.id.', $file));
    $surfaceKey = requireString($surface['key'] ?? null, sprintf('%s has no surface.key.', $file));
    requireString($surface['name'] ?? null, sprintf('%s has no surface.name.', $file));

    if (($keysById[$surfaceId] ?? null) !== $surfaceKey || ($idsByKey[$surfaceKey] ?? null) !== $surfaceId) {
        throw new RuntimeException(sprintf(
            '%s surface id/key (%d/%s) does not match the canonical registry.',
            $file,
            $surfaceId,
            $surfaceKey,
        ));
    }

    if (pathinfo($file, PATHINFO_FILENAME) !== $surfaceKey) {
        throw new RuntimeException(sprintf('%s filename must match surface key %s.', $file, $surfaceKey));
    }

    if (isset($validatedSurfaces[$surfaceKey])) {
        throw new RuntimeException(sprintf('Surface %s is seeded more than once.', $surfaceKey));
    }
    $validatedSurfaces[$surfaceKey] = true;

    $records = $bank['records'] ?? null;
    if (!is_array($records) || $records === []) {
        throw new RuntimeException(sprintf('%s has no Bank records.', $file));
    }

    /** @var array<string, true> $recordIds */
    $recordIds = [];
    /** @var array<string, true> $optionPaths */
    $optionPaths = [];
    $unreviewed = 0;

    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            throw new RuntimeException(sprintf('%s record %d is invalid.', $file, $index));
        }

        $recordId = requireString($record['id'] ?? null, sprintf('%s record %d has no id.', $file, $index));
        $featureGroup = requireString(
            $record['feature_group'] ?? null,
            sprintf('%s record %s has no feature_group.', $file, $recordId),
        );
        $optionPath = requireString(
            $record['option_path'] ?? null,
            sprintf('%s record %s has no option_path.', $file, $recordId),
        );
        requireString($record['label'] ?? null, sprintf('%s record %s has no label.', $file, $recordId));

        if ($featureGroup === '') {
            throw new RuntimeException(sprintf('%s record %s has an empty feature group.', $file, $recordId));
        }
        if (!str_starts_with($recordId, $surfaceKey . '.')) {
            throw new RuntimeException(sprintf('%s record id %s must start with %s.', $file, $recordId, $surfaceKey . '.'));
        }
        if (isset($recordIds[$recordId])) {
            throw new RuntimeException(sprintf('%s duplicates record id %s.', $file, $recordId));
        }
        if (isset($optionPaths[$optionPath])) {
            throw new RuntimeException(sprintf('%s duplicates option_path %s.', $file, $optionPath));
        }
        $recordIds[$recordId] = true;
        $optionPaths[$optionPath] = true;

        $classification = requireString(
            $record['classification'] ?? null,
            sprintf('%s record %s has no classification.', $file, $recordId),
        );
        $hardSoft = requireString(
            $record['hard_soft'] ?? null,
            sprintf('%s record %s has no hard_soft classification.', $file, $recordId),
        );
        $horizon = requireString(
            $record['horizon'] ?? null,
            sprintf('%s record %s has no horizon.', $file, $recordId),
        );
        $adoption = requireString(
            $record['adoption'] ?? null,
            sprintf('%s record %s has no adoption decision.', $file, $recordId),
        );
        $priority = requireString(
            $record['priority'] ?? null,
            sprintf('%s record %s has no priority.', $file, $recordId),
        );

        if (!in_array($classification, $allowedClassifications, true)) {
            throw new RuntimeException(sprintf('%s record %s has invalid classification %s.', $file, $recordId, $classification));
        }
        if (!in_array($hardSoft, $allowedHardSoft, true)) {
            throw new RuntimeException(sprintf('%s record %s has invalid hard_soft %s.', $file, $recordId, $hardSoft));
        }
        if (!in_array($horizon, $allowedHorizons, true)) {
            throw new RuntimeException(sprintf('%s record %s has invalid horizon %s.', $file, $recordId, $horizon));
        }
        if (!in_array($adoption, $allowedAdoption, true)) {
            throw new RuntimeException(sprintf('%s record %s has invalid adoption %s.', $file, $recordId, $adoption));
        }
        if (!in_array($priority, $allowedPriorities, true)) {
            throw new RuntimeException(sprintf('%s record %s has invalid priority %s.', $file, $recordId, $priority));
        }

        if ($adoption === 'UNREVIEWED') {
            ++$unreviewed;
        }
    }

    // Parent references are checked after all IDs are known so ordering remains free.
    foreach ($records as $record) {
        if (!is_array($record)) {
            continue;
        }
        $parentId = $record['parent_id'] ?? null;
        if ($parentId !== null && (!is_string($parentId) || !isset($recordIds[$parentId]))) {
            throw new RuntimeException(sprintf(
                '%s record %s references missing parent %s.',
                $file,
                (string) ($record['id'] ?? '?'),
                is_scalar($parentId) ? (string) $parentId : '[invalid]',
            ));
        }
    }

    $coverage = $bank['coverage'] ?? null;
    if (!is_array($coverage)) {
        throw new RuntimeException(sprintf('%s is missing coverage.', $file));
    }

    $declaredRecords = requireInteger(
        $coverage['records'] ?? null,
        sprintf('%s coverage.records must be an integer.', $file),
    );
    $declaredUnreviewed = requireInteger(
        $coverage['unreviewed'] ?? null,
        sprintf('%s coverage.unreviewed must be an integer.', $file),
    );
    $declaredClassified = requireInteger(
        $coverage['adopted_or_classified'] ?? null,
        sprintf('%s coverage.adopted_or_classified must be an integer.', $file),
    );

    $recordCount = count($records);
    if ($declaredRecords !== $recordCount) {
        throw new RuntimeException(sprintf('%s declares %d records but contains %d.', $file, $declaredRecords, $recordCount));
    }
    if ($declaredUnreviewed !== $unreviewed) {
        throw new RuntimeException(sprintf('%s declares %d unreviewed records but contains %d.', $file, $declaredUnreviewed, $unreviewed));
    }
    if ($declaredClassified !== ($recordCount - $unreviewed)) {
        throw new RuntimeException(sprintf(
            '%s declares %d adopted/classified records but expected %d.',
            $file,
            $declaredClassified,
            $recordCount - $unreviewed,
        ));
    }

    $totalRecords += $recordCount;
}

fwrite(
    STDOUT,
    sprintf(
        "Master Options Bank contract: PASS (%d seeded surface(s), %d record(s)).\n",
        count($validatedSurfaces),
        $totalRecords,
    ),
);
