<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassCompiler;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetContentClassDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetDefinition;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DashboardWidgetContentClassCompilerTest extends TestCase
{
    public function testCompilesEveryReviewedStructuredContentClass(): void
    {
        $compiler = new DashboardWidgetContentClassCompiler();

        foreach (DashboardWidgetContentClassDescriptor::TRUSTED_TYPES as $contentType) {
            $descriptor = $compiler->compile($this->definition(contentType: $contentType));

            self::assertInstanceOf(DashboardWidgetContentClassDescriptor::class, $descriptor);
            self::assertSame('11111111-1111-4111-8111-111111111111', $descriptor->definitionId);
            self::assertSame(3, $descriptor->revision);
            self::assertSame($contentType, $descriptor->contentType);
        }
    }

    public function testRejectsWrongOwnerTypeSchemaOrStatus(): void
    {
        $compiler = new DashboardWidgetContentClassCompiler();

        foreach ([
            $this->definition(ownerSurfaceId: 9),
            $this->definition(type: 'listing'),
            $this->definition(schemaVersion: 2),
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

    public function testRejectsMissingMalformedUnknownAndDeferredContentClasses(): void
    {
        $compiler = new DashboardWidgetContentClassCompiler();

        $cases = [
            $this->definition(includeType: false),
            $this->definition(contentType: 'Rich Text'),
            $this->definition(contentType: 'rich text'),
            $this->definition(contentType: str_repeat('a', 65)),
            $this->definition(contentType: 'iframe'),
            $this->definition(contentType: 'rss'),
            $this->definition(contentType: 'video'),
            $this->definition(contentType: 'listing'),
            $this->definition(contentType: 'activity'),
            $this->definition(contentType: 'form_action'),
            $this->definition(contentType: 'site_health'),
            $this->definition(contentType: 'shortcode'),
            $this->definition(contentType: 'block'),
            $this->definition(contentType: 'registered_provider'),
            $this->definition(contentType: 'javascript'),
            $this->definition(contentType: 'php'),
            $this->definition(contentType: 'callback'),
            $this->definition(contentType: 'class'),
        ];

        foreach ($cases as $definition) {
            try {
                $compiler->compile($definition);
                self::fail('Expected untrusted Dashboard Widget content class to be rejected.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    private function definition(
        int $ownerSurfaceId = DashboardWidgetDefinition::OWNER_SURFACE_ID,
        string $type = DashboardWidgetDefinition::TYPE,
        int $schemaVersion = 1,
        DefinitionStatus $status = DefinitionStatus::Published,
        string $contentType = DashboardWidgetContentClassDescriptor::TYPE_RICH_TEXT,
        bool $includeType = true,
    ): Definition {
        $widget = [];
        if ($includeType) {
            $widget['type'] = $contentType;
        }

        return new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'sales-overview',
            type: $type,
            schemaVersion: $schemaVersion,
            ownerSurfaceId: $ownerSurfaceId,
            status: $status,
            payload: ['widget' => $widget],
            revision: 3,
            dependencies: [],
        );
    }
}
