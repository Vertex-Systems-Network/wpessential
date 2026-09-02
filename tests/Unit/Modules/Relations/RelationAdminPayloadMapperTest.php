<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Relations\RelationAdminPayloadMapper;

final class RelationAdminPayloadMapperTest extends TestCase
{
    public function testMapsCompleteNativeEditorPayloadWithoutDroppingAdvancedValues(): void
    {
        $payload = (new RelationAdminPayloadMapper())->map([
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'description' => 'Canonical authorship relation.',
            'cardinality' => 'many_to_many',
            'reciprocal' => '1',
            'bidirectional_traversal' => '1',
            'from_type' => 'post',
            'from_subtype' => 'book',
            'from_label' => 'Books',
            'to_type' => 'user',
            'to_subtype' => 'ignored',
            'to_label' => 'Authors',
            'from_min' => '1',
            'from_max' => '12',
            'to_min' => '0',
            'to_max' => '',
            'unique_edge' => '0',
        ]);

        self::assertSame('book_authors', $payload['relation_key']);
        self::assertSame(['reciprocal' => true, 'bidirectional_traversal' => true], $payload['direction']);
        self::assertSame('book', $payload['from']['object_subtype']);
        self::assertNull($payload['to']['object_subtype']);
        self::assertSame([
            'from_min' => 1,
            'from_max' => 12,
            'to_min' => 0,
            'to_max' => null,
        ], $payload['bounds']);
        self::assertFalse($payload['unique_edge']);
    }

    public function testRejectsInvalidOptionalMaximumInsteadOfCoercingIt(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('from_max');
        (new RelationAdminPayloadMapper())->map([
            'relation_key' => 'book_authors',
            'title' => 'Book Authors',
            'cardinality' => 'many_to_many',
            'from_type' => 'post',
            'from_subtype' => 'book',
            'from_label' => 'Books',
            'to_type' => 'user',
            'to_label' => 'Authors',
            'from_min' => '0',
            'from_max' => '0',
            'to_min' => '0',
            'to_max' => '',
        ]);
    }
}
