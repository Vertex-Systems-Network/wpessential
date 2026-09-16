<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Relations;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Relations\RelationEndpointObjectAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class RelationEndpointObjectAuthorizerTest extends TestCase
{
    /** @var array{object_type:string,object_subtype:?string,label:string} */
    private const ENDPOINT = [
        'object_type' => 'post',
        'object_subtype' => 'book',
        'label' => 'Books',
    ];

    public function testMatchingActiveWordPressContextCanReachEndpointAuthorization(): void
    {
        $observedActorId = null;
        $authorizer = new RelationEndpointObjectAuthorizer(
            static fn (array $endpoint, int $objectId): bool => $objectId === 31,
            static function (array $endpoint, int $objectId, int $actorId) use (&$observedActorId): bool {
                $observedActorId = $actorId;
                return true;
            },
            static fn (): ?int => 7,
            static fn (): int => 17,
            static fn (): ?int => 9,
        );

        $authorizer->assertCanMutate(
            self::ENDPOINT,
            31,
            new ExecutionContext(new Principal(7), 17, networkId: 9),
            'source',
        );

        self::assertSame(7, $observedActorId);
    }

    public function testMismatchedActiveUserFailsBeforeEndpointProbe(): void
    {
        $probes = 0;
        $authorizer = $this->authorizer(
            activeUserId: 8,
            activeSiteId: 17,
            activeNetworkId: 9,
            probes: $probes,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not bound to the active user/site');
        try {
            $authorizer->assertCanMutate(
                self::ENDPOINT,
                31,
                new ExecutionContext(new Principal(7), 17, networkId: 9),
                'source',
            );
        } finally {
            self::assertSame(0, $probes);
        }
    }

    public function testMismatchedActiveSiteFailsBeforeEndpointProbe(): void
    {
        $probes = 0;
        $authorizer = $this->authorizer(
            activeUserId: 7,
            activeSiteId: 18,
            activeNetworkId: 9,
            probes: $probes,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not bound to the active user/site');
        try {
            $authorizer->assertCanMutate(
                self::ENDPOINT,
                31,
                new ExecutionContext(new Principal(7), 17, networkId: 9),
                'source',
            );
        } finally {
            self::assertSame(0, $probes);
        }
    }

    public function testMismatchedActiveNetworkFailsBeforeEndpointProbe(): void
    {
        $probes = 0;
        $authorizer = $this->authorizer(
            activeUserId: 7,
            activeSiteId: 17,
            activeNetworkId: 10,
            probes: $probes,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not bound to the active network');
        try {
            $authorizer->assertCanMutate(
                self::ENDPOINT,
                31,
                new ExecutionContext(new Principal(7), 17, networkId: 9),
                'source',
            );
        } finally {
            self::assertSame(0, $probes);
        }
    }

    public function testNonUserPrincipalFailsBeforeEndpointProbe(): void
    {
        $probes = 0;
        $authorizer = $this->authorizer(
            activeUserId: 7,
            activeSiteId: 17,
            activeNetworkId: 9,
            probes: $probes,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('requires an authenticated user');
        try {
            $authorizer->assertCanMutate(
                self::ENDPOINT,
                31,
                new ExecutionContext(new Principal(7, 'service'), 17, networkId: 9),
                'source',
            );
        } finally {
            self::assertSame(0, $probes);
        }
    }

    private function authorizer(
        int $activeUserId,
        int $activeSiteId,
        ?int $activeNetworkId,
        int &$probes,
    ): RelationEndpointObjectAuthorizer {
        return new RelationEndpointObjectAuthorizer(
            static function (array $endpoint, int $objectId) use (&$probes): bool {
                ++$probes;
                return true;
            },
            static function (array $endpoint, int $objectId, int $actorId) use (&$probes): bool {
                ++$probes;
                return true;
            },
            static fn (): ?int => $activeUserId,
            static fn (): int => $activeSiteId,
            static fn (): ?int => $activeNetworkId,
        );
    }
}
