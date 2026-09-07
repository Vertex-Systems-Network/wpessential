<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Modules\Listings\Portability\ListingPortabilityService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;

final class ListingPortabilityUnknownKeyTest extends TestCase
{
    public function testRejectsUndeclaredPayloadChannel(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ListingPortabilityService(new ListingUnknownKeyQuery(), new ListingUnknownKeyBlueprints()))->validateImport([
            'package_version' => 1,
            'definition_id' => '11111111-1111-4111-8111-111111111111',
            'revision' => 1,
            'schema_version' => 1,
            'payload' => ['query_source_ref' => 'wordpress.posts', 'source_rows' => [['id' => 1]]],
            'dependencies' => [
                'query_source_ref' => 'wordpress.posts',
                'blueprint' => ['id' => ListingUnknownKeyBlueprints::ID, 'revision' => 1],
            ],
            'source_scope' => ['site_id' => 1, 'network_id' => null],
        ], new ExecutionContext(new Principal(7), 2));
    }
}

final class ListingUnknownKeyQuery implements QueryReadConsumerInterface
{
    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return ['contract_version' => self::CONTRACT_VERSION, 'source_ref' => $sourceRef, 'available' => true];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        return [];
    }
}

final class ListingUnknownKeyBlueprints implements ComponentBlueprintRegistryInterface
{
    public const ID = '22222222-2222-4222-8222-222222222222';

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        return null;
    }
}
