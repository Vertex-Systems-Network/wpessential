<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\Roles\RoleRuntimeEnvironmentInterface;
use WPEssential\Modules\Roles\RolesReadService;
use WPEssential\Modules\Taxonomies\TaxonomyRoleImpactReadModel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;

final class TaxonomyRoleImpactReadModelTest extends TestCase
{
    public function testCanonicalConsumerPreservesAllowDenyAbsentAndDeduplicatesCapabilities(): void
    {
        $environment = new CountingRoleEnvironment([
            'administrator' => [
                'name' => 'Administrator',
                'capabilities' => [
                    'manage_categories' => true,
                    'edit_posts' => true,
                ],
            ],
            'editor' => [
                'name' => 'Editor',
                'capabilities' => [
                    'manage_categories' => false,
                    'edit_posts' => true,
                ],
            ],
            'subscriber' => [
                'name' => 'Subscriber',
                'capabilities' => ['read' => true],
            ],
        ]);
        $model = new TaxonomyRoleImpactReadModel($this->roles($environment));
        $context = new ExecutionContext(new Principal(8), 1);

        $impact = $model->forCapabilities([
            'manage_terms' => 'manage_categories',
            'edit_terms' => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ], $context);

        self::assertSame('healthy', $impact['state']);
        self::assertCount(4, $impact['operations']);
        self::assertSame(2, $environment->readCount, 'Repeated taxonomy operations must reuse one Surface 30 query per unique capability.');

        $manage = $impact['operations'][0]['impact'];
        self::assertSame(['administrator'], $manage['explicit_allow_roles']);
        self::assertSame(['editor'], $manage['explicit_deny_roles']);
        self::assertSame(['subscriber'], $manage['absent_roles']);
        self::assertStringContainsString('not final user authorization', implode(' ', $impact['caveats']));
    }

    public function testAuthoredCapabilityMapAndMetaContextCaveatRemainVisible(): void
    {
        $environment = new CountingRoleEnvironment([
            'administrator' => [
                'name' => 'Administrator',
                'capabilities' => ['edit_post' => true],
            ],
        ]);
        $model = new TaxonomyRoleImpactReadModel($this->roles($environment));
        $capabilities = $model->effectiveCapabilities([
            'capabilities' => [
                'manage_terms' => 'edit_post',
                'assign_terms' => 'custom_assign_terms',
            ],
        ]);

        self::assertSame('edit_post', $capabilities['manage_terms']);
        self::assertSame('manage_categories', $capabilities['edit_terms']);
        self::assertSame('custom_assign_terms', $capabilities['assign_terms']);

        $impact = $model->forCapabilities($capabilities, new ExecutionContext(new Principal(8), 1));
        self::assertTrue($impact['operations'][0]['impact']['context_required']);
        self::assertSame('contextual_meta', $impact['operations'][0]['impact']['classification']);
    }

    public function testUnavailableSurface30TruthFailsVisibleWithoutPrivateFallback(): void
    {
        $environment = new CountingRoleEnvironment([], available: false);
        $impact = (new TaxonomyRoleImpactReadModel($this->roles($environment)))->forCapabilities(
            ['manage_terms' => 'manage_categories'],
            new ExecutionContext(new Principal(8), 1),
        );

        self::assertSame('unavailable', $impact['state']);
        self::assertSame([], $impact['operations'][0]['impact']['explicit_allow_roles']);
        self::assertSame([], $impact['operations'][0]['impact']['explicit_deny_roles']);
        self::assertSame([], $impact['operations'][0]['impact']['absent_roles']);
    }

    public function testMissingCanonicalServiceFailsVisibleWithoutWordPressFallback(): void
    {
        $impact = (new TaxonomyRoleImpactReadModel())->forCapabilities(
            ['manage_terms' => 'manage_categories'],
            new ExecutionContext(new Principal(8), 1),
        );

        self::assertSame('unavailable', $impact['state']);
        self::assertSame('manage_categories', $impact['operations'][0]['capability']);
        self::assertSame([], $impact['operations'][0]['impact']['explicit_allow_roles']);
        self::assertStringContainsString(
            'does not fall back to direct WordPress role-store inspection',
            implode(' ', $impact['caveats']),
        );
    }

    private function roles(RoleRuntimeEnvironmentInterface $environment): RolesReadService
    {
        $checker = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };

        return new RolesReadService(new PolicyEngine($checker), $environment);
    }
}

final class CountingRoleEnvironment implements RoleRuntimeEnvironmentInterface
{
    public int $readCount = 0;

    /** @param array<string,array{name:string,capabilities:array<string,bool>}> $roles */
    public function __construct(
        private readonly array $roles,
        private readonly bool $available = true,
    ) {}

    public function available(): bool
    {
        return $this->available;
    }

    public function siteExists(int $siteId): bool
    {
        return $siteId === 1;
    }

    public function networkIdForSite(int $siteId): ?int
    {
        return $siteId === 1 ? 1 : null;
    }

    public function isMultisite(): bool
    {
        return false;
    }

    public function rolesForSite(int $siteId): array
    {
        $this->readCount++;
        return $this->roles;
    }
}
