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
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class FieldValueTargetResolver
{
    private const SUPPORTED_LOCATION_SOURCES = ['post_type', 'post_status', 'entity_id'];

    /** @var Closure(int):string|false */
    private Closure $getPostType;

    /** @var Closure(int):string|false */
    private Closure $getPostStatus;

    /**
     * @param null|callable(int):string|false $getPostType
     * @param null|callable(int):string|false $getPostStatus
     */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly FieldGroupDefinitionNormalizer $normalizer,
        ?callable $getPostType = null,
        ?callable $getPostStatus = null,
    ) {
        $this->getPostType = $getPostType !== null
            ? Closure::fromCallable($getPostType)
            : static function (int $postId): string|false {
                if (!function_exists('get_post_type')) {
                    throw new LogicException('WordPress get_post_type() is unavailable.');
                }
                return get_post_type($postId);
            };
        $this->getPostStatus = $getPostStatus !== null
            ? Closure::fromCallable($getPostStatus)
            : static function (int $postId): string|false {
                if (!function_exists('get_post_status')) {
                    throw new LogicException('WordPress get_post_status() is unavailable.');
                }
                return get_post_status($postId);
            };
    }

    public function resolve(string $groupId, string $fieldUuid, int $postId): ResolvedFieldValueTarget
    {
        $this->assertUuid($groupId, 'Field Group id');
        $this->assertUuid($fieldUuid, 'Field uuid');
        if ($postId < 1) {
            throw new InvalidArgumentException('Field value target post_id must be a positive integer.');
        }

        $definition = $this->definitions->get($groupId);
        if (!$definition instanceof Definition
            || $definition->type !== FieldGroupDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID
            || $definition->status !== DefinitionStatus::Published
        ) {
            throw new RuntimeException('Published Field Group target is not available.');
        }

        try {
            $payload = $this->normalizer->normalize($definition->payload, true);
        } catch (InvalidArgumentException $error) {
            throw new RuntimeException('Published Field Group payload is outside the canonical Surface 3 contract.', 0, $error);
        }

        $postType = ($this->getPostType)($postId);
        $postStatus = ($this->getPostStatus)($postId);
        if (!is_string($postType) || $postType === '' || !is_string($postStatus) || $postStatus === '') {
            throw new RuntimeException('Field value target post is not available.');
        }

        $this->assertLocationSupported($payload['locations'] ?? []);
        if (!$this->matchesLocation($payload['locations'] ?? [], $postId, $postType, $postStatus)) {
            throw new FieldValueTargetMismatchException('Published Field Group does not target the requested post.');
        }

        $fields = $payload['fields'] ?? null;
        if (!is_array($fields) || !array_is_list($fields)) {
            throw new RuntimeException('Published Field Group fields are malformed.');
        }

        foreach ($fields as $field) {
            if (!is_array($field) || ($field['uuid'] ?? null) !== $fieldUuid) {
                continue;
            }
            $fieldKey = $field['key'] ?? null;
            if (!is_string($fieldKey) || $fieldKey === '') {
                throw new RuntimeException('Resolved Field target key is malformed.');
            }
            return new ResolvedFieldValueTarget(
                groupId: $definition->id,
                groupRevision: $definition->revision,
                fieldUuid: $fieldUuid,
                fieldKey: $fieldKey,
                postId: $postId,
                postType: $postType,
                field: $field,
            );
        }

        throw new RuntimeException('Top-level Field value target is not available in the published Field Group.');
    }

    private function assertLocationSupported(mixed $locations): void
    {
        if (!is_array($locations) || !array_is_list($locations) || $locations === []) {
            throw new RuntimeException('Published Field Group has no certified post target location.');
        }
        foreach ($locations as $group) {
            if (!is_array($group) || !array_is_list($group) || $group === []) {
                throw new RuntimeException('Published Field Group location contract is malformed.');
            }
            foreach ($group as $rule) {
                $source = is_array($rule) ? ($rule['source'] ?? null) : null;
                if (!is_string($source) || !in_array($source, self::SUPPORTED_LOCATION_SOURCES, true)) {
                    throw new LogicException(sprintf(
                        'Field value Ability V1 does not certify location source "%s".',
                        is_string($source) ? $source : 'unknown',
                    ));
                }
            }
        }
    }

    private function matchesLocation(mixed $locations, int $postId, string $postType, string $postStatus): bool
    {
        foreach ($locations as $group) {
            $matched = true;
            foreach ($group as $rule) {
                if (!$this->matchesRule($rule, $postId, $postType, $postStatus)) {
                    $matched = false;
                    break;
                }
            }
            if ($matched) {
                return true;
            }
        }
        return false;
    }

    /** @param array<string,mixed> $rule */
    private function matchesRule(array $rule, int $postId, string $postType, string $postStatus): bool
    {
        $source = $rule['source'] ?? null;
        $operator = $rule['operator'] ?? null;
        $negate = $rule['negate'] ?? false;
        if (!is_string($source) || !is_string($operator) || !is_bool($negate) || !array_key_exists('value', $rule)) {
            throw new RuntimeException('Published Field Group location rule is malformed.');
        }

        $actual = match ($source) {
            'post_type' => $postType,
            'post_status' => $postStatus,
            'entity_id' => $postId,
            default => throw new LogicException('Unsupported Field value target location source.'),
        };
        $expected = $this->normalizeExpected($source, $rule['value']);

        $match = match ($operator) {
            'equals' => $this->equals($actual, $expected),
            'not_equals' => !$this->equals($actual, $expected),
            'in' => $this->contains($actual, $expected),
            'not_in' => !$this->contains($actual, $expected),
            default => throw new RuntimeException('Published Field Group location operator is malformed.'),
        };

        return $negate ? !$match : $match;
    }

    private function normalizeExpected(string $source, mixed $value): mixed
    {
        if (is_array($value)) {
            if (!array_is_list($value)) {
                throw new RuntimeException('Field Group location list value must be a list.');
            }
            return array_map(fn (mixed $item): mixed => $this->normalizeExpectedScalar($source, $item), $value);
        }
        return $this->normalizeExpectedScalar($source, $value);
    }

    private function normalizeExpectedScalar(string $source, mixed $value): int|string
    {
        if ($source === 'entity_id') {
            if (is_int($value) && $value > 0) {
                return $value;
            }
            if (is_string($value)) {
                $normalized = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($normalized !== false) {
                    return $normalized;
                }
            }
            throw new RuntimeException('Field Group entity_id location value must be a positive platform-range post ID.');
        }
        if (!is_string($value) || $value === '') {
            throw new RuntimeException(sprintf('Field Group %s location value must be a non-empty string.', $source));
        }
        return $value;
    }

    private function equals(int|string $actual, mixed $expected): bool
    {
        return !is_array($expected) && $actual === $expected;
    }

    private function contains(int|string $actual, mixed $expected): bool
    {
        if (!is_array($expected) || !array_is_list($expected)) {
            throw new RuntimeException('Field Group in/not_in location operator requires a list value.');
        }
        return in_array($actual, $expected, true);
    }

    private function assertUuid(string $value, string $label): void
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase RFC 4122 UUID.', $label));
        }
    }
}
