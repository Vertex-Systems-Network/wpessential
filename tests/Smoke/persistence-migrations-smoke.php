<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Contracts\MigrationInterface;
use WPEssential\Platform\Database\Migrations\InMemoryMigrationStateStore;
use WPEssential\Platform\Database\Migrations\MigrationRegistry;
use WPEssential\Platform\Database\Migrations\MigrationRunner;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionTableGateway;
use WPEssential\Platform\Definitions\PersistentDefinitionRepository;

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$gateway = new InMemoryDefinitionTableGateway();
$repository = new PersistentDefinitionRepository($gateway);

$parentId = '11111111-1111-4111-8111-111111111111';
$childId = '22222222-2222-4222-8222-222222222222';

$parent = new Definition(
    id: $parentId,
    slug: 'parent',
    type: 'query',
    schemaVersion: 1,
    ownerSurfaceId: 6,
    status: DefinitionStatus::Draft,
    payload: ['b' => 2, 'a' => 1],
);
$repository->save($parent);
$loadedParent = $repository->get($parentId);
expect($loadedParent !== null, 'persisted definition must round trip');
expect($loadedParent->checksum === $parent->computedChecksum(), 'persistence must materialize canonical checksum');

$child = new Definition(
    id: $childId,
    slug: 'child',
    type: 'listing',
    schemaVersion: 1,
    ownerSurfaceId: 9,
    status: DefinitionStatus::Published,
    payload: ['source' => $parentId],
    dependencies: [$parentId],
);
$repository->save($child);
expect(count($repository->dependentsOf($parentId)) === 1, 'dependency projection must preserve dependents');
expect(count($repository->byType('listing')) === 1, 'type lookup must use persistence gateway');

$parentRevision2 = new Definition(
    id: $parentId,
    slug: 'parent',
    type: 'query',
    schemaVersion: 1,
    ownerSurfaceId: 6,
    status: DefinitionStatus::Published,
    payload: ['a' => 1, 'b' => 3],
    revision: 2,
);
$repository->save($parentRevision2);
expect($repository->get($parentId)?->revision === 2, 'persisted revision must advance');

try {
    $repository->save($parent);
    expect(false, 'stale persisted revision must fail');
} catch (RuntimeException) {
}

final class SmokeMigration implements MigrationInterface
{
    public function __construct(
        private string $migrationId,
        private int $migrationSequence,
        private array &$log,
        private bool $destructive = false,
        private ?string $recovery = null,
        private bool $fail = false,
    ) {
    }

    public function id(): string { return $this->migrationId; }
    public function sequence(): int { return $this->migrationSequence; }
    public function isDestructive(): bool { return $this->destructive; }
    public function recoveryPlan(): ?string { return $this->recovery; }

    public function apply(): void
    {
        $this->log[] = $this->migrationId;
        if ($this->fail) {
            throw new RuntimeException('simulated migration failure');
        }
    }
}

$log = [];
$registry = new MigrationRegistry();
$registry->register(new SmokeMigration('001.create-definitions', 10, $log));
$registry->register(new SmokeMigration('002.add-definition-indexes', 20, $log));
$state = new InMemoryMigrationStateStore();
$runner = new MigrationRunner($registry, $state);
expect($runner->runPending() === ['001.create-definitions', '002.add-definition-indexes'], 'migrations must run in sequence order');
expect($log === ['001.create-definitions', '002.add-definition-indexes'], 'migration execution order must be deterministic');
expect($runner->runPending() === [], 'applied migrations must not rerun');

$destructiveLog = [];
$destructiveRegistry = new MigrationRegistry();
$destructiveRegistry->register(new SmokeMigration('003.destructive-without-recovery', 30, $destructiveLog, true));
try {
    (new MigrationRunner($destructiveRegistry, new InMemoryMigrationStateStore()))->runPending();
    expect(false, 'destructive migration without recovery must fail closed');
} catch (RuntimeException) {
}
expect($destructiveLog === [], 'unsafe destructive migration must not execute');

$failureLog = [];
$failureRegistry = new MigrationRegistry();
$failureRegistry->register(new SmokeMigration('004.failing-migration', 40, $failureLog, false, null, true));
$failureState = new InMemoryMigrationStateStore();
try {
    (new MigrationRunner($failureRegistry, $failureState))->runPending();
    expect(false, 'failed migration must surface failure');
} catch (RuntimeException) {
}
expect($failureState->appliedIds() === [], 'failed migration must not be marked applied');

fwrite(STDOUT, "WPEssential persistence/migrations smoke PASS\n");
