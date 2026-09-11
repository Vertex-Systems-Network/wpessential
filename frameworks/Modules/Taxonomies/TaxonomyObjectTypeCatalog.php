<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use Throwable;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final readonly class TaxonomyObjectTypeCatalog
{
    public function __construct(
        private AbilityRegistry $abilities,
        private WordPressExecutionContextFactory $contexts,
    ) {}

    /**
     * @return list<array{key:string,label:string,source:string,status:string,runtime_registered:bool,state:string}>
     */
    public function entries(): array
    {
        /** @var array<string,array{key:string,label:string,source:string,status:string,runtime_registered:bool,state:string}> $entries */
        $entries = [];

        if (function_exists('get_post_types')) {
            $runtime = get_post_types(['show_ui' => true], 'objects');
            if (is_array($runtime)) {
                foreach ($runtime as $key => $object) {
                    if (!is_string($key) || $key === '' || !is_object($object)) {
                        continue;
                    }
                    $source = $this->runtimeSource($object);
                    $entries[$key] = [
                        'key' => $key,
                        'label' => $this->runtimeLabel($object, $key),
                        'source' => $source,
                        'status' => 'registered',
                        'runtime_registered' => true,
                        'state' => $source === 'wordpress' ? 'healthy' : 'external',
                    ];
                }
            }
        }

        foreach ($this->canonicalPostTypes() as $definition) {
            $payload = is_array($definition['payload'] ?? null) ? $definition['payload'] : [];
            $key = is_string($payload['post_type_key'] ?? null) ? trim($payload['post_type_key']) : '';
            if ($key === '') {
                continue;
            }
            $name = is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
            $status = is_string($definition['status'] ?? null) ? $definition['status'] : 'unknown';
            $registered = function_exists('post_type_exists') ? post_type_exists($key) : isset($entries[$key]);
            $entries[$key] = [
                'key' => $key,
                'label' => $name !== '' ? $name : ($entries[$key]['label'] ?? $key),
                'source' => 'wpessential',
                'status' => $status,
                'runtime_registered' => $registered,
                'state' => $status !== 'published' ? 'disabled' : ($registered ? 'healthy' : 'missing'),
            ];
        }

        ksort($entries, SORT_STRING);
        return array_values($entries);
    }

    /** @return list<array<string,mixed>> */
    private function canonicalPostTypes(): array
    {
        try {
            $current = $this->contexts->current();
            $context = new ExecutionContext(
                principal: $current->principal,
                siteId: $current->siteId,
                channel: ExecutionChannel::Ui,
                networkId: $current->networkId,
                correlationId: $current->correlationId,
            );
            $result = $this->abilities->execute('wpessential/cpt/list', [], $context);
            if (!is_array($result) || !is_array($result['definitions'] ?? null)) {
                return [];
            }

            $definitions = [];
            foreach ($result['definitions'] as $definition) {
                if (is_array($definition)) {
                    $definitions[] = $definition;
                }
            }
            return $definitions;
        } catch (Throwable) {
            return [];
        }
    }

    private function runtimeSource(object $object): string
    {
        $properties = get_object_vars($object);
        return ($properties['_builtin'] ?? false) === true ? 'wordpress' : 'runtime';
    }

    private function runtimeLabel(object $object, string $fallback): string
    {
        $labels = $object->labels ?? null;
        if (is_object($labels)) {
            $name = $labels->name ?? null;
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        $label = $object->label ?? null;
        return is_string($label) && trim($label) !== '' ? trim($label) : $fallback;
    }
}
