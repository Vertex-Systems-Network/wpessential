<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Taxonomies\TaxonomyAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyCptUiImportCommitAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyCptUiImportMapper;
use WPEssential\Modules\Taxonomies\TaxonomyCptUiImportPreviewService;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyCptUiImportCommitTest extends TestCase
{
    public function testCreateAndRevisionSafeUpdateUseCanonicalSavePath(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);
        $context = new ExecutionContext(new Principal(1), 1);

        $created = $handler->handle(['source' => $this->source('Book Genres')], $context);
        self::assertIsArray($created);
        self::assertIsArray($created['definition'] ?? null);
        $definition = $created['definition'];
        self::assertSame('draft', $definition['status']);
        self::assertSame(1, $definition['revision']);
        self::assertSame('book_genre', $definition['payload']['taxonomy_key']);
        self::assertSame('Book Genres', $definition['payload']['name']);
        self::assertSame(['future_cptui_field'], $created['import']['unsupported_fields']);

        $id = $definition['id'];
        self::assertIsString($id);
        $persisted = $repository->get($id);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(DefinitionStatus::Draft, $persisted->status);
        self::assertSame(1, $persisted->revision);

        $updated = $handler->handle([
            'source' => $this->source('Imported Genres'),
            'id' => $id,
            'expected_revision' => 1,
        ], $context);
        self::assertIsArray($updated);
        self::assertSame($id, $updated['definition']['id']);
        self::assertSame('draft', $updated['definition']['status']);
        self::assertSame(2, $updated['definition']['revision']);
        self::assertSame('Imported Genres', $updated['definition']['payload']['name']);

        $persisted = $repository->get($id);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(2, $persisted->revision);
        self::assertSame('Imported Genres', $persisted->payload['name']);
    }

    public function testStaleRevisionAndTaxonomyKeyChangeFailClosedWithoutMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);
        $context = new ExecutionContext(new Principal(1), 1);
        $created = $handler->handle(['source' => $this->source('Book Genres')], $context);
        $id = $created['definition']['id'];
        self::assertIsString($id);

        $updated = $handler->handle([
            'source' => $this->source('Imported Genres'),
            'id' => $id,
            'expected_revision' => 1,
        ], $context);
        self::assertSame(2, $updated['definition']['revision']);

        try {
            $handler->handle([
                'source' => $this->source('Stale Genres'),
                'id' => $id,
                'expected_revision' => 1,
            ], $context);
            self::fail('Expected stale revision to fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('Taxonomy write conflict', $exception->getMessage());
        }

        $persisted = $repository->get($id);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(2, $persisted->revision);
        self::assertSame('Imported Genres', $persisted->payload['name']);

        $renamedSource = $this->source('Renamed Genres');
        $renamedSource['name'] = 'renamed_genre';
        try {
            $handler->handle([
                'source' => $renamedSource,
                'id' => $id,
                'expected_revision' => 2,
            ], $context);
            self::fail('Expected taxonomy key change to fail closed.');
        } catch (InvalidArgumentException $exception) {
            self::assertStringContainsString('immutable', $exception->getMessage());
        }

        $persisted = $repository->get($id);
        self::assertInstanceOf(Definition::class, $persisted);
        self::assertSame(2, $persisted->revision);
        self::assertSame('book_genre', $persisted->payload['taxonomy_key']);
    }

    public function testExecutableSourceIsRejectedBeforeCanonicalMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $handler = $this->handler($repository);
        $source = $this->source('Unsafe Genres');
        $source['rest_controller_class'] = 'Vendor\\UnsafeController';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('executable callback/class input');
        try {
            $handler->handle(
                ['source' => $source],
                new ExecutionContext(new Principal(1), 1),
            );
        } finally {
            self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
        }
    }

    private function handler(InMemoryDefinitionRepository $repository): TaxonomyCptUiImportCommitAbilityHandler
    {
        $projector = new TaxonomyDefinitionProjector();
        $validation = new TaxonomyValidationService($repository, $projector);
        $preview = new TaxonomyCptUiImportPreviewService(
            new TaxonomyCptUiImportMapper(),
            $validation,
        );
        $save = new TaxonomyAbilityHandler(
            $repository,
            $projector,
            $validation,
            TaxonomyAbilityHandler::SAVE,
        );
        return new TaxonomyCptUiImportCommitAbilityHandler($preview, $save);
    }

    /** @return array<string,mixed> */
    private function source(string $label): array
    {
        return [
            'name' => 'book_genre',
            'label' => $label,
            'singular_label' => 'Book Genre',
            'description' => 'Imported from CPT UI',
            'object_types' => ['post'],
            'public' => '1',
            'show_in_rest' => 'true',
            'hierarchical' => '0',
            'rewrite' => '1',
            'rewrite_slug' => 'library/genre',
            'rewrite_withfront' => '0',
            'query_var' => '1',
            'query_var_slug' => 'book_genre',
            'rest_base' => 'genres',
            'rest_namespace' => 'acme/v1',
            'labels' => ['menu_name' => $label],
            'future_cptui_field' => 'warning-only',
        ];
    }
}
