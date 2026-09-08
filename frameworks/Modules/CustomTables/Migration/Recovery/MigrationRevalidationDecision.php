<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class MigrationRevalidationDecision
{
    /** @param list<string> $reasons */
    private function __construct(
        public bool $allowed,
        public array $reasons,
    ) {
    }

    public static function evaluate(
        MigrationGenerationStamp $reviewed,
        MigrationGenerationStamp $current,
    ): self {
        $reasons = [];

        if ($reviewed->planFingerprint !== $current->planFingerprint) {
            $reasons[] = 'plan_fingerprint_changed';
        }
        if ($reviewed->sourceObservedFingerprint !== $current->sourceObservedFingerprint) {
            $reasons[] = 'source_observed_fingerprint_changed';
        }
        if ($reviewed->targetDefinitionId !== $current->targetDefinitionId) {
            $reasons[] = 'target_definition_changed';
        }
        if ($reviewed->targetRevision !== $current->targetRevision) {
            $reasons[] = 'target_revision_changed';
        }
        if ($reviewed->targetSchemaVersion !== $current->targetSchemaVersion) {
            $reasons[] = 'target_schema_version_changed';
        }
        if ($reviewed->provider !== $current->provider) {
            $reasons[] = 'provider_changed';
        }
        if ($reviewed->providerVersion !== $current->providerVersion) {
            $reasons[] = 'provider_version_changed';
        }

        return new self($reasons === [], $reasons);
    }

    /** @return array{allowed:bool,reasons:list<string>} */
    public function canonical(): array
    {
        $reasons = $this->reasons;
        sort($reasons);

        return [
            'allowed' => $this->allowed,
            'reasons' => $reasons,
        ];
    }
}
