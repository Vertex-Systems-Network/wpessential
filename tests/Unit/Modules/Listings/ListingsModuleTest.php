<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use LogicException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Listings\Definition\ListingCompiledDescriptor;
use WPEssential\Modules\Listings\Definition\ListingRenderBinding;
use WPEssential\Modules\Listings\ListingsModule;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Modules\Listings\Rendering\ListingServerRenderer;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Components\ComponentBlueprintRegistry;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\DynamicValues\DynamicValueRouter;
use WPEssential\Platform\Rendering\BlueprintRendererDispatcher;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;
use WPEssential\Platform\Rendering\RenderingServiceRegistrar;

final class ListingsModuleTest extends TestCase
{
    public function testManifestUsesSharedProActivationAndQueryDependency(): void
    {
        $manifest = (new ListingsModule())->manifest();

        self::assertSame('listings', $manifest->id);
        self::assertSame('Dynamic Listings', $manifest->name);
        self::assertSame('pro', $manifest->edition);
        self::assertSame(['query'], $manifest->dependencies);
    }

    public function testDefaultActivationPolicyDoesNotAdmitListingsProModule(): void
    {
        $kernel = new Kernel();
        $kernel->registerModule(new ListingsModule());

        self::assertFalse($kernel->modules()->has('listings'));
    }

    public function testBootPublishesRendererUsingCanonicalSharedRuntimeGraph(): void
    {
        $services = $this->canonicalServices();
        $blueprints = $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS);
        $dynamicValues = $services->get(RenderingServiceRegistrar::SERVICE_DYNAMIC_VALUES);
        $renderer = $services->get(RenderingServiceRegistrar::SERVICE_RENDERER);

        self::assertInstanceOf(ComponentBlueprintRegistry::class, $blueprints);
        self::assertInstanceOf(DynamicValueRouter::class, $dynamicValues);
        self::assertInstanceOf(BlueprintRendererDispatcher::class, $renderer);

        $blueprint = new ComponentBlueprintDescriptor(
            id: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            ownerSurfaceId: 9,
            componentType: 'reference.card',
            bindingSchema: [
                'title' => 'string',
                'subtitle' => 'string',
            ],
        );
        $blueprints->register($blueprint);
        $dynamicValues->register('profile.owner', new ListingsModuleDynamicResolver());
        $renderer->register('reference.card', new ListingsModuleRendererDelegate());

        $module = new ListingsModule();
        $module->register($services);

        self::assertFalse($services->has(ListingsModule::SERVICE_QUERY_READER));
        self::assertFalse($services->has(ListingsModule::SERVICE_SERVER_RENDERER));

        $module->boot($services);

        self::assertInstanceOf(
            ListingQueryReader::class,
            $services->get(ListingsModule::SERVICE_QUERY_READER),
        );
        $serverRenderer = $services->get(ListingsModule::SERVICE_SERVER_RENDERER);
        self::assertInstanceOf(ListingServerRenderer::class, $serverRenderer);

        $result = $serverRenderer->render(
            new ListingCompiledDescriptor(
                listingId: 'listing.runtime-proof',
                revision: 1,
                querySourceRef: 'wordpress.posts',
                blueprintId: $blueprint->id,
                blueprintRevision: 1,
                layoutMode: 'list',
                columns: 1,
                assetHandles: [],
                compatibilityFingerprint: str_repeat('a', 64),
                renderBindings: [
                    new ListingRenderBinding(
                        kind: 'query_field',
                        bindingKey: 'title',
                        expectedType: 'string',
                        queryFieldRef: 'post.title',
                    ),
                    new ListingRenderBinding(
                        kind: 'dynamic_value',
                        bindingKey: 'subtitle',
                        expectedType: 'string',
                        sourceRef: 'profile.owner',
                        valueRef: 'profile.subtitle',
                        resourceType: 'post',
                        resourceIdFieldRef: 'post.id',
                    ),
                ],
            ),
            new ListingQueryBinding(
                sourceRef: 'wordpress.posts',
                projection: ['post.id', 'post.title'],
                pageSize: 10,
            ),
            [],
            new ExecutionContext(new Principal(7), 1),
        );

        self::assertTrue($result->success);
        self::assertSame(1, $result->returned);
        self::assertStringContainsString('<strong>Hello</strong>', $result->html);
        self::assertStringContainsString('<em>Profile 42</em>', $result->html);
    }

    public function testMissingSharedRuntimeFailsBeforePublishingListingsServices(): void
    {
        $services = new ServiceRegistry();
        $services->set(QueryModule::SERVICE_READ_CONSUMER, new ListingsModuleQueryConsumer());

        try {
            (new ListingsModule())->register($services);
            self::fail('Listings registration must reject a missing shared rendering runtime.');
        } catch (LogicException) {
            self::assertTrue(true);
        }

        self::assertFalse($services->has(ListingsModule::SERVICE_QUERY_READER));
        self::assertFalse($services->has(ListingsModule::SERVICE_SERVER_RENDERER));
    }

    public function testInvalidBlueprintGraphFailsBeforePublishingListingsServices(): void
    {
        $services = $this->canonicalServices();
        $blueprints = $services->get(RenderingServiceRegistrar::SERVICE_BLUEPRINTS);
        self::assertInstanceOf(ComponentBlueprintRegistry::class, $blueprints);

        $firstId = '11111111-1111-4111-8111-111111111111';
        $secondId = '22222222-2222-4222-8222-222222222222';
        $blueprints->register(new ComponentBlueprintDescriptor(
            id: $firstId,
            revision: 1,
            ownerSurfaceId: 9,
            componentType: 'reference.first',
            dependencyIds: [$secondId],
        ));
        $blueprints->register(new ComponentBlueprintDescriptor(
            id: $secondId,
            revision: 1,
            ownerSurfaceId: 9,
            componentType: 'reference.second',
            dependencyIds: [$firstId],
        ));

        $module = new ListingsModule();
        $module->register($services);

        try {
            $module->boot($services);
            self::fail('Listings boot must reject an invalid shared Blueprint dependency graph.');
        } catch (LogicException) {
            self::assertTrue(true);
        }

        self::assertFalse($services->has(ListingsModule::SERVICE_QUERY_READER));
        self::assertFalse($services->has(ListingsModule::SERVICE_SERVER_RENDERER));
    }

    private function canonicalServices(): ServiceRegistry
    {
        $services = new ServiceRegistry();
        (new RenderingServiceRegistrar())->register($services);
        $services->set(QueryModule::SERVICE_READ_CONSUMER, new ListingsModuleQueryConsumer());

        return $services;
    }
}

final class ListingsModuleQueryConsumer implements QueryReadConsumerInterface
{
    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $sourceRef,
            'source_type' => 'wordpress.posts',
            'capability_version' => 1,
            'available' => true,
            'field_schema' => [
                'post.id' => 'integer',
                'post.title' => 'string',
            ],
            'predicates' => ['eq'],
            'sort_modes' => ['field'],
            'pagination_modes' => ['offset'],
            'max_page_size' => self::MAX_PAGE_SIZE,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'source_ref' => $request['source_ref'],
            'projection' => $request['projection'],
            'rows' => [[
                'post.id' => 42,
                'post.title' => 'Hello',
            ]],
            'returned' => 1,
        ];
    }
}

final class ListingsModuleDynamicResolver implements DynamicValueResolverInterface
{
    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult
    {
        return new DynamicValueResult(
            true,
            'Profile ' . (string) $request->resourceId,
            sourceEvidence: 'trusted-test-owner',
        );
    }
}

final class ListingsModuleRendererDelegate implements RendererInterface
{
    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        return new RenderOutput(
            true,
            '<strong>' . (string) ($input->bindings['title'] ?? '') . '</strong>'
            . '<em>' . (string) ($input->bindings['subtitle'] ?? '') . '</em>',
        );
    }
}
