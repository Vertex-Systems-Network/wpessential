<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyValidationServiceTest extends TestCase
{
    public function testValidCandidateReturnsNonMutatingPassReport(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate(['payload' => $this->payload()]);

        self::assertTrue($report['valid']);
        self::assertSame([], $report['issues']);
        self::assertSame('library_genre', $report['candidate']['taxonomy_key']);
        self::assertIsArray($report['diagnostics']);
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testDiagnosticsExposeCanonicalEffectiveArgsOverridesAndSafePreviews(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $payload = array_merge($this->payload(), [
            'labels' => ['menu_name' => 'Book Genres'],
            'rest_base' => 'genres',
            'rewrite' => ['slug' => 'library/genre', 'hierarchical' => true],
            'default_term' => ['name' => 'General'],
            'runtime_providers' => [
                'rest_controller' => 'wordpress.terms',
                'meta_box' => 'wordpress.disabled',
            ],
        ]);

        $report = $this->validation($repository)->validate(['payload' => $payload]);
        $diagnostics = $report['diagnostics'];

        self::assertTrue($report['valid']);
        self::assertIsArray($diagnostics);
        self::assertSame('Book Genres', $diagnostics['effective_args']['labels']['menu_name']);
        self::assertSame(['name' => 'General'], $diagnostics['effective_args']['default_term']);
        self::assertSame('Book Genres', $diagnostics['overrides']['labels']['menu_name']);
        self::assertSame(['slug' => 'library/genre', 'hierarchical' => true], $diagnostics['overrides']['rewrite']);
        self::assertSame([
            'meta_box' => 'wordpress.disabled',
            'rest_controller' => 'wordpress.terms',
        ], $diagnostics['provider_ids']);
        self::assertSame('/wp/v2/genres', $diagnostics['previews']['rest']['route']);
        self::assertSame('/library/genre/{parent/.../}{term-slug}/', $diagnostics['previews']['rewrite']['path_pattern']);
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testDiagnosticsMaterializeEffectiveCapabilityDefaultsAndPartialOverrides(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $defaults = $this->validation($repository)->validate(['payload' => $this->payload()]);

        self::assertSame([
            'manage_terms' => 'manage_categories',
            'edit_terms' => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ], $defaults['diagnostics']['effective_args']['capabilities']);
        self::assertArrayNotHasKey('capabilities', $defaults['diagnostics']['overrides']);

        $custom = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'capabilities' => [
                    'manage_terms' => 'manage_library_genres',
                    'assign_terms' => 'assign_library_genres',
                ],
            ]),
        ]);

        self::assertTrue($custom['valid']);
        self::assertSame([
            'manage_terms' => 'manage_library_genres',
            'edit_terms' => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'assign_library_genres',
        ], $custom['diagnostics']['effective_args']['capabilities']);
        self::assertSame([
            'manage_terms' => 'manage_library_genres',
            'assign_terms' => 'assign_library_genres',
        ], $custom['diagnostics']['overrides']['capabilities']);
    }

    public function testInvalidCandidateDoesNotExposeMisleadingDiagnostics(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), ['taxonomy_key' => 'category']),
        ]);

        self::assertFalse($report['valid']);
        self::assertNull($report['diagnostics']);
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testAssociationHealthClassifiesCanonicalDisabledPostTypeWithoutMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save(new Definition(
            id: '33333333-3333-4333-8333-333333333333',
            slug: 'library-book',
            type: 'post_type',
            schemaVersion: 1,
            ownerSurfaceId: 1,
            status: DefinitionStatus::Disabled,
            payload: [
                'post_type_key' => 'library_book',
                'name' => 'Books',
                'singular_name' => 'Book',
            ],
        ));

        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), ['object_types' => ['library_book']]),
        ]);

        self::assertTrue($report['valid']);
        self::assertIsArray($report['diagnostics']);
        self::assertSame('library_book', $report['diagnostics']['association_health'][0]['key']);
        self::assertSame('disabled', $report['diagnostics']['association_health'][0]['state']);
        self::assertTrue($report['diagnostics']['association_health'][0]['canonical']);
        self::assertSame('disabled', $report['diagnostics']['association_health'][0]['canonical_status']);
        self::assertCount(1, $repository->byType('post_type'));
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testReservedKeyIsReportedAsBlockedWithoutSaving(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), ['taxonomy_key' => 'category']),
        ]);

        self::assertFalse($report['valid']);
        self::assertSame('registration_schema_invalid', $report['issues'][0]['id']);
        self::assertSame('blocked', $report['issues'][0]['severity']);
        self::assertSame([], $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testDuplicateCanonicalKeyIsReportedAcrossLifecycleStates(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, $this->payload());
        $this->status($repository, $created['id'], 1, 'archived');

        $report = $this->validation($repository)->validate(['payload' => $this->payload()]);

        self::assertFalse($report['valid']);
        self::assertContains('duplicate_definition', array_column($report['issues'], 'id'));
        self::assertCount(1, $repository->byType(TaxonomyDefinitionProjector::DEFINITION_TYPE));
    }

    public function testExistingRuntimeKeyRenameIsReportedAsBlocked(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, $this->payload());

        $report = $this->validation($repository)->validate([
            'id' => $created['id'],
            'payload' => array_merge($this->payload(), ['taxonomy_key' => 'library_topic']),
        ]);

        self::assertFalse($report['valid']);
        self::assertContains('runtime_key_immutable', array_column($report['issues'], 'id'));
        self::assertSame(1, $repository->get($created['id'])?->revision);
    }

    public function testPublishedCanonicalRoutingCollisionsAreCompatibilityWarnings(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, array_merge($this->payload(), [
            'rewrite' => ['slug' => 'shared/topic'],
            'query_var' => 'shared_topic',
        ]));
        $this->status($repository, $created['id'], 1, 'published');

        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'taxonomy_key' => 'library_topic',
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'rewrite' => ['slug' => 'shared/topic'],
                'query_var' => 'shared_topic',
            ]),
        ]);

        self::assertTrue($report['valid']);
        $issuesById = [];
        foreach ($report['issues'] as $issue) {
            $issuesById[$issue['id']] = $issue;
        }
        self::assertArrayHasKey('rewrite_route_collision', $issuesById);
        self::assertArrayHasKey('query_var_collision', $issuesById);
        self::assertSame('compatibility_warning', $issuesById['rewrite_route_collision']['severity']);
        self::assertSame('compatibility_warning', $issuesById['query_var_collision']['severity']);
        self::assertSame('rewrite', $issuesById['rewrite_route_collision']['field']);
        self::assertSame('query_var', $issuesById['query_var_collision']['field']);
    }

    public function testPublishedCanonicalRestRouteCollisionIsCompatibilityWarning(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, array_merge($this->payload(), [
            'rest_base' => 'shared-terms',
            'rest_namespace' => 'acme/v1',
        ]));
        $this->status($repository, $created['id'], 1, 'published');

        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'taxonomy_key' => 'library_topic',
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'rest_base' => 'shared-terms',
                'rest_namespace' => 'acme/v1',
            ]),
        ]);

        self::assertTrue($report['valid']);
        $issuesById = [];
        foreach ($report['issues'] as $issue) {
            $issuesById[$issue['id']] = $issue;
        }
        self::assertArrayHasKey('rest_route_collision', $issuesById);
        self::assertSame('compatibility_warning', $issuesById['rest_route_collision']['severity']);
        self::assertSame('rest_base', $issuesById['rest_route_collision']['field']);
        self::assertStringContainsString('/acme/v1/shared-terms', $issuesById['rest_route_collision']['message']);
    }

    public function testDisabledCanonicalTaxonomyDoesNotCreateRoutingCollisionWarning(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $this->save($repository, array_merge($this->payload(), [
            'rewrite' => ['slug' => 'shared/topic'],
            'query_var' => 'shared_topic',
            'rest_base' => 'shared-terms',
            'rest_namespace' => 'acme/v1',
        ]));

        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'taxonomy_key' => 'library_topic',
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'rewrite' => ['slug' => 'shared/topic'],
                'query_var' => 'shared_topic',
                'rest_base' => 'shared-terms',
                'rest_namespace' => 'acme/v1',
            ]),
        ]);

        $ids = array_column($report['issues'], 'id');
        self::assertNotContains('rewrite_route_collision', $ids);
        self::assertNotContains('query_var_collision', $ids);
        self::assertNotContains('rest_route_collision', $ids);
    }

    public function testPublishedTaxonomyWithoutRestExposureDoesNotOwnRestRoute(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, array_merge($this->payload(), [
            'show_in_rest' => false,
            'rest_base' => 'shared-terms',
            'rest_namespace' => 'acme/v1',
        ]));
        $this->status($repository, $created['id'], 1, 'published');

        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'taxonomy_key' => 'library_topic',
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'rest_base' => 'shared-terms',
                'rest_namespace' => 'acme/v1',
            ]),
        ]);

        self::assertNotContains('rest_route_collision', array_column($report['issues'], 'id'));
    }

    public function testShowInMenuWarnsWhenExplicitlyEnabledUnderDisabledShowUi(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $explicitParent = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'show_ui' => false,
                'show_in_menu' => true,
            ]),
        ]);
        $inheritedParent = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'public' => false,
                'show_in_menu' => true,
            ]),
        ]);

        foreach ([$explicitParent, $inheritedParent] as $report) {
            self::assertTrue($report['valid']);
            $issuesById = [];
            foreach ($report['issues'] as $issue) {
                $issuesById[$issue['id']] = $issue;
            }
            self::assertArrayHasKey('show_in_menu_dormant', $issuesById);
            self::assertSame('compatibility_warning', $issuesById['show_in_menu_dormant']['severity']);
            self::assertSame('show_in_menu', $issuesById['show_in_menu_dormant']['field']);
            self::assertStringContainsString('WordPress forces show_in_menu=false', $issuesById['show_in_menu_dormant']['message']);
        }
    }

    public function testDormantWarningsClearWhenParentStatesMakeSettingsEffective(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'publicly_queryable' => true,
                'query_var' => 'library_genre_query',
            ]),
        ]);

        $ids = array_column($report['issues'], 'id');
        self::assertTrue($report['valid']);
        self::assertNotContains('show_in_menu_dormant', $ids);
        self::assertNotContains('query_var_dormant', $ids);
    }

    public function testDormantQueryVarWarnsAndDoesNotClaimCanonicalCollision(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, array_merge($this->payload(), [
            'query_var' => 'shared_topic',
        ]));
        $this->status($repository, $created['id'], 1, 'published');

        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'taxonomy_key' => 'library_topic',
                'name' => 'Topics',
                'singular_name' => 'Topic',
                'publicly_queryable' => false,
                'query_var' => 'shared_topic',
            ]),
        ]);

        $issuesById = [];
        foreach ($report['issues'] as $issue) {
            $issuesById[$issue['id']] = $issue;
        }
        self::assertTrue($report['valid']);
        self::assertArrayHasKey('query_var_dormant', $issuesById);
        self::assertSame('compatibility_warning', $issuesById['query_var_dormant']['severity']);
        self::assertSame('query_var', $issuesById['query_var_dormant']['field']);
        self::assertStringContainsString('front-end query_var to false', $issuesById['query_var_dormant']['message']);
        self::assertArrayNotHasKey('query_var_collision', $issuesById);
    }

    public function testExplicitlyDisabledQueryVarDoesNotProduceDormantWarning(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), [
                'publicly_queryable' => false,
                'query_var' => false,
            ]),
        ]);

        self::assertTrue($report['valid']);
        self::assertNotContains('query_var_dormant', array_column($report['issues'], 'id'));
    }

    private function validation(InMemoryDefinitionRepository $repository): TaxonomyValidationService
    {
        return new TaxonomyValidationService(
            $repository,
            new TaxonomyDefinitionProjector(),
        );
    }

    private function handler(InMemoryDefinitionRepository $repository, string $action): TaxonomyAbilityHandler
    {
        $projector = new TaxonomyDefinitionProjector();
        return new TaxonomyAbilityHandler(
            $repository,
            $projector,
            new TaxonomyValidationService($repository, $projector),
            $action,
        );
    }

    /** @param array<string,mixed> $payload @return array<string,mixed> */
    private function save(InMemoryDefinitionRepository $repository, array $payload): array
    {
        return $this->handler($repository, TaxonomyAbilityHandler::SAVE)
            ->handle(['payload' => $payload], $this->context())['definition'];
    }

    private function status(
        InMemoryDefinitionRepository $repository,
        string $id,
        int $revision,
        string $status,
    ): void {
        $this->handler($repository, TaxonomyAbilityHandler::STATUS)->handle([
            'id' => $id,
            'expected_revision' => $revision,
            'status' => $status,
        ], $this->context());
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'taxonomy_key' => 'library_genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'public' => true,
            'show_in_rest' => true,
        ];
    }
}
