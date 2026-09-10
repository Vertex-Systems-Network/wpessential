<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyLabelPolicy;

final class TaxonomyLabelPolicyTest extends TestCase
{
    public function testOwnsCompleteReviewedOverrideInventoryAndGenerationModes(): void
    {
        $keys = TaxonomyLabelPolicy::overrideKeys();

        self::assertCount(28, $keys);
        self::assertSame($keys, array_values(array_unique($keys)));
        self::assertSame(TaxonomyLabelPolicy::GENERATION_COMMON, TaxonomyLabelPolicy::generationMode('menu_name'));
        self::assertSame(TaxonomyLabelPolicy::GENERATION_COMMON, TaxonomyLabelPolicy::generationMode('not_found'));
        self::assertSame(TaxonomyLabelPolicy::GENERATION_HIERARCHICAL, TaxonomyLabelPolicy::generationMode('parent_item'));
        self::assertSame(TaxonomyLabelPolicy::GENERATION_FLAT, TaxonomyLabelPolicy::generationMode('popular_items'));
        self::assertSame(TaxonomyLabelPolicy::GENERATION_WORDPRESS, TaxonomyLabelPolicy::generationMode('template_name'));
        self::assertSame(TaxonomyLabelPolicy::GENERATION_WORDPRESS, TaxonomyLabelPolicy::generationMode('name_field_description'));
    }

    public function testCompilesHierarchyAwareLabelsAndPreservesExplicitOverrides(): void
    {
        $flat = TaxonomyLabelPolicy::compile('Genres', 'Genre', false, true, []);
        self::assertSame('Popular Genres', $flat['popular_items']);
        self::assertArrayNotHasKey('parent_item', $flat);
        self::assertArrayNotHasKey('template_name', $flat);

        $hierarchical = TaxonomyLabelPolicy::compile('Genres', 'Genre', true, true, [
            'menu_name' => 'Library Genres',
        ]);
        self::assertSame('Library Genres', $hierarchical['menu_name']);
        self::assertSame('Parent Genre', $hierarchical['parent_item']);
        self::assertArrayNotHasKey('popular_items', $hierarchical);
        self::assertArrayNotHasKey('template_name', $hierarchical);
    }

    public function testAutomaticOptOutLeavesWordPressDefaultsUnmaterialized(): void
    {
        self::assertSame([
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'menu_name' => 'Curated Genres',
        ], TaxonomyLabelPolicy::compile('Genres', 'Genre', false, false, [
            'menu_name' => 'Curated Genres',
        ]));
    }

    public function testRejectsUnsupportedOverrideKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported Taxonomy label');

        TaxonomyLabelPolicy::compile('Genres', 'Genre', false, true, [
            'unsafe_callback_label' => 'Nope',
        ]);
    }
}
