<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class AdminColumnsViewImportAbilityHandler implements AbilityHandlerInterface
{
    public const ABILITY = 'wpessential/admin-columns/import-view';
    public const AJAX_TYPE = 'admin-columns.import.view';

    private const INPUT_KEYS = ['document', 'remaps'];

    public function __construct(private AdminColumnsViewImportService $imports)
    {
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $this->assertKnownKeys($input);
        $context->principal;

        $document = $input['document'] ?? null;
        if (!is_string($document)) {
            throw new InvalidArgumentException('Portable View import document must be a string.');
        }
        $remaps = $input['remaps'] ?? null;
        if (!is_array($remaps) || array_is_list($remaps)) {
            throw new InvalidArgumentException('Portable View import remaps must be an object/map.');
        }

        $result = $this->imports->commitCreate($document, $remaps, DefinitionStatus::Draft);
        return [
            'contract_version' => $result['contract_version'],
            'source_view_id' => $result['source_view_id'],
            'source_revision' => $result['source_revision'],
            'destination' => $this->serialize($result['destination']),
        ];
    }

    /** @param array<string,mixed> $input */
    private function assertKnownKeys(array $input): void
    {
        if (array_is_list($input)) {
            throw new InvalidArgumentException('Portable View import input must be an object/map.');
        }
        foreach (array_keys($input) as $key) {
            if (!in_array($key, self::INPUT_KEYS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Portable View import input contains unsupported key "%s".',
                    (string) $key,
                ));
            }
        }
        foreach (self::INPUT_KEYS as $key) {
            if (!array_key_exists($key, $input)) {
                throw new InvalidArgumentException(sprintf(
                    'Portable View import input requires "%s".',
                    $key,
                ));
            }
        }
    }

    /** @return array<string,mixed> */
    private function serialize(Definition $definition): array
    {
        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->checksum,
        ];
    }
}
