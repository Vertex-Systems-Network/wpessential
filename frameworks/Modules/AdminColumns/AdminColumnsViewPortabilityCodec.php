<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use JsonException;
use LengthException;

final readonly class AdminColumnsViewPortabilityCodec
{
    public const CONTRACT_VERSION = 1;

    private const MAX_DOCUMENT_BYTES = 262144;
    private const SENSITIVE_SOURCE_OWNERS = [
        'fields',
        'taxonomy',
        'relations',
        'media',
        'status',
        'query',
        'provider',
        'renderer',
    ];
    private const REMAP_KEYS = [
        'target_keys',
        'source_references',
        'assignment_roles',
        'assignment_users',
        'assignment_capabilities',
    ];

    public function __construct(private AdminColumnsViewDefinitionNormalizer $normalizer)
    {
    }

    /** @param array<string,mixed> $payload */
    public function encode(string $viewId, int $revision, array $payload): string
    {
        $this->uuid($viewId, 'Portable View id');
        if ($revision < 1) {
            throw new InvalidArgumentException('Portable View revision must be positive.');
        }

        $normalized = $this->normalizer->normalize($payload);
        $document = [
            'contract_version' => self::CONTRACT_VERSION,
            'definition_type' => AdminColumnsViewDefinitionNormalizer::DEFINITION_TYPE,
            'owner_surface_id' => AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID,
            'view_id' => $viewId,
            'source_revision' => $revision,
            'payload' => $normalized,
        ];

        try {
            $encoded = json_encode(
                $document,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $error) {
            throw new InvalidArgumentException('Portable View document could not be encoded.', 0, $error);
        }

        if (strlen($encoded) > self::MAX_DOCUMENT_BYTES) {
            throw new LengthException('Portable View document exceeds the V1 byte budget.');
        }
        return $encoded;
    }

    /**
     * @param array<string,mixed> $remaps
     * @return array{contract_version:int,view_id:string,source_revision:int,payload:array<string,mixed>}
     */
    public function decode(string $document, array $remaps): array
    {
        if ($document === '' || strlen($document) > self::MAX_DOCUMENT_BYTES) {
            throw new LengthException('Portable View document is empty or exceeds the V1 byte budget.');
        }
        if (preg_match('//u', $document) !== 1) {
            throw new InvalidArgumentException('Portable View document must be valid UTF-8.');
        }

        try {
            $decoded = json_decode($document, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new InvalidArgumentException('Portable View document is not valid JSON.', 0, $error);
        }
        if (!is_array($decoded) || array_is_list($decoded)) {
            throw new InvalidArgumentException('Portable View document root must be an object/map.');
        }
        $this->assertKnownKeys(
            $decoded,
            ['contract_version', 'definition_type', 'owner_surface_id', 'view_id', 'source_revision', 'payload'],
            'Portable View document',
        );
        if (($decoded['contract_version'] ?? null) !== self::CONTRACT_VERSION
            || ($decoded['definition_type'] ?? null) !== AdminColumnsViewDefinitionNormalizer::DEFINITION_TYPE
            || ($decoded['owner_surface_id'] ?? null) !== AdminColumnsViewDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new InvalidArgumentException('Portable View contract identity is unsupported.');
        }

        $viewId = $decoded['view_id'] ?? null;
        $sourceRevision = $decoded['source_revision'] ?? null;
        $payload = $decoded['payload'] ?? null;
        if (!is_string($viewId)) {
            throw new InvalidArgumentException('Portable View id is malformed.');
        }
        $this->uuid($viewId, 'Portable View id');
        if (!is_int($sourceRevision) || $sourceRevision < 1) {
            throw new InvalidArgumentException('Portable View source revision is malformed.');
        }
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('Portable View payload is malformed.');
        }

        $normalized = $this->normalizer->normalize($payload);
        $remapSets = $this->remapSets($remaps);
        $consumed = array_fill_keys(self::REMAP_KEYS, []);
        $remapped = $this->remapPayload($normalized, $remapSets, $consumed);
        $this->assertNoUnusedRemaps($remapSets, $consumed);

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'view_id' => $viewId,
            'source_revision' => $sourceRevision,
            'payload' => $this->normalizer->normalize($remapped),
        ];
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,array<string,string|int>> $remaps
     * @param array<string,array<string,true>> $consumed
     * @return array<string,mixed>
     */
    private function remapPayload(array $payload, array $remaps, array &$consumed): array
    {
        $target = $payload['target'];
        if (!is_array($target)) {
            throw new InvalidArgumentException('Normalized portable target is malformed.');
        }
        $targetKey = $target['key'];
        if (!is_string($targetKey)) {
            throw new InvalidArgumentException('Normalized portable target key is malformed.');
        }
        $target['key'] = $this->requiredStringRemap(
            $targetKey,
            $remaps['target_keys'],
            $consumed['target_keys'],
            'target key',
        );
        $payload['target'] = $target;

        $columns = $payload['columns'];
        if (!is_array($columns) || !array_is_list($columns)) {
            throw new InvalidArgumentException('Normalized portable columns are malformed.');
        }
        foreach ($columns as $index => $column) {
            if (!is_array($column) || !is_array($column['source'] ?? null)) {
                throw new InvalidArgumentException(sprintf('Normalized portable column %d is malformed.', $index));
            }
            $owner = $column['source']['owner'] ?? null;
            $reference = $column['source']['reference'] ?? null;
            if (!is_string($owner) || !is_string($reference)) {
                throw new InvalidArgumentException(sprintf('Normalized portable column %d source is malformed.', $index));
            }
            if (in_array($owner, self::SENSITIVE_SOURCE_OWNERS, true)) {
                $mapKey = $owner . '|' . $reference;
                $column['source']['reference'] = $this->requiredStringRemap(
                    $mapKey,
                    $remaps['source_references'],
                    $consumed['source_references'],
                    sprintf('source reference %s', $mapKey),
                );
            }
            $columns[$index] = $column;
        }
        $payload['columns'] = $columns;

        if (isset($payload['assignment'])) {
            if (!is_array($payload['assignment'])) {
                throw new InvalidArgumentException('Normalized portable assignment is malformed.');
            }
            $assignment = $payload['assignment'];
            $assignment['roles'] = $this->remapStringList(
                $assignment['roles'] ?? [],
                $remaps['assignment_roles'],
                $consumed['assignment_roles'],
                'assignment role',
            );
            $assignment['capabilities'] = $this->remapStringList(
                $assignment['capabilities'] ?? [],
                $remaps['assignment_capabilities'],
                $consumed['assignment_capabilities'],
                'assignment capability',
            );
            $assignment['users'] = $this->remapUserList(
                $assignment['users'] ?? [],
                $remaps['assignment_users'],
                $consumed['assignment_users'],
            );
            $payload['assignment'] = $assignment;
        }

        return $payload;
    }

    /**
     * @param array<string,mixed> $remaps
     * @return array<string,array<string,string|int>>
     */
    private function remapSets(array $remaps): array
    {
        $this->assertKnownKeys($remaps, self::REMAP_KEYS, 'Portable View remaps');
        $sets = [];
        foreach (self::REMAP_KEYS as $key) {
            $value = $remaps[$key] ?? [];
            if (!is_array($value) || array_is_list($value) || count($value) > 200) {
                throw new InvalidArgumentException(sprintf('Portable View remap set "%s" is malformed or over budget.', $key));
            }
            $normalized = [];
            $destinations = [];
            foreach ($value as $from => $to) {
                if ($key === 'assignment_users' && is_int($from) && $from > 0) {
                    $from = (string) $from;
                }
                if (!is_string($from) || $from === '' || strlen($from) > 384) {
                    throw new InvalidArgumentException(sprintf('Portable View remap set "%s" contains an invalid source key.', $key));
                }
                if ($key === 'assignment_users') {
                    if (preg_match('/^[1-9][0-9]*$/', $from) !== 1 || !is_int($to) || $to < 1) {
                        throw new InvalidArgumentException('Portable View assignment user remaps require positive integer source/destination ids.');
                    }
                    $destinationKey = (string) $to;
                } else {
                    if (!is_string($to) || preg_match('/^[a-z0-9][a-z0-9._:-]{0,191}$/', $to) !== 1) {
                        throw new InvalidArgumentException(sprintf('Portable View remap set "%s" contains an invalid destination.', $key));
                    }
                    $destinationKey = $to;
                }
                if (isset($destinations[$destinationKey])) {
                    throw new InvalidArgumentException(sprintf('Portable View remap set "%s" cannot collapse multiple references to one destination.', $key));
                }
                $destinations[$destinationKey] = true;
                $normalized[$from] = $to;
            }
            $sets[$key] = $normalized;
        }
        return $sets;
    }

    /**
     * @param array<string,string|int> $map
     * @param array<string,true> $consumed
     */
    private function requiredStringRemap(string $from, array $map, array &$consumed, string $label): string
    {
        if (!array_key_exists($from, $map) || !is_string($map[$from])) {
            throw new InvalidArgumentException(sprintf('Portable View requires an explicit remap for %s.', $label));
        }
        $consumed[$from] = true;
        return $map[$from];
    }

    /**
     * @param mixed $value
     * @param array<string,string|int> $map
     * @param array<string,true> $consumed
     * @return list<string>
     */
    private function remapStringList(mixed $value, array $map, array &$consumed, string $label): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Portable View normalized assignment list is malformed.');
        }
        $result = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException('Portable View normalized assignment string is malformed.');
            }
            $result[] = $this->requiredStringRemap($item, $map, $consumed, $label . ' ' . $item);
        }
        return $result;
    }

    /**
     * @param mixed $value
     * @param array<string,string|int> $map
     * @param array<string,true> $consumed
     * @return list<int>
     */
    private function remapUserList(mixed $value, array $map, array &$consumed): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new InvalidArgumentException('Portable View normalized assignment users are malformed.');
        }
        $result = [];
        foreach ($value as $userId) {
            if (!is_int($userId) || $userId < 1) {
                throw new InvalidArgumentException('Portable View normalized assignment user is malformed.');
            }
            $key = (string) $userId;
            if (!array_key_exists($key, $map) || !is_int($map[$key]) || $map[$key] < 1) {
                throw new InvalidArgumentException(sprintf('Portable View requires an explicit remap for assignment user %d.', $userId));
            }
            $consumed[$key] = true;
            $result[] = $map[$key];
        }
        return $result;
    }

    /**
     * @param array<string,array<string,string|int>> $remaps
     * @param array<string,array<string,true>> $consumed
     */
    private function assertNoUnusedRemaps(array $remaps, array $consumed): void
    {
        foreach ($remaps as $set => $map) {
            foreach (array_keys($map) as $from) {
                if (!isset($consumed[$set][$from])) {
                    throw new InvalidArgumentException(sprintf(
                        'Portable View remap set "%s" contains an unused or foreign mapping for "%s".',
                        $set,
                        $from,
                    ));
                }
            }
        }
    }

    /** @param array<string,mixed> $map @param list<string> $allowed */
    private function assertKnownKeys(array $map, array $allowed, string $label): void
    {
        foreach (array_keys($map) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf('%s contains unsupported key "%s".', $label, (string) $key));
            }
        }
    }

    private function uuid(string $value, string $label): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException($label . ' must be a lowercase RFC 4122 UUID.');
        }
    }
}
