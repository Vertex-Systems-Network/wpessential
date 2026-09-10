<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class TaxonomyAdaptiveLabelsTest extends TestCase
{
    public function testGeneratesCategoryLikeLabelsForHierarchicalTaxonomyByDefault(): void
    {
        $labels = $this->projectLabels([
            'hierarchical' => true,
        ]);

        self::assertSame('Genres', $labels['name']);
        self::assertSame('Genre', $labels['singular_name']);
        self::assertSame('Genres', $labels['menu_name']);
        self::assertSame('Parent Genre', $labels['parent_item']);
        self::assertSame('Parent Genre:', $labels['parent_item_colon']);
        self::assertArrayNotHasKey('popular_items', $labels);
        self::assertArrayNotHasKey('separate_items_with_commas', $labels);
    }

    public function testGeneratesTagLikeLabelsForNonHierarchicalTaxonomyByDefault(): void
    {
        $labels = $this->projectLabels([
            'hierarchical' => false,
        ]);

        self::assertSame('Popular Genres', $labels['popular_items']);
        self::assertSame('Separate Genres with commas', $labels['separate_items_with_commas']);
        self::assertSame('Add or remove Genres', $labels['add_or_remove_items']);
        self::assertSame('Choose from the most used Genres', $labels['choose_from_most_used']);
        self::assertArrayNotHasKey('parent_item', $labels);
        self::assertArrayNotHasKey('parent_item_colon', $labels);
    }

    public function testExplicitLabelOverridesAlwaysWinOverGeneratedLabels(): void
    {
        $labels = $this->projectLabels([
            'hierarchical' => true,
            'labels' => [
                'menu_name' => 'Library Genres',
                'parent_item' => 'Broader Genre',
                'not_found' => 'No library genres found',
            ],
        ]);

        self::assertSame('Library Genres', $labels['menu_name']);
        self::assertSame('Broader Genre', $labels['parent_item']);
        self::assertSame('No library genres found', $labels['not_found']);
        self::assertSame('Parent Genre:', $labels['parent_item_colon']);
    }

    public function testAutomaticLabelsCanBeDisabledWithoutDiscardingExplicitOverrides(): void
    {
        $labels = $this->projectLabels([
            'automatic_labels' => false,
            'hierarchical' => false,
            'labels' => [
                'menu_name' => 'Curated Genres',
            ],
        ]);

        self::assertSame([
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'menu_name' => 'Curated Genres',
        ], $labels);
    }

    public function testRejectsNonBooleanAutomaticLabelsFlag(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('automatic_labels must be boolean.');

        $this->projectLabels([
            'automatic_labels' => 'yes',
        ]);
    }

    /** @param array<string,mixed> $extra */
    private function projectLabels(array $extra): array
    {
        $definition = new Definition(
            id: '33333333-3333-4333-8333-333333333333',
            slug: 'adaptive-genre-definition',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: array_merge([
                'taxonomy_key' => 'adaptive_genre',
                'object_types' => ['post'],
                'name' => 'Genres',
                'singular_name' => 'Genre',
            ], $extra),
        );

        $registration = (new TaxonomyDefinitionProjector())->project($definition);
        $labels = $registration->payload['args']['labels'] ?? null;
        self::assertIsArray($labels);

        return $labels;
    }
}
