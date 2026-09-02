<?php

declare(strict_types=1);

namespace WPEssential\Modules\Relations;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class RelationEdgeMutationAbilityHandler implements AbilityHandlerInterface
{
    public const CONNECT = 'connect';
    public const DISCONNECT = 'disconnect';

    public function __construct(
        private RelationEdgeMutationService $mutations,
        private string $action,
    ) {
        if (!in_array($this->action, [self::CONNECT, self::DISCONNECT], true)) {
            throw new InvalidArgumentException('Unsupported Relation edge mutation ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $relationDefinitionId = $input['relation_definition_id'] ?? null;
        $fromObjectId = $input['from_object_id'] ?? null;
        $toObjectId = $input['to_object_id'] ?? null;

        if (!is_string($relationDefinitionId)) {
            throw new InvalidArgumentException('relation_definition_id must be a lowercase RFC 4122 UUID.');
        }
        if (!is_int($fromObjectId) || $fromObjectId < 1) {
            throw new InvalidArgumentException('from_object_id must be a positive integer.');
        }
        if (!is_int($toObjectId) || $toObjectId < 1) {
            throw new InvalidArgumentException('to_object_id must be a positive integer.');
        }

        $mutation = match ($this->action) {
            self::CONNECT => $this->mutations->connect($relationDefinitionId, $fromObjectId, $toObjectId, $context),
            self::DISCONNECT => $this->mutations->disconnect($relationDefinitionId, $fromObjectId, $toObjectId, $context),
            default => throw new RuntimeException('Unsupported Relation edge mutation ability action.'),
        };

        return ['mutation' => $mutation];
    }
}
