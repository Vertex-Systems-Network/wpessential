<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$bankDirectory = $root . '/config/product/options-bank';
$surfaceRegistryPath = $root . '/config/product/competitor-parity-surfaces.json';
$schemaPath = $root . '/config/product/options-bank.schema.json';
$semanticSchemaPath = $root . '/config/product/options-bank-semantic-relations.schema.json';
$semanticRegistryPath = $root . '/config/product/options-bank-semantic-relations.json';

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
    'NATIVE_HARD', 'WPE_HARD', 'SOFT_NATIVE', 'PROVIDER_SOFT', 'COMPATIBILITY',
    'EXPERT', 'EXPERIMENTAL', 'DEFERRED', 'REJECTED_UNSAFE', 'WPE_EXCEED',
];
$allowedHardSoft = ['HARD', 'SOFT', 'HYBRID'];
$allowedHorizons = ['CURRENT_NATIVE', 'CURRENT_MARKET', 'WPE_FUTURE'];
$allowedAdoption = [
    'UNREVIEWED', 'MUST_HAVE', 'PARITY', 'COMPETITIVE', 'WPE_EXCEED',
    'PROVIDER', 'EXPERT_ONLY', 'LATER', 'REJECT',
];
$allowedPriorities = [
    'P0_NATIVE', 'P0_PARITY', 'P1_CORE', 'P1_EXCEED',
    'P2_COMPETITIVE', 'P3_LATER', 'NOT_SCHEDULED',
];
$allowedSemanticRelations = ['ALIAS', 'EFFECTIVE_DERIVATION'];

// Keep the canonical discovery schemas parseable without introducing another
// runtime dependency merely to validate JSON Schema during a smoke test.
readJsonObject($schemaPath);
readJsonObject($semanticSchemaPath);

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

/** @var array<string, array<string, true>> $surfaceRecordIds */
$surfaceRecordIds = [];
/** @var array<string, array<string, true>> $surfaceOptionPaths */
$surfaceOptionPaths = [];
/** @var list<array{file:string,surface:string,id:string,parent:string}> $parentReferences */
$parentReferences = [];
/** @var array<string, true> $seededSurfaces */
$seededSurfaces = [];
$totalRecords = 0;

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

    $filename = pathinfo($file, PATHINFO_FILENAME);
    if ($filename !== $surfaceKey && !str_starts_with($filename, $surfaceKey . '--')) {
        throw new RuntimeException(sprintf(
            '%s filename must be %s.json or start with %s--.',
            $file,
            $surfaceKey,
            $surfaceKey,
        ));
    }
    $seededSurfaces[$surfaceKey] = true;

    $records = $bank['records'] ?? null;
    if (!is_array($records) || $records === []) {
        throw new RuntimeException(sprintf('%s has no Bank records.', $file));
    }

    $unreviewed = 0;
    foreach ($records as $index => $record) {
        if (!is_array($record)) {
            throw new RuntimeException(sprintf('%s record %d is invalid.', $file, $index));
        }

        $recordId = requireString($record['id'] ?? null, sprintf('%s record %d has no id.', $file, $index));
        requireString($record['feature_group'] ?? null, sprintf('%s record %s has no feature_group.', $file, $recordId));
        $optionPath = requireString($record['option_path'] ?? null, sprintf('%s record %s has no option_path.', $file, $recordId));
        requireString($record['label'] ?? null, sprintf('%s record %s has no label.', $file, $recordId));

        if (!str_starts_with($recordId, $surfaceKey . '.')) {
            throw new RuntimeException(sprintf('%s record id %s must start with %s.', $file, $recordId, $surfaceKey . '.'));
        }
        if (isset($surfaceRecordIds[$surfaceKey][$recordId])) {
            throw new RuntimeException(sprintf('%s duplicates record id %s across Bank shards.', $file, $recordId));
        }
        if (isset($surfaceOptionPaths[$surfaceKey][$optionPath])) {
            throw new RuntimeException(sprintf('%s duplicates option_path %s across Bank shards.', $file, $optionPath));
        }
        $surfaceRecordIds[$surfaceKey][$recordId] = true;
        $surfaceOptionPaths[$surfaceKey][$optionPath] = true;

        $classification = requireString($record['classification'] ?? null, sprintf('%s record %s has no classification.', $file, $recordId));
        $hardSoft = requireString($record['hard_soft'] ?? null, sprintf('%s record %s has no hard_soft.', $file, $recordId));
        $horizon = requireString($record['horizon'] ?? null, sprintf('%s record %s has no horizon.', $file, $recordId));
        $adoption = requireString($record['adoption'] ?? null, sprintf('%s record %s has no adoption.', $file, $recordId));
        $priority = requireString($record['priority'] ?? null, sprintf('%s record %s has no priority.', $file, $recordId));

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

        $parentId = $record['parent_id'] ?? null;
        if ($parentId !== null) {
            if (!is_string($parentId) || trim($parentId) === '') {
                throw new RuntimeException(sprintf('%s record %s has invalid parent_id.', $file, $recordId));
            }
            $parentReferences[] = [
                'file' => $file,
                'surface' => $surfaceKey,
                'id' => $recordId,
                'parent' => $parentId,
            ];
        }
    }

    $coverage = $bank['coverage'] ?? null;
    if (!is_array($coverage)) {
        throw new RuntimeException(sprintf('%s is missing coverage.', $file));
    }

    $declaredRecords = requireInteger($coverage['records'] ?? null, sprintf('%s coverage.records must be an integer.', $file));
    $declaredUnreviewed = requireInteger($coverage['unreviewed'] ?? null, sprintf('%s coverage.unreviewed must be an integer.', $file));
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

foreach ($parentReferences as $reference) {
    if (!isset($surfaceRecordIds[$reference['surface']][$reference['parent']])) {
        throw new RuntimeException(sprintf(
            '%s record %s references missing parent %s across surface %s shards.',
            $reference['file'],
            $reference['id'],
            $reference['parent'],
            $reference['surface'],
        ));
    }
}

$semanticRegistry = readJsonObject($semanticRegistryPath);
if (($semanticRegistry['schema_version'] ?? null) !== 1 || ($semanticRegistry['bank_version'] ?? null) !== 'v1') {
    throw new RuntimeException('Options Bank semantic relationship registry has an unsupported version.');
}
requireString(
    $semanticRegistry['snapshot_date'] ?? null,
    'Options Bank semantic relationship registry is missing snapshot_date.',
);

$semanticRelationships = $semanticRegistry['relationships'] ?? null;
if (!is_array($semanticRelationships)) {
    throw new RuntimeException('Options Bank semantic relationship registry is missing relationships.');
}

/** @var array<string, array<string, true>> $semanticSources */
$semanticSources = [];
/** @var array<string, array<string, string>> $aliasTargets */
$aliasTargets = [];
foreach ($semanticRelationships as $index => $relationship) {
    if (!is_array($relationship)) {
        throw new RuntimeException(sprintf('Semantic relationship %d is invalid.', $index));
    }

    $surfaceKey = requireString(
        $relationship['surface'] ?? null,
        sprintf('Semantic relationship %d has no surface.', $index),
    );
    $sourceId = requireString(
        $relationship['source_id'] ?? null,
        sprintf('Semantic relationship %d has no source_id.', $index),
    );
    $targetId = requireString(
        $relationship['target_id'] ?? null,
        sprintf('Semantic relationship %d has no target_id.', $index),
    );
    $relation = requireString(
        $relationship['relation'] ?? null,
        sprintf('Semantic relationship %d has no relation.', $index),
    );
    requireString(
        $relationship['rationale'] ?? null,
        sprintf('Semantic relationship %d has no rationale.', $index),
    );

    if (!in_array($relation, $allowedSemanticRelations, true)) {
        throw new RuntimeException(sprintf(
            'Semantic relationship %s -> %s has invalid relation %s.',
            $sourceId,
            $targetId,
            $relation,
        ));
    }
    if (!isset($idsByKey[$surfaceKey])) {
        throw new RuntimeException(sprintf('Semantic relationship references unknown surface %s.', $surfaceKey));
    }
    if ($sourceId === $targetId) {
        throw new RuntimeException(sprintf('Semantic relationship %s cannot target itself.', $sourceId));
    }
    if (!isset($surfaceRecordIds[$surfaceKey][$sourceId])) {
        throw new RuntimeException(sprintf(
            'Semantic relationship source %s does not exist on surface %s.',
            $sourceId,
            $surfaceKey,
        ));
    }
    if (!isset($surfaceRecordIds[$surfaceKey][$targetId])) {
        throw new RuntimeException(sprintf(
            'Semantic relationship target %s does not exist on surface %s.',
            $targetId,
            $surfaceKey,
        ));
    }
    if (isset($semanticSources[$surfaceKey][$sourceId])) {
        throw new RuntimeException(sprintf(
            'Semantic relationship source %s is mapped more than once on surface %s.',
            $sourceId,
            $surfaceKey,
        ));
    }
    $semanticSources[$surfaceKey][$sourceId] = true;

    if ($relation === 'ALIAS') {
        $aliasTargets[$surfaceKey][$sourceId] = $targetId;
    }
}

foreach ($aliasTargets as $surfaceKey => $targets) {
    foreach ($targets as $sourceId => $targetId) {
        if (isset($targets[$targetId])) {
            throw new RuntimeException(sprintf(
                'Semantic alias %s on surface %s must point directly to a canonical record, not alias %s.',
                $sourceId,
                $surfaceKey,
                $targetId,
            ));
        }
    }
}

fwrite(
    STDOUT,
    sprintf(
        "Master Options Bank contract: PASS (%d seeded surface(s), %d shard(s), %d record(s), %d semantic relationship(s)).\n",
        count($seededSurfaces),
        count($files),
        $totalRecords,
        count($semanticRelationships),
    ),
);
