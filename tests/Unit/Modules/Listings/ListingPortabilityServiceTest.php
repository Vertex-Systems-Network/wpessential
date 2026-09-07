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
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class ListingPortabilityServiceTest extends TestCase
{
    public function testExportsOnlyAuthoredConfigurationAndStableDependencies(): void
    {
        $service = new ListingPortabilityService(new ListingPortableQuery(), new ListingPortableBlueprints());
        $package = $service->export($this->definition(), $this->context());
        $data = $package->toArray();

        self::assertSame('wordpress.posts', $data['dependencies']['query_source_ref']);
        self::assertSame(ListingPortableBlueprints::ID, $data['dependencies']['blueprint']['id']);
        self::assertArrayNotHasKey('rows', $data);
        self::assertArrayNotHasKey('credentials', $data);
    }

    public function testRejectsUnavailableDependencyOnImport(): void
    {
        $service = new ListingPortabilityService(new ListingPortableQuery(false), new ListingPortableBlueprints());
        $data = (new ListingPortabilityService(new ListingPortableQuery(), new ListingPortableBlueprints()))
            ->export($this->definition(), $this->context())
            ->toArray();

        $this->expectException(InvalidArgumentException::class);
        $service->validateImport($data, $this->context());
    }

    public function testRejectsExecutableAuthoredPayload(): void
    {
        $definition = $this->definition();
        $unsafe = new Definition(
            $definition->id,
            $definition->slug,
            $definition->type,
            $definition->schemaVersion,
            $definition->ownerSurfaceId,
            $definition->status,
            array_merge($definition->payload, ['unsafe' => '<script>alert(1)</script>']),
            $definition->revision,
        );

        $this->expectException(InvalidArgumentException::class);
        (new ListingPortabilityService(new ListingPortableQuery(), new ListingPortableBlueprints()))->export($unsafe, $this->context());
    }

    private function definition(): Definition
    {
        return new Definition(
            '11111111-1111-4111-8111-111111111111',
            'posts',
            'listing',
            1,
            9,
            DefinitionStatus::Published,
            [
                'query_source_ref' => 'wordpress.posts',
                'blueprint' => ['id' => ListingPortableBlueprints::ID, 'revision' => 1],
                'layout' => ['mode' => 'list', 'columns' => 1],
                'assets' => [],
            ],
            2,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1, networkId: 2);
    }
}

final class ListingPortableQuery implements QueryReadConsumerInterface
{
    public function __construct(private bool $available = true) {}

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_ref' => $sourceRef,
            'available' => $this->available,
            'field_schema' => ['post_id' => 'int'],
            'max_page_size' => 100,
        ];
    }

    public function read(array $request, ExecutionContext $context): array
    {
        return [];
    }
}

final class ListingPortableBlueprints implements ComponentBlueprintRegistryInterface
{
    public const ID = '22222222-2222-4222-8222-222222222222';

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        if ($blueprintId !== self::ID || $revision !== 1) {
            return null;
        }
        return new ComponentBlueprintDescriptor(self::ID, 1, 9, 'listing.card', bindingSchema: ['title' => 'string']);
    }
}
