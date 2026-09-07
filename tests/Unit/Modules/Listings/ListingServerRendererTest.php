<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\Listings\Definition\ListingCompiledDescriptor;
use WPEssential\Modules\Listings\Definition\ListingRenderBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Modules\Listings\Rendering\ListingServerRenderer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class ListingServerRendererTest extends TestCase
{
    public function testComposesQueryAndDynamicBindingsThroughSharedContracts(): void
    {
        $renderer = new ListingPlanRenderer();
        $service = $this->service(new ListingPlanDynamicValues(), $renderer);

        $result = $service->render($this->descriptor(), $this->binding(), [], $this->context());

        self::assertTrue($result->success);
        self::assertSame(1, $result->returned);
        self::assertStringContainsString('<strong>Hello:VIP-7</strong>', $result->html);
        self::assertSame(['wpe-listing', 'wpe-card'], $result->assetHandles);
        self::assertSame(1, $renderer->calls);
    }

    public function testFailsClosedWhenCompiledPlanNeedsUnprojectedField(): void
    {
        $service = $this->service(new ListingPlanDynamicValues(), new ListingPlanRenderer());
        $binding = new ListingQueryBinding('wordpress.posts', ['title']);

        $result = $service->render($this->descriptor(), $binding, [], $this->context());

        self::assertFalse($result->success);
        self::assertSame('query_projection_binding_mismatch', $result->failureCode);
    }

    public function testFailsClosedWhenDynamicValueIsUnresolved(): void
    {
        $dynamic = new ListingPlanDynamicValues();
        $dynamic->fail = true;
        $renderer = new ListingPlanRenderer();
        $service = $this->service($dynamic, $renderer);

        $result = $service->render($this->descriptor(), $this->binding(), [], $this->context());

        self::assertFalse($result->success);
        self::assertSame(RenderFailureCode::UnsupportedValueSource->value, $result->failureCode);
        self::assertSame(0, $renderer->calls);
    }

    public function testFailsClosedWhenResourceIdRowFieldIsMissing(): void
    {
        $query = new ListingPlanQueryConsumer();
        $query->omitId = true;
        $service = new ListingServerRenderer(
            new ListingQueryReader($query),
            new ListingPlanBlueprintRegistry(),
            new ListingPlanRenderer(),
            new ListingPlanDynamicValues(),
        );

        $result = $service->render($this->descriptor(), $this->binding(), [], $this->context());

        self::assertFalse($result->success);
        self::assertSame('missing_dynamic_resource_id', $result->failureCode);
    }

    public function testRendersMultipleRowsDeterministically(): void
    {
        $query = new ListingPlanQueryConsumer();
        $query->twoRows = true;
        $service = new ListingServerRenderer(
            new ListingQueryReader($query),
            new ListingPlanBlueprintRegistry(),
            new ListingPlanRenderer(),
            new ListingPlanDynamicValues(),
        );

        $result = $service->render($this->descriptor(), $this->binding(), [], $this->context());

        self::assertTrue($result->success);
        self::assertSame(2, $result->returned);
        self::assertStringContainsString('<strong>Hello:VIP-7</strong>', $result->html);
        self::assertStringContainsString('<strong>World:VIP-8</strong>', $result->html);
    }

    private function service(ListingPlanDynamicValues $dynamic, ListingPlanRenderer $renderer): ListingServerRenderer
    {
        return new ListingServerRenderer(
            new ListingQueryReader(new ListingPlanQueryConsumer()),
            new ListingPlanBlueprintRegistry(),
            $renderer,
            $dynamic,
        );
    }

    private function descriptor(): ListingCompiledDescriptor
    {
        return new ListingCompiledDescriptor(
            listingId: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            querySourceRef: 'wordpress.posts',
            blueprintId: ListingPlanBlueprintRegistry::BLUEPRINT_ID,
            blueprintRevision: 2,
            layoutMode: 'list',
            columns: 1,
            assetHandles: ['wpe-listing'],
            compatibilityFingerprint: str_repeat('a', 64),
            renderBindings: [
                new ListingRenderBinding(
                    kind: 'query_field',
                    bindingKey: 'title',
                    expectedType: 'string',
                    queryFieldRef: 'title',
                ),
                new ListingRenderBinding(
                    kind: 'dynamic_value',
                    bindingKey: 'badge',
                    expectedType: 'string',
                    sourceRef: 'fields.profile',
                    valueRef: 'membership_badge',
                    resourceType: 'post',
                    resourceIdFieldRef: 'id',
                ),
            ],
        );
    }

    private function binding(): ListingQueryBinding
    {
        return new ListingQueryBinding('wordpress.posts', ['title', 'id']);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class ListingPlanQueryConsumer implements QueryReadConsumerInterface
{
    public bool $omitId = false;
    public bool $twoRows = false;

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $sourceRef,
            'source_type' => 'wordpress.posts',
            'capability_version' => 1,
            'available' => true,
            'field_schema' => ['title' => 'string', 'id' => 'int'],
            'predicates' => ['comparison'],
            'sort_modes' => ['field'],
            'pagination_modes' => ['offset'],
            'max_page_size' => 100,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        $rows = [['title' => 'Hello', 'id' => 7]];
        if ($this->twoRows) {
            $rows[] = ['title' => 'World', 'id' => 8];
        }
        if ($this->omitId) {
            unset($rows[0]['id']);
        }
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['title', 'id'],
            'rows' => $rows,
            'returned' => count($rows),
            'error' => null,
        ];
    }
}

final class ListingPlanBlueprintRegistry implements ComponentBlueprintRegistryInterface
{
    public const BLUEPRINT_ID = '22222222-2222-4222-8222-222222222222';

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        if ($blueprintId !== self::BLUEPRINT_ID || $revision !== 2) {
            return null;
        }
        return new ComponentBlueprintDescriptor(
            id: self::BLUEPRINT_ID,
            revision: 2,
            ownerSurfaceId: 9,
            componentType: 'listing.card',
            assetHandles: ['wpe-card'],
            bindingSchema: ['title' => 'string', 'badge' => 'string'],
        );
    }
}

final class ListingPlanDynamicValues implements DynamicValueResolverInterface
{
    public bool $fail = false;

    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult
    {
        if ($this->fail) {
            return new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource);
        }
        return new DynamicValueResult(true, 'VIP-' . (string) $request->resourceId);
    }
}

final class ListingPlanRenderer implements RendererInterface
{
    public int $calls = 0;

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        ++$this->calls;
        return new RenderOutput(
            true,
            '<strong>' . (string) ($input->bindings['title'] ?? '') . ':' . (string) ($input->bindings['badge'] ?? '') . '</strong>',
            ['wpe-card'],
        );
    }
}
