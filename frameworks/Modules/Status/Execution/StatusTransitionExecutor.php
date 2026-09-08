<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Execution;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;
use RuntimeException;
use Throwable;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Modules\Status\Definition\StatusRegistrationDescriptor;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

final class StatusTransitionExecutor
{
    /** @var list<string> */
    private const CORE_LIFECYCLE_RESERVED = [
        'future',
        'trash',
        'auto-draft',
        'inherit',
        'request-pending',
        'request-confirmed',
        'request-failed',
        'request-completed',
    ];

    /** @var Closure(int):string|false */
    private Closure $readPostStatus;

    /** @var Closure(int):string|false */
    private Closure $readPostType;

    /** @var Closure(string):bool */
    private Closure $statusRegistered;

    /** @var Closure(int,string):mixed */
    private Closure $updatePostStatus;

    /**
     * @param null|callable(int):string|false $readPostStatus
     * @param null|callable(int):string|false $readPostType
     * @param null|callable(string):bool $statusRegistered
     * @param null|callable(int,string):mixed $updatePostStatus
     */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly StatusTransitionPolicy $policy,
        private readonly WordPressPostResourceAuthorizer $resources,
        private readonly CapabilityCheckerInterface $capabilities,
        private readonly StatusDefinitionCompiler $compiler = new StatusDefinitionCompiler(),
        ?callable $readPostStatus = null,
        ?callable $readPostType = null,
        ?callable $statusRegistered = null,
        ?callable $updatePostStatus = null,
    ) {
        $this->readPostStatus = $readPostStatus !== null
            ? Closure::fromCallable($readPostStatus)
            : static function (int $postId): string|false {
                if (!function_exists('get_post_status')) {
                    throw new LogicException('WordPress post-status API is unavailable.');
                }

                return get_post_status($postId);
            };

        $this->readPostType = $readPostType !== null
            ? Closure::fromCallable($readPostType)
            : static function (int $postId): string|false {
                if (!function_exists('get_post_type')) {
                    throw new LogicException('WordPress post-type API is unavailable.');
                }

                return get_post_type($postId);
            };

        $this->statusRegistered = $statusRegistered !== null
            ? Closure::fromCallable($statusRegistered)
            : static function (string $status): bool {
                if (!function_exists('get_post_status_object')) {
                    throw new LogicException('WordPress registered-status API is unavailable.');
                }

                return get_post_status_object($status) !== null;
            };

        $this->updatePostStatus = $updatePostStatus !== null
            ? Closure::fromCallable($updatePostStatus)
            : static function (int $postId, string $targetStatus): mixed {
                if (!function_exists('wp_update_post')) {
                    throw new LogicException('WordPress post mutation API is unavailable.');
                }

                return wp_update_post(
                    ['ID' => $postId, 'post_status' => $targetStatus],
                    true,
                );
            };
    }

    public function execute(
        ExecutionContext $context,
        int $postId,
        string $expectedFromStatus,
        string $targetStatus,
        ?string $reason = null,
        bool $programmatic = false,
    ): StatusTransitionExecutionResult {
        $this->assertStatusKey($expectedFromStatus, 'expected current');
        $this->assertStatusKey($targetStatus, 'target');
        $this->assertNotReservedLifecycle($expectedFromStatus);
        $this->assertNotReservedLifecycle($targetStatus);

        [$currentStatus, $postType] = $this->serverState($postId);
        if ($currentStatus !== $expectedFromStatus) {
            throw new RuntimeException('Status transition rejected because the current status is stale.');
        }
        if ($currentStatus === $targetStatus) {
            throw new RuntimeException('Same-status updates are not Status transitions.');
        }
        $this->assertNotReservedLifecycle($currentStatus);

        $rule = $this->policy->ruleFor($currentStatus, $targetStatus);
        if ($rule === null || !$this->policy->allows($currentStatus, $targetStatus, programmatic: $programmatic)) {
            throw new RuntimeException('Status transition edge is not allowed by the bounded policy.');
        }
        if (!$this->policy->acceptsReason($currentStatus, $targetStatus, $reason)) {
            throw new RuntimeException('Status transition reason does not satisfy the bounded policy.');
        }

        $this->assertRegistered($currentStatus);
        $this->assertRegistered($targetStatus);
        $descriptors = $this->publishedDescriptors();
        $this->assertApplicable($descriptors[$currentStatus] ?? null, $postType, 'current');
        $this->assertApplicable($descriptors[$targetStatus] ?? null, $postType, 'target');

        $this->resources->assertCanEdit($context, $postId);
        if ($rule->capability !== null) {
            try {
                $allowed = $this->capabilities->can($context, $rule->capability);
            } catch (Throwable $exception) {
                throw new RuntimeException('Status transition capability evaluation failed.', 0, $exception);
            }
            if (!$allowed) {
                throw new RuntimeException('Status transition additional capability is denied.');
            }
        }

        try {
            $updatedPostId = ($this->updatePostStatus)($postId, $targetStatus);
        } catch (Throwable $exception) {
            throw new RuntimeException('WordPress Status transition mutation failed.', 0, $exception);
        }
        if (!is_int($updatedPostId) || $updatedPostId !== $postId) {
            throw new RuntimeException('WordPress Status transition returned an invalid mutation result.');
        }

        try {
            $verifiedStatus = ($this->readPostStatus)($postId);
        } catch (Throwable $exception) {
            throw new RuntimeException('WordPress Status transition verification failed.', 0, $exception);
        }
        if ($verifiedStatus !== $targetStatus) {
            throw new RuntimeException('WordPress Status transition could not be verified.');
        }

        $normalizedReason = $reason === null ? null : trim($reason);

        return new StatusTransitionExecutionResult(
            postId: $postId,
            fromStatus: $currentStatus,
            toStatus: $targetStatus,
            postType: $postType,
            reason: $normalizedReason,
            programmatic: $programmatic,
        );
    }

    /** @return array{string,string} */
    private function serverState(int $postId): array
    {
        if ($postId < 1) {
            throw new RuntimeException('Status transition post id must be positive.');
        }

        try {
            $status = ($this->readPostStatus)($postId);
            $postType = ($this->readPostType)($postId);
        } catch (Throwable $exception) {
            throw new RuntimeException('WordPress post state could not be read.', 0, $exception);
        }

        if (!is_string($status) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $status)) {
            throw new RuntimeException('WordPress post has no bounded registered current status.');
        }
        if (!is_string($postType) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $postType)) {
            throw new RuntimeException('WordPress post has no bounded current post type.');
        }

        return [$status, $postType];
    }

    /** @return array<string,StatusRegistrationDescriptor> */
    private function publishedDescriptors(): array
    {
        $descriptors = [];
        try {
            $definitions = $this->definitions->byType(StatusDefinitionCompiler::TYPE);
            foreach ($definitions as $definition) {
                if ($definition->status !== DefinitionStatus::Published) {
                    continue;
                }
                $descriptor = $this->compiler->compile($definition);
                if (isset($descriptors[$descriptor->key])) {
                    throw new RuntimeException('Duplicate Published Status definition key.');
                }
                $descriptors[$descriptor->key] = $descriptor;
            }
        } catch (Throwable $exception) {
            if ($exception instanceof RuntimeException) {
                throw $exception;
            }
            throw new RuntimeException('Published Status definitions could not be resolved.', 0, $exception);
        }

        return $descriptors;
    }

    private function assertRegistered(string $status): void
    {
        try {
            $registered = ($this->statusRegistered)($status);
        } catch (Throwable $exception) {
            throw new RuntimeException('WordPress registered Status lookup failed.', 0, $exception);
        }
        if (!$registered) {
            throw new RuntimeException('Status transition references an unregistered WordPress status.');
        }
    }

    private function assertApplicable(
        ?StatusRegistrationDescriptor $descriptor,
        string $postType,
        string $side,
    ): void {
        if ($descriptor !== null && !in_array($postType, $descriptor->postTypes, true)) {
            throw new RuntimeException('WPEssential ' . $side . ' Status is not applicable to the server-read post type.');
        }
    }

    private function assertStatusKey(string $status, string $label): void
    {
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $status)) {
            throw new RuntimeException('Status transition ' . $label . ' status key is invalid.');
        }
    }

    private function assertNotReservedLifecycle(string $status): void
    {
        if (in_array($status, self::CORE_LIFECYCLE_RESERVED, true)) {
            throw new RuntimeException('Core lifecycle Status must use its documented owner path.');
        }
    }
}
