<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityDescriptor;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetVisibilityCompilerTest extends TestCase
{
    public function testCompilesPublishedOwnedDefinitionIntoTypedVisibilityDescriptor(): void
    {
        $descriptor = (new DashboardWidgetVisibilityCompiler())->compile($this->definition(
            visibility: [
                'roles' => ['administrator', 'editor'],
                'capabilities' => ['manage_options', 'edit_posts'],
                'users' => [7, 11],
            ],
        ));

        self::assertInstanceOf(DashboardWidgetVisibilityDescriptor::class, $descriptor);
        self::assertSame('11111111-1111-4111-8111-111111111111', $descriptor->definitionId);
        self::assertSame(3, $descriptor->revision);
        self::assertSame(['administrator', 'editor'], $descriptor->roles);
        self::assertSame(['manage_options', 'edit_posts'], $descriptor->capabilities);
        self::assertSame([7, 11], $descriptor->users);
    }

    public function testMissingVisibilityCompilesToDeterministicEmptyAudienceMetadata(): void
    {
        $descriptor = (new DashboardWidgetVisibilityCompiler())->compile(
            $this->definition(includeVisibility: false),
        );

        self::assertSame([], $descriptor->roles);
        self::assertSame([], $descriptor->capabilities);
        self::assertSame([], $descriptor->users);
    }

    public function testRejectsWrongOwnerTypeOrStatus(): void
    {
        $compiler = new DashboardWidgetVisibilityCompiler();

        foreach ([
            $this->definition(ownerSurfaceId: 9),
            $this->definition(type: 'listing'),
            $this->definition(status: DefinitionStatus::Draft),
        ] as $definition) {
            try {
                $compiler->compile($definition);
                self::fail('Expected non-runtime Dashboard Widget definition to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsMalformedOrUnknownVisibilityMetadata(): void
    {
        $compiler = new DashboardWidgetVisibilityCompiler();

        $cases = [
            ['roles' => ['Bad Role']],
            ['roles' => ['editor', 'editor']],
            ['capabilities' => ['manage options']],
            ['capabilities' => ['edit_posts', 'edit_posts']],
            ['users' => [0]],
            ['users' => [7, 7]],
            ['users' => ['7']],
            ['condition' => ['key' => 'unsupported']],
        ];

        foreach ($cases as $visibility) {
            try {
                $compiler->compile($this->definition(visibility: $visibility));
                self::fail('Expected malformed Dashboard Widget visibility metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsUnboundedVisibilityLists(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DashboardWidgetVisibilityCompiler())->compile($this->definition(
            visibility: ['users' => range(1, DashboardWidgetVisibilityDescriptor::MAX_USERS + 1)],
        ));
    }

    /**
     * @param array<string,mixed> $visibility
     */
    private function definition(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        string $type = DashboardWidgetDefinition::TYPE,
        DefinitionStatus $status = DefinitionStatus::Published,
        array $visibility = [],
        bool $includeVisibility = true,
    ): Definition {
        $widget = [
            'key' => 'sales-overview',
            'title' => 'Sales Overview',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => false,
        ];

        if ($includeVisibility) {
            $widget['visibility'] = $visibility;
        }

        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'sales-overview',
            type: $type,
            schemaVersion: 1,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );
    }
}
