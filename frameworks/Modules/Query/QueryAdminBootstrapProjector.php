<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class QueryAdminBootstrapProjector
{
    private const DRAFT_UUID = '00000000-0000-4000-8000-000000000006';

    public function __construct(private DataSourceRegistryInterface $dataSources) {}

    /** @return array<string,mixed> */
    public function project(): array
    {
        $sources = [];
        $descriptor = $this->dataSources->find(WordPressPostsQueryCompiler::SOURCE_REF);
        if ($descriptor instanceof DataSourceDescriptor && $descriptor->isAvailable()) {
            $sources[] = $this->projectSource($descriptor);
        }

        return [
            'surface' => 'query',
            'identity' => [
                'uuid' => self::DRAFT_UUID,
                'key' => 'query-admin-draft',
                'name' => 'Query admin draft',
                'revision' => 1,
                'lifecycle' => 'draft',
            ],
            'sources' => $sources,
        ];
    }

    /** @return array<string,mixed> */
    private function projectSource(DataSourceDescriptor $descriptor): array
    {
        $schema = $descriptor->fieldSchema;
        ksort($schema, SORT_STRING);
        $fields = [];
        foreach ($schema as $reference => $logicalType) {
            $fields[] = [
                'ref' => $reference,
                'label' => $this->label($reference),
                'logicalType' => $logicalType,
            ];
        }

        return [
            'sourceRef' => $descriptor->id,
            'sourceType' => $descriptor->sourceType,
            'capabilityVersion' => $descriptor->capabilityVersion,
            'label' => $this->label($descriptor->id),
            'fields' => $fields,
            'predicates' => array_values($descriptor->predicates),
            'maxPageSize' => $descriptor->maxPageSize,
            'supportsRelations' => $descriptor->supportsRelations,
        ];
    }

    private function label(string $reference): string
    {
        return ucwords(str_replace(['.', '_', '-'], ' ', $reference));
    }
}
