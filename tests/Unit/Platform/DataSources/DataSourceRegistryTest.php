<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\DataSources;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use WPEssential\Platform\DataSources\DataSourceAuthorizationMapping;
use WPEssential\Platform\DataSources\DataSourceAvailability;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class DataSourceRegistryTest extends TestCase
{
    public function testRegistryResolvesDescriptorsAndReturnsDeterministicOrder(): void
    {
        $registry = new DataSourceRegistry();
        $zeta = $this->descriptor('zeta.source');
        $alpha = $this->descriptor('alpha.source');

        $registry->register($zeta);
        $registry->register($alpha);

        self::assertTrue($registry->has('alpha.source'));
        self::assertSame($alpha, $registry->find('alpha.source'));
        self::assertSame($zeta, $registry->require('zeta.source'));
        self::assertSame(['alpha.source', 'zeta.source'], array_map(
            static fn (DataSourceDescriptor $descriptor): string => $descriptor->id,
            $registry->all(),
        ));
    }

    public function testDuplicateRegistrationFailsClosed(): void
    {
        $registry = new DataSourceRegistry();
        $descriptor = $this->descriptor('posts.native');
        $registry->register($descriptor);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already registered');

        $registry->register($descriptor);
    }

    public function testUnknownRequiredSourceFailsClosed(): void
    {
        $registry = new DataSourceRegistry();

        self::assertFalse($registry->has('missing.source'));
        self::assertNull($registry->find('missing.source'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown Data Source');

        $registry->require('missing.source');
    }

    public function testDegradedSourceRequiresExplicitReason(): void
    {
        $descriptor = new DataSourceDescriptor(
            id: 'remote.crm',
            sourceType: 'remote.provider',
            capabilityVersion: 1,
            fieldSchema: ['record.id' => 'integer'],
            availability: DataSourceAvailability::Degraded,
            degradedReason: 'provider_not_connected',
        );

        self::assertFalse($descriptor->isAvailable());
        self::assertSame('provider_not_connected', $descriptor->degradedReason);
    }

    public function testDegradedSourceWithoutReasonIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must declare a reason');

        new DataSourceDescriptor(
            id: 'remote.crm',
            sourceType: 'remote.provider',
            capabilityVersion: 1,
            fieldSchema: ['record.id' => 'integer'],
            availability: DataSourceAvailability::Degraded,
        );
    }

    public function testPolicyCannotBeDisabled(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('canonical Policy authorization');

        new DataSourceDescriptor(
            id: 'posts.native',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            policyRequired: false,
        );
    }

    public function testLegacyDescriptorRemainsValidButExecutionMappingFailsClosed(): void
    {
        $descriptor = $this->descriptor('posts.native');

        self::assertFalse($descriptor->hasAuthorizationMapping());
        self::assertNull($descriptor->authorization);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no canonical Policy authorization mapping');

        $descriptor->requireAuthorizationMapping();
    }

    public function testDescriptorExposesValidatedAuthorizationMapping(): void
    {
        $mapping = new DataSourceAuthorizationMapping(
            ability: 'wpessential/query/execute',
            capability: 'read',
            resourceType: 'post',
        );
        $descriptor = new DataSourceDescriptor(
            id: 'wordpress.posts',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            authorization: $mapping,
        );

        self::assertTrue($descriptor->hasAuthorizationMapping());
        self::assertSame($mapping, $descriptor->authorization);
        self::assertSame($mapping, $descriptor->requireAuthorizationMapping());
    }

    public function testMalformedAuthorizationAbilityIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('wpessential/<domain>/<action>');

        new DataSourceAuthorizationMapping(
            ability: 'query.execute',
            capability: 'read',
        );
    }

    public function testMalformedAuthorizationCapabilityIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stable WordPress capability key');

        new DataSourceAuthorizationMapping(
            ability: 'wpessential/query/execute',
            capability: 'read posts',
        );
    }

    public function testMalformedAuthorizationResourceTypeIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stable semantic identifier');

        new DataSourceAuthorizationMapping(
            ability: 'wpessential/query/execute',
            capability: 'read',
            resourceType: 'Post Type',
        );
    }

    public function testNonCacheableSourceCannotDeclareGenerationKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-cacheable Data Sources');

        new DataSourceDescriptor(
            id: 'posts.native',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            cacheable: false,
            cacheGenerationKeys: ['posts.generation'],
        );
    }

    public function testMalformedCapabilityMetadataIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('stable lowercase semantic identifier');

        new DataSourceDescriptor(
            id: 'posts.native',
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: ['post.id' => 'integer'],
            predicates: ['RAW SQL'],
        );
    }

    public function testDescriptorExposesNoRawExecutionEscapeHatch(): void
    {
        $properties = array_map(
            static fn ($property): string => strtolower($property->getName()),
            (new ReflectionClass(DataSourceDescriptor::class))->getProperties(),
        );

        foreach (['sql', 'table', 'tablename', 'callback', 'callable', 'credentials', 'endpoint'] as $forbidden) {
            self::assertNotContains($forbidden, $properties);
        }
    }

    private function descriptor(string $id): DataSourceDescriptor
    {
        return new DataSourceDescriptor(
            id: $id,
            sourceType: 'wordpress.posts',
            capabilityVersion: 1,
            fieldSchema: [
                'post.id' => 'integer',
                'post.title' => 'string',
            ],
            predicates: ['eq', 'contains'],
            sortModes: ['field'],
            paginationModes: ['offset'],
            aggregationModes: ['count'],
            supportsRelations: true,
            scopes: ['site'],
            maxPageSize: 100,
            maxBatchSize: 100,
            cacheable: true,
            cacheGenerationKeys: ['posts.generation'],
            diagnosticsAvailable: true,
        );
    }
}
