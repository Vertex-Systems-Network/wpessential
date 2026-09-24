<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRuntimeRenderResult;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetVisibilityDecision;
use WPEssential\Platform\Rendering\RenderFailureCode;

final class DashboardWidgetRuntimeRenderResultTest extends TestCase
{
    public function testFactoriesExposeOnlyBoundedV1Shapes(): void
    {
        $missing = DashboardWidgetRuntimeRenderResult::missingDefinition();
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_MISSING_DEFINITION, $missing->status);
        self::assertSame('', $missing->html);
        self::assertSame([], $missing->assetHandles);
        self::assertNull($missing->visibilityReason);
        self::assertNull($missing->renderFailure);

        $invalid = DashboardWidgetRuntimeRenderResult::invalidDefinition();
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_INVALID_DEFINITION, $invalid->status);

        $denied = DashboardWidgetRuntimeRenderResult::visibilityDenied(
            DashboardWidgetVisibilityDecision::REASON_USER_MISMATCH,
        );
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_VISIBILITY_DENIED, $denied->status);
        self::assertSame(DashboardWidgetVisibilityDecision::REASON_USER_MISMATCH, $denied->visibilityReason);
        self::assertSame('', $denied->html);
        self::assertSame([], $denied->assetHandles);

        $rendererFailed = DashboardWidgetRuntimeRenderResult::rendererFailed(RenderFailureCode::MissingBlueprint);
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERER_FAILED, $rendererFailed->status);
        self::assertSame(RenderFailureCode::MissingBlueprint, $rendererFailed->renderFailure);
        self::assertSame('', $rendererFailed->html);
        self::assertSame([], $rendererFailed->assetHandles);

        $runtimeFailed = DashboardWidgetRuntimeRenderResult::runtimeFailure();
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RUNTIME_FAILURE, $runtimeFailed->status);
        self::assertSame('', $runtimeFailed->html);
        self::assertSame([], $runtimeFailed->assetHandles);
        self::assertNull($runtimeFailed->visibilityReason);
        self::assertNull($runtimeFailed->renderFailure);

        $rendered = DashboardWidgetRuntimeRenderResult::rendered('<p>safe</p>', ['wpe-dashboard-widget']);
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED, $rendered->status);
        self::assertSame('<p>safe</p>', $rendered->html);
        self::assertSame(['wpe-dashboard-widget'], $rendered->assetHandles);
        self::assertNull($rendered->visibilityReason);
        self::assertNull($rendered->renderFailure);

        $renderedError = DashboardWidgetRuntimeRenderResult::renderedError(
            '<p>safe error presentation</p>',
            ['wpe-dashboard-error'],
            RenderFailureCode::DependencyMismatch,
        );
        self::assertSame(DashboardWidgetRuntimeRenderResult::STATUS_RENDERED_ERROR, $renderedError->status);
        self::assertSame('<p>safe error presentation</p>', $renderedError->html);
        self::assertSame(['wpe-dashboard-error'], $renderedError->assetHandles);
        self::assertNull($renderedError->visibilityReason);
        self::assertSame(RenderFailureCode::DependencyMismatch, $renderedError->renderFailure);
    }

    public function testVisibilityDeniedRejectsAllowedReason(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DashboardWidgetRuntimeRenderResult::visibilityDenied(
            DashboardWidgetVisibilityDecision::REASON_ALLOWED,
        );
    }

    public function testRenderedRejectsMalformedOrDuplicateAssetHandles(): void
    {
        try {
            DashboardWidgetRuntimeRenderResult::rendered('<p>safe</p>', ['not-wpe']);
            self::fail('Expected malformed asset handle rejection.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        try {
            DashboardWidgetRuntimeRenderResult::renderedError(
                '<p>safe</p>',
                ['not-wpe'],
                RenderFailureCode::InvalidInput,
            );
            self::fail('Expected malformed rendered-error asset handle rejection.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        DashboardWidgetRuntimeRenderResult::rendered('<p>safe</p>', ['wpe-safe', 'wpe-safe']);
    }
}
