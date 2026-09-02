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

    public function testPreservesAdvancedPoliciesAndDirectionCompositionDuringBasicEdit(): void
    {
        $existing = [
            'relation_key' => 'book_authors',
            'title' => 'Old title',
            'direction' => [
                'reciprocal' => false,
                'bidirectional_traversal' => true,
                'parent_relation' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            ],
            'storage_mode' => 'provider',
            'pivot_enabled' => true,
            'multisite_scope' => 'network',
            'portability' => ['definition' => true, 'edges' => true, 'pivot' => false],
        ];

        $payload = (new RelationAdminPayloadMapper())->map([
            'relation_key' => 'book_authors',
            'title' => 'Updated title',
            'description' => '',
            'cardinality' => 'many_to_many',
            'reciprocal' => '1',
            'bidirectional_traversal' => '0',
            'from_type' => 'custom_table',
            'from_subtype' => 'books',
            'from_label' => 'Books',
            'to_type' => 'registered_entity',
            'to_subtype' => 'authors',
            'to_label' => 'Authors',
            'from_min' => '0',
            'from_max' => '',
            'to_min' => '0',
            'to_max' => '',
            'unique_edge' => '1',
        ], $existing);

        self::assertSame('Updated title', $payload['title']);
        self::assertTrue($payload['direction']['reciprocal']);
        self::assertFalse($payload['direction']['bidirectional_traversal']);
        self::assertSame('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', $payload['direction']['parent_relation']);
        self::assertSame('provider', $payload['storage_mode']);
        self::assertTrue($payload['pivot_enabled']);
        self::assertSame('network', $payload['multisite_scope']);
        self::assertTrue($payload['portability']['edges']);
        self::assertSame('books', $payload['from']['object_subtype']);
        self::assertSame('authors', $payload['to']['object_subtype']);
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
