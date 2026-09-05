<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use WPEssential\Contracts\FieldQueryConsumerInterface;
use WPEssential\Contracts\FieldValueReadConsumerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final class FieldValueReadConsumer implements FieldValueReadConsumerInterface
{
    private const REFERENCE_PATTERN = '/^fields\.([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})\.([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/';

    /** @var Closure(list<int>):void */
    private Closure $primePostCaches;

    /**
     * @param null|callable(list<int>):void $primePostCaches
     */
    public function __construct(
        private readonly FieldQueryConsumerInterface $fields,
        private readonly FieldValueTargetResolver $targets,
        private readonly PostMetaValueStore $values,
        private readonly WordPressPostResourceAuthorizer $authorization,
        ?callable $primePostCaches = null,
    ) {
        $this->primePostCaches = $primePostCaches !== null
            ? Closure::fromCallable($primePostCaches)
            : static function (array $postIds): void {
                if (!function_exists('get_posts')) {
                    throw new LogicException('WordPress get_posts() is unavailable for bounded Field value cache preloading.');
                }

                // One bounded WordPress query primes post objects and post-meta
                // cache before the per-resource authorization/read loop. This is
                // cache preparation only; no value is returned before the exact
                // post resource has passed the retained authorization boundary.
                get_posts([
                    'post__in' => $postIds,
                    'post_type' => 'any',
                    'post_status' => 'any',
                    'posts_per_page' => count($postIds),
                    'orderby' => 'post__in',
                    'order' => 'ASC',
                    'no_found_rows' => true,
                    'ignore_sticky_posts' => true,
                    'update_post_meta_cache' => true,
                    'update_post_term_cache' => false,
                    'suppress_filters' => true,
                ]);
            };
    }

    public function readValues(
        string $fieldReference,
        array $postIds,
        ExecutionContext $context,
    ): array {
        [$groupId, $fieldUuid] = $this->referenceIdentity($fieldReference);
        $this->assertPostIds($postIds);

        $description = $this->fields->describe($fieldReference, $context);
        $this->assertDescription($description, $fieldReference, $fieldUuid);

        ($this->primePostCaches)($postIds);

        $rows = [];
        foreach ($postIds as $postId) {
            // Authorization is intentionally checked before target resolution so
            // an unauthorized caller cannot use target/location errors to learn
            // protected per-post Field applicability.
            $this->authorization->assertCanRead($context, $postId);
            $target = $this->targets->resolve($groupId, $fieldUuid, $postId);
            if ($target->groupRevision !== $description['group_revision']) {
                throw new RuntimeException('Field value read consumer detected a Field Group revision change during the bounded read.');
            }

            $rows[] = [
                'post_id' => $postId,
                'value' => $this->values->read($target->field, $target->postType, $target->postId),
            ];
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'field_ref' => $fieldReference,
            'group_revision' => $description['group_revision'],
            'field_uuid' => $fieldUuid,
            'logical_type' => $description['logical_type'],
            'storage_owner' => $description['storage_owner'],
            'rows' => $rows,
        ];
    }

    /** @return array{0:string,1:string} */
    private function referenceIdentity(string $fieldReference): array
    {
        if (preg_match(self::REFERENCE_PATTERN, $fieldReference, $matches) !== 1) {
            throw new InvalidArgumentException('Field value read reference must use fields.<group-uuid>.<field-uuid>.');
        }

        return [$matches[1], $matches[2]];
    }

    /** @param list<int> $postIds */
    private function assertPostIds(array $postIds): void
    {
        if (!array_is_list($postIds)
            || $postIds === []
            || count($postIds) > self::MAX_POST_IDS
        ) {
            throw new InvalidArgumentException('Field value read post ids must be a non-empty bounded list.');
        }

        $seen = [];
        foreach ($postIds as $postId) {
            if (!is_int($postId) || $postId < 1 || isset($seen[$postId])) {
                throw new InvalidArgumentException('Field value read post ids must be unique positive integers.');
            }
            $seen[$postId] = true;
        }
    }

    /** @param array<string,mixed> $description */
    private function assertDescription(array $description, string $fieldReference, string $fieldUuid): void
    {
        if (($description['contract_version'] ?? null) !== FieldQueryConsumerInterface::CONTRACT_VERSION
            || ($description['field_ref'] ?? null) !== $fieldReference
            || ($description['field_uuid'] ?? null) !== $fieldUuid
            || !is_int($description['group_revision'] ?? null)
            || $description['group_revision'] < 1
            || !is_string($description['logical_type'] ?? null)
            || !in_array($description['logical_type'], ['string', 'boolean', 'integer', 'number'], true)
            || ($description['storage_owner'] ?? null) !== FieldGroupRuntimeStorageProjection::NATIVE_POST_META
        ) {
            throw new RuntimeException('Field value read consumer received malformed or unsupported owner metadata.');
        }
    }
}
