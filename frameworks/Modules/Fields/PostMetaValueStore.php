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

    /** @var Closure(int,string,mixed):int|false */
    private Closure $addPostMeta;

    /** @var Closure(mixed):mixed */
    private Closure $slash;

    /**
     * @param null|callable(int):string|false $getPostType
     * @param null|callable(int,string):bool $metadataExists
     * @param null|callable(int,string,bool):mixed $getPostMeta
     * @param null|callable(int,string,mixed):int|bool $updatePostMeta
     * @param null|callable(int,string):bool $deletePostMeta
     * @param null|callable(int,string,mixed):int|false $addPostMeta
     * @param null|callable(mixed):mixed $slash
     */
    public function __construct(
        private readonly PostMetaRegistrationCompiler $compiler = new PostMetaRegistrationCompiler(),
        private readonly FieldValueNormalizer $values = new FieldValueNormalizer(),
        private readonly FieldValuePersistenceGuard $persistence = new FieldValuePersistenceGuard(),
        ?callable $getPostType = null,
        ?callable $metadataExists = null,
        ?callable $getPostMeta = null,
        ?callable $updatePostMeta = null,
        ?callable $deletePostMeta = null,
        ?callable $addPostMeta = null,
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
        $this->addPostMeta = $addPostMeta !== null
            ? Closure::fromCallable($addPostMeta)
            : static function (int $postId, string $metaKey, mixed $value): int|false {
                if (!function_exists('add_post_meta')) {
                    throw new LogicException('WordPress add_post_meta() is unavailable.');
                }
                return add_post_meta($postId, $metaKey, $value, false);
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
        $single = $args['single'] ?? null;
        if ($single === false) {
            return $this->writeMultipleRows($field, $registration, $postId, $value);
        }
        if ($single !== true) {
            throw new LogicException('Compiled post-meta single-value contract is malformed.');
        }

        $canonical = $this->persistence->assertSafe($this->values->normalize($field, $value));
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
     * @param array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>} $registration
     */
    private function writeMultipleRows(
        array $field,
        array $registration,
        int $postId,
        mixed $value,
    ): PostMetaValueWriteResult {
        $canonical = $this->persistence->assertSafe($this->values->normalize($field, $value));
        if ($canonical === null) {
            $canonical = [];
        }
        if (!is_array($canonical) || !array_is_list($canonical)) {
            throw new LogicException('Multiple-row post-meta replacement requires a canonical list value.');
        }

        $metaKey = $registration['meta_key'];
        $fieldUuid = $registration['field_uuid'];
        if (!is_string($metaKey) || !is_string($fieldUuid)) {
            throw new LogicException('Compiled post-meta provenance is malformed.');
        }

        $exists = ($this->metadataExists)($postId, $metaKey);
        $snapshot = $exists ? $this->readRegistration($field, $registration, $postId) : [];
        if (!is_array($snapshot) || !array_is_list($snapshot)) {
            throw new RuntimeException(sprintf('Existing multi-row post meta "%s" is not a canonical list.', $metaKey));
        }

        if (!$exists && $canonical === []) {
            return new PostMetaValueWriteResult(PostMetaValueWriteResult::ABSENT, $fieldUuid, $metaKey, null);
        }
        if ($this->sameValue($snapshot, $canonical)) {
            return new PostMetaValueWriteResult(PostMetaValueWriteResult::UNCHANGED, $fieldUuid, $metaKey, $canonical);
        }

        try {
            $persisted = $this->replaceMultipleRows($field, $registration, $postId, $canonical);
        } catch (Throwable $failure) {
            try {
                $this->restoreMultipleRows($field, $registration, $postId, $snapshot);
            } catch (PostMetaRecoveryException $recoveryFailure) {
                throw new PostMetaRecoveryException(sprintf(
                    'Multi-row post-meta write for "%s" failed and recovery could not verify the original row set; state is uncertain.',
                    $metaKey,
                ), 0, $recoveryFailure);
            }

            throw new RuntimeException(sprintf(
                'Multi-row post-meta write for "%s" failed; the original row set was verified as restored.',
                $metaKey,
            ), 0, $failure);
        }

        if ($canonical === []) {
            return new PostMetaValueWriteResult(PostMetaValueWriteResult::DELETED, $fieldUuid, $metaKey, null);
        }

        return new PostMetaValueWriteResult(PostMetaValueWriteResult::WRITTEN, $fieldUuid, $metaKey, $persisted);
    }

    /**
     * @param array<string,mixed> $field
     * @param array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>} $registration
     * @param list<mixed> $desired
     * @return list<mixed>
     */
    private function replaceMultipleRows(
        array $field,
        array $registration,
        int $postId,
        array $desired,
    ): array {
        $metaKey = $registration['meta_key'];
        if (!is_string($metaKey)) {
            throw new LogicException('Compiled post-meta key is malformed.');
        }
        $nativeMetaKey = (string) ($this->slash)($metaKey);

        if (($this->metadataExists)($postId, $metaKey)) {
            $deleteResult = ($this->deletePostMeta)($postId, $nativeMetaKey);
            if (($this->metadataExists)($postId, $metaKey)) {
                throw new RuntimeException(sprintf(
                    'WordPress %s clearing multi-row post meta "%s" and rows still exist.',
                    $deleteResult ? 'reported success' : 'reported failure',
                    $metaKey,
                ));
            }
        }

        foreach ($desired as $row) {
            // add_post_meta() may report false while filters or concurrent code still establish the desired row.
            // Final canonical verification is authoritative, so false is not accepted or rejected in isolation.
            ($this->addPostMeta)($postId, $nativeMetaKey, ($this->slash)($row));
        }

        if ($desired === []) {
            if (($this->metadataExists)($postId, $metaKey)) {
                throw new RuntimeException(sprintf('Multi-row post meta "%s" was expected to be absent after clearing.', $metaKey));
            }
            return [];
        }

        if (!($this->metadataExists)($postId, $metaKey)) {
            throw new RuntimeException(sprintf('Multi-row post meta "%s" is absent after replacement.', $metaKey));
        }

        $persisted = $this->readRegistration($field, $registration, $postId);
        if (!is_array($persisted) || !array_is_list($persisted) || !$this->sameValue($persisted, $desired)) {
            throw new RuntimeException(sprintf('Canonical verification failed after replacing multi-row post meta "%s".', $metaKey));
        }

        return $persisted;
    }

    /**
     * @param array<string,mixed> $field
     * @param array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>} $registration
     * @param list<mixed> $snapshot
     */
    private function restoreMultipleRows(
        array $field,
        array $registration,
        int $postId,
        array $snapshot,
    ): void {
        $metaKey = $registration['meta_key'];
        if (!is_string($metaKey)) {
            throw new PostMetaRecoveryException('Cannot recover multi-row post meta with a malformed compiled key.');
        }

        try {
            $exists = ($this->metadataExists)($postId, $metaKey);
            $current = $exists ? $this->readRegistration($field, $registration, $postId) : [];
            if (is_array($current) && array_is_list($current) && $this->sameValue($current, $snapshot)) {
                return;
            }
        } catch (Throwable) {
            // A corrupt/partial current value must not prevent a best-effort public-API restore attempt.
        }

        try {
            $nativeMetaKey = (string) ($this->slash)($metaKey);
            if (($this->metadataExists)($postId, $metaKey)) {
                ($this->deletePostMeta)($postId, $nativeMetaKey);
                if (($this->metadataExists)($postId, $metaKey)) {
                    throw new RuntimeException(sprintf('Unable to clear partial multi-row post meta "%s" during recovery.', $metaKey));
                }
            }

            foreach ($snapshot as $row) {
                ($this->addPostMeta)($postId, $nativeMetaKey, ($this->slash)($row));
            }

            if ($snapshot === []) {
                if (($this->metadataExists)($postId, $metaKey)) {
                    throw new RuntimeException(sprintf('Recovery expected multi-row post meta "%s" to remain absent.', $metaKey));
                }
                return;
            }

            if (!($this->metadataExists)($postId, $metaKey)) {
                throw new RuntimeException(sprintf('Recovery did not recreate multi-row post meta "%s".', $metaKey));
            }

            $restored = $this->readRegistration($field, $registration, $postId);
            if (!is_array($restored) || !array_is_list($restored) || !$this->sameValue($restored, $snapshot)) {
                throw new RuntimeException(sprintf('Recovery verification failed for multi-row post meta "%s".', $metaKey));
            }
        } catch (Throwable $error) {
            throw new PostMetaRecoveryException(sprintf(
                'Recovery failed for multi-row post meta "%s"; persisted state is uncertain.',
                $metaKey,
            ), 0, $error);
        }
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
            return $this->persistence->assertSafe($this->values->normalize($field, $cast));
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
        if (!is_string($value)) {
            throw new RuntimeException('Persisted integer meta is not a canonical integer representation.');
        }
        $validated = filter_var($value, FILTER_VALIDATE_INT);
        if ($validated === false) {
            throw new RuntimeException('Persisted integer meta is outside the platform integer range.');
        }
        return $validated;
    }

    private function nativeNumber(mixed $value): int|float
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            if (!is_finite($value)) {
                throw new RuntimeException('Persisted number meta must be finite.');
            }
            return $value;
        }
        if (!is_string($value) || !is_numeric($value)) {
            throw new RuntimeException('Persisted number meta is not numeric.');
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);
        $normalized = $integer !== false ? $integer : (float) $value;
        if (is_float($normalized) && !is_finite($normalized)) {
            throw new RuntimeException('Persisted number meta is outside the finite numeric range.');
        }
        return $normalized;
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
