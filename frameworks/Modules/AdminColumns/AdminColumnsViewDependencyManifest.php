<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class AdminColumnsViewDependencyManifest
{
    public const CONTRACT_VERSION = 1;

    public function __construct(private AdminColumnsViewDefinitionNormalizer $normalizer)
    {
    }

    /**
     * Project a deterministic, read-only dependency manifest from one canonical
     * Admin Columns View payload.
     *
     * This method performs no provider discovery, persistence, authorization,
     * Query execution or peer-private reads.
     *
     * @param array<string,mixed> $payload
     * @return array{
     *   contract_version:int,
     *   view_key:string,
     *   target:array{type:string,key:string},
     *   sources:list<array{owner:string,reference:string,column_keys:list<string>}>,
     *   assignments:array{roles:list<string>,users:list<int>,capabilities:list<string>}
     * }
     */
    public function project(array $payload): array
    {
        $normalized = $this->normalizer->normalize($payload);

        /** @var string $viewKey */
        $viewKey = $normalized['view_key'];
        /** @var array{type:string,key:string} $target */
        $target = $normalized['target'];
        /** @var list<array{key:string,source:array{owner:string,reference:string}}> $columns */
        $columns = $normalized['columns'];

        $sources = [];
        $sourceIndexes = [];
        foreach ($columns as $column) {
            $owner = $column['source']['owner'];
            $reference = $column['source']['reference'];
            $identity = $owner . "\x1f" . $reference;

            if (!array_key_exists($identity, $sourceIndexes)) {
                $sourceIndexes[$identity] = count($sources);
                $sources[] = [
                    'owner' => $owner,
                    'reference' => $reference,
                    'column_keys' => [],
                ];
            }

            $index = $sourceIndexes[$identity];
            $sources[$index]['column_keys'][] = $column['key'];
        }

        /** @var array{roles:list<string>,users:list<int>,capabilities:list<string>} $assignments */
        $assignments = $normalized['assignment'] ?? [
            'roles' => [],
            'users' => [],
            'capabilities' => [],
        ];

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'view_key' => $viewKey,
            'target' => [
                'type' => $target['type'],
                'key' => $target['key'],
            ],
            'sources' => $sources,
            'assignments' => [
                'roles' => $assignments['roles'],
                'users' => $assignments['users'],
                'capabilities' => $assignments['capabilities'],
            ],
        ];
    }
}
