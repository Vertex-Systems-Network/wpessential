<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use Throwable;
use WPEssential\Contracts\AdminColumnsSourceCatalogInterface;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class AdminColumnsAdminBootstrapProjector
{
    private const CONTRACT_VERSION = 1;
    private const MAX_TARGETS = 100;
    private const MAX_SOURCES = 100;
    private const MAX_OWNER_SOURCES = 100;
    private const MAX_LABEL_BYTES = 191;
    private const SAFE_FORMATS = ['text', 'number', 'date', 'boolean', 'image', 'badge', 'link'];
    private const SAFE_OWNERS = ['fields', 'taxonomy', 'relations', 'media', 'status', 'query', 'provider', 'renderer'];
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    public function __construct(
        private DataSourceRegistryInterface $dataSources,
        private ?Closure $postTypeProvider = null,
        private ?AdminColumnsSourceCatalogInterface $ownerSources = null,
    ) {}

    /** @return array<string,mixed> */
    public function project(): array
    {
        $descriptor = $this->dataSources->find(WordPressPostsQueryCompiler::SOURCE_REF);
        if (!$descriptor instanceof DataSourceDescriptor || !$descriptor->isAvailable()) {
            return [
                'surface' => 'columns',
                'contractVersion' => self::CONTRACT_VERSION,
                'targets' => [],
                'sources' => [],
            ];
        }

        return [
            'surface' => 'columns',
            'contractVersion' => self::CONTRACT_VERSION,
            'targets' => $this->targets(),
            'sources' => [...$this->sources($descriptor), ...$this->optionalOwnerSources()],
        ];
    }

    /** @return list<array<string,mixed>> */
    private function targets(): array
    {
        $targets = [];
        foreach ($this->postTypes() as $key => $postType) {
            $name = null;
            $label = null;
            if (is_object($postType)) {
                $candidate = $postType->name ?? $key;
                $name = is_string($candidate) ? $candidate : null;
                $labels = $postType->labels ?? null;
                if (is_object($labels) && is_string($labels->name ?? null)) {
                    $label = $labels->name;
                } elseif (is_string($postType->label ?? null)) {
                    $label = $postType->label;
                }
            } elseif (is_string($postType)) {
                $name = is_string($key) ? $key : $postType;
                $label = $postType;
            }

            if (!$this->identifier($name) || !is_string($label) || trim($label) === '') {
                continue;
            }

            $targets[$name] = [
                'type' => 'post_type',
                'key' => $name,
                'label' => $this->boundedLabel(trim($label)),
                'capabilities' => $this->readOnlyCapabilities(),
            ];
        }

        ksort($targets, SORT_STRING);
        return array_slice(array_values($targets), 0, self::MAX_TARGETS);
    }

    /** @return list<array<string,mixed>> */
    private function sources(DataSourceDescriptor $descriptor): array
    {
        $schema = $descriptor->fieldSchema;
        ksort($schema, SORT_STRING);
        $sources = [];
        foreach ($schema as $reference => $logicalType) {
            if (!$this->identifier($reference) || !is_string($logicalType)) {
                continue;
            }
            $sources[] = [
                'owner' => 'native',
                'reference' => $reference,
                'label' => $this->label($reference),
                'formats' => $this->formats($logicalType),
                'capabilities' => $this->readOnlyCapabilities(),
            ];
            if (count($sources) >= self::MAX_SOURCES) {
                break;
            }
        }
        return $sources;
    }

    /** @return list<array<string,mixed>> */
    private function optionalOwnerSources(): array
    {
        if (!$this->ownerSources instanceof AdminColumnsSourceCatalogInterface) {
            return [];
        }

        try {
            $candidateSources = $this->ownerSources->adminColumnSources();
        } catch (Throwable) {
            return [];
        }

        $sources = [];
        foreach ($candidateSources as $candidate) {
            $source = $this->ownerSource($candidate);
            if ($source === null) {
                continue;
            }
            $sources[$source['reference']] = $source;
            if (count($sources) >= self::MAX_OWNER_SOURCES) {
                break;
            }
        }
        ksort($sources, SORT_STRING);
        return array_values($sources);
    }

    /** @param array<string,mixed> $candidate */
    private function ownerSource(array $candidate): ?array
    {
        $owner = $candidate['owner'] ?? null;
        $reference = $candidate['reference'] ?? null;
        $label = $candidate['label'] ?? null;
        $formats = $candidate['formats'] ?? null;
        $capabilities = $candidate['capabilities'] ?? null;

        if (!is_string($owner)
            || !in_array($owner, self::SAFE_OWNERS, true)
            || !$this->identifier($reference)
            || !is_string($label)
            || trim($label) === ''
            || !is_array($formats)
            || !array_is_list($formats)
            || $formats === []
            || !is_array($capabilities)
            || array_is_list($capabilities)
        ) {
            return null;
        }

        $safeFormats = [];
        foreach ($formats as $format) {
            if (!is_string($format) || !in_array($format, self::SAFE_FORMATS, true)) {
                return null;
            }
            $safeFormats[$format] = true;
        }

        foreach (['sort', 'filter', 'edit', 'export'] as $capability) {
            if (($capabilities[$capability] ?? null) !== false) {
                return null;
            }
        }

        $ownerMetadata = $this->ownerMetadata($owner, $reference, $candidate['ownerMetadata'] ?? null);
        if ($owner === 'fields' && $ownerMetadata === null) {
            return null;
        }

        $source = [
            'owner' => $owner,
            'reference' => $reference,
            'label' => $this->boundedLabel(trim($label)),
            'formats' => array_keys($safeFormats),
            'capabilities' => $this->readOnlyCapabilities(),
        ];
        if ($ownerMetadata !== null) {
            $source['ownerMetadata'] = $ownerMetadata;
        }

        return $source;
    }

    /** @return array<string,mixed>|null */
    private function ownerMetadata(string $owner, string $reference, mixed $value): ?array
    {
        if ($owner !== 'fields') {
            return null;
        }
        if (!is_array($value) || array_is_list($value)) {
            return null;
        }
        foreach (array_keys($value) as $key) {
            if (!in_array($key, ['groupRevision', 'fieldUuid', 'logicalType', 'storageOwner', 'postTypes'], true)) {
                return null;
            }
        }

        $groupRevision = $value['groupRevision'] ?? null;
        $fieldUuid = $value['fieldUuid'] ?? null;
        $logicalType = $value['logicalType'] ?? null;
        $storageOwner = $value['storageOwner'] ?? null;
        $postTypes = $value['postTypes'] ?? null;

        if (!is_int($groupRevision)
            || $groupRevision < 1
            || !is_string($fieldUuid)
            || preg_match(self::UUID_PATTERN, $fieldUuid) !== 1
            || !is_string($logicalType)
            || preg_match('/^[a-z0-9][a-z0-9_|<>.-]{0,63}$/', $logicalType) !== 1
            || $storageOwner !== 'native_post_meta'
            || !is_array($postTypes)
            || !array_is_list($postTypes)
            || $postTypes === []
            || count($postTypes) > self::MAX_TARGETS
            || !$this->fieldsReference($reference, $fieldUuid)
        ) {
            return null;
        }

        $safePostTypes = [];
        foreach ($postTypes as $postType) {
            if (!is_string($postType)
                || preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $postType) !== 1
            ) {
                return null;
            }
            $safePostTypes[$postType] = true;
        }
        ksort($safePostTypes, SORT_STRING);

        return [
            'groupRevision' => $groupRevision,
            'fieldUuid' => $fieldUuid,
            'logicalType' => $logicalType,
            'storageOwner' => 'native_post_meta',
            'postTypes' => array_keys($safePostTypes),
        ];
    }

    private function fieldsReference(string $reference, string $fieldUuid): bool
    {
        return preg_match(
            '/^fields\.[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.'
                . preg_quote($fieldUuid, '/') . '$/',
            $reference,
        ) === 1;
    }

    /** @return array{sort:bool,filter:bool,edit:bool,export:bool} */
    private function readOnlyCapabilities(): array
    {
        // Query currently advertises source-level capability only, not exact
        // per-field sort/filter capability. Do not infer or duplicate compiler
        // internals here. A later owner-certified capability seam may widen it.
        return [
            'sort' => false,
            'filter' => false,
            'edit' => false,
            'export' => false,
        ];
    }

    /** @return list<string> */
    private function formats(string $logicalType): array
    {
        return match ($logicalType) {
            'integer', 'number', 'float' => ['number', 'text'],
            'datetime', 'date' => ['date', 'text'],
            'boolean', 'bool' => ['boolean', 'text'],
            default => ['text'],
        };
    }

    /** @return array<mixed> */
    private function postTypes(): array
    {
        if ($this->postTypeProvider instanceof Closure) {
            $value = ($this->postTypeProvider)();
            return is_array($value) ? $value : [];
        }
        if (!function_exists('get_post_types')) {
            return [];
        }
        $value = get_post_types(['show_ui' => true], 'objects');
        return is_array($value) ? $value : [];
    }

    private function identifier(mixed $value): bool
    {
        return is_string($value)
            && preg_match('/^[a-z0-9][a-z0-9._:-]{0,191}$/', $value) === 1;
    }

    private function label(string $reference): string
    {
        return $this->boundedLabel(ucwords(str_replace(['.', '_', '-'], ' ', $reference)));
    }

    private function boundedLabel(string $label): string
    {
        return substr($label, 0, self::MAX_LABEL_BYTES);
    }
}
