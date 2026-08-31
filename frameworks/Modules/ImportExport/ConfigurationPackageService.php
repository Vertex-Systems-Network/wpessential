<?php

declare(strict_types=1);

namespace WPEssential\Modules\ImportExport;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeImportAbilityRegistrar;
use WPEssential\Modules\Taxonomies\TaxonomyImportAbilityRegistrar;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class ConfigurationPackageService
{
    public function __construct(
        private AbilityRegistry $abilities,
        private DefinitionPackageCodec $codec,
    ) {}

    /** @param array<string,mixed> $input @return array<string,mixed> */
    public function export(array $input, ExecutionContext $context): array
    {
        $includeCpt = ($input['include_cpt'] ?? true) === true;
        $includeTaxonomy = ($input['include_taxonomy'] ?? true) === true;
        if (!$includeCpt && !$includeTaxonomy) {
            throw new InvalidArgumentException('Select at least one definition owner for export.');
        }

        $definitions = [];
        if ($includeCpt) {
            $definitions = array_merge($definitions, $this->ownerList('wpessential/cpt/list', $context));
        }
        if ($includeTaxonomy) {
            $definitions = array_merge($definitions, $this->ownerList('wpessential/taxonomy/list', $context));
        }

        $package = $this->codec->create($definitions);
        return [
            'package' => $package,
            'package_json' => $this->codec->encode($package),
            'package_checksum' => $this->codec->packageChecksum($package),
            'definition_count' => count($definitions),
        ];
    }

    /** @param array<string,mixed> $input @return array<string,mixed> */
    public function preflight(array $input, ExecutionContext $context): array
    {
        $json = $this->packageJson($input);
        $strategy = $this->strategy($input);
        $package = $this->codec->decode($json);
        $checksum = $this->codec->packageChecksum($package);
        $definitions = $package['definitions'];
        if (!is_array($definitions)) {
            throw new InvalidArgumentException('Definition package records are invalid.');
        }

        $targets = array_merge(
            $this->ownerList('wpessential/cpt/list', $context),
            $this->ownerList('wpessential/taxonomy/list', $context),
        );
        $targetsById = [];
        $targetsByOwnerKey = [];
        $targetsByTypeSlug = [];
        foreach ($targets as $target) {
            $id = $target['id'] ?? null;
            $owner = $target['owner_surface_id'] ?? null;
            $type = $target['type'] ?? null;
            $slug = $target['slug'] ?? null;
            $key = $this->definitionKey($target);
            if (is_string($id)) {
                $targetsById[$id] = $target;
            }
            if (is_int($owner) && $key !== null) {
                $targetsByOwnerKey[$owner . ':' . $key] = $target;
            }
            if (is_string($type) && is_string($slug)) {
                $targetsByTypeSlug[$type . ':' . $slug] = $target;
            }
        }

        $availableIds = array_fill_keys(array_keys($targetsById), true);
        foreach ($definitions as $record) {
            if (is_array($record) && is_string($record['id'] ?? null)) {
                $availableIds[$record['id']] = true;
            }
        }

        $items = [];
        $counts = [
            'create' => 0,
            'update' => 0,
            'no_change' => 0,
            'blocked' => 0,
        ];

        foreach ($definitions as $record) {
            if (!is_array($record) || array_is_list($record)) {
                throw new InvalidArgumentException('Definition package record is invalid.');
            }
            $item = $this->preflightRecord(
                $record,
                $strategy,
                $targetsById,
                $targetsByOwnerKey,
                $targetsByTypeSlug,
                $availableIds,
                $context,
            );
            $items[] = $item;
            $action = $item['action'] ?? 'blocked';
            if (is_string($action) && isset($counts[$action])) {
                ++$counts[$action];
            }
        }

        return [
            'valid' => $counts['blocked'] === 0,
            'strategy' => $strategy,
            'package_checksum' => $checksum,
            'counts' => $counts,
            'items' => $items,
        ];
    }

    /** @param array<string,mixed> $input @return array<string,mixed> */
    public function import(array $input, ExecutionContext $context): array
    {
        $json = $this->packageJson($input);
        $strategy = $this->strategy($input);
        $expectedChecksum = $input['expected_package_checksum'] ?? null;
        if (!is_string($expectedChecksum) || preg_match('/^[0-9a-f]{64}$/', $expectedChecksum) !== 1) {
            throw new InvalidArgumentException('Import requires the exact package checksum from a successful preflight.');
        }

        $preflight = $this->preflight([
            'package_json' => $json,
            'strategy' => $strategy,
        ], $context);
        if (($preflight['valid'] ?? null) !== true
            || !hash_equals($expectedChecksum, (string) ($preflight['package_checksum'] ?? ''))
        ) {
            throw new RuntimeException('Definition package changed or no longer passes preflight.');
        }

        $package = $this->codec->decode($json);
        $definitions = $package['definitions'];
        $items = $preflight['items'] ?? null;
        if (!is_array($definitions) || !is_array($items) || count($definitions) !== count($items)) {
            throw new RuntimeException('Definition package preflight result is inconsistent.');
        }

        $results = [];
        foreach ($definitions as $index => $record) {
            if (!is_array($record) || !is_array($items[$index] ?? null)) {
                throw new RuntimeException('Definition package import plan is invalid.');
            }
            $plan = $items[$index];
            if (($plan['action'] ?? null) === 'no_change') {
                $results[] = [
                    'id' => $record['id'] ?? '',
                    'action' => 'no_change',
                ];
                continue;
            }

            $ownerSurfaceId = $record['owner_surface_id'] ?? null;
            $ability = match ($ownerSurfaceId) {
                1 => CustomPostTypeImportAbilityRegistrar::ABILITY,
                2 => TaxonomyImportAbilityRegistrar::ABILITY,
                default => throw new RuntimeException('Definition package contains an unsupported owner surface.'),
            };
            $ownerInput = [
                'definition' => $record,
                'strategy' => $strategy,
            ];
            if (($plan['action'] ?? null) === 'update') {
                $revision = $plan['expected_revision'] ?? null;
                if (!is_int($revision) || $revision < 1) {
                    throw new RuntimeException('Definition package update plan is missing its target revision.');
                }
                $ownerInput['expected_revision'] = $revision;
            }
            $result = $this->abilities->execute($ability, $ownerInput, $context);
            if (!is_array($result) || !is_string($result['action'] ?? null)) {
                throw new RuntimeException('Definition owner returned an invalid import result.');
            }
            $results[] = [
                'id' => $record['id'] ?? '',
                'action' => $result['action'],
            ];
        }

        return [
            'package_checksum' => $expectedChecksum,
            'results' => $results,
            'counts' => $this->resultCounts($results),
        ];
    }

    /**
     * @param array<string,mixed> $record
     * @param array<string,array<string,mixed>> $targetsById
     * @param array<string,array<string,mixed>> $targetsByOwnerKey
     * @param array<string,array<string,mixed>> $targetsByTypeSlug
     * @param array<string,bool> $availableIds
     * @return array<string,mixed>
     */
    private function preflightRecord(
        array $record,
        string $strategy,
        array $targetsById,
        array $targetsByOwnerKey,
        array $targetsByTypeSlug,
        array $availableIds,
        ExecutionContext $context,
    ): array {
        $id = $record['id'] ?? null;
        $owner = $record['owner_surface_id'] ?? null;
        $type = $record['type'] ?? null;
        $slug = $record['slug'] ?? null;
        $key = $this->definitionKey($record);
        if (!is_string($id)
            || !is_int($owner)
            || !is_string($type)
            || !is_string($slug)
            || $key === null
        ) {
            return $this->blockedItem($record, 'unsupported_definition', 'Definition metadata is unsupported.');
        }

        foreach (($record['dependencies'] ?? []) as $dependency) {
            if (is_string($dependency) && !isset($availableIds[$dependency])) {
                return $this->blockedItem(
                    $record,
                    'missing_dependency',
                    sprintf('Required dependency %s is not present in the package or target site.', $dependency),
                );
            }
        }

        $existing = $targetsById[$id] ?? null;
        if (is_array($existing)) {
            if (($existing['owner_surface_id'] ?? null) !== $owner || ($existing['type'] ?? null) !== $type) {
                return $this->blockedItem($record, 'uuid_owner_conflict', 'Definition UUID is owned by a different target surface/type.');
            }
            if ($this->sameSemanticDefinition($existing, $record)) {
                return $this->planItem($record, 'no_change', 'Same UUID and semantic definition already exist.');
            }
            if ($strategy !== 'update_existing') {
                return $this->blockedItem($record, 'same_uuid_modified', 'Same UUID exists with different content; create-only mode will not overwrite it.');
            }
        }

        $keyCollision = $targetsByOwnerKey[$owner . ':' . $key] ?? null;
        if (is_array($keyCollision) && ($keyCollision['id'] ?? null) !== $id) {
            return $this->blockedItem(
                $record,
                'key_collision',
                sprintf('Runtime key "%s" belongs to a different target UUID.', $key),
            );
        }
        $slugCollision = $targetsByTypeSlug[$type . ':' . $slug] ?? null;
        if (is_array($slugCollision) && ($slugCollision['id'] ?? null) !== $id) {
            return $this->blockedItem(
                $record,
                'slug_collision',
                sprintf('Definition slug "%s" belongs to a different target UUID.', $slug),
            );
        }

        $validationAbility = match ($owner) {
            1 => 'wpessential/cpt/validate',
            2 => 'wpessential/taxonomy/validate',
            default => null,
        };
        if ($validationAbility === null) {
            return $this->blockedItem($record, 'unsupported_owner', 'Definition owner is outside the WP122 package scope.');
        }

        $validationInput = ['payload' => $record['payload'] ?? []];
        if (is_array($existing)) {
            $validationInput['id'] = $id;
        }
        try {
            $validation = $this->abilities->execute($validationAbility, $validationInput, $context);
        } catch (Throwable $exception) {
            return $this->blockedItem($record, 'validation_error', $exception->getMessage());
        }
        if (!is_array($validation) || ($validation['valid'] ?? null) !== true) {
            $message = 'Definition owner validation blocked this package record.';
            if (is_array($validation['issues'] ?? null)) {
                foreach ($validation['issues'] as $issue) {
                    if (is_array($issue)
                        && ($issue['severity'] ?? null) === 'blocked'
                        && is_string($issue['message'] ?? null)
                    ) {
                        $message = $issue['message'];
                        break;
                    }
                }
            }
            return $this->blockedItem($record, 'validation_blocked', $message);
        }

        $action = is_array($existing) ? 'update' : 'create';
        $item = $this->planItem(
            $record,
            $action,
            $action === 'create'
                ? 'Definition can be created with its portable UUID.'
                : 'Definition can be updated through its canonical owner.',
        );
        if (is_array($existing)) {
            $revision = $existing['revision'] ?? null;
            if (!is_int($revision) || $revision < 1) {
                return $this->blockedItem($record, 'invalid_target_revision', 'Target definition revision is invalid.');
            }
            $item['expected_revision'] = $revision;
        }
        return $item;
    }

    /** @param array<string,mixed> $record @return array<string,mixed> */
    private function planItem(array $record, string $action, string $message): array
    {
        return [
            'id' => $record['id'] ?? '',
            'type' => $record['type'] ?? '',
            'owner_surface_id' => $record['owner_surface_id'] ?? 0,
            'key' => $this->definitionKey($record) ?? '',
            'action' => $action,
            'code' => $action,
            'message' => $message,
        ];
    }

    /** @param array<string,mixed> $record @return array<string,mixed> */
    private function blockedItem(array $record, string $code, string $message): array
    {
        $item = $this->planItem($record, 'blocked', $message);
        $item['code'] = $code;
        return $item;
    }

    /** @param array<string,mixed> $definition */
    private function definitionKey(array $definition): ?string
    {
        $owner = $definition['owner_surface_id'] ?? null;
        $payload = $definition['payload'] ?? null;
        if (!is_array($payload)) {
            return null;
        }
        $field = match ($owner) {
            1 => 'post_type_key',
            2 => 'taxonomy_key',
            default => null,
        };
        if ($field === null) {
            return null;
        }
        $key = $payload[$field] ?? null;
        return is_string($key) && trim($key) !== '' ? trim($key) : null;
    }

    /** @param array<string,mixed> $target @param array<string,mixed> $record */
    private function sameSemanticDefinition(array $target, array $record): bool
    {
        return ($target['slug'] ?? null) === ($record['slug'] ?? null)
            && ($target['status'] ?? null) === ($record['status'] ?? null)
            && ($target['dependencies'] ?? null) === ($record['dependencies'] ?? null)
            && is_string($target['checksum'] ?? null)
            && is_string($record['checksum'] ?? null)
            && hash_equals($target['checksum'], $record['checksum']);
    }

    /** @return list<array<string,mixed>> */
    private function ownerList(string $ability, ExecutionContext $context): array
    {
        $result = $this->abilities->execute($ability, [], $context);
        if (!is_array($result) || !is_array($result['definitions'] ?? null)) {
            throw new RuntimeException(sprintf('Definition owner "%s" returned an invalid list.', $ability));
        }
        $definitions = [];
        foreach ($result['definitions'] as $definition) {
            if (!is_array($definition) || array_is_list($definition)) {
                throw new RuntimeException(sprintf('Definition owner "%s" returned an invalid record.', $ability));
            }
            $definitions[] = $definition;
        }
        return $definitions;
    }

    /** @param array<string,mixed> $input */
    private function packageJson(array $input): string
    {
        $json = $input['package_json'] ?? null;
        if (!is_string($json)) {
            throw new InvalidArgumentException('Definition package JSON is required.');
        }
        return $json;
    }

    /** @param array<string,mixed> $input */
    private function strategy(array $input): string
    {
        $strategy = $input['strategy'] ?? 'create_only';
        if (!is_string($strategy) || !in_array($strategy, ['create_only', 'update_existing'], true)) {
            throw new InvalidArgumentException('Package strategy must be create_only or update_existing.');
        }
        return $strategy;
    }

    /** @param list<array<string,mixed>> $results @return array{created:int,updated:int,no_change:int} */
    private function resultCounts(array $results): array
    {
        $counts = ['created' => 0, 'updated' => 0, 'no_change' => 0];
        foreach ($results as $result) {
            $action = $result['action'] ?? null;
            if (is_string($action) && isset($counts[$action])) {
                ++$counts[$action];
            }
        }
        return $counts;
    }
}
