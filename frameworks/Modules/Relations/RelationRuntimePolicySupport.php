<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class RelationRuntimePolicySupport
{
    /**
     * @param array<string,mixed> $payload
     * @return list<array{id:string,field:string,message:string}>
     */
    public function issues(array $payload): array
    {
        $issues = [];

        $direction = $payload['direction'] ?? null;
        if (is_array($direction) && ($direction['parent_relation'] ?? null) !== null) {
            $issues[] = $this->issue(
                'relation.runtime.parent_relation.unsupported',
                'direction.parent_relation',
                'Parent Relation composition requires a separately certified composition runtime.',
            );
        }

        $ordering = $payload['edge_ordering'] ?? null;
        if (is_array($ordering)
            && (($ordering['ordered_from'] ?? false) === true
                || ($ordering['ordered_to'] ?? false) === true
                || ($ordering['order_mode'] ?? null) !== null)
        ) {
            $issues[] = $this->issue(
                'relation.runtime.edge_ordering.unsupported',
                'edge_ordering',
                'Configurable Relation edge ordering requires separately certified ordering persistence.',
            );
        }

        $storageMode = $payload['storage_mode'] ?? 'shared_relation_table';
        if ($storageMode !== 'shared_relation_table') {
            $issues[] = $this->issue(
                'relation.runtime.storage_mode.unsupported',
                'storage_mode',
                sprintf('Relation storage mode "%s" does not have a certified runtime adapter.', (string) $storageMode),
            );
        }

        $storage = $payload['storage_config'] ?? null;
        if (is_array($storage)
            && (($storage['separate_table'] ?? false) === true
                || ($storage['table_name'] ?? null) !== null
                || ($storage['index_strategy'] ?? null) !== null
                || ($storage['foreign_keys'] ?? false) === true)
        ) {
            $issues[] = $this->issue(
                'relation.runtime.storage_config.unsupported',
                'storage_config',
                'Custom Relation storage configuration requires a separately certified storage adapter or migration.',
            );
        }

        if (($payload['pivot_enabled'] ?? false) === true) {
            $issues[] = $this->issue(
                'relation.runtime.pivot.unsupported',
                'pivot_enabled',
                'Relation pivot metadata requires the canonical Fields-owned pivot schema contract before publishing.',
            );
        }

        $pivot = $payload['pivot_policy'] ?? null;
        if (is_array($pivot)
            && (($pivot['required_validation'] ?? false) === true
                || ($pivot['queryable'] ?? false) === true
                || ($pivot['index_policy'] ?? null) !== null)
        ) {
            $issues[] = $this->issue(
                'relation.runtime.pivot_policy.unsupported',
                'pivot_policy',
                'Relation pivot policy requires certified Fields/Data Source integration before publishing.',
            );
        }

        if (array_key_exists('deletion_policy', $payload)) {
            $issues[] = $this->issue(
                'relation.runtime.deletion_policy.unsupported',
                'deletion_policy',
                'Per-definition Relation deletion policy is not yet wired to object and definition deletion hooks.',
            );
        }

        if (array_key_exists('editor_policy', $payload)) {
            $issues[] = $this->issue(
                'relation.runtime.editor_policy.unsupported',
                'editor_policy',
                'Per-definition Relation editor policy is not yet enforced by the certified native editor runtime.',
            );
        }

        if (array_key_exists('permissions_policy', $payload)) {
            $issues[] = $this->issue(
                'relation.runtime.permissions_policy.unsupported',
                'permissions_policy',
                'Per-definition Relation permission references require the shared capability policy contract before publishing.',
            );
        }

        if (array_key_exists('rest_policy', $payload)) {
            $issues[] = $this->issue(
                'relation.runtime.rest_policy.unsupported',
                'rest_policy',
                'Per-definition Relation REST exposure policy is not yet enforced by the shared REST/Data Source layer.',
            );
        }

        if (($payload['multisite_scope'] ?? 'site') !== 'site') {
            $issues[] = $this->issue(
                'relation.runtime.multisite_scope.unsupported',
                'multisite_scope',
                'Network-scoped Relation definitions require a certified network-scope definition and Data Source contract.',
            );
        }

        $portability = $payload['portability'] ?? null;
        if (is_array($portability)
            && (($portability['definition'] ?? true) !== true
                || ($portability['edges'] ?? false) === true
                || ($portability['pivot'] ?? false) === true)
        ) {
            $issues[] = $this->issue(
                'relation.runtime.portability.unsupported',
                'portability',
                'Certified Relations portability currently supports create-only definition portability only.',
            );
        }

        return $issues;
    }

    /** @return array{id:string,field:string,message:string} */
    private function issue(string $id, string $field, string $message): array
    {
        return [
            'id' => $id,
            'field' => $field,
            'message' => $message,
        ];
    }
}
