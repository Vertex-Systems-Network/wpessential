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

        foreach ([
            'planFingerprint' => 'plan_fingerprint_changed',
            'sourceObservedFingerprint' => 'source_observed_fingerprint_changed',
            'targetDefinitionId' => 'target_definition_changed',
            'targetRevision' => 'target_revision_changed',
            'targetSchemaVersion' => 'target_schema_version_changed',
            'provider' => 'provider_changed',
            'providerVersion' => 'provider_version_changed',
        ] as $property => $reason) {
            if ($reviewed->{$property} !== $current->{$property}) {
                $reasons[] = $reason;
            }
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
