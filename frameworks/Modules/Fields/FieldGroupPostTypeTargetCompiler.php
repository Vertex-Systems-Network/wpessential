<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class FieldGroupPostTypeTargetCompiler
{
    private const ALLOWED_SOURCES = ['post_type', 'post_status', 'entity_id'];
    private const OPERATORS = ['equals', 'not_equals', 'in', 'not_in'];

    /**
     * Compile normalized OR-of-AND Field Group location rules into a deterministic finite
     * set of WordPress post types suitable for subtype-specific metadata registration.
     *
     * post_status and entity_id remain runtime visibility/value-access constraints and do
     * not narrow Core metadata registration below the post-type boundary.
     *
     * @param array<string,mixed> $fieldGroup
     * @return list<string>
     */
    public function compile(array $fieldGroup): array
    {
        $locations = $fieldGroup['locations'] ?? null;
        if (!is_array($locations) || !array_is_list($locations) || $locations === []) {
            throw new InvalidArgumentException('Runtime post-meta binding requires at least one Field Group location rule group.');
        }

        $compiled = [];
        foreach ($locations as $group) {
            if (!is_array($group) || !array_is_list($group) || $group === []) {
                throw new InvalidArgumentException('Each Field Group location group must be a non-empty rule list.');
            }

            $positive = null;
            $excluded = [];
            $hasFinitePositiveAnchor = false;

            foreach ($group as $rule) {
                if (!is_array($rule) || array_is_list($rule)) {
                    throw new InvalidArgumentException('Each normalized Field Group location rule must be an object/map.');
                }

                $source = $rule['source'] ?? null;
                $operator = $rule['operator'] ?? null;
                $negate = $rule['negate'] ?? null;

                if (!is_string($source) || !in_array($source, self::ALLOWED_SOURCES, true)) {
                    throw new InvalidArgumentException('Runtime post-meta binding V1 encountered an unsupported or unbounded location source.');
                }
                if (!is_string($operator) || !in_array($operator, self::OPERATORS, true)) {
                    throw new InvalidArgumentException('Runtime post-meta binding V1 encountered an unsupported location operator.');
                }
                if (!is_bool($negate)) {
                    throw new InvalidArgumentException('Normalized Field Group location negate must be boolean.');
                }
                if (!array_key_exists('value', $rule)) {
                    throw new InvalidArgumentException('Normalized Field Group location rule requires a value.');
                }

                if ($source !== 'post_type') {
                    continue;
                }

                $effectiveOperator = $this->effectiveOperator($operator, $negate);
                $postTypes = $this->postTypes($effectiveOperator, $rule['value']);

                if ($effectiveOperator === 'equals' || $effectiveOperator === 'in') {
                    $hasFinitePositiveAnchor = true;
                    $positive = $positive === null
                        ? $postTypes
                        : array_values(array_intersect($positive, $postTypes));
                    continue;
                }

                foreach ($postTypes as $postType) {
                    $excluded[$postType] = true;
                }
            }

            if (!$hasFinitePositiveAnchor || $positive === null) {
                throw new InvalidArgumentException('Every registrable Field Group location group requires a finite positive post_type anchor.');
            }

            $resolved = [];
            foreach ($positive as $postType) {
                if (!isset($excluded[$postType])) {
                    $resolved[$postType] = true;
                }
            }

            foreach (array_keys($resolved) as $postType) {
                $compiled[$postType] = true;
            }
        }

        $postTypes = array_keys($compiled);
        sort($postTypes, SORT_STRING);
        if ($postTypes === []) {
            throw new InvalidArgumentException('Field Group location rules do not resolve to any finite post type target.');
        }

        return $postTypes;
    }

    private function effectiveOperator(string $operator, bool $negate): string
    {
        if (!$negate) {
            return $operator;
        }

        return match ($operator) {
            'equals' => 'not_equals',
            'not_equals' => 'equals',
            'in' => 'not_in',
            'not_in' => 'in',
            default => throw new InvalidArgumentException('Runtime post-meta binding V1 encountered an unsupported location operator.'),
        };
    }

    /** @return list<string> */
    private function postTypes(string $operator, mixed $value): array
    {
        if ($operator === 'equals' || $operator === 'not_equals') {
            return [$this->postType($value)];
        }

        if (!is_array($value) || !array_is_list($value) || $value === []) {
            throw new InvalidArgumentException('post_type in/not_in rules require a non-empty list of post types.');
        }

        $postTypes = [];
        foreach ($value as $item) {
            $postTypes[$this->postType($item)] = true;
        }

        $result = array_keys($postTypes);
        sort($result, SORT_STRING);
        return $result;
    }

    private function postType(mixed $value): string
    {
        if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $value) !== 1) {
            throw new InvalidArgumentException('post_type location values must be lowercase WordPress machine keys up to 20 characters.');
        }
        return $value;
    }
}
