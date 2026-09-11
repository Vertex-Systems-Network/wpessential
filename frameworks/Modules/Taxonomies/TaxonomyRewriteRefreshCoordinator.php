<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class TaxonomyRewriteRefreshCoordinator
{
    public const OPTION_KEY = 'wpessential_taxonomy_rewrite_refresh_pending_v1';

    /** @var Closure(string):mixed */
    private readonly Closure $readPending;

    /** @var Closure(string):bool */
    private readonly Closure $writePending;

    /** @var Closure(string):bool */
    private readonly Closure $deletePending;

    /** @var Closure():bool */
    private readonly Closure $softFlush;

    public function __construct(
        ?Closure $readPending = null,
        ?Closure $writePending = null,
        ?Closure $deletePending = null,
        ?Closure $softFlush = null,
    ) {
        $this->readPending = $readPending ?? static function (string $key): mixed {
            if (!function_exists('get_option')) {
                return false;
            }

            return get_option($key, false);
        };
        $this->writePending = $writePending ?? static function (string $key): bool {
            if (!function_exists('update_option')) {
                return false;
            }

            return update_option($key, 1, false);
        };
        $this->deletePending = $deletePending ?? static function (string $key): bool {
            if (!function_exists('delete_option')) {
                return false;
            }

            return delete_option($key);
        };
        $this->softFlush = $softFlush ?? static function (): bool {
            if (!function_exists('flush_rewrite_rules')) {
                return false;
            }

            flush_rewrite_rules(false);

            return true;
        };
    }

    public function scheduleForMutation(?Definition $before, Definition $after): bool
    {
        if ($this->routingSignature($before) === $this->routingSignature($after)) {
            return false;
        }
        if ($this->isPending()) {
            return true;
        }

        return ($this->writePending)(self::OPTION_KEY);
    }

    public function flushPending(): bool
    {
        if (!$this->isPending()) {
            return false;
        }
        if (!(($this->softFlush)())) {
            return false;
        }
        if (!(($this->deletePending)(self::OPTION_KEY))) {
            return false;
        }

        return !$this->isPending();
    }

    private function isPending(): bool
    {
        return in_array(($this->readPending)(self::OPTION_KEY), [1, '1', true], true);
    }

    /**
     * Mirrors only the WordPress taxonomy inputs that can change cached rewrite rules.
     * Validation/projector ownership remains elsewhere; this signature decides whether a refresh is necessary.
     *
     * @return array<string,mixed>|null
     */
    private function routingSignature(?Definition $definition): ?array
    {
        if (!$definition instanceof Definition || $definition->status !== DefinitionStatus::Published) {
            return null;
        }

        $payload = $definition->payload;
        $key = is_string($payload['taxonomy_key'] ?? null) ? trim($payload['taxonomy_key']) : '';
        $rewrite = $payload['rewrite'] ?? true;
        if ($rewrite === false) {
            return ['rewrite' => false];
        }

        $rewriteMap = is_array($rewrite) && !array_is_list($rewrite) ? $rewrite : [];
        $public = is_bool($payload['public'] ?? null) ? $payload['public'] : true;
        $publiclyQueryable = is_bool($payload['publicly_queryable'] ?? null)
            ? $payload['publicly_queryable']
            : $public;
        $queryVar = $payload['query_var'] ?? true;
        if (!$publiclyQueryable || $queryVar === false) {
            $effectiveQueryVar = false;
        } elseif ($queryVar === true) {
            $effectiveQueryVar = $key;
        } else {
            $effectiveQueryVar = is_string($queryVar) ? trim($queryVar) : $key;
        }

        return [
            'rewrite' => [
                'slug' => is_string($rewriteMap['slug'] ?? null)
                    ? trim($rewriteMap['slug'], '/')
                    : $key,
                'with_front' => is_bool($rewriteMap['with_front'] ?? null)
                    ? $rewriteMap['with_front']
                    : true,
                'hierarchical' => is_bool($rewriteMap['hierarchical'] ?? null)
                    ? $rewriteMap['hierarchical']
                    : false,
                'ep_mask' => is_int($rewriteMap['ep_mask'] ?? null)
                    ? $rewriteMap['ep_mask']
                    : 0,
            ],
            'taxonomy_hierarchical' => is_bool($payload['hierarchical'] ?? null)
                ? $payload['hierarchical']
                : false,
            'query_var' => $effectiveQueryVar,
        ];
    }
}
