<?php

declare(strict_types=1);

namespace WPEssential\Platform\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Auth\ExecutionChannel;

final readonly class AbilityDescriptor
{
    /**
     * @param list<ExecutionChannel> $channels
     * @param array<string, mixed> $inputSchema
     * @param array<string, mixed> $outputSchema
     */
    public function __construct(
        public string $name,
        public int $ownerSurfaceId,
        public string $capability,
        public bool $mutates,
        public array $channels = [ExecutionChannel::Internal],
        public array $inputSchema = [],
        public array $outputSchema = [],
    ) {
        if (!preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $this->name)) {
            throw new InvalidArgumentException('Ability name must use wpessential/<domain>/<action>.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Ability owner must be a canonical surface id 1..56.');
        }
        if (!preg_match('/^[a-z0-9_]+$/', $this->capability)) {
            throw new InvalidArgumentException('Ability capability must be a stable WordPress capability key.');
        }
        if ($this->channels === []) {
            throw new InvalidArgumentException('Ability must allow at least one explicit execution channel.');
        }
        foreach ($this->channels as $channel) {
            if (!$channel instanceof ExecutionChannel) {
                throw new InvalidArgumentException('Ability channels must be ExecutionChannel values.');
            }
        }
    }

    public function allows(ExecutionChannel $channel): bool
    {
        return in_array($channel, $this->channels, true);
    }
}
