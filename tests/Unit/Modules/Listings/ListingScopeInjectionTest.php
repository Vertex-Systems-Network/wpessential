<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Listings;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Listings\Scope\ListingScopeGuard;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class ListingScopeInjectionTest extends TestCase
{
    /** @return iterable<string,array{string}> */
    public static function publicScopeKeys(): iterable
    {
        yield 'site id' => ['site_id'];
        yield 'blog id' => ['blog_id'];
        yield 'network id' => ['network_id'];
        yield 'compact site id' => ['siteid'];
        yield 'compact network id' => ['networkid'];
    }

    #[DataProvider('publicScopeKeys')]
    public function testRejectsPublicScopeSelector(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ListingScopeGuard())->fromContext(
            new ExecutionContext(new Principal(7), 1, networkId: 2),
            [$key => 99],
        );
    }
}
