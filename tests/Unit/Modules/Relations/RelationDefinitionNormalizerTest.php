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
    }

    public function testManyToOneDefaultsInvertDirectionalCardinality(): void
    {
        $payload = $this->payload();
        $payload['cardinality'] = 'many_to_one';

        $normalized = (new RelationDefinitionNormalizer())->normalize($payload);

        self::assertSame(1, $normalized['bounds']['from_max']);
        self::assertNull($normalized['bounds']['to_max']);
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
