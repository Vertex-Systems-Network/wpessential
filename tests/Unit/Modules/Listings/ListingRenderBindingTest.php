<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Definition\ListingRenderBinding;

final class ListingRenderBindingTest extends TestCase
{
    public function testCarriesExplicitDynamicValueResourceMapping(): void
    {
        $binding = new ListingRenderBinding(
            kind: 'dynamic_value',
            bindingKey: 'title',
            expectedType: 'string',
            sourceRef: 'fields.post',
            valueRef: 'field.title',
            resourceType: 'post',
            resourceIdFieldRef: 'post_id',
        );

        self::assertSame('post_id', $binding->resourceIdFieldRef);
        self::assertSame('fields.post', $binding->sourceRef);
    }

    public function testRejectsDynamicValueWithoutResourceIdField(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListingRenderBinding(
            kind: 'dynamic_value',
            bindingKey: 'title',
            expectedType: 'string',
            sourceRef: 'fields.post',
            valueRef: 'field.title',
            resourceType: 'post',
        );
    }

    public function testRejectsExecutableSemanticChannel(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListingRenderBinding('query_field', 'title', 'string', '<?php');
    }
}
