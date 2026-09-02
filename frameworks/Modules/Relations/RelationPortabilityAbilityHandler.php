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

final readonly class RelationPortabilityAbilityHandler implements AbilityHandlerInterface
{
    public const EXPORT = 'export';
    public const IMPORT = 'import';

    public function __construct(
        private RelationPortabilityService $portability,
        private string $action,
    ) {
        if (!in_array($this->action, [self::EXPORT, self::IMPORT], true)) {
            throw new InvalidArgumentException('Unsupported Relation portability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::EXPORT => $this->export($input),
            self::IMPORT => $this->import($input),
            default => throw new RuntimeException('Unsupported Relation portability action.'),
        };
    }

    /** @param array<string,mixed> $input */
    private function export(array $input): array
    {
        $ids = $input['definition_ids'] ?? [];
        if (!is_array($ids) || !array_is_list($ids)) {
            throw new InvalidArgumentException('Relation portability definition_ids must be a list.');
        }
        foreach ($ids as $id) {
            if (!is_string($id)) {
                throw new InvalidArgumentException(
                    'Relation portability definition_ids must contain strings.',
                );
            }
        }

        /** @var list<string> $ids */
        return $this->portability->export($ids);
    }

    /** @param array<string,mixed> $input */
    private function import(array $input): array
    {
        $envelope = $input['envelope'] ?? null;
        if (!is_array($envelope) || array_is_list($envelope)) {
            throw new InvalidArgumentException('Relation portability envelope must be an object/map.');
        }

        return $this->portability->import($envelope);
    }
}
