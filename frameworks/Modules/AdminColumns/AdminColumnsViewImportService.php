<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/**
 * Create-only persistence bridge from the certified portability codec into the
 * authoritative revisioned Admin Columns View definition service.
 */
final readonly class AdminColumnsViewImportService
{
    public const CONTRACT_VERSION = 1;

    public function __construct(
        private AdminColumnsViewPortabilityCodec $codec,
        private AdminColumnsViewDefinitionService $views,
    ) {}

    /**
     * @param array<string,mixed> $remaps
     * @return array{
     *   contract_version:int,
     *   source_view_id:string,
     *   source_revision:int,
     *   destination:Definition
     * }
     */
    public function commitCreate(
        string $document,
        array $remaps,
        DefinitionStatus $status = DefinitionStatus::Draft,
    ): array {
        $decoded = $this->codec->decode($document, $remaps);
        $definition = $this->views->importCreate(
            $decoded['payload'],
            $decoded['view_id'],
            $status,
        );

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'source_view_id' => $decoded['view_id'],
            'source_revision' => $decoded['source_revision'],
            'destination' => $definition,
        ];
    }
}
