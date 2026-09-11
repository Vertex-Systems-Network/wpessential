<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

final readonly class TaxonomyCptUiImportPreviewService
{
    public function __construct(
        private TaxonomyCptUiImportMapper $mapper,
        private TaxonomyValidationService $validation,
    ) {}

    /**
     * @param array<string,mixed> $source
     * @return array{
     *   valid:bool,
     *   payload:array<string,mixed>,
     *   rejected_fields:list<string>,
     *   unsupported_fields:list<string>,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>,
     *   diagnostics:?array<string,mixed>
     * }
     */
    public function preview(array $source, ?string $definitionId = null): array
    {
        $mapped = $this->mapper->map($source);
        $issues = $mapped['issues'];
        if ($mapped['unsupported_fields'] !== []) {
            $issues[] = [
                'id' => 'cptui_unsupported_fields',
                'severity' => 'compatibility_warning',
                'field' => 'source',
                'message' => sprintf(
                    'Unsupported CPT UI field(s) were not mapped: %s.',
                    implode(', ', $mapped['unsupported_fields']),
                ),
            ];
        }

        foreach ($issues as $issue) {
            if ($issue['severity'] === 'blocked') {
                return [
                    'valid' => false,
                    'payload' => $mapped['payload'],
                    'rejected_fields' => $mapped['rejected_fields'],
                    'unsupported_fields' => $mapped['unsupported_fields'],
                    'issues' => $issues,
                    'diagnostics' => null,
                ];
            }
        }

        $validationInput = ['payload' => $mapped['payload']];
        if ($definitionId !== null && $definitionId !== '') {
            $validationInput['id'] = $definitionId;
        }
        $validation = $this->validation->validate($validationInput);
        return [
            'valid' => $validation['valid'],
            'payload' => $mapped['payload'],
            'rejected_fields' => $mapped['rejected_fields'],
            'unsupported_fields' => $mapped['unsupported_fields'],
            'issues' => array_merge($issues, $validation['issues']),
            'diagnostics' => $validation['diagnostics'],
        ];
    }
}
