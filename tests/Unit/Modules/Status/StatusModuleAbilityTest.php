<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Status\Abilities\StatusTransitionAbilityHandler;
use WPEssential\Modules\Status\Execution\StatusTransitionExecutor;
use WPEssential\Modules\Status\Registration\StatusNativeRegistrar;
use WPEssential\Modules\Status\StatusModule;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Modules\Status\Transition\StatusTransitionRule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

final class StatusModuleAbilityTest extends TestCase
{
    public function testDefaultFreeKernelDoesNotAdmitProStatusModule(): void
    {
        $kernel = new Kernel();
        $kernel->registerModule(new StatusModule());

        self::assertFalse($kernel->modules()->has('status'));
    }

    public function testModuleFailsClosedWhenCanonicalSharedServicesAreMissing(): void
    {
        $services = new ServiceRegistry();
        $module = new StatusModule();

        $this->expectException(LogicException::class);
        try {
            $module->register($services);
        } finally {
            self::assertFalse($services->has(StatusModule::SERVICE_TRANSITION_EXECUTOR));
        }
    }

    public function testModulePublishesResolverBackedExecutorAndTransitionAbility(): void
    {
        [$services, $definitions, $abilities] = $this->sharedServices();
        $hookInstalled = false;
        $module = new StatusModule(
            registrarFactory: static function ($repository) use (&$hookInstalled): StatusNativeRegistrar {
                return new StatusNativeRegistrar(
                    definitions: $repository,
                    addAction: static function (string $hook, callable $callback, int $priority) use (&$hookInstalled): void {
                        self::assertSame('init', $hook);
                        self::assertSame(20, $priority);
                        $hookInstalled = true;
                    },
                );
            },
        );

        $module->register($services);
        $module->boot($services);

        self::assertTrue($hookInstalled);
        self::assertInstanceOf(
            StatusTransitionExecutor::class,
            $services->get(StatusModule::SERVICE_TRANSITION_EXECUTOR),
        );
        $descriptor = $abilities->descriptor(StatusModule::ABILITY_TRANSITION);
        self::assertNotNull($descriptor);
        self::assertSame(5, $descriptor->ownerSurfaceId);
        self::assertTrue($descriptor->mutates);
        self::assertSame('edit_posts', $descriptor->capability);
        self::assertSame(
            [ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest],
            $descriptor->channels,
        );
        self::assertSame([], $definitions->byType('status-transition-policy'));
    }

    public function testAbilityHandlerRejectsScopeSelectorsAndDelegatesWithSameContext(): void
    {
        $definitions = new InMemoryDefinitionRepository();
        $policy = new StatusTransitionPolicy([
            new StatusTransitionRule('draft', 'pending'),
        ]);
        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return $context->principal->userId === 7 && $context->siteId === 3 && $capability === 'edit_posts';
            }
        };
        $resources = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 7,
            currentSiteId: static fn (): int => 3,
            currentNetworkId: static fn (): ?int => 2,
            currentUserCan: static fn (string $capability, int $postId): bool => $capability === 'edit_post' && $postId === 42,
        );
        $status = 'draft';
        $executor = new StatusTransitionExecutor(
            definitions: $definitions,
            policy: $policy,
            resources: $resources,
            capabilities: $capabilities,
            readPostStatus: static function (int $postId) use (&$status): string|false {
                return $postId === 42 ? $status : false;
            },
            readPostType: static fn (int $postId): string|false => $postId === 42 ? 'post' : false,
            statusRegistered: static fn (string $key): bool => in_array($key, ['draft', 'pending'], true),
            updatePostStatus: static function (int $postId, string $target) use (&$status): int {
                $status = $target;
                return $postId;
            },
        );
        $handler = new StatusTransitionAbilityHandler($executor);
        $context = new ExecutionContext(new Principal(7), siteId: 3, networkId: 2);

        try {
            $handler->handle([
                'post_id' => 42,
                'expected_current_status' => 'draft',
                'target_status' => 'pending',
                'site_id' => 99,
            ], $context);
            self::fail('Public scope selectors must be rejected.');
        } catch (InvalidArgumentException) {
            self::assertSame('draft', $status);
        }

        $result = $handler->handle([
            'post_id' => 42,
            'expected_current_status' => 'draft',
            'target_status' => 'pending',
        ], $context);

        self::assertSame('pending', $status);
        self::assertSame(42, $result['post_id']);
        self::assertSame('draft', $result['from_status']);
        self::assertSame('pending', $result['to_status']);
        self::assertSame('post', $result['post_type']);
        self::assertFalse($result['programmatic']);
    }

    /** @return array{ServiceRegistry,InMemoryDefinitionRepository,AbilityRegistry} */
    private function sharedServices(): array
    {
        $services = new ServiceRegistry();
        $definitions = new InMemoryDefinitionRepository();
        $capabilities = new class implements CapabilityCheckerInterface {
            public function can(ExecutionContext $context, string $capability): bool
            {
                return true;
            }
        };
        $abilities = new AbilityRegistry(new PolicyEngine($capabilities));
        $resources = new WordPressPostResourceAuthorizer(
            currentUserId: static fn (): ?int => 1,
            currentSiteId: static fn (): int => 1,
            currentNetworkId: static fn (): ?int => 1,
            currentUserCan: static fn (string $capability, int $postId): bool => true,
        );

        $services->set('platform.definitions', $definitions);
        $services->set('platform.abilities', $abilities);
        $services->set(WordPressAuthorizationServices::CAPABILITY_CHECKER, $capabilities);
        $services->set(WordPressAuthorizationServices::POST_RESOURCES, $resources);

        return [$services, $definitions, $abilities];
    }
}
