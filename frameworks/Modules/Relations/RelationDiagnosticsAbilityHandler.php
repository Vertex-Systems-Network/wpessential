<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;

final readonly class RelationDiagnosticsAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(
        private DefinitionRepositoryInterface $definitions,
        private RelationDefinitionNormalizer $normalizer,
        private RelationDefinitionValidationService $validation,
        private RelationEndpointSupport $endpoints,
        private ?WpdbRelationEdgeGateway $gateway = null,
    ) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $definitionId = $input['definition_id'] ?? null;
        if ($definitionId !== null && (!is_string($definitionId) || !$this->isUuid($definitionId))) {
            throw new InvalidArgumentException(
                'Relation diagnostics definition_id must be a lowercase RFC 4122 UUID.',
            );
        }

        $definitions = $definitionId === null
            ? $this->ownedDefinitions()
            : [$this->owned($definitionId)];
        $relations = array_map($this->diagnose(...), $definitions);
        $unhealthy = count(array_filter(
            $relations,
            static fn (array $diagnostic): bool => $diagnostic['issues'] !== [],
        ));

        return [
            'summary' => [
                'relations' => count($relations),
                'unhealthy' => $unhealthy,
                'persistence_available' => $this->gateway instanceof WpdbRelationEdgeGateway,
            ],
            'relations' => $relations,
        ];
    }

    /** @return list<Definition> */
    private function ownedDefinitions(): array
    {
        $definitions = array_values(array_filter(
            $this->definitions->byType(RelationDefinitionNormalizer::DEFINITION_TYPE),
            static fn (Definition $definition): bool =>
                $definition->ownerSurfaceId === RelationDefinitionNormalizer::OWNER_SURFACE_ID,
        ));
        usort(
            $definitions,
            static fn (Definition $left, Definition $right): int =>
                [$left->slug, $left->id] <=> [$right->slug, $right->id],
        );
        return $definitions;
    }

    /** @return array<string,mixed> */
    private function diagnose(Definition $definition): array
    {
        $issues = [];
        $normalized = null;
        try {
            $normalized = $this->normalizer->normalize(
                $definition->payload,
                $definition->status->value === 'published',
            );
        } catch (Throwable $error) {
            $issues[] = $this->issue(
                'relation.diagnostics.payload-invalid',
                'payload',
                $error->getMessage(),
            );
        }

        $report = $this->validation->validate([
            'payload' => $definition->payload,
            'status' => $definition->status->value,
        ]);
        foreach ($report['issues'] as $issue) {
            $issues[] = $issue;
        }

        $checksumStatus = 'missing';
        try {
            if ($definition->checksum !== null) {
                $checksumStatus = hash_equals($definition->checksum, $definition->computedChecksum())
                    ? 'valid'
                    : 'mismatch';
                if ($checksumStatus === 'mismatch') {
                    $issues[] = $this->issue(
                        'relation.diagnostics.checksum-mismatch',
                        'checksum',
                        'Stored Relation checksum does not match the canonical payload.',
                    );
                }
            } else {
                $issues[] = $this->issue(
                    'relation.diagnostics.checksum-missing',
                    'checksum',
                    'Stored Relation definition does not have a canonical checksum.',
                );
            }
        } catch (Throwable $error) {
            $checksumStatus = 'error';
            $issues[] = $this->issue(
                'relation.diagnostics.checksum-error',
                'checksum',
                'Relation checksum could not be recomputed: ' . $error->getMessage(),
            );
        }

        $edgeRevision = null;
        if ($this->gateway instanceof WpdbRelationEdgeGateway) {
            try {
                $edgeRevision = $this->gateway->revision($definition->id);
            } catch (Throwable $error) {
                $issues[] = $this->issue(
                    'relation.diagnostics.persistence-error',
                    'persistence',
                    'Relation edge persistence state could not be read: ' . $error->getMessage(),
                );
            }
        }

        $from = is_array($normalized['from'] ?? null) ? $normalized['from'] : null;
        $to = is_array($normalized['to'] ?? null) ? $normalized['to'] : null;

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'relation_key' => is_string($normalized['relation_key'] ?? null)
                ? $normalized['relation_key']
                : null,
            'status' => $definition->status->value,
            'schema_version' => $definition->schemaVersion,
            'definition_revision' => $definition->revision,
            'checksum_status' => $checksumStatus,
            'cardinality' => is_string($normalized['cardinality'] ?? null)
                ? $normalized['cardinality']
                : null,
            'direction' => is_array($normalized['direction'] ?? null)
                ? $normalized['direction']
                : null,
            'unique_edge' => is_bool($normalized['unique_edge'] ?? null)
                ? $normalized['unique_edge']
                : null,
            'endpoints' => [
                'from' => $this->endpointDiagnostic($from),
                'to' => $this->endpointDiagnostic($to),
            ],
            'persistence' => [
                'available' => $this->gateway instanceof WpdbRelationEdgeGateway,
                'edge_revision' => $edgeRevision,
            ],
            'issues' => $issues,
        ];
    }

    /** @param null|array<string,mixed> $endpoint @return array<string,mixed> */
    private function endpointDiagnostic(?array $endpoint): array
    {
        if ($endpoint === null) {
            return [
                'object_type' => null,
                'object_subtype' => null,
                'supported' => false,
                'reason' => 'Endpoint is not canonically normalized.',
            ];
        }

        $type = $endpoint['object_type'] ?? null;
        $subtype = $endpoint['object_subtype'] ?? null;
        $label = $endpoint['label'] ?? null;
        if (!is_string($type) || (!is_string($subtype) && $subtype !== null) || !is_string($label)) {
            return [
                'object_type' => is_string($type) ? $type : null,
                'object_subtype' => is_string($subtype) ? $subtype : null,
                'supported' => false,
                'reason' => 'Endpoint shape is malformed.',
            ];
        }

        /** @var array{object_type:string,object_subtype:?string,label:string} $endpoint */
        $supported = $this->endpoints->supports($endpoint);
        return [
            'object_type' => $type,
            'object_subtype' => $subtype,
            'supported' => $supported,
            'reason' => $supported ? null : $this->endpoints->unsupportedReason($endpoint),
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

    private function owned(string $id): Definition
    {
        $definition = $this->definitions->get($id);
        if (!$definition instanceof Definition
            || $definition->type !== RelationDefinitionNormalizer::DEFINITION_TYPE
            || $definition->ownerSurfaceId !== RelationDefinitionNormalizer::OWNER_SURFACE_ID
        ) {
            throw new RuntimeException('Relation definition was not found in canonical Surface 4.');
        }
        return $definition;
    }

    private function isUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) === 1;
    }
}
