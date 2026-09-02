<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\RelationDefinitionNormalizer;

final class RelationDefinitionNormalizerTest extends TestCase
{
    public function testOneToManyDefaultsPreserveDirectionalCardinality(): void
    {
        $normalized = (new RelationDefinitionNormalizer())->normalize($this->payload());

        self::assertSame('book_authors', $normalized['relation_key']);
        self::assertSame('one_to_many', $normalized['cardinality']);
        self::assertSame([
            'from_min' => 0,
            'from_max' => null,
            'to_min' => 0,
            'to_max' => 1,
        ], $normalized['bounds']);
        self::assertSame([
            'reciprocal' => false,
            'bidirectional_traversal' => true,
        ], $normalized['direction']);
        self::assertTrue($normalized['unique_edge']);
        self::assertArrayNotHasKey('storage_mode', $normalized);
        self::assertArrayNotHasKey('pivot_enabled', $normalized);
        self::assertArrayNotHasKey('multisite_scope', $normalized);
    }

    public function testManyToOneDefaultsInvertDirectionalCardinality(): void
    {
        $payload = $this->payload();
        $payload['cardinality'] = 'many_to_one';

        $normalized = (new RelationDefinitionNormalizer())->normalize($payload);

        self::assertSame(1, $normalized['bounds']['from_max']);
        self::assertNull($normalized['bounds']['to_max']);
    }

    public function testCanonicalAdvancedPoliciesNormalizeOnlyWhenAuthored(): void
    {
        $payload = $this->payload();
        $payload['direction']['parent_relation'] = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
        $payload['edge_ordering'] = [
            'ordered_from' => true,
            'ordered_to' => false,
            'order_mode' => 'manual',
        ];
        $payload['storage_mode'] = 'provider';
        $payload['storage_config'] = [
            'separate_table' => true,
            'table_name' => 'book_author_edges',
            'index_strategy' => 'balanced',
            'foreign_keys' => false,
        ];
        $payload['pivot_enabled'] = true;
        $payload['pivot_policy'] = [
            'required_validation' => true,
            'queryable' => true,
            'index_policy' => 'selected',
        ];
        $payload['deletion_policy'] = [
            'delete_edges' => true,
            'from_object' => 'restrict',
            'to_object' => 'cascade_provider',
        ];
        $payload['editor_policy'] = [
            'from' => ['enabled' => true, 'context' => 'side', 'position' => 'high', 'collapsed' => false],
            'to' => ['enabled' => false, 'context' => null, 'position' => null, 'collapsed' => true],
            'search' => true,
            'ajax' => true,
            'exclude_connected' => true,
            'show_inverse' => true,
        ];
        $payload['permissions_policy'] = [
            'view' => 'read',
            'connect' => 'edit_posts',
            'disconnect' => 'edit_posts',
            'manage_definition' => 'manage_options',
            'from_capability' => 'edit_post',
            'to_capability' => 'edit_user',
            'rest_write' => 'edit_posts',
        ];
        $payload['rest_policy'] = ['expose' => true, 'namespace' => 'wpessential/v1'];
        $payload['multisite_scope'] = 'network';
        $payload['portability'] = ['definition' => true, 'edges' => true, 'pivot' => true];

        $normalized = (new RelationDefinitionNormalizer())->normalize($payload);

        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $normalized['direction']['parent_relation']);
        self::assertSame('provider', $normalized['storage_mode']);
        self::assertSame('book_author_edges', $normalized['storage_config']['table_name']);
        self::assertTrue($normalized['pivot_enabled']);
        self::assertSame('restrict', $normalized['deletion_policy']['from_object']);
        self::assertSame('edit_posts', $normalized['permissions_policy']['connect']);
        self::assertSame('wpessential/v1', $normalized['rest_policy']['namespace']);
        self::assertSame('network', $normalized['multisite_scope']);
        self::assertTrue($normalized['portability']['edges']);
    }

    public function testCardinalityCannotBeRelaxedByExplicitBounds(): void
    {
        $payload = $this->payload();
        $payload['bounds'] = ['to_max' => 2];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cardinality requires to_max to equal 1');
        (new RelationDefinitionNormalizer())->normalize($payload);
    }

    public function testMinimumCannotExceedMaximum(): void
    {
        $payload = $this->payload();
        $payload['bounds'] = ['to_min' => 2, 'to_max' => 1];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minimum cannot exceed maximum');
        (new RelationDefinitionNormalizer())->normalize($payload);
    }

    public function testSubtypeIsRejectedWhenEndpointTypeDoesNotOwnOne(): void
    {
        $payload = $this->payload();
        $payload['to'] = [
            'object_type' => 'user',
            'object_subtype' => 'administrator',
            'label' => 'Author',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('object_subtype is not applicable');
        (new RelationDefinitionNormalizer())->normalize($payload);
    }

    public function testUnknownExecutableConfigurationFailsClosed(): void
    {
        $payload = $this->payload();
        $payload['raw_sql'] = 'SELECT * FROM wp_posts';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported Relation option');
        (new RelationDefinitionNormalizer())->normalize($payload);
    }

    public function testUnknownAdvancedPolicyConfigurationFailsClosed(): void
    {
        $payload = $this->payload();
        $payload['storage_config'] = ['raw_table_sql' => 'unsafe'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported Relation storage_config option');
        (new RelationDefinitionNormalizer())->normalize($payload);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'description' => 'Connect books to their authors.',
            'cardinality' => 'one_to_many',
            'direction' => [
                'reciprocal' => false,
                'bidirectional_traversal' => true,
            ],
            'from' => [
                'object_type' => 'post',
                'object_subtype' => 'book',
                'label' => 'Books',
            ],
            'to' => [
                'object_type' => 'user',
                'label' => 'Authors',
            ],
            'unique_edge' => true,
        ];
    }
}
