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

final class ListingPortabilityDependencyKeysTest extends TestCase
{
    public function testRejectsUnknownDependencyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ListingPortabilityService(new ListingDependencyKeyQuery(), new ListingDependencyKeyBlueprints()))->validateImport([
            'package_version' => 1,
            'definition_id' => '11111111-1111-4111-8111-111111111111',
            'revision' => 1,
            'schema_version' => 1,
            'payload' => [
                'query_source_ref' => 'wordpress.posts',
                'blueprint' => ['id' => ListingDependencyKeyBlueprints::ID, 'revision' => 1],
                'layout' => ['mode' => 'list', 'columns' => 1],
                'assets' => [],
            ],
            'dependencies' => [
                'query_source_ref' => 'wordpress.posts',
                'blueprint' => ['id' => ListingDependencyKeyBlueprints::ID, 'revision' => 1],
                'nearest_match' => 'forbidden',
            ],
            'source_scope' => ['site_id' => 1, 'network_id' => null],
        ], new ExecutionContext(new Principal(7), 2));
    }
}

final class ListingDependencyKeyQuery implements QueryReadConsumerInterface
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

final class ListingDependencyKeyBlueprints implements ComponentBlueprintRegistryInterface
{
    public const ID = '22222222-2222-4222-8222-222222222222';

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        return null;
    }
}
