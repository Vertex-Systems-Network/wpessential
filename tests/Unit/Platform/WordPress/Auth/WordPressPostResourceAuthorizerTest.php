<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\WordPress\Auth;

use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

final class WordPressPostResourceAuthorizerTest extends TestCase
{
    public function testEditAuthorizationBindsContextAndDelegatesExactMetaCapability(): void
    {
        $calls = [];
        $authorizer = $this->authorizer(
            currentUserCan: static function (string $capability, int $postId) use (&$calls): bool {
                $calls[] = [$capability, $postId];
                return true;
            },
        );

        $authorizer->assertCanEdit($this->context(), 91);

        self::assertSame([['edit_post', 91]], $calls);
    }

    public function testReadAndDeleteUseExactPostMetaCapabilities(): void
    {
        $calls = [];
        $authorizer = $this->authorizer(
            currentUserCan: static function (string $capability, int $postId) use (&$calls): bool {
                $calls[] = [$capability, $postId];
                return true;
            },
        );

        $authorizer->assertCanRead($this->context(), 12);
        $authorizer->assertCanDelete($this->context(), 13);

        self::assertSame([
            ['read_post', 12],
            ['delete_post', 13],
        ], $calls);
    }

    public function testAnonymousOrNonUserPrincipalFailsBeforeCapabilityEvaluation(): void
    {
        $calls = 0;
        $authorizer = $this->authorizer(
            currentUserCan: static function (string $capability, int $postId) use (&$calls): bool {
                ++$calls;
                return true;
            },
        );

        try {
            $authorizer->assertCanEdit(new ExecutionContext(new Principal(null), 3, networkId: 2), 91);
            self::fail('Anonymous context must fail closed.');
        } catch (RuntimeException) {
            self::assertSame(0, $calls);
        }

        $this->expectException(RuntimeException::class);
        $authorizer->assertCanEdit(new ExecutionContext(new Principal(7, 'service'), 3, networkId: 2), 91);
    }

    /** @dataProvider mismatchedContexts */
    public function testMismatchedActiveContextFailsBeforeCapabilityEvaluation(ExecutionContext $context): void
    {
        $calls = 0;
        $authorizer = $this->authorizer(
            currentUserCan: static function (string $capability, int $postId) use (&$calls): bool {
                ++$calls;
                return true;
            },
        );

        try {
            $authorizer->assertCanEdit($context, 91);
            self::fail('Mismatched context must fail closed.');
        } catch (RuntimeException) {
            self::assertSame(0, $calls);
        }
    }

    /** @return iterable<string,array{ExecutionContext}> */
    public static function mismatchedContexts(): iterable
    {
        yield 'user' => [new ExecutionContext(new Principal(8), 3, networkId: 2)];
        yield 'site' => [new ExecutionContext(new Principal(7), 4, networkId: 2)];
        yield 'network' => [new ExecutionContext(new Principal(7), 3, networkId: 4)];
    }

    public function testInvalidPostIdFailsBeforeCapabilityEvaluation(): void
    {
        $calls = 0;
        $authorizer = $this->authorizer(
            currentUserCan: static function (string $capability, int $postId) use (&$calls): bool {
                ++$calls;
                return true;
            },
        );

        try {
            $authorizer->assertCanEdit($this->context(), 0);
            self::fail('Invalid post id must fail closed.');
        } catch (RuntimeException) {
            self::assertSame(0, $calls);
        }
    }

    public function testDeniedMetaCapabilityFailsClosed(): void
    {
        $authorizer = $this->authorizer(
            currentUserCan: static fn (string $capability, int $postId): bool => false,
        );

        $this->expectException(RuntimeException::class);
        $authorizer->assertCanEdit($this->context(), 91);
    }

    public function testUnavailableWordPressContextApiFailsClosed(): void
    {
        $authorizer = new WordPressPostResourceAuthorizer(
            currentUserId: static function (): ?int {
                throw new LogicException('unavailable');
            },
            currentSiteId: static fn (): int => 3,
            currentNetworkId: static fn (): ?int => 2,
            currentUserCan: static fn (string $capability, int $postId): bool => true,
        );

        $this->expectException(LogicException::class);
        $authorizer->assertCanEdit($this->context(), 91);
    }

    /** @param callable(string,int):bool $currentUserCan */
    private function authorizer(callable $currentUserCan): WordPressPostResourceAuthorizer
    {
        return new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentNetworkId: static fn (): ?int => 2,
            currentUserCan: $currentUserCan,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 3, networkId: 2);
    }
}
