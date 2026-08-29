<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) require $path;
});

use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityEnvironmentInterface;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityExposure;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

function wpAbilityExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

final class FakeWordPressAbilityEnvironment implements WordPressAbilityEnvironmentInterface
{
    public bool $available = true;
    public string $action = '';
    public ?int $userId = 7;
    public int $siteId = 1;
    public ?int $networkId = 1;
    public bool $rest = false;
    public bool $cli = false;
    /** @var array<string, bool> */
    public array $capabilities = ['wpe_manage_fields' => true];
    /** @var array<string, array<string, mixed>> */
    public array $categories = [];
    /** @var array<string, array<string, mixed>> */
    public array $abilities = [];

    public function abilitiesApiAvailable(): bool { return $this->available; }
    public function doingAction(string $hook): bool { return $this->action === $hook; }
    public function currentUserId(): ?int { return $this->userId; }
    public function currentSiteId(): int { return $this->siteId; }
    public function currentNetworkId(): ?int { return $this->networkId; }
    public function currentUserCan(string $capability): bool { return $this->capabilities[$capability] ?? false; }
    public function isRestRequest(): bool { return $this->rest; }
    public function isCli(): bool { return $this->cli; }
    public function registerCategory(string $slug, array $args): bool { $this->categories[$slug] = $args; return true; }
    public function registerAbility(string $name, array $args): bool { $this->abilities[$name] = $args; return true; }
}

$environment = new FakeWordPressAbilityEnvironment();
$checker = new WordPressCapabilityChecker($environment);
$policy = new PolicyEngine($checker);
$registry = new AbilityRegistry($policy);

$handler = new class implements AbilityHandlerInterface {
    public function handle(array $input, ExecutionContext $context): mixed
    {
        return ['value' => $input['value'] ?? null, 'channel' => $context->channel->value, 'user' => $context->principal->userId];
    }
};

$descriptor = new AbilityDescriptor(
    name: 'wpessential/fields/update',
    ownerSurfaceId: 3,
    capability: 'wpe_manage_fields',
    mutates: true,
    channels: [ExecutionChannel::Internal, ExecutionChannel::Rest],
    inputSchema: ['type' => 'object', 'properties' => ['value' => ['type' => 'string']]],
    outputSchema: ['type' => 'object'],
);
$registry->register($descriptor, $handler);

$contexts = new WordPressExecutionContextFactory($environment);
$bridge = new WordPressAbilityBridge($registry, $environment, $contexts);
$bridge->expose(new WordPressAbilityExposure(
    'wpessential/fields/update',
    'Update fields',
    'Updates a field resource through WPEssential policy and canonical owner.',
    showInRest: true,
));

$environment->action = 'wp_abilities_api_categories_init';
wpAbilityExpect($bridge->registerCategory(), 'bridge must register WPE category on correct hook');
wpAbilityExpect(isset($environment->categories['wpessential']), 'WPE category must exist');

$environment->action = 'wp_abilities_api_init';
$registered = $bridge->registerAbilities();
wpAbilityExpect($registered === ['wpessential/fields-update'], 'internal three-segment name must map to core-compatible name');
$core = $environment->abilities['wpessential/fields-update'] ?? null;
wpAbilityExpect(is_array($core), 'core ability registration must be captured');
wpAbilityExpect(($core['meta']['show_in_rest'] ?? null) === true, 'REST exposure must be explicit');
wpAbilityExpect(($core['permission_callback'])(['value' => 'x']) === true, 'permission callback must authorize current principal');

$environment->rest = true;
$result = ($core['execute_callback'])(['value' => 'x']);
wpAbilityExpect($result['channel'] === 'rest' && $result['user'] === 7, 'REST execution must re-enter internal registry with REST context');

$environment->userId = 8;
$forgedContext = new ExecutionContext(new Principal(7), 1, ExecutionChannel::Rest);
wpAbilityExpect($checker->can($forgedContext, 'wpe_manage_fields') === false, 'context principal cannot impersonate a different current WordPress user');
$environment->capabilities['wpe_manage_fields'] = false;
wpAbilityExpect(($core['permission_callback'])(['value' => 'x']) === false, 'current WordPress capability denial must deny core ability execution');
$environment->userId = 7;
$environment->capabilities['wpe_manage_fields'] = true;

$internalOnly = new AbilityDescriptor(
    name: 'wpessential/fields/internal',
    ownerSurfaceId: 3,
    capability: 'wpe_manage_fields',
    mutates: false,
    channels: [ExecutionChannel::Internal],
    outputSchema: ['type' => 'object'],
);
$registry->register($internalOnly, $handler);
try {
    $bridge->expose(new WordPressAbilityExposure('wpessential/fields/internal', 'Internal', 'Internal only.', true));
    wpAbilityExpect(false, 'REST exposure must require descriptor REST allowlist');
} catch (RuntimeException) {
}

$collisionA = new AbilityDescriptor('wpessential/a-b/c', 3, 'wpe_manage_fields', false, [ExecutionChannel::Internal], outputSchema: ['type' => 'object']);
$collisionB = new AbilityDescriptor('wpessential/a/b-c', 3, 'wpe_manage_fields', false, [ExecutionChannel::Internal], outputSchema: ['type' => 'object']);
$registry2 = new AbilityRegistry($policy);
$registry2->register($collisionA, $handler);
$registry2->register($collisionB, $handler);
$bridge2 = new WordPressAbilityBridge($registry2, $environment, $contexts);
$bridge2->expose(new WordPressAbilityExposure('wpessential/a-b/c', 'A', 'A.'));
try {
    $bridge2->expose(new WordPressAbilityExposure('wpessential/a/b-c', 'B', 'B.'));
    wpAbilityExpect(false, 'core-name collision must fail closed');
} catch (RuntimeException) {
}

$environment->action = 'init';
try {
    $bridge->registerAbilities();
    wpAbilityExpect(false, 'ability registration outside wp_abilities_api_init must fail');
} catch (RuntimeException) {
}

fwrite(STDOUT, "WPEssential WordPress abilities bridge smoke PASS\n");
