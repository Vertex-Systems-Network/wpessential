<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\Listings\Portability\ListingPortabilityService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;

final class ListingPortabilityImportScopeTest extends TestCase
{
    public function testSourceSiteDoesNotOverrideTargetExecutionContext(): void
    {
        $service = new ListingPortabilityService(new ListingImportScopeQuery(), new ListingImportScopeBlueprints());
        $package = $service->validateImport([
            'package_version' => 1,
            'definition_id' => '11111111-1111-4111-8111-111111111111',
            'revision' => 1,
            'schema_version' => 1,
            'payload' => [
                'query_source_ref' => 'wordpress.posts',
                'blueprint' => ['id' => ListingImportScopeBlueprints::ID, 'revision' => 1],
                'layout' => ['mode' => 'list', 'columns' => 1],
                'assets' => [],
            ],
            'dependencies' => [
                'query_source_ref' => 'wordpress.posts',
                'blueprint' => ['id' => ListingImportScopeBlueprints::ID, 'revision' => 1],
            ],
            'source_scope' => ['site_id' => 7, 'network_id' => 2],
        ], new ExecutionContext(new Principal(7), 19, networkId: 4));

        self::assertSame(7, $package->sourceSiteId);
        self::assertSame(19, ListingImportScopeQuery::$describedSiteId);
    }
}

final class ListingImportScopeQuery implements QueryReadConsumerInterface
{
    public static int $describedSiteId = 0;

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        self::$describedSiteId = $context->siteId;
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $sourceRef,
            'available' => true,
            'field_schema' => ['post_id' => 'int'],
            'max_page_size' => 100,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        return [];
    }
}

final class ListingImportScopeBlueprints implements ComponentBlueprintRegistryInterface
{
    public const ID = '22222222-2222-4222-8222-222222222222';

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        return $blueprintId === self::ID && $revision === 1
            ? new ComponentBlueprintDescriptor(self::ID, 1, 9, 'listing.card')
            : null;
    }
}
