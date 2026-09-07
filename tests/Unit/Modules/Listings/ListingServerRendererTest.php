<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Modules\Listings\Definition\ListingCompiledDescriptor;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Modules\Listings\Rendering\ListingServerRenderer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

final class ListingServerRendererTest extends TestCase
{
    public function testComposesAuthorizedRowsThroughSharedRenderer(): void
    {
        $service = new ListingServerRenderer(
            new ListingQueryReader(new ListingSsrQueryConsumer()),
            new ListingSsrBlueprintRegistry(),
            new ListingSsrRenderer(),
        );

        $result = $service->render($this->descriptor(), $this->binding(), [], $this->context());

        self::assertTrue($result->success);
        self::assertSame(1, $result->returned);
        self::assertStringContainsString('<ul class="wpe-listing wpe-listing--list">', $result->html);
        self::assertStringContainsString('<strong>Hello</strong>', $result->html);
        self::assertSame(['wpe-listing', 'wpe-card'], $result->assetHandles);
    }

    public function testFailsClosedWhenDescriptorAndBindingSourcesDiffer(): void
    {
        $service = new ListingServerRenderer(
            new ListingQueryReader(new ListingSsrQueryConsumer()),
            new ListingSsrBlueprintRegistry(),
            new ListingSsrRenderer(),
        );
        $binding = new ListingQueryBinding('wordpress.pages', ['title']);

        $result = $service->render($this->descriptor(), $binding, [], $this->context());

        self::assertFalse($result->success);
        self::assertSame('', $result->html);
        self::assertSame('listing_query_source_mismatch', $result->failureCode);
    }

    public function testRendersExplicitServerEmptyState(): void
    {
        $query = new ListingSsrQueryConsumer();
        $query->empty = true;
        $service = new ListingServerRenderer(
            new ListingQueryReader($query),
            new ListingSsrBlueprintRegistry(),
            new ListingSsrRenderer(),
        );

        $result = $service->render($this->descriptor(), $this->binding(), [], $this->context());

        self::assertTrue($result->success);
        self::assertSame(0, $result->returned);
        self::assertStringContainsString('role="status"', $result->html);
    }

    private function descriptor(): ListingCompiledDescriptor
    {
        return new ListingCompiledDescriptor(
            listingId: '11111111-1111-4111-8111-111111111111',
            revision: 1,
            querySourceRef: 'wordpress.posts',
            blueprintId: ListingSsrBlueprintRegistry::BLUEPRINT_ID,
            blueprintRevision: 2,
            layoutMode: 'list',
            columns: 1,
            assetHandles: ['wpe-listing'],
            compatibilityFingerprint: str_repeat('a', 64),
        );
    }

    private function binding(): ListingQueryBinding
    {
        return new ListingQueryBinding('wordpress.posts', ['title']);
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}

final class ListingSsrQueryConsumer implements QueryReadConsumerInterface
{
    public bool $empty = false;

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $sourceRef,
            'source_type' => 'wordpress.posts',
            'capability_version' => 1,
            'available' => true,
            'field_schema' => ['title' => 'string'],
            'predicates' => ['comparison'],
            'sort_modes' => ['field'],
            'pagination_modes' => ['offset'],
            'max_page_size' => 100,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        $rows = $this->empty ? [] : [['title' => 'Hello']];
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'ok' => true,
            'source_ref' => 'wordpress.posts',
            'projection' => ['title'],
            'rows' => $rows,
            'returned' => count($rows),
            'error' => null,
        ];
    }
}

final class ListingSsrBlueprintRegistry implements ComponentBlueprintRegistryInterface
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
            bindingSchema: ['title' => 'string'],
        );
    }
}

final class ListingSsrRenderer implements RendererInterface
{
    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        return new RenderOutput(true, '<strong>' . (string) ($input->bindings['title'] ?? '') . '</strong>', ['wpe-card']);
    }
}
