<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupPostTypeTargetCompiler;

final class FieldGroupPostTypeTargetCompilerTest extends TestCase
{
    public function testCompilesOrOfAndGroupsToSortedUniqueFiniteTargets(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'catalog_fields',
            'title' => 'Catalog Fields',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [
                [
                    ['source' => 'post_type', 'operator' => 'in', 'value' => ['post', 'page', 'post']],
                    ['source' => 'post_status', 'operator' => 'not_equals', 'value' => 'trash'],
                ],
                [
                    ['source' => 'post_type', 'operator' => 'equals', 'value' => 'product'],
                    ['source' => 'entity_id', 'operator' => 'in', 'value' => [10, 11]],
                ],
            ],
        ], true);

        self::assertSame(
            ['page', 'post', 'product'],
            (new FieldGroupPostTypeTargetCompiler())->compile($group),
        );
    }

    public function testPositiveIntersectionAndNegativeSubtractionPreserveAndSemantics(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'intersected',
            'title' => 'Intersected',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'not_equals', 'value' => 'page'],
                ['source' => 'post_type', 'operator' => 'in', 'value' => ['post', 'page', 'product']],
                ['source' => 'post_type', 'operator' => 'in', 'value' => ['post', 'page']],
            ]],
        ], true);

        self::assertSame(
            ['post'],
            (new FieldGroupPostTypeTargetCompiler())->compile($group),
        );
    }

    public function testNegatedNegativeOperatorCanEstablishEffectiveFinitePositiveAnchor(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'double_negative',
            'title' => 'Double Negative',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'not_in', 'value' => ['page', 'post'], 'negate' => true],
            ]],
        ], true);

        self::assertSame(
            ['page', 'post'],
            (new FieldGroupPostTypeTargetCompiler())->compile($group),
        );
    }

    public function testRejectsNegativeOnlyPostTypeUniverse(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'negative_only',
            'title' => 'Negative Only',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'not_in', 'value' => ['page', 'post']],
            ]],
        ], true);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupPostTypeTargetCompiler())->compile($group);
    }

    public function testNegatedEqualsDoesNotCountAsPositiveAnchor(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'negated_equals',
            'title' => 'Negated Equals',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'post', 'negate' => true],
            ]],
        ], true);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupPostTypeTargetCompiler())->compile($group);
    }

    public function testRejectsUnsupportedLocationSourceInsteadOfBroadeningScope(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'template_scoped',
            'title' => 'Template Scoped',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'page'],
                ['source' => 'page_template', 'operator' => 'equals', 'value' => 'templates/landing.php'],
            ]],
        ], true);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupPostTypeTargetCompiler())->compile($group);
    }

    public function testContradictoryOrGroupDoesNotBroadenAnotherFiniteGroup(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'mixed_or',
            'title' => 'Mixed OR',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [
                [
                    ['source' => 'post_type', 'operator' => 'equals', 'value' => 'post'],
                    ['source' => 'post_type', 'operator' => 'not_equals', 'value' => 'post'],
                ],
                [
                    ['source' => 'post_type', 'operator' => 'equals', 'value' => 'page'],
                ],
            ],
        ], true);

        self::assertSame(
            ['page'],
            (new FieldGroupPostTypeTargetCompiler())->compile($group),
        );
    }

    public function testRejectsWhenAllGroupsResolveToNoFiniteTarget(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'empty_target',
            'title' => 'Empty Target',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'post'],
                ['source' => 'post_type', 'operator' => 'not_equals', 'value' => 'post'],
            ]],
        ], true);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupPostTypeTargetCompiler())->compile($group);
    }

    public function testRejectsMalformedPostTypeValues(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'malformed_target',
            'title' => 'Malformed Target',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'equals', 'value' => 'Invalid Post Type'],
            ]],
        ], true);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupPostTypeTargetCompiler())->compile($group);
    }
}
