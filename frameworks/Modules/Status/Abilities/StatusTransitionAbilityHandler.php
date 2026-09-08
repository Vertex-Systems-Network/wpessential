<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Abilities;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutor;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class StatusTransitionAbilityHandler implements AbilityHandlerInterface
{
    /** @var list<string> */
    private const INPUT_KEYS = [
        'post_id',
        'expected_current_status',
        'target_status',
        'reason',
        'programmatic',
    ];

    public function __construct(private StatusTransitionExecutor $executor) {}

    /** @param array<string,mixed> $input */
    public function handle(array $input, ExecutionContext $context): array
    {
        $this->assertKnownKeys($input);

        $postId = $input['post_id'] ?? null;
        $expected = $input['expected_current_status'] ?? null;
        $target = $input['target_status'] ?? null;
        $reason = $input['reason'] ?? null;
        $programmatic = $input['programmatic'] ?? false;

        if (!is_int($postId) || $postId < 1) {
            throw new InvalidArgumentException('Status transition Ability post_id must be a positive integer.');
        }
        if (!is_string($expected) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $expected)) {
            throw new InvalidArgumentException('Status transition Ability expected_current_status is invalid.');
        }
        if (!is_string($target) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $target)) {
            throw new InvalidArgumentException('Status transition Ability target_status is invalid.');
        }
        if ($reason !== null && !is_string($reason)) {
            throw new InvalidArgumentException('Status transition Ability reason must be null or string.');
        }
        if (!is_bool($programmatic)) {
            throw new InvalidArgumentException('Status transition Ability programmatic must be boolean.');
        }

        $result = $this->executor->execute(
            context: $context,
            postId: $postId,
            expectedFromStatus: $expected,
            targetStatus: $target,
            reason: $reason,
            programmatic: $programmatic,
        );

        return [
            'post_id' => $result->postId,
            'from_status' => $result->fromStatus,
            'to_status' => $result->toStatus,
            'post_type' => $result->postType,
            'reason' => $result->reason,
            'programmatic' => $result->programmatic,
        ];
    }

    /** @param array<string,mixed> $input */
    private function assertKnownKeys(array $input): void
    {
        foreach (array_keys($input) as $key) {
            if (!is_string($key) || !in_array($key, self::INPUT_KEYS, true)) {
                throw new InvalidArgumentException('Status transition Ability contains an unsupported input key.');
            }
        }
    }
}
