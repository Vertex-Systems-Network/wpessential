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
use WPEssential\Modules\Status\Admin\StatusAdminFallbackRenderer;
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
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
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
            self::assertFalse($services->has(StatusModule::SERVICE_ADMIN_FALLBACK));
        }
    }

    public function testModulePublishesResolverBackedExecutorTransitionAndAdminAbilities(): void
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
        foreach (['list', 'get', 'save', 'status'] as $action) {
            $admin = $abilities->descriptor('wpessential/status/admin/' . $action);
            self::assertNotNull($admin);
            self::assertSame(5, $admin->ownerSurfaceId);
            self::assertSame('manage_options', $admin->capability);
            self::assertSame(in_array($action, ['save', 'status'], true), $admin->mutates);
        }
        self::assertInstanceOf(StatusAdminFallbackRenderer::class, $services->get(StatusModule::SERVICE_ADMIN_FALLBACK));

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
        $environment = new class implements WordPressAbilityEnvironmentInterface {
            public function abilitiesApiAvailable(): bool { return false; }
            public function doingAction(string $hook): bool { return false; }
            public function currentUserId(): ?int { return 1; }
            public function currentSiteId(): int { return 1; }
            public function currentNetworkId(): ?int { return 1; }
            public function currentUserCan(string $capability): bool { return true; }
            public function isRestRequest(): bool { return false; }
            public function isCli(): bool { return false; }
            public function registerCategory(string $slug, array $args): bool { return true; }
            public function registerAbility(string $name, array $args): bool { return true; }
        };
        $contexts = new WordPressExecutionContextFactory($environment);
        $bridge = new WordPressAbilityBridge($abilities, $environment, $contexts);

        $services->set('platform.definitions', $definitions);
        $services->set('platform.abilities', $abilities);
        $services->set('platform.abilities.wordpress', $bridge);
        $services->set('platform.abilities.contexts', $contexts);
        $services->set('platform.ajax.routes', new AjaxRouteRegistry());
        $services->set(WordPressAuthorizationServices::CAPABILITY_CHECKER, $capabilities);
        $services->set(WordPressAuthorizationServices::POST_RESOURCES, $resources);

        return [$services, $definitions, $abilities];
    }
}
