<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetComponentBlueprintCatalog;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetEmptyStateDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetErrorStateDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetQueryBindingDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRenderSourceDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Rendering\RenderInput;

final class DashboardWidgetRenderSourceCompilerTest extends TestCase
{
    public function testCompilesCanonicalRichTextBlueprintAndLiteralBindings(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprint = $catalog->forContentType('rich_text');
        self::assertNotNull($blueprint);

        $descriptor = $this->compiler()->compile($this->definition());

        self::assertInstanceOf(DashboardWidgetRenderSourceDescriptor::class, $descriptor);
        self::assertSame('11111111-1111-4111-8111-111111111111', $descriptor->definitionId);
        self::assertSame(3, $descriptor->definitionRevision);
        self::assertSame($blueprint->id, $descriptor->blueprintId);
        self::assertSame($blueprint->revision, $descriptor->blueprintRevision);
        self::assertSame(['content' => 'Orders'], $descriptor->bindings);

        $renderInput = $descriptor->toRenderInput();
        self::assertInstanceOf(RenderInput::class, $renderInput);
        self::assertSame($blueprint->id, $renderInput->blueprintId);
        self::assertSame(['content' => 'Orders'], $renderInput->bindings);
    }

    public function testAcceptsAllSevenCanonicalContentClassBlueprintPairs(): void
    {
        $cases = [
            'rich_text' => ['content' => 'Orders'],
            'kpi' => ['label' => 'Orders', 'value' => '12'],
            'chart' => ['labels' => ['A', 'B'], 'values' => [2, 4]],
            'quick_links' => ['labels' => ['Docs'], 'urls' => ['https://example.com/docs']],
            'announcement' => ['title' => 'Notice', 'text' => 'Maintenance'],
            'support_onboarding' => ['title' => 'Help', 'text' => 'Read the guide'],
            'icon_link' => ['icon' => 'gear', 'label' => 'Settings', 'url' => '/settings'],
        ];

        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $compiler = $this->compiler();

        foreach ($cases as $contentType => $values) {
            $blueprint = $catalog->forContentType($contentType);
            self::assertNotNull($blueprint);

            $bindings = [];
            foreach ($values as $key => $value) {
                $bindings[$key] = ['source' => 'literal', 'value' => $value];
            }

            $descriptor = $compiler->compile($this->definition(
                contentType: $contentType,
                renderSource: $this->renderSource($contentType, $bindings),
            ));

            self::assertSame($blueprint->id, $descriptor->blueprintId);
            self::assertSame($blueprint->revision, $descriptor->blueprintRevision);
        }
    }

    public function testRejectsCrossClassCanonicalBlueprintMismatch(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->compiler()->compile($this->definition(
            contentType: 'rich_text',
            renderSource: $this->renderSource('kpi', [
                'label' => ['source' => 'literal', 'value' => 'Orders'],
                'value' => ['source' => 'literal', 'value' => '12'],
            ]),
        ));
    }

    public function testRejectsWrongDefinitionOwnerTypeSchemaStatusOrContentClass(): void
    {
        $compiler = $this->compiler();

        foreach ([
            $this->definition(ownerSurfaceId: 9),
            $this->definition(type: 'listing'),
            $this->definition(schemaVersion: 2),
            $this->definition(status: DefinitionStatus::Draft),
            $this->definition(contentType: 'iframe'),
        ] as $definition) {
            try {
                $compiler->compile($definition);
                self::fail('Expected non-trusted Dashboard Widget definition to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsMissingUnknownOrMalformedRenderSource(): void
    {
        $compiler = $this->compiler();
        $valid = $this->renderSource('rich_text');

        try {
            $compiler->compile($this->definition(includeRenderSource: false));
            self::fail('Expected missing Dashboard Widget render source to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        foreach ([
            [],
            $valid + ['provider' => 'unsafe'],
            array_replace($valid, ['kind' => 'provider']),
            array_replace($valid, ['blueprint_id' => strtoupper((string) $valid['blueprint_id'])]),
            array_replace($valid, ['blueprint_revision' => 0]),
            array_replace($valid, ['blueprint_revision' => '1']),
            array_replace($valid, ['bindings' => ['not-an-envelope']]),
        ] as $renderSource) {
            try {
                $compiler->compile($this->definition(renderSource: $renderSource));
                self::fail('Expected malformed Dashboard Widget render source to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsMissingRegisteredBlueprintAndCrossSurfaceOwnership(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $contentClassCompiler = new \WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler();

        try {
            (new DashboardWidgetRenderSourceCompiler(
                new ComponentBlueprintRegistry(),
                $contentClassCompiler,
                $catalog,
            ))->compile($this->definition());
            self::fail('Expected missing canonical Blueprint registration to be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $canonical = $catalog->forContentType('rich_text');
        self::assertNotNull($canonical);
        $crossSurfaceRegistry = new ComponentBlueprintRegistry();
        $crossSurfaceRegistry->register(new ComponentBlueprintDescriptor(
            id: $canonical->id,
            revision: $canonical->revision,
            ownerSurfaceId: 9,
            componentType: $canonical->componentType,
            bindingSchema: $canonical->bindingSchema,
        ));

        $this->expectException(InvalidArgumentException::class);
        (new DashboardWidgetRenderSourceCompiler(
            $crossSurfaceRegistry,
            $contentClassCompiler,
            $catalog,
        ))->compile($this->definition());
    }

    public function testRejectsBindingKeySourceTypeAndExecutableMismatches(): void
    {
        $compiler = $this->compiler();

        $cases = [
            [],
            [
                'content' => ['source' => 'literal', 'value' => 'Orders'],
                'extra' => ['source' => 'literal', 'value' => 'x'],
            ],
            ['content' => ['source' => 'provider', 'value' => 'Orders']],
            ['content' => ['source' => 'literal', 'value' => 'Orders', 'callback' => 'unsafe']],
            ['content' => ['source' => 'literal', 'value' => 12]],
            ['content' => ['source' => 'literal', 'value' => '<script>alert(1)</script>']],
            ['content' => ['source' => 'literal', 'value' => null]],
        ];

        foreach ($cases as $bindings) {
            try {
                $compiler->compile($this->definition(
                    renderSource: $this->renderSource('rich_text', $bindings),
                ));
                self::fail('Expected unsafe or incompatible Dashboard Widget render binding to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }


    public function testCompilesBoundedDynamicContextTokenBinding(): void
    {
        $descriptor = $this->compiler()->compile($this->definition(
            renderSource: $this->renderSource('rich_text', [
                'content' => [
                    'source' => 'dynamic',
                    'source_ref' => 'context.site',
                    'value_ref' => 'display_name',
                    'resource' => 'site',
                ],
            ]),
        ));

        self::assertSame([], $descriptor->bindings);
        self::assertSame(
            [
                'content' => [
                    'source_ref' => 'context.site',
                    'value_ref' => 'display_name',
                    'resource' => 'site',
                    'binding_type' => 'string',
                ],
            ],
            $descriptor->dynamicBindings,
        );
    }

    public function testRejectsMalformedDynamicContextTokenBindings(): void
    {
        $cases = [
            ['source' => 'dynamic', 'source_ref' => '', 'value_ref' => 'name', 'resource' => 'site'],
            ['source' => 'dynamic', 'source_ref' => 'context.site', 'value_ref' => 'bad/value', 'resource' => 'site'],
            ['source' => 'dynamic', 'source_ref' => 'context.site', 'value_ref' => 'name', 'resource' => 'post'],
            ['source' => 'dynamic', 'source_ref' => 'context.site', 'value_ref' => 'name', 'resource' => 'site', 'resource_id' => 99],
        ];

        foreach ($cases as $binding) {
            try {
                $this->compiler()->compile($this->definition(
                    renderSource: $this->renderSource('rich_text', ['content' => $binding]),
                ));
                self::fail('Expected malformed Dashboard Widget Dynamic binding to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testCompilesBoundedQueryBindingsWithDerivedSortedProjection(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $chart = $catalog->forContentType('chart');
        self::assertNotNull($chart);

        $descriptor = $this->compiler()->compile($this->definition(
            contentType: 'chart',
            renderSource: [
                'kind' => 'component_blueprint',
                'blueprint_id' => $chart->id,
                'blueprint_revision' => $chart->revision,
                'query' => [
                    'contract_version' => 1,
                    'source_ref' => 'wordpress.posts',
                    'filters' => [['field_ref' => 'post.status', 'operator' => 'eq', 'value' => 'publish']],
                    'order_by' => [['field_ref' => 'post.date', 'direction' => 'desc']],
                    'page_size' => 20,
                    'offset' => 0,
                ],
                'bindings' => [
                    'labels' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'column'],
                    'values' => ['source' => 'query', 'field_ref' => 'post.id', 'mode' => 'column'],
                ],
            ],
        ));

        self::assertInstanceOf(DashboardWidgetQueryBindingDescriptor::class, $descriptor->query);
        self::assertSame([], $descriptor->bindings);
        self::assertSame(['post.id', 'post.title'], $descriptor->query->projection);
        self::assertSame('wordpress.posts', $descriptor->query->sourceRef);
        self::assertArrayNotHasKey('search', $descriptor->query->request());
    }

    public function testAllowsMixedLiteralAndQueryBindings(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $announcement = $catalog->forContentType('announcement');
        self::assertNotNull($announcement);

        $descriptor = $this->compiler()->compile($this->definition(
            contentType: 'announcement',
            renderSource: [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'query' => [
                    'contract_version' => 1,
                    'source_ref' => 'wordpress.posts',
                    'page_size' => 1,
                ],
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'Latest'],
                    'text' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'first'],
                ],
            ],
        ));

        self::assertSame(['title' => 'Latest'], $descriptor->bindings);
        self::assertNotNull($descriptor->query);
        self::assertSame(['post.title'], $descriptor->query->projection);
    }

    public function testRejectsQueryPairingUnknownKeysAndBounds(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        self::assertNotNull($richText);

        $base = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'query' => [
                'contract_version' => 1,
                'source_ref' => 'wordpress.posts',
            ],
            'bindings' => [
                'content' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'first'],
            ],
        ];

        $cases = [
            array_diff_key($base, ['query' => true]),
            array_replace($base, ['bindings' => ['content' => ['source' => 'literal', 'value' => 'Safe']]]),
            array_replace_recursive($base, ['query' => ['search' => 'forbidden']]),
            array_replace_recursive($base, ['query' => ['projection' => ['post.title']]]),
            array_replace_recursive($base, ['query' => ['page_size' => 51]]),
            array_replace_recursive($base, ['query' => ['offset' => 1001]]),
            array_replace_recursive($base, ['bindings' => ['content' => ['callback' => 'forbidden']]]),
        ];

        foreach ($cases as $renderSource) {
            try {
                $this->compiler()->compile($this->definition(renderSource: $renderSource));
                self::fail('Expected invalid bounded Query binding definition to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testCompilesQueryBoundTrustedEmptyState(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        $announcement = $catalog->forContentType('announcement');
        self::assertNotNull($richText);
        self::assertNotNull($announcement);

        $descriptor = $this->compiler()->compile($this->definition(renderSource: [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'query' => [
                'contract_version' => 1,
                'source_ref' => 'wordpress.posts',
                'page_size' => 1,
            ],
            'bindings' => [
                'content' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'first'],
            ],
            'empty_state' => [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'No results'],
                    'text' => ['source' => 'literal', 'value' => 'Nothing to display yet.'],
                ],
            ],
        ]));

        self::assertInstanceOf(DashboardWidgetEmptyStateDescriptor::class, $descriptor->emptyState);
        self::assertSame($announcement->id, $descriptor->emptyState->blueprintId);
        self::assertSame(
            ['text' => 'Nothing to display yet.', 'title' => 'No results'],
            $descriptor->emptyState->bindings,
        );
    }

    public function testRejectsUnboundedOrNonQueryEmptyStateMetadata(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        $announcement = $catalog->forContentType('announcement');
        $kpi = $catalog->forContentType('kpi');
        self::assertNotNull($richText);
        self::assertNotNull($announcement);
        self::assertNotNull($kpi);

        $querySource = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'query' => [
                'contract_version' => 1,
                'source_ref' => 'wordpress.posts',
                'page_size' => 1,
            ],
            'bindings' => [
                'content' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'first'],
            ],
            'empty_state' => [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'No results'],
                    'text' => ['source' => 'literal', 'value' => 'Nothing to display yet.'],
                ],
            ],
        ];

        $literalWithEmpty = $this->renderSource('rich_text');
        $literalWithEmpty['empty_state'] = $querySource['empty_state'];

        $wrongBlueprint = $querySource;
        $wrongBlueprint['empty_state'] = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $kpi->id,
            'blueprint_revision' => $kpi->revision,
            'bindings' => [
                'label' => ['source' => 'literal', 'value' => 'No results'],
                'value' => ['source' => 'literal', 'value' => '0'],
            ],
        ];

        $queryEnvelope = $querySource;
        $queryEnvelope['empty_state']['bindings']['text'] = [
            'source' => 'query',
            'value' => 'forbidden',
        ];

        $oversized = $querySource;
        $oversized['empty_state']['bindings']['text']['value'] = str_repeat(
            'x',
            DashboardWidgetEmptyStateDescriptor::MAX_STRING_BYTES + 1,
        );

        $oversizedObject = $querySource;
        $oversizedObject['empty_state']['bindings']['title']['value'] = str_repeat('a', 2000);
        $oversizedObject['empty_state']['bindings']['text']['value'] = str_repeat('b', 2000);

        $wrongRevision = $querySource;
        $wrongRevision['empty_state']['blueprint_revision'] = 2;

        $unknownKey = $querySource;
        $unknownKey['empty_state']['provider'] = 'forbidden';

        foreach ([$literalWithEmpty, $wrongBlueprint, $queryEnvelope, $oversized, $oversizedObject, $wrongRevision, $unknownKey] as $renderSource) {
            try {
                $this->compiler()->compile($this->definition(renderSource: $renderSource));
                self::fail('Expected invalid Dashboard Widget empty-state metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testCompilesTrustedErrorStateForLiteralAndQuerySources(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $richText = $catalog->forContentType('rich_text');
        $announcement = $catalog->forContentType('announcement');
        self::assertNotNull($richText);
        self::assertNotNull($announcement);

        $literalSource = $this->renderSource('rich_text');
        $literalSource['error_state'] = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $announcement->id,
            'blueprint_revision' => $announcement->revision,
            'bindings' => [
                'title' => ['source' => 'literal', 'value' => 'Widget unavailable'],
                'text' => ['source' => 'literal', 'value' => 'Please try again later.'],
            ],
        ];
        $literalDescriptor = $this->compiler()->compile($this->definition(renderSource: $literalSource));
        self::assertInstanceOf(DashboardWidgetErrorStateDescriptor::class, $literalDescriptor->errorState);

        $querySource = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $richText->id,
            'blueprint_revision' => $richText->revision,
            'query' => [
                'contract_version' => 1,
                'source_ref' => 'wordpress.posts',
                'page_size' => 1,
            ],
            'bindings' => [
                'content' => ['source' => 'query', 'field_ref' => 'post.title', 'mode' => 'first'],
            ],
            'empty_state' => [
                'kind' => 'component_blueprint',
                'blueprint_id' => $announcement->id,
                'blueprint_revision' => $announcement->revision,
                'bindings' => [
                    'title' => ['source' => 'literal', 'value' => 'No results'],
                    'text' => ['source' => 'literal', 'value' => 'Nothing to display yet.'],
                ],
            ],
            'error_state' => $literalSource['error_state'],
        ];
        $queryDescriptor = $this->compiler()->compile($this->definition(renderSource: $querySource));

        self::assertInstanceOf(DashboardWidgetEmptyStateDescriptor::class, $queryDescriptor->emptyState);
        self::assertInstanceOf(DashboardWidgetErrorStateDescriptor::class, $queryDescriptor->errorState);
    }

    public function testRejectsUnboundedOrExecutableErrorStateMetadata(): void
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $announcement = $catalog->forContentType('announcement');
        $kpi = $catalog->forContentType('kpi');
        self::assertNotNull($announcement);
        self::assertNotNull($kpi);

        $base = $this->renderSource('rich_text');
        $base['error_state'] = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $announcement->id,
            'blueprint_revision' => $announcement->revision,
            'bindings' => [
                'title' => ['source' => 'literal', 'value' => 'Widget unavailable'],
                'text' => ['source' => 'literal', 'value' => 'Please try again later.'],
            ],
        ];

        $wrongBlueprint = $base;
        $wrongBlueprint['error_state'] = [
            'kind' => 'component_blueprint',
            'blueprint_id' => $kpi->id,
            'blueprint_revision' => $kpi->revision,
            'bindings' => [
                'label' => ['source' => 'literal', 'value' => 'Unavailable'],
                'value' => ['source' => 'literal', 'value' => '0'],
            ],
        ];

        $dynamic = $base;
        $dynamic['error_state']['bindings']['text'] = ['source' => 'query', 'value' => 'forbidden'];

        $unsafe = $base;
        $unsafe['error_state']['bindings']['text']['value'] = 'javascript:alert(1)';

        $oversized = $base;
        $oversized['error_state']['bindings']['text']['value'] = str_repeat(
            'x',
            DashboardWidgetErrorStateDescriptor::MAX_STRING_BYTES + 1,
        );

        $oversizedObject = $base;
        $oversizedObject['error_state']['bindings']['title']['value'] = str_repeat('a', 2000);
        $oversizedObject['error_state']['bindings']['text']['value'] = str_repeat('b', 2000);

        $wrongRevision = $base;
        $wrongRevision['error_state']['blueprint_revision'] = 2;

        $missingBinding = $base;
        unset($missingBinding['error_state']['bindings']['text']);

        $nested = $base;
        $nested['error_state']['error_state'] = [];

        foreach ([
            $wrongBlueprint,
            $dynamic,
            $unsafe,
            $oversized,
            $oversizedObject,
            $wrongRevision,
            $missingBinding,
            $nested,
        ] as $renderSource) {
            try {
                $this->compiler()->compile($this->definition(renderSource: $renderSource));
                self::fail('Expected invalid Dashboard Widget error-state metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function compiler(): DashboardWidgetRenderSourceCompiler
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $registry = new ComponentBlueprintRegistry();
        foreach ($catalog->all() as $blueprint) {
            $registry->register($blueprint);
        }

        return new DashboardWidgetRenderSourceCompiler(
            $registry,
            null,
            $catalog,
        );
    }

    /**
     * @param array<string,mixed>|null $renderSource
     */
    private function definition(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        string $type = DashboardWidgetDefinition::TYPE,
        int $schemaVersion = 1,
        DefinitionStatus $status = DefinitionStatus::Published,
        string $contentType = 'rich_text',
        ?array $renderSource = null,
        bool $includeRenderSource = true,
    ): Definition {
        $widget = ['type' => $contentType];
        if ($includeRenderSource) {
            $widget['render_source'] = $renderSource ?? $this->renderSource($contentType);
        }

        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'sales-overview',
            type: $type,
            schemaVersion: $schemaVersion,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );
    }

    /**
     * @param array<string,mixed>|null $bindings
     * @return array<string,mixed>
     */
    private function renderSource(string $contentType, ?array $bindings = null): array
    {
        $catalog = new DashboardWidgetComponentBlueprintCatalog();
        $blueprint = $catalog->forContentType($contentType);
        self::assertNotNull($blueprint);

        if ($bindings === null) {
            $bindings = match ($contentType) {
                'rich_text' => ['content' => ['source' => 'literal', 'value' => 'Orders']],
                'kpi' => [
                    'label' => ['source' => 'literal', 'value' => 'Orders'],
                    'value' => ['source' => 'literal', 'value' => '12'],
                ],
                'chart' => [
                    'labels' => ['source' => 'literal', 'value' => ['A']],
                    'values' => ['source' => 'literal', 'value' => [1]],
                ],
                'quick_links' => [
                    'labels' => ['source' => 'literal', 'value' => ['Docs']],
                    'urls' => ['source' => 'literal', 'value' => ['https://example.com/docs']],
                ],
                'announcement', 'support_onboarding' => [
                    'title' => ['source' => 'literal', 'value' => 'Notice'],
                    'text' => ['source' => 'literal', 'value' => 'Text'],
                ],
                'icon_link' => [
                    'icon' => ['source' => 'literal', 'value' => 'gear'],
                    'label' => ['source' => 'literal', 'value' => 'Settings'],
                    'url' => ['source' => 'literal', 'value' => '/settings'],
                ],
                default => [],
            };
        }

        return [
            'kind' => 'component_blueprint',
            'blueprint_id' => $blueprint->id,
            'blueprint_revision' => $blueprint->revision,
            'bindings' => $bindings,
        ];
    }
}
