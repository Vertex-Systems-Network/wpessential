<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * User-scoped persistence for bounded Admin Columns personal presentation state.
 *
 * Authorization and shared View persistence are intentionally outside this store.
 */
final readonly class AdminColumnsPersonalPreferenceStore
{
    public const CONTRACT_VERSION = 1;

    private const META_PREFIX = '_wpessential_admin_columns_view_pref_';
    private const STORED_KEYS = [
        'contract_version',
        'view_id',
        'view_revision',
        'preference',
    ];

    private Closure $reader;
    private Closure $writer;
    private Closure $deleter;

    public function __construct(
        private AdminColumnsPersonalPreferenceResolver $resolver,
        ?Closure $reader = null,
        ?Closure $writer = null,
        ?Closure $deleter = null,
    ) {
        $this->reader = $reader ?? static fn (int $userId, string $key): mixed => \get_user_meta($userId, $key, true);
        $this->writer = $writer ?? static function (int $userId, string $key, array $value): void {
            \update_user_meta($userId, $key, $value);
        };
        $this->deleter = $deleter ?? static function (int $userId, string $key): void {
            \delete_user_meta($userId, $key);
        };
    }

    /**
     * @param list<array{key:string,enabled?:bool}> $columns
     * @return array{
     *   contract_version:int,
     *   user_id:int,
     *   view_id:string,
     *   view_revision:int,
     *   stored_view_revision:int|null,
     *   applied:bool,
     *   reason:string,
     *   state:array<string,mixed>
     * }
     */
    public function load(int $userId, string $viewId, int $viewRevision, array $columns): array
    {
        $this->assertUserId($userId);
        $defaultState = $this->resolver->resolve($viewId, $viewRevision, $columns, []);
        $raw = ($this->reader)($userId, $this->metaKey($viewId));

        if ($raw === '' || $raw === null) {
            return $this->result(
                $userId,
                $viewId,
                $viewRevision,
                null,
                false,
                'not_found',
                $defaultState,
            );
        }

        $stored = $this->storedEnvelope($raw, $viewId);
        $storedRevision = $stored['view_revision'];
        if ($storedRevision !== $viewRevision) {
            return $this->result(
                $userId,
                $viewId,
                $viewRevision,
                $storedRevision,
                false,
                'view_revision_changed',
                $defaultState,
            );
        }

        return $this->result(
            $userId,
            $viewId,
            $viewRevision,
            $storedRevision,
            true,
            'applied',
            $this->resolver->resolve($viewId, $viewRevision, $columns, $stored['preference']),
        );
    }

    /**
     * @param list<array{key:string,enabled?:bool}> $columns
     * @param array<string,mixed> $preference
     * @return array{
     *   contract_version:int,
     *   user_id:int,
     *   view_id:string,
     *   view_revision:int,
     *   stored_view_revision:int|null,
     *   applied:bool,
     *   reason:string,
     *   state:array<string,mixed>
     * }
     */
    public function save(
        int $userId,
        string $viewId,
        int $viewRevision,
        array $columns,
        array $preference,
    ): array {
        $this->assertUserId($userId);
        $resolved = $this->resolver->resolve($viewId, $viewRevision, $columns, $preference);
        $stored = [
            'contract_version' => self::CONTRACT_VERSION,
            'view_id' => $viewId,
            'view_revision' => $viewRevision,
            'preference' => $this->preferencePayload($resolved),
        ];
        $key = $this->metaKey($viewId);

        ($this->writer)($userId, $key, $stored);
        if (($this->reader)($userId, $key) !== $stored) {
            throw new RuntimeException('Admin Columns personal preference write-back verification failed.');
        }

        return $this->load($userId, $viewId, $viewRevision, $columns);
    }

    public function reset(int $userId, string $viewId): void
    {
        $this->assertUserId($userId);
        $key = $this->metaKey($viewId);
        ($this->deleter)($userId, $key);

        $remaining = ($this->reader)($userId, $key);
        if ($remaining !== '' && $remaining !== null) {
            throw new RuntimeException('Admin Columns personal preference reset verification failed.');
        }
    }

    /**
     * @return array{contract_version:int,view_id:string,view_revision:int,preference:array<string,mixed>}
     */
    private function storedEnvelope(mixed $raw, string $viewId): array
    {
        if (!is_array($raw) || array_is_list($raw)) {
            throw new RuntimeException('Stored Admin Columns personal preference envelope is malformed.');
        }
        if (array_keys($raw) !== self::STORED_KEYS) {
            throw new RuntimeException('Stored Admin Columns personal preference envelope has unsupported keys.');
        }
        if (($raw['contract_version'] ?? null) !== self::CONTRACT_VERSION
            || ($raw['view_id'] ?? null) !== $viewId
            || !is_int($raw['view_revision'] ?? null)
            || $raw['view_revision'] < 1
            || !is_array($raw['preference'] ?? null)
            || array_is_list($raw['preference'])
        ) {
            throw new RuntimeException('Stored Admin Columns personal preference envelope is invalid.');
        }

        /** @var array{contract_version:int,view_id:string,view_revision:int,preference:array<string,mixed>} $raw */
        return $raw;
    }

    /** @param array<string,mixed> $resolved @return array<string,mixed> */
    private function preferencePayload(array $resolved): array
    {
        return [
            'chosen_view_id' => $resolved['chosen_view_id'],
            'temporary_sort' => $resolved['temporary_sort'],
            'temporary_filters' => $resolved['temporary_filters'],
            'hidden_columns' => $resolved['hidden_columns'],
            'density' => $resolved['density'],
            'saved_filter_state' => $resolved['saved_filter_state'],
        ];
    }

    /**
     * @param array<string,mixed> $state
     * @return array{
     *   contract_version:int,
     *   user_id:int,
     *   view_id:string,
     *   view_revision:int,
     *   stored_view_revision:int|null,
     *   applied:bool,
     *   reason:string,
     *   state:array<string,mixed>
     * }
     */
    private function result(
        int $userId,
        string $viewId,
        int $viewRevision,
        ?int $storedViewRevision,
        bool $applied,
        string $reason,
        array $state,
    ): array {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'user_id' => $userId,
            'view_id' => $viewId,
            'view_revision' => $viewRevision,
            'stored_view_revision' => $storedViewRevision,
            'applied' => $applied,
            'reason' => $reason,
            'state' => $state,
        ];
    }

    private function assertUserId(int $userId): void
    {
        if ($userId < 1) {
            throw new InvalidArgumentException('Admin Columns personal preference user id must be positive.');
        }
    }

    private function metaKey(string $viewId): string
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $viewId) !== 1) {
            throw new InvalidArgumentException('Admin Columns personal preference View id must be a lowercase RFC 4122 UUID.');
        }

        return self::META_PREFIX . str_replace('-', '', $viewId);
    }
}
