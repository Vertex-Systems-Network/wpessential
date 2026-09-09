<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition\Probe;

if (!defined('ABSPATH')) {
    exit;
}

use JsonException;
use WPEssential\Modules\CustomTables\Definition\TableColumnDescriptor;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;
use WPEssential\Modules\CustomTables\Schema\WordPressCt1SchemaIntrospector;

final readonly class WordPressMetadataPreconditionFactsProvider implements MetadataPreconditionFactsProviderInterface
{
    public function __construct(private WordPressCt1SchemaIntrospector $introspector)
    {
    }

    /** @throws JsonException */
    public function facts(
        TableSchemaDescriptor $descriptor,
        ProviderCapabilityProfile $capabilities,
    ): MetadataPreconditionFacts {
        $observed = $this->introspector->observe($descriptor);
        $fingerprints = [];

        foreach ($observed->columns as $column) {
            $fingerprints[$this->columnTarget($descriptor, $column)] = hash(
                'sha256',
                json_encode(
                    $column->canonical(),
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                ),
            );
        }

        return new MetadataPreconditionFacts(
            tableExists: $observed->exists,
            features: [
                'instant_add_column' => $capabilities->instantAddColumnCandidate,
                'instant_default_change' => $capabilities->instantDefaultChangeCandidate,
                'inplace_varchar_widening' => $capabilities->inplaceVarcharWideningCandidate,
            ],
            columnFingerprints: $fingerprints,
        );
    }

    private function columnTarget(TableSchemaDescriptor $descriptor, TableColumnDescriptor $column): string
    {
        return sprintf('column.%s.%s', $descriptor->tableKey, $column->key);
    }
}
