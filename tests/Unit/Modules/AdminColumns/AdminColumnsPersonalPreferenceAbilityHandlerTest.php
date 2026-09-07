<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\AdminColumns\AdminColumnsPersonalPreferenceAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsPersonalPreferenceResolver;
use WPEssential\Modules\AdminColumns\AdminColumnsPersonalPreferenceStore;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class AdminColumnsPersonalPreferenceAbilityHandlerTest extends TestCase
{
    public function testFixedSaveLoadAndResetHandlersShareCurrentUserState(): void
    {
        [$load, $save, $reset, $view] = $this->handlers();
        $context = new ExecutionContext(new Principal(17), 1);

        $saved = $save->handle([
            'view_id' => $view->id,
            'expected_view_revision' => 1,
            'preference' => [
                'chosen_view_id' => $view->id,
                'hidden_columns' => ['status'],
                'density' => 'compact',
            ],
        ], $context);

        self::assertSame(17, $saved['user_id']);
        self::assertTrue($saved['applied']);
        self::assertSame(['status'], $saved['state']['hidden_columns']);
        self::assertSame('compact', $saved['state']['density']);

        $loaded = $load->handle([
            'view_id' => $view->id,
            'expected_view_revision' => 1,
        ], $context);
        self::assertTrue($loaded['applied']);
        self::assertSame(['status'], $loaded['state']['hidden_columns']);

        $resetState = $reset->handle([
            'view_id' => $view->id,
            'expected_view_revision' => 1,
        ], $context);
        self::assertFalse($resetState['applied']);
        self::assertSame('not_found', $resetState['reason']);
        self::assertSame([], $resetState['state']['hidden_columns']);
    }

    public function testActionAndUserIdentityCannotBeSelectedByCaller(): void
    {
        [$load, , , $view] = $this->handlers();
        $context = new ExecutionContext(new Principal(17), 1);

        foreach ([
            ['action' => 'save'],
            ['user_id' => 99],
            ['preference' => []],
        ] as $extra) {
            try {
                $load->handle(array_merge([
                    'view_id' => $view->id,
                    'expected_view_revision' => 1,
                ], $extra), $context);
                self::fail('Expected fixed load handler to reject caller-selected operation/user/payload.');
            } catch (InvalidArgumentException $error) {
                self::assertStringContainsString('unsupported key', $error->getMessage());
            }
        }
    }

    public function testRejectsUnauthenticatedPrincipalAndStaleViewRevision(): void
    {
        [$load, , , $view] = $this->handlers();

        try {
            $load->handle([
                'view_id' => $view->id,
                'expected_view_revision' => 1,
            ], new ExecutionContext(new Principal(null), 1));
            self::fail('Expected unauthenticated principal to fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('authenticated user principal', $error->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View revision changed');
        $load->handle([
            'view_id' => $view->id,
            'expected_view_revision' => 2,
        ], new ExecutionContext(new Principal(17), 1));
    }

    public function testRejectsUnsupportedFixedHandlerAction(): void
    {
        [$views, $store] = $this->fixtureServices();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('handler action is unsupported');
        new AdminColumnsPersonalPreferenceAbilityHandler($views, $store, 'delete');
    }

    /**
     * @return array{
     *   AdminColumnsPersonalPreferenceAbilityHandler,
     *   AdminColumnsPersonalPreferenceAbilityHandler,
     *   AdminColumnsPersonalPreferenceAbilityHandler,
     *   Definition
     * }
     */
    private function handlers(): array
    {
        [$views, $store, $view] = $this->fixtureServices(withView: true);

        return [
            new AdminColumnsPersonalPreferenceAbilityHandler(
                $views,
                $store,
                AdminColumnsPersonalPreferenceAbilityHandler::LOAD,
            ),
            new AdminColumnsPersonalPreferenceAbilityHandler(
                $views,
                $store,
                AdminColumnsPersonalPreferenceAbilityHandler::SAVE,
            ),
            new AdminColumnsPersonalPreferenceAbilityHandler(
                $views,
                $store,
                AdminColumnsPersonalPreferenceAbilityHandler::RESET,
            ),
            $view,
        ];
    }

    /**
     * @return array{AdminColumnsViewDefinitionService,AdminColumnsPersonalPreferenceStore,Definition}|array{AdminColumnsViewDefinitionService,AdminColumnsPersonalPreferenceStore}
     */
    private function fixtureServices(bool $withView = false): array
    {
        $repository = new class implements DefinitionRepositoryInterface {
            /** @var array<string,Definition> */
            private array $definitions = [];

            public function save(Definition $definition): void
            {
                $this->definitions[$definition->id] = $definition;
            }

            public function get(string $id): ?Definition
            {
                return $this->definitions[$id] ?? null;
            }

            public function byType(string $type): array
            {
                return array_values(array_filter(
                    $this->definitions,
                    static fn (Definition $definition): bool => $definition->type === $type,
                ));
            }

            public function dependentsOf(string $id): array
            {
                return [];
            }
        };

        $views = new AdminColumnsViewDefinitionService(
            $repository,
            new AdminColumnsViewDefinitionNormalizer(),
            static fn (): string => '01990f6e-1f30-4000-8000-000000000371',
        );

        $meta = [];
        $store = new AdminColumnsPersonalPreferenceStore(
            new AdminColumnsPersonalPreferenceResolver(),
            reader: static function (int $userId, string $key) use (&$meta): mixed {
                return $meta[$userId][$key] ?? '';
            },
            writer: static function (int $userId, string $key, array $value) use (&$meta): void {
                $meta[$userId][$key] = $value;
            },
            deleter: static function (int $userId, string $key) use (&$meta): void {
                unset($meta[$userId][$key]);
            },
        );

        if (!$withView) {
            return [$views, $store];
        }

        $view = $views->save([
            'view_key' => 'personal_test',
            'name' => 'Personal test',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000372',
                    'key' => 'title',
                    'label' => 'Title',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                ],
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000373',
                    'key' => 'status',
                    'label' => 'Status',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.status'],
                ],
            ],
        ], DefinitionStatus::Published);

        return [$views, $store, $view];
    }
}
