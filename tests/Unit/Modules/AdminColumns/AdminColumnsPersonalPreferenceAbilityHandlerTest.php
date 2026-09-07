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
    public function testSaveLoadAndResetAreBoundToAuthenticatedPrincipalAndAuthoritativeView(): void
    {
        [$handler, $view] = $this->handler();
        $context = new ExecutionContext(new Principal(17), 1);

        $saved = $handler->handle([
            'action' => 'save',
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

        $loaded = $handler->handle([
            'action' => 'load',
            'view_id' => $view->id,
            'expected_view_revision' => 1,
        ], $context);
        self::assertTrue($loaded['applied']);
        self::assertSame(['status'], $loaded['state']['hidden_columns']);

        $reset = $handler->handle([
            'action' => 'reset',
            'view_id' => $view->id,
            'expected_view_revision' => 1,
        ], $context);
        self::assertFalse($reset['applied']);
        self::assertSame('not_found', $reset['reason']);
        self::assertSame([], $reset['state']['hidden_columns']);
    }

    public function testRejectsUnauthenticatedOrNonUserPrincipalAndCallerUserId(): void
    {
        [$handler, $view] = $this->handler();

        try {
            $handler->handle([
                'action' => 'load',
                'view_id' => $view->id,
                'expected_view_revision' => 1,
            ], new ExecutionContext(new Principal(null), 1));
            self::fail('Expected unauthenticated principal to fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('authenticated user principal', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported key');
        $handler->handle([
            'action' => 'load',
            'view_id' => $view->id,
            'expected_view_revision' => 1,
            'user_id' => 99,
        ], new ExecutionContext(new Principal(17), 1));
    }

    public function testRejectsStaleViewRevisionAndUnavailableColumns(): void
    {
        [$handler, $view] = $this->handler();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('View revision changed');
        $handler->handle([
            'action' => 'load',
            'view_id' => $view->id,
            'expected_view_revision' => 2,
        ], new ExecutionContext(new Principal(17), 1));
    }

    /** @return array{AdminColumnsPersonalPreferenceAbilityHandler,Definition} */
    private function handler(): array
    {
        $definitions = [];
        $repository = new class($definitions) implements DefinitionRepositoryInterface {
            /** @var array<string,Definition> */
            private array $definitions = [];

            /** @param array<string,Definition> $definitions */
            public function __construct(array $definitions)
            {
                $this->definitions = $definitions;
            }

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

        return [new AdminColumnsPersonalPreferenceAbilityHandler($views, $store), $view];
    }
}
