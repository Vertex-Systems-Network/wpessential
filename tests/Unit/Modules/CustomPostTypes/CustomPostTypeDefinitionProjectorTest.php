<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeRegistrationProvider;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;

final class CustomPostTypeDefinitionProjectorTest extends TestCase
{
    public function testProjectsPublishedSurfaceOneDefinitionToTypedPostTypeRegistration(): void
    {
        $definition = $this->definition(DefinitionStatus::Published, [
            'post_type_key' => 'library_book',
            'name' => 'Books',
            'singular_name' => 'Book',
            'labels' => ['add_new_item' => 'Add Book', 'not_found' => ''],
            'publicly_queryable' => null,
            'supports' => ['title', 'editor', 'thumbnail', 'title'],
            'has_archive' => 'books',
            'rewrite' => ['slug' => 'library/books', 'with_front' => false, 'pages' => true],
            'taxonomies' => ['category'],
        ]);

        $registration = (new CustomPostTypeDefinitionProjector())->project($definition);

        self::assertSame(RegistrationKind::PostType, $registration->kind);
        self::assertSame('library_book', $registration->key);
        self::assertTrue($registration->payload['public']);
        self::assertTrue($registration->payload['show_in_rest']);
        self::assertSame(['title', 'editor', 'thumbnail'], $registration->payload['supports']);
        self::assertSame('Books', $registration->payload['labels']['name']);
        self::assertSame('Add Book', $registration->payload['labels']['add_new_item']);
        self::assertArrayNotHasKey('not_found', $registration->payload['labels']);
        self::assertArrayNotHasKey('publicly_queryable', $registration->payload);
        self::assertSame('books', $registration->payload['has_archive']);
        self::assertSame('library/books', $registration->payload['rewrite']['slug']);
    }

    public function testRejectsReservedCorePostTypeKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CustomPostTypeDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'post_type_key' => 'post',
            'name' => 'Posts',
            'singular_name' => 'Post',
        ]));
    }

    public function testRejectsExecutableOrUnplannedRegistrationField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new CustomPostTypeDefinitionProjector())->project($this->definition(DefinitionStatus::Published, [
            'post_type_key' => 'book',
            'name' => 'Books',
            'singular_name' => 'Book',
            'register_meta_box_cb' => 'dangerous_callback',
        ]));
    }

    public function testProviderOnlyEmitsPublishedDefinitions(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(DefinitionStatus::Published, [
            'post_type_key' => 'book', 'name' => 'Books', 'singular_name' => 'Book',
        ]));
        $repository->save(new Definition(
            id: '22222222-2222-4222-8222-222222222222',
            slug: 'movie-definition',
            type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 1,
            status: DefinitionStatus::Draft,
            payload: ['name' => 'Movies', 'singular_name' => 'Movie'],
        ));

        $definitions = iterator_to_array((new CustomPostTypeRegistrationProvider($repository))->definitions());

        self::assertCount(1, $definitions);
        self::assertSame('book', $definitions[0]->key);
    }

    /** @param array<string,mixed> $payload */
    private function definition(DefinitionStatus $status, array $payload): Definition
    {
        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'book-definition',
            type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: 1,
            status: $status,
            payload: $payload,
        );
    }
}
