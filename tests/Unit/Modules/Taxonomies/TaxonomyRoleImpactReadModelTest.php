<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\RoleImpactReadServiceInterface;
use WPEssential\Modules\Taxonomies\TaxonomyRoleImpactReadModel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

/**
 * Free-owned coverage for the Taxonomy role-impact read model. The real role service
 * lives in Pro, so these tests use a fake RoleImpactReadServiceInterface.
 */
final class TaxonomyRoleImpactReadModelTest extends TestCase
{
    public function testPreservesAllowDenyAbsentAndDeduplicatesCapabilities(): void
    {
        $service = new FakeRoleImpactService([
            'manage_categories' => [
                'state' => 'healthy',
                'capability' => 'manage_categories',
                'explicit_allow_roles' => ['administrator'],
                'explicit_deny_roles' => ['editor'],
                'absent_roles' => ['subscriber'],
                'caveats' => [],
            ],
            'edit_posts' => [
                'state' => 'healthy',
                'capability' => 'edit_posts',
                'explicit_allow_roles' => ['administrator', 'editor'],
                'explicit_deny_roles' => [],
                'absent_roles' => ['subscriber'],
                'caveats' => [],
            ],
        ]);
        $model = new TaxonomyRoleImpactReadModel($service);

        $impact = $model->forCapabilities([
            'manage_terms' => 'manage_categories',
            'edit_terms' => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ], new ExecutionContext(new Principal(8), 1));

        self::assertSame('healthy', $impact['state']);
        self::assertCount(4, $impact['operations']);
        self::assertSame(['manage_categories', 'edit_posts'], $service->queried, 'One query per unique capability.');

        $manage = $impact['operations'][0]['impact'];
        self::assertSame(['administrator'], $manage['explicit_allow_roles']);
        self::assertSame(['editor'], $manage['explicit_deny_roles']);
        self::assertSame(['subscriber'], $manage['absent_roles']);
        self::assertSame('assign_terms', $impact['operations'][3]['operation']);
        self::assertStringContainsString('not final user authorization', implode(' ', $impact['caveats']));
    }

    public function testDegradedAndUnavailableImpactsDriveOverallState(): void
    {
        $service = new FakeRoleImpactService([
            'a' => ['state' => 'degraded', 'caveats' => ['Role store partially readable.']],
            'b' => ['state' => 'healthy', 'caveats' => []],
        ]);
        $context = new ExecutionContext(new Principal(1), 1);
        $model = new TaxonomyRoleImpactReadModel($service);

        $degraded = $model->forCapabilities(['manage_terms' => 'a', 'assign_terms' => 'b'], $context);
        self::assertSame('degraded', $degraded['state']);
        self::assertContains('Role store partially readable.', $degraded['caveats']);

        $service->impacts['b'] = ['state' => 'unavailable'];
        $unavailable = (new TaxonomyRoleImpactReadModel($service))
            ->forCapabilities(['manage_terms' => 'a', 'assign_terms' => 'b'], $context);
        self::assertSame('unavailable', $unavailable['state']);
    }

    public function testWithoutServiceOrContextReportsUnavailableWithoutFallback(): void
    {
        $capabilities = ['manage_terms' => 'manage_categories', 'assign_terms' => 'edit_posts'];

        foreach ([
            (new TaxonomyRoleImpactReadModel())->forCapabilities($capabilities, new ExecutionContext(new Principal(1), 1)),
            (new TaxonomyRoleImpactReadModel(new FakeRoleImpactService([])))->forCapabilities($capabilities),
        ] as $impact) {
            self::assertSame('unavailable', $impact['state']);
            self::assertCount(2, $impact['operations']);
            self::assertSame('unavailable', $impact['operations'][0]['impact']['state']);
            self::assertSame('manage_categories', $impact['operations'][0]['impact']['capability']);
            self::assertSame([], $impact['operations'][1]['impact']['explicit_allow_roles']);
            self::assertStringContainsString('does not fall back', implode(' ', $impact['caveats']));
        }
    }

    public function testEffectiveCapabilitiesUseDefaultsAndTrimmedAuthoredOverrides(): void
    {
        $model = new TaxonomyRoleImpactReadModel();

        self::assertSame([
            'manage_terms' => 'manage_categories',
            'edit_terms' => 'manage_categories',
            'delete_terms' => 'manage_categories',
            'assign_terms' => 'edit_posts',
        ], $model->effectiveCapabilities([]));

        $effective = $model->effectiveCapabilities(['capabilities' => [
            'manage_terms' => '  manage_genres ',
            'edit_terms' => '   ',
            'unknown' => 'ignored',
        ]]);
        self::assertSame('manage_genres', $effective['manage_terms']);
        self::assertSame('manage_categories', $effective['edit_terms']);
        self::assertArrayNotHasKey('unknown', $effective);

        self::assertSame('manage_categories', $model->effectiveCapabilities(['capabilities' => ['x', 'y']])['manage_terms']);
    }
}

final class FakeRoleImpactService implements RoleImpactReadServiceInterface
{
    /** @var list<string> */
    public array $queried = [];

    /** @param array<string,array<string,mixed>> $impacts */
    public function __construct(public array $impacts)
    {
    }

    public function capabilityImpact(string $capability, ExecutionContext $context): array
    {
        $this->queried[] = $capability;
        return $this->impacts[$capability] ?? ['state' => 'unavailable'];
    }
}
