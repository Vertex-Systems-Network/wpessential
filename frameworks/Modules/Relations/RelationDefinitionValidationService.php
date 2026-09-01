<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class RelationDefinitionValidationService
{
    public function __construct(
        private RelationDefinitionNormalizer $normalizer = new RelationDefinitionNormalizer(),
        private RelationEndpointSupport $endpoints = new RelationEndpointSupport(),
    ) {}

    /**
     * @param array<string,mixed> $input
     * @return array{
     *   valid:bool,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>,
     *   candidate:array{relation_key:?string,cardinality:?string,from_type:?string,to_type:?string}
     * }
     */
    public function validate(array $input): array
    {
        $issues = [];
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            return $this->report([
                $this->issue('relation.payload.invalid', 'payload', 'Relation payload must be an object/map.'),
            ]);
        }

        $statusValue = $input['status'] ?? DefinitionStatus::Draft->value;
        if (!is_string($statusValue)) {
            return $this->report([
                $this->issue('relation.status.invalid', 'status', 'Relation status must be a string.'),
            ]);
        }
        $status = DefinitionStatus::tryFrom($statusValue);
        if (!$status instanceof DefinitionStatus) {
            return $this->report([
                $this->issue(
                    'relation.status.unsupported',
                    'status',
                    'Relation status must be draft, published, disabled, or archived.',
                ),
            ]);
        }

        try {
            $normalized = $this->normalizer->normalize($payload, $status === DefinitionStatus::Published);
        } catch (InvalidArgumentException $exception) {
            return $this->report([
                $this->issue('relation.definition.invalid', 'payload', $exception->getMessage()),
            ]);
        }

        if ($status === DefinitionStatus::Published) {
            foreach (['from', 'to'] as $side) {
                $endpoint = $normalized[$side] ?? null;
                if (!is_array($endpoint)) {
                    $issues[] = $this->issue(
                        'relation.endpoint.invalid',
                        $side,
                        sprintf('Relation %s endpoint is invalid.', $side),
                    );
                    continue;
                }

                /** @var array{object_type:string,object_subtype:?string,label:string} $endpoint */
                if (!$this->endpoints->supports($endpoint)) {
                    $issues[] = $this->issue(
                        'relation.endpoint.unsupported',
                        $side,
                        $this->endpoints->unsupportedReason($endpoint),
                    );
                }
            }
        }

        return [
            'valid' => $issues === [],
            'issues' => $issues,
            'candidate' => [
                'relation_key' => is_string($normalized['relation_key'] ?? null) ? $normalized['relation_key'] : null,
                'cardinality' => is_string($normalized['cardinality'] ?? null) ? $normalized['cardinality'] : null,
                'from_type' => $this->endpointType($normalized['from'] ?? null),
                'to_type' => $this->endpointType($normalized['to'] ?? null),
            ],
        ];
    }

    /**
     * @param list<array{id:string,severity:string,field:string,message:string}> $issues
     * @return array{
     *   valid:bool,
     *   issues:list<array{id:string,severity:string,field:string,message:string}>,
     *   candidate:array{relation_key:?string,cardinality:?string,from_type:?string,to_type:?string}
     * }
     */
    private function report(array $issues): array
    {
        return [
            'valid' => false,
            'issues' => $issues,
            'candidate' => [
                'relation_key' => null,
                'cardinality' => null,
                'from_type' => null,
                'to_type' => null,
            ],
        ];
    }

    /** @return array{id:string,severity:string,field:string,message:string} */
    private function issue(string $id, string $field, string $message): array
    {
        return [
            'id' => $id,
            'severity' => 'blocked',
            'field' => $field,
            'message' => $message,
        ];
    }

    private function endpointType(mixed $endpoint): ?string
    {
        if (!is_array($endpoint)) {
            return null;
        }
        $type = $endpoint['object_type'] ?? null;
        return is_string($type) ? $type : null;
    }
}
