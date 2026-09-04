<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Modules\Query\WordPressPostsQueryCompiler;
use WPEssential\Platform\DataSources\DataSourceDescriptor;

final readonly class AdminColumnsAdminBootstrapProjector
{
    private const CONTRACT_VERSION = 1;
    private const MAX_TARGETS = 100;
    private const MAX_SOURCES = 100;

    public function __construct(
        private DataSourceRegistryInterface $dataSources,
        private ?Closure $postTypeProvider = null,
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
            'sources' => $this->sources($descriptor),
        ];
    }

    /** @return list<array<string,mixed>> */
    private function targets(): array
    {
        $raw = $this->postTypes();
        $targets = [];
        foreach ($raw as $key => $postType) {
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
                'label' => mb_substr(trim($label), 0, 191),
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
        return mb_substr(ucwords(str_replace(['.', '_', '-'], ' ', $reference)), 0, 191);
    }
}
