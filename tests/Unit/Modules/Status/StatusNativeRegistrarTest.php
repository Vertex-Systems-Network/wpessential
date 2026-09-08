<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use LogicException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Status\Registration\StatusNativeRegistrar;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class StatusNativeRegistrarTest extends TestCase
{
    public function testRegistersInitHookAtPriorityTwenty(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $capturedHook = null;
        $capturedPriority = null;
        $capturedCallback = null;

        $registrar = new StatusNativeRegistrar(
            definitions: $repository,
            statusExists: static fn (string $key): bool => false,
            registerStatus: static fn (string $key, array $arguments): object => (object) ['name' => $key],
            labelCountFactory: static fn (string $singular, string $plural): array => [$singular, $plural],
            addAction: static function (string $hook, callable $callback, int $priority) use (&$capturedHook, &$capturedCallback, &$capturedPriority): void {
                $capturedHook = $hook;
                $capturedCallback = $callback;
                $capturedPriority = $priority;
            },
        );

        $registrar->register();

        self::assertSame('init', $capturedHook);
        self::assertSame(20, $capturedPriority);
        self::assertIsCallable($capturedCallback);
        self::assertSame([], $registrar->errors());
    }

    public function testRegistersPublishedDefinitionsWithExactNativeProjectionOnly(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'review-ready',
            DefinitionStatus::Published,
            visibility: ['public' => true],
        ));
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'draft-only',
            DefinitionStatus::Draft,
        ));

        $calls = [];
        $registrar = new StatusNativeRegistrar(
            definitions: $repository,
            statusExists: static fn (string $key): bool => false,
            registerStatus: static function (string $key, array $arguments) use (&$calls): object {
                $calls[$key] = $arguments;
                return (object) ['name' => $key];
            },
            labelCountFactory: static fn (string $singular, string $plural): array => [
                'singular' => $singular,
                'plural' => $plural,
            ],
            addAction: static function (string $hook, callable $callback, int $priority): void {},
        );

        $registrar->registerActive();

        self::assertSame(['review-ready'], $registrar->registered());
        self::assertSame([], $registrar->conflicts());
        self::assertSame([], $registrar->errors());
        self::assertSame(['review-ready'], array_keys($calls));
        self::assertSame([
            'label' => 'Review Ready',
            'label_count' => ['singular' => 'Review Ready', 'plural' => 'Review Ready Items'],
            'public' => true,
            'internal' => false,
            'protected' => false,
            'private' => false,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'date_floating' => false,
        ], $calls['review-ready']);
        self::assertArrayNotHasKey('post_types', $calls['review-ready']);
        self::assertArrayNotHasKey('_builtin', $calls['review-ready']);
    }

    public function testDuplicatePublishedKeysFailBeforeAnyRegistrationMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'review-ready',
            DefinitionStatus::Published,
        ));
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'review-ready',
            DefinitionStatus::Published,
        ));

        $mutations = 0;
        $registrar = new StatusNativeRegistrar(
            definitions: $repository,
            statusExists: static fn (string $key): bool => false,
            registerStatus: static function (string $key, array $arguments) use (&$mutations): object {
                ++$mutations;
                return (object) ['name' => $key];
            },
            labelCountFactory: static fn (string $singular, string $plural): array => [$singular, $plural],
            addAction: static function (string $hook, callable $callback, int $priority): void {},
        );

        $registrar->registerActive();

        self::assertSame(0, $mutations);
        self::assertSame([], $registrar->registered());
        self::assertArrayHasKey('review-ready', $registrar->errors());
    }

    public function testExistingStatusCollisionFailsWholeBatchBeforeMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'review-ready',
            DefinitionStatus::Published,
        ));
        $repository->save($this->definition(
            '22222222-2222-4222-8222-222222222222',
            'qa-ready',
            DefinitionStatus::Published,
        ));

        $mutations = 0;
        $registrar = new StatusNativeRegistrar(
            definitions: $repository,
            statusExists: static fn (string $key): bool => $key === 'qa-ready',
            registerStatus: static function (string $key, array $arguments) use (&$mutations): object {
                ++$mutations;
                return (object) ['name' => $key];
            },
            labelCountFactory: static fn (string $singular, string $plural): array => [$singular, $plural],
            addAction: static function (string $hook, callable $callback, int $priority): void {},
        );

        $registrar->registerActive();

        self::assertSame(0, $mutations);
        self::assertSame([], $registrar->registered());
        self::assertSame(['qa-ready'], $registrar->conflicts());
    }

    public function testRegistrarProcessesAtMostOncePerRequest(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'review-ready',
            DefinitionStatus::Published,
        ));

        $mutations = 0;
        $registrar = new StatusNativeRegistrar(
            definitions: $repository,
            statusExists: static fn (string $key): bool => false,
            registerStatus: static function (string $key, array $arguments) use (&$mutations): object {
                ++$mutations;
                return (object) ['name' => $key];
            },
            labelCountFactory: static fn (string $singular, string $plural): array => [$singular, $plural],
            addAction: static function (string $hook, callable $callback, int $priority): void {},
        );

        $registrar->registerActive();
        $registrar->registerActive();

        self::assertSame(1, $mutations);
        self::assertSame(['review-ready'], $registrar->registered());
    }

    public function testUnavailableRuntimeApiFailsClosedBeforeMutation(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $repository->save($this->definition(
            '11111111-1111-4111-8111-111111111111',
            'review-ready',
            DefinitionStatus::Published,
        ));

        $mutations = 0;
        $registrar = new StatusNativeRegistrar(
            definitions: $repository,
            statusExists: static function (string $key): bool {
                throw new LogicException('unavailable');
            },
            registerStatus: static function (string $key, array $arguments) use (&$mutations): object {
                ++$mutations;
                return (object) ['name' => $key];
            },
            labelCountFactory: static fn (string $singular, string $plural): array => [$singular, $plural],
            addAction: static function (string $hook, callable $callback, int $priority): void {},
        );

        $registrar->registerActive();

        self::assertSame(0, $mutations);
        self::assertSame([], $registrar->registered());
        self::assertArrayHasKey('runtime', $registrar->errors());
    }

    /** @param array<string,bool> $visibility */
    private function definition(
        string $id,
        string $key,
        DefinitionStatus $status,
        array $visibility = [],
    ): Definition {
        $payload = [
            'key' => $key,
            'labels' => [
                'label' => ucwords(str_replace('-', ' ', $key)),
                'count_singular' => ucwords(str_replace('-', ' ', $key)),
                'count_plural' => ucwords(str_replace('-', ' ', $key)) . ' Items',
            ],
            'post_types' => ['post'],
        ];
        if ($visibility !== []) {
            $payload['visibility'] = $visibility;
        }

        return new Definition(
            id: $id,
            slug: $key,
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: $status,
            payload: $payload,
        );
    }
}
