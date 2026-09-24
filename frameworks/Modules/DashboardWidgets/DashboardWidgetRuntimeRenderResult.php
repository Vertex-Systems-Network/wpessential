<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Rendering\RenderFailureCode;

final readonly class DashboardWidgetRuntimeRenderResult
{
    public const STATUS_MISSING_DEFINITION = 'missing_definition';
    public const STATUS_INVALID_DEFINITION = 'invalid_definition';
    public const STATUS_VISIBILITY_DENIED = 'visibility_denied';
    public const STATUS_RENDERER_FAILED = 'renderer_failed';
    public const STATUS_RUNTIME_FAILURE = 'runtime_failure';
    public const STATUS_RENDERED = 'rendered';
    public const STATUS_RENDERED_ERROR = 'rendered_error';

    /** @var list<string> */
    private const STATUSES = [
        self::STATUS_MISSING_DEFINITION,
        self::STATUS_INVALID_DEFINITION,
        self::STATUS_VISIBILITY_DENIED,
        self::STATUS_RENDERER_FAILED,
        self::STATUS_RUNTIME_FAILURE,
        self::STATUS_RENDERED,
        self::STATUS_RENDERED_ERROR,
    ];

    /**
     * @param list<string> $assetHandles
     */
    private function __construct(
        public string $status,
        public string $html,
        public array $assetHandles,
        public ?string $visibilityReason,
        public ?RenderFailureCode $renderFailure,
    ) {
        if (!in_array($this->status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Dashboard Widget runtime render status is unsupported.');
        }

        $this->assertAssetHandles($this->assetHandles);
        $this->assertShape();
    }

    public static function missingDefinition(): self
    {
        return new self(self::STATUS_MISSING_DEFINITION, '', [], null, null);
    }

    public static function invalidDefinition(): self
    {
        return new self(self::STATUS_INVALID_DEFINITION, '', [], null, null);
    }

    public static function visibilityDenied(string $reason): self
    {
        DashboardWidgetVisibilityDecision::deny($reason);

        return new self(self::STATUS_VISIBILITY_DENIED, '', [], $reason, null);
    }

    public static function rendererFailed(RenderFailureCode $failure): self
    {
        return new self(self::STATUS_RENDERER_FAILED, '', [], null, $failure);
    }

    public static function runtimeFailure(): self
    {
        return new self(self::STATUS_RUNTIME_FAILURE, '', [], null, null);
    }

    /** @param list<string> $assetHandles */
    public static function rendered(string $html, array $assetHandles = []): self
    {
        return new self(self::STATUS_RENDERED, $html, $assetHandles, null, null);
    }

    /** @param list<string> $assetHandles */
    public static function renderedError(
        string $html,
        array $assetHandles,
        RenderFailureCode $primaryFailure,
    ): self
    {
        return new self(
            self::STATUS_RENDERED_ERROR,
            $html,
            $assetHandles,
            null,
            $primaryFailure,
        );
    }

    private function assertShape(): void
    {
        if ($this->status === self::STATUS_VISIBILITY_DENIED) {
            if ($this->html !== '' || $this->assetHandles !== [] || $this->visibilityReason === null || $this->renderFailure !== null) {
                throw new InvalidArgumentException('Visibility-denied Dashboard Widget runtime results must fail closed.');
            }
            DashboardWidgetVisibilityDecision::deny($this->visibilityReason);
            return;
        }

        if ($this->status === self::STATUS_RENDERER_FAILED) {
            if ($this->html !== '' || $this->assetHandles !== [] || $this->visibilityReason !== null || $this->renderFailure === null) {
                throw new InvalidArgumentException('Renderer-failed Dashboard Widget runtime results must fail closed.');
            }
            return;
        }

        if ($this->status === self::STATUS_RENDERED) {
            if ($this->visibilityReason !== null || $this->renderFailure !== null) {
                throw new InvalidArgumentException('Rendered Dashboard Widget runtime results cannot carry failure metadata.');
            }
            return;
        }

        if ($this->status === self::STATUS_RENDERED_ERROR) {
            if ($this->visibilityReason !== null || $this->renderFailure === null) {
                throw new InvalidArgumentException(
                    'Rendered-error Dashboard Widget runtime results require the original typed primary render failure metadata.',
                );
            }
            return;
        }

        if ($this->html !== '' || $this->assetHandles !== [] || $this->visibilityReason !== null || $this->renderFailure !== null) {
            throw new InvalidArgumentException('Failed Dashboard Widget runtime results must not expose render payloads.');
        }
    }

    /** @param list<string> $assetHandles */
    private function assertAssetHandles(array $assetHandles): void
    {
        if (!array_is_list($assetHandles) || count($assetHandles) !== count(array_unique($assetHandles))) {
            throw new InvalidArgumentException('Dashboard Widget runtime asset handles must be a unique ordered list.');
        }

        foreach ($assetHandles as $handle) {
            if (!is_string($handle) || preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $handle) !== 1) {
                throw new InvalidArgumentException('Dashboard Widget runtime assets must reference registered WPEssential handles.');
            }
        }
    }
}
