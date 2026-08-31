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
use Throwable;

final class PostMetaValueStore
{
    /** @var Closure(int):string|false */
    private Closure $getPostType;

    /** @var Closure(int,string):bool */
    private Closure $metadataExists;

    /** @var Closure(int,string,bool):mixed */
    private Closure $getPostMeta;

    /** @var Closure(int,string,mixed):int|bool */
    private Closure $updatePostMeta;

    /** @var Closure(int,string):bool */
    private Closure $deletePostMeta;

    /** @var Closure(mixed):mixed */
    private Closure $slash;

    /**
     * @param null|callable(int):string|false $getPostType
     * @param null|callable(int,string):bool $metadataExists
     * @param null|callable(int,string,bool):mixed $getPostMeta
     * @param null|callable(int,string,mixed):int|bool $updatePostMeta
     * @param null|callable(int,string):bool $deletePostMeta
     * @param null|callable(mixed):mixed $slash
     */
    public function __construct(
        private readonly PostMetaRegistrationCompiler $compiler = new PostMetaRegistrationCompiler(),
        private readonly FieldValueNormalizer $values = new FieldValueNormalizer(),
        ?callable $getPostType = null,
        ?callable $metadataExists = null,
        ?callable $getPostMeta = null,
        ?callable $updatePostMeta = null,
        ?callable $deletePostMeta = null,
        ?callable $slash = null,
    ) {
        $this->getPostType = $getPostType !== null
            ? Closure::fromCallable($getPostType)
            : static function (int $postId): string|false {
                if (!function_exists('get_post_type')) {
                    throw new LogicException('WordPress get_post_type() is unavailable.');
                }
                return get_post_type($postId);
            };
        $this->metadataExists = $metadataExists !== null
            ? Closure::fromCallable($metadataExists)
            : static function (int $postId, string $metaKey): bool {
                if (!function_exists('metadata_exists')) {
                    throw new LogicException('WordPress metadata_exists() is unavailable.');
                }
                return metadata_exists('post', $postId, $metaKey);
            };
        $this->getPostMeta = $getPostMeta !== null
            ? Closure::fromCallable($getPostMeta)
            : static function (int $postId, string $metaKey, bool $single): mixed {
                if (!function_exists('get_post_meta')) {
                    throw new LogicException('WordPress get_post_meta() is unavailable.');
                }
                return get_post_meta($postId, $metaKey, $single);
            };
        $this->updatePostMeta = $updatePostMeta !== null
            ? Closure::fromCallable($updatePostMeta)
            : static function (int $postId, string $metaKey, mixed $value): int|bool {
                if (!function_exists('update_post_meta')) {
                    throw new LogicException('WordPress update_post_meta() is unavailable.');
                }
                return update_post_meta($postId, $metaKey, $value);
            };
        $this->deletePostMeta = $deletePostMeta !== null
            ? Closure::fromCallable($deletePostMeta)
            : static function (int $postId, string $metaKey): bool {
                if (!function_exists('delete_post_meta')) {
                    throw new LogicException('WordPress delete_post_meta() is unavailable.');
                }
                return delete_post_meta($postId, $metaKey);
            };
        $this->slash = $slash !== null
            ? Closure::fromCallable($slash)
            : static function (mixed $value): mixed {
                if (!function_exists('wp_slash')) {
                    throw new LogicException('WordPress wp_slash() is unavailable.');
                }
                if (!is_string($value) && !is_array($value)) {
                    return $value;
                }
                return wp_slash($value);
            };
    }

    /** @param array<string,mixed> $field */
    public function read(array $field, string $postType, int $postId): mixed
    {
        $registration = $this->context($field, $postType, $postId);
        return $this->readRegistration($field, $registration, $postId);
    }

    /** @param array<string,mixed> $field */
    public function write(array $field, string $postType, int $postId, mixed $value): PostMetaValueWriteResult
    {
        $registration = $this->context($field, $postType, $postId);
        $args = $registration['args'];
        if (($args['single'] ?? null) !== true) {
            throw new LogicException('Multiple-row post-meta replacement is not certified in persistence V1.');
        }

        $canonical = $this->values->normalize($field, $value);
        $metaKey = $registration['meta_key'];
        $fieldUuid = $registration['field_uuid'];
        if (!is_string($metaKey) || !is_string($fieldUuid)) {
            throw new LogicException('Compiled post-meta provenance is malformed.');
        }

        $exists = ($this->metadataExists)($postId, $metaKey);
        $current = $exists ? $this->readRegistration($field, $registration, $postId) : null;

        if ($canonical === null) {
            if (!$exists) {
                return new PostMetaValueWriteResult(PostMetaValueWriteResult::ABSENT, $fieldUuid, $metaKey, null);
            }

            $deleteResult = ($this->deletePostMeta)($postId, (string) ($this->slash)($metaKey));
            if (($this->metadataExists)($postId, $metaKey)) {
                throw new RuntimeException(sprintf(
                    'WordPress %s deleting post meta "%s" and the value still exists.',
                    $deleteResult ? 'reported success' : 'reported failure',
                    $metaKey,
                ));
            }

            // Verified state wins if a concurrent delete/filter returned false after the value disappeared.
            return new PostMetaValueWriteResult(PostMetaValueWriteResult::DELETED, $fieldUuid, $metaKey, null);
        }

        if ($exists && $this->sameValue($current, $canonical)) {
            return new PostMetaValueWriteResult(PostMetaValueWriteResult::UNCHANGED, $fieldUuid, $metaKey, $canonical);
        }

        $nativeResult = ($this->updatePostMeta)(
            $postId,
            (string) ($this->slash)($metaKey),
            ($this->slash)($canonical),
        );

        try {
            $persisted = $this->readRegistration($field, $registration, $postId);
        } catch (Throwable $error) {
            throw new RuntimeException(sprintf('Post-meta write verification failed for "%s".', $metaKey), 0, $error);
        }

        if (!$this->sameValue($persisted, $canonical)) {
            throw new RuntimeException(sprintf(
                'WordPress %s updating post meta "%s" but canonical verification did not match.',
                $nativeResult === false ? 'reported failure' : 'reported success',
                $metaKey,
            ));
        }

        // update_post_meta() may return false for no-change/filter/concurrency paths; post-write state is authoritative.
        return new PostMetaValueWriteResult(PostMetaValueWriteResult::WRITTEN, $fieldUuid, $metaKey, $persisted);
    }

    /**
     * @param array<string,mixed> $field
     * @return array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>}
     */
    private function context(array $field, string $postType, int $postId): array
    {
        if ($postId <= 0) {
            throw new InvalidArgumentException('Post-meta persistence requires a positive post ID.');
        }

        $registration = $this->compiler->compile($field, $postType);
        $actualPostType = ($this->getPostType)($postId);
        if ($actualPostType !== $postType) {
            throw new InvalidArgumentException(sprintf(
                'Post %d belongs to post type "%s", not "%s".',
                $postId,
                is_string($actualPostType) ? $actualPostType : 'unknown',
                $postType,
            ));
        }

        return $registration;
    }

    /**
     * @param array<string,mixed> $field
     * @param array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>} $registration
     */
    private function readRegistration(array $field, array $registration, int $postId): mixed
    {
        $metaKey = $registration['meta_key'];
        $args = $registration['args'];
        if (!($this->metadataExists)($postId, $metaKey)) {
            return null;
        }

        $single = ($args['single'] ?? null) === true;
        $type = $args['type'] ?? null;
        if (!is_string($type)) {
            throw new RuntimeException('Compiled post-meta type is malformed.');
        }

        $raw = ($this->getPostMeta)($postId, $metaKey, $single);
        try {
            $cast = $single
                ? $this->castNativeValue($raw, $type)
                : $this->castNativeList($raw, $type);
            return $this->values->normalize($field, $cast);
        } catch (InvalidArgumentException $error) {
            throw new RuntimeException(sprintf(
                'Persisted post meta "%s" is outside the canonical Field value contract.',
                $metaKey,
            ), 0, $error);
        }
    }

    private function castNativeValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'string' => $this->nativeString($value),
            'integer' => $this->nativeInteger($value),
            'number' => $this->nativeNumber($value),
            'boolean' => $this->nativeBoolean($value),
            'array' => $this->nativeArray($value),
            default => throw new RuntimeException(sprintf('Unsupported native post-meta type "%s".', $type)),
        };
    }

    /** @return list<mixed> */
    private function castNativeList(mixed $value, string $type): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new RuntimeException('Non-single post meta must be returned as a list.');
        }
        return array_map(fn (mixed $item): mixed => $this->castNativeValue($item, $type), $value);
    }

    private function nativeString(mixed $value): string
    {
        if (!is_string($value)) {
            throw new RuntimeException('Persisted string meta is not a string.');
        }
        return $value;
    }

    private function nativeInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value) || preg_match('/^-?\d+$/', $value) !== 1) {
            throw new RuntimeException('Persisted integer meta is not a canonical integer representation.');
        }
        return (int) $value;
    }

    private function nativeNumber(mixed $value): int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (!is_string($value) || !is_numeric($value)) {
            throw new RuntimeException('Persisted number meta is not numeric.');
        }
        return str_contains($value, '.') || stripos($value, 'e') !== false ? (float) $value : (int) $value;
    }

    private function nativeBoolean(mixed $value): bool
    {
        return match (true) {
            $value === true, $value === 1, $value === '1' => true,
            $value === false, $value === 0, $value === '0', $value === '' => false,
            default => throw new RuntimeException('Persisted boolean meta is not a canonical WordPress boolean representation.'),
        };
    }

    /** @return list<mixed> */
    private function nativeArray(mixed $value): array
    {
        if (!is_array($value) || !array_is_list($value)) {
            throw new RuntimeException('Persisted array meta must be a list.');
        }
        return $value;
    }

    private function sameValue(mixed $left, mixed $right): bool
    {
        if ((is_int($left) || is_float($left)) && (is_int($right) || is_float($right))) {
            return (float) $left === (float) $right;
        }
        return $left === $right;
    }
}
