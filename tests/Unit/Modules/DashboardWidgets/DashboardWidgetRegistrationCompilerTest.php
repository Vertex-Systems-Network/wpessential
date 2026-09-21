<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationDescriptor;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetRegistrationCompilerTest extends TestCase
{
    public function testCompilesPublishedOwnedDefinitionIntoTypedDescriptor(): void
    {
        $descriptor = (new DashboardWidgetRegistrationCompiler())->compile($this->definition());

        self::assertInstanceOf(DashboardWidgetRegistrationDescriptor::class, $descriptor);
        self::assertSame('11111111-1111-4111-8111-111111111111', $descriptor->definitionId);
        self::assertSame(3, $descriptor->revision);
        self::assertSame('sales-overview', $descriptor->key);
        self::assertSame('Sales Overview', $descriptor->title);
        self::assertSame('normal', $descriptor->context);
        self::assertSame('default', $descriptor->priority);
        self::assertTrue($descriptor->networkDashboard);
    }

    public function testRejectsWrongOwnerTypeOrStatus(): void
    {
        $compiler = new DashboardWidgetRegistrationCompiler();

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

    public function testRegistrationCompilationFailsClosedOnMalformedVisibilityMetadata(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new DashboardWidgetRegistrationCompiler())->compile($this->definition(
            visibility: ['roles' => ['Bad Role']],
        ));
    }

    public function testRejectsMalformedRegistrationMetadata(): void
    {
        $compiler = new DashboardWidgetRegistrationCompiler();
        $valid = [
            'key' => 'sales-overview',
            'title' => 'Sales Overview',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => false,
        ];

        $cases = [
            array_replace($valid, ['key' => 'Bad Key']),
            array_replace($valid, ['title' => '<script>alert(1)</script>']),
            array_replace($valid, ['context' => 'unknown']),
            array_replace($valid, ['priority' => 'urgent']),
            array_replace($valid, ['network_dashboard' => 'yes']),
            $valid + ['callback' => 'arbitrary_php'],
        ];

        foreach ($cases as $widget) {
            try {
                $compiler->compile($this->definition(widget: $widget));
                self::fail('Expected malformed Dashboard Widget registration metadata to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    /**
     * @param array<string,mixed>|null $widget
     */
    private function definition(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        string $type = DashboardWidgetDefinition::TYPE,
        DefinitionStatus $status = DefinitionStatus::Published,
        ?array $widget = null,
        ?array $visibility = null,
    ): Definition {
        $widget = $widget ?? [
            'key' => 'sales-overview',
            'title' => 'Sales Overview',
            'context' => 'normal',
            'priority' => 'default',
            'network_dashboard' => true,
        ];
        if ($visibility !== null) {
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
