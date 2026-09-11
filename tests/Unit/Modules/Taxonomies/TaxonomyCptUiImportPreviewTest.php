<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyCptUiImportMapper;
use WPEssential\Modules\Taxonomies\TaxonomyCptUiImportPreviewService;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyCptUiImportPreviewTest extends TestCase
{
    public function testPreviewMapsSupportedCptUiFieldsWithoutMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $service = new TaxonomyCptUiImportPreviewService(
            new TaxonomyCptUiImportMapper(),
            new TaxonomyValidationService($repository, new TaxonomyDefinitionProjector()),
        );

        $report = $service->preview([
            'name' => 'book_genre',
            'label' => 'Book Genres',
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
            'labels' => ['menu_name' => 'Book Genres'],
            'future_cptui_field' => 'preserve-as-warning-only',
        ]);

        self::assertTrue($report['valid']);
        self::assertSame('book_genre', $report['payload']['taxonomy_key']);
        self::assertSame(['post'], $report['payload']['object_types']);
        self::assertFalse($report['payload']['automatic_labels']);
        self::assertSame(['slug' => 'library/genre', 'with_front' => false], $report['payload']['rewrite']);
        self::assertSame('book_genre', $report['payload']['query_var']);
        self::assertSame('genres', $report['payload']['rest_base']);
        self::assertSame('acme/v1', $report['payload']['rest_namespace']);
        self::assertSame(['menu_name' => 'Book Genres'], $report['payload']['labels']);
        self::assertSame(['future_cptui_field'], $report['unsupported_fields']);
        self::assertSame([], $report['rejected_fields']);
        self::assertContains('cptui_unsupported_fields', array_column($report['issues'], 'id'));
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testExecutableCptUiFieldsFailClosedAndAreNeverMapped(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $service = new TaxonomyCptUiImportPreviewService(
            new TaxonomyCptUiImportMapper(),
            new TaxonomyValidationService($repository, new TaxonomyDefinitionProjector()),
        );

        $report = $service->preview([
            'name' => 'book_genre',
            'label' => 'Book Genres',
            'singular_label' => 'Book Genre',
            'object_types' => ['post'],
            'rest_controller_class' => 'Vendor\\UnsafeController',
            'meta_box_cb' => 'unsafe_callback',
        ]);

        self::assertFalse($report['valid']);
        self::assertSame(['meta_box_cb', 'rest_controller_class'], $report['rejected_fields']);
        self::assertContains('cptui_executable_field_rejected', array_column($report['issues'], 'id'));
        self::assertArrayNotHasKey('rest_controller_class', $report['payload']);
        self::assertArrayNotHasKey('meta_box_cb', $report['payload']);
        self::assertNull($report['diagnostics']);
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }
}
