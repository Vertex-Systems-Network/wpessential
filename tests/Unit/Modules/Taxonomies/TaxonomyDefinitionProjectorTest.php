<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyRegistrationProvider;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;

final class TaxonomyDefinitionProjectorTest extends TestCase
{
    public function testProjectsPublishedSurfaceTwoDefinitionToTypedTaxonomyRegistration(): void
    {
        $definition = $this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'book_genre',
            'object_types' => ['post', 'library_book', 'post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'labels' => [
                'add_new_item' => 'Add Genre',
                'menu_name' => 'Book Genres',
                'template_name' => 'Genre Pattern',
                'not_found' => '',
            ],
            'hierarchical' => true,
            'publicly_queryable' => null,
            'rewrite' => ['slug' => 'library/genre', 'with_front' => false, 'hierarchical' => true],
            'default_term' => [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Fallback genre.',
            ],
            'args' => [
                'orderby' => 'term_order',
                'order' => 'desc',
                'fields' => 'ids',
            ],
        ]);

        $registration = (new TaxonomyDefinitionProjector())->project($definition);

        self::assertSame(RegistrationKind::Taxonomy, $registration->kind);
        self::assertSame('book_genre', $registration->key);
        self::assertSame(['post', 'library_book'], $registration->payload['object_types']);
        self::assertTrue($registration->payload['args']['public']);
        self::assertTrue($registration->payload['args']['show_in_rest']);
        self::assertTrue($registration->payload['args']['hierarchical']);
        self::assertSame('Genres', $registration->payload['args']['labels']['name']);
        self::assertSame('Add Genre', $registration->payload['args']['labels']['add_new_item']);
        self::assertSame('Book Genres', $registration->payload['args']['labels']['menu_name']);
        self::assertSame('Genre Pattern', $registration->payload['args']['labels']['template_name']);
        self::assertArrayNotHasKey('not_found', $registration->payload['args']['labels']);
        self::assertArrayNotHasKey('publicly_queryable', $registration->payload['args']);
        self::assertSame('library/genre', $registration->payload['args']['rewrite']['slug']);
        self::assertSame([
            'name' => 'General',
            'slug' => 'general',
            'description' => 'Fallback genre.',
        ], $registration->payload['args']['default_term']);
        self::assertSame([
            'orderby' => 'term_order',
            'order' => 'DESC',
            'fields' => 'ids',
        ], $registration->payload['args']['args']);
    }

    public function testRejectsReservedCoreTaxonomyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'category',
            'object_types' => ['post'],
            'name' => 'Categories',
            'singular_name' => 'Category',
        ]));
    }

    public function testRejectsTaxonomyWithoutObjectTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'genre',
            'object_types' => [],
            'name' => 'Genres',
            'singular_name' => 'Genre',
        ]));
    }

    public function testRejectsExecutableOrUnplannedRegistrationField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'meta_box_cb' => 'dangerous_callback',
        ]));
    }

    public function testRejectsMalformedDefaultTerm(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'default_term' => ['slug' => 'general'],
        ]));
    }

    public function testRejectsUnboundedObjectTermArgument(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'args' => ['meta_query' => [['key' => 'unsafe-expensive-query']]],
        ]));
    }

    public function testRejectsInvalidObjectTermArgumentValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'args' => ['order' => 'random'],
        ]));
    }

    public function testProviderOnlyEmitsPublishedDefinitions(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Published, [
            'taxonomy_key' => 'genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
        ]));
        $repository->save(new Definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'topic-definition',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Draft,
            payload: ['name' => 'Topics', 'singular_name' => 'Topic'],
        ));

        $definitions = iterator_to_array((new TaxonomyRegistrationProvider($repository))->definitions());

        self::assertCount(1, $definitions);
        self::assertSame('genre', $definitions[0]->key);
    }

    /** @param array<string,mixed> $payload */
    private function definition(DefinitionStatus $status, array $payload): Definition
    {
        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'genre-definition',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: $status,
            payload: $payload,
        );
    }
}
