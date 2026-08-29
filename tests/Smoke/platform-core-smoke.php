<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\Events\DomainEvent;
use WPEssential\Platform\Events\EventBus;

function coreExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$id = '123e4567-e89b-42d3-a456-426614174000';
$dep = '123e4567-e89b-42d3-a456-426614174001';
$payloadA = ['b' => 2, 'a' => ['z' => 1, 'y' => 2]];
$payloadB = ['a' => ['y' => 2, 'z' => 1], 'b' => 2];
$definitionA = new Definition($id, 'sample', 'field.group', 1, 3, DefinitionStatus::Draft, $payloadA);
$definitionB = new Definition($id, 'sample', 'field.group', 1, 3, DefinitionStatus::Draft, $payloadB);
coreExpect($definitionA->computedChecksum() === $definitionB->computedChecksum(), 'definition checksum must be canonical across associative key order');

$repo = new InMemoryDefinitionRepository();
$repo->save($definitionA);
$dependent = new Definition($dep, 'dependent', 'field.group', 1, 3, DefinitionStatus::Draft, [], dependencies: [$id]);
$repo->save($dependent);
coreExpect(count($repo->dependentsOf($id)) === 1, 'repository must resolve declared dependents');
try {
    $repo->save($definitionA);
    coreExpect(false, 'stale definition revision must fail');
} catch (RuntimeException) {
    // expected
}

$checker = new class implements CapabilityCheckerInterface {
    public function can(ExecutionContext $context, string $capability): bool
    {
        return $context->principal->userId === 7 && $capability === 'wpe_manage_fields';
    }
};
$policy = new PolicyEngine($checker);
$registry = new AbilityRegistry($policy);
$handler = new class implements AbilityHandlerInterface {
    public function handle(array $input, ExecutionContext $context): mixed
    {
        return ['ok' => true, 'value' => $input['value'] ?? null, 'user' => $context->principal->userId];
    }
};
$descriptor = new AbilityDescriptor(
    name: 'wpessential/fields/update',
    ownerSurfaceId: 3,
    capability: 'wpe_manage_fields',
    mutates: true,
    channels: [ExecutionChannel::Internal],
);
$registry->register($descriptor, $handler);

$allowed = new ExecutionContext(new Principal(7), 1, ExecutionChannel::Internal);
$result = $registry->execute('wpessential/fields/update', ['value' => 'x'], $allowed);
coreExpect($result['ok'] === true && $result['user'] === 7, 'authorized internal ability must execute');

try {
    $registry->execute('wpessential/fields/update', [], new ExecutionContext(new Principal(7), 1, ExecutionChannel::Ui));
    coreExpect(false, 'registration must not imply UI exposure');
} catch (RuntimeException $exception) {
    coreExpect(str_contains($exception->getMessage(), 'not exposed'), 'channel denial should be explicit');
}

try {
    $registry->execute('wpessential/fields/update', [], new ExecutionContext(new Principal(8), 1, ExecutionChannel::Internal));
    coreExpect(false, 'capability denied principal must not execute');
} catch (RuntimeException $exception) {
    coreExpect(str_contains($exception->getMessage(), 'capability_denied'), 'capability denial should be explicit');
}

try {
    $registry->execute('wpessential/fields/update', [], new ExecutionContext(new Principal(null), 1, ExecutionChannel::Internal));
    coreExpect(false, 'unauthenticated principal must not execute');
} catch (RuntimeException $exception) {
    coreExpect(str_contains($exception->getMessage(), 'unauthenticated'), 'unauthenticated denial should be explicit');
}

$events = new EventBus();
$seen = [];
$events->listen('definition.saved', static function (DomainEvent $event) use (&$seen): void {
    $seen[] = [$event->payload['id'] ?? null, $event->occurredAt->format(DATE_ATOM)];
});
$event = new DomainEvent('definition.saved', ['id' => $id], 'corr-1');
$firstTimestamp = $event->occurredAt;
$events->dispatch($event);
coreExpect(count($seen) === 1 && $seen[0][0] === $id, 'typed event bus must dispatch matching event');
coreExpect($event->occurredAt === $firstTimestamp, 'event occurrence time must be stable');

fwrite(STDOUT, "WPEssential platform core smoke PASS\n");
