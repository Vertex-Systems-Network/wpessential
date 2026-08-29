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

use WPEssential\Platform\Assets\AssetDescriptor;
use WPEssential\Platform\Assets\AssetLoadStrategy;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Assets\AssetScope;
use WPEssential\Platform\Integrations\IntegrationDescriptor;
use WPEssential\Platform\Integrations\IntegrationRegistry;
use WPEssential\Platform\Secrets\InMemorySecretVault;

function expect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$vault = new InMemorySecretVault();
$reference = $vault->store('provider.primary', 23, 'top-secret', ['environment' => 'test']);
expect($vault->resolve($reference)->reveal() === 'top-secret', 'vault must resolve current reference');
expect(json_encode($vault->resolve($reference), JSON_THROW_ON_ERROR) === '"[REDACTED]"', 'sensitive value must serialize redacted');

$rotated = $vault->rotate($reference, 'rotated-secret');
try {
    $vault->resolve($reference);
    expect(false, 'stale vault reference must fail after rotation');
} catch (RuntimeException) {
}
expect($vault->resolve($rotated)->reveal() === 'rotated-secret', 'rotated reference must resolve');

try {
    $vault->store('provider.invalid', 23, 'secret', ['api_key' => 'must-not-be-metadata']);
    expect(false, 'secret-bearing metadata must fail closed');
} catch (InvalidArgumentException) {
}

$vault->revoke($rotated);
try {
    $vault->resolve($rotated);
    expect(false, 'revoked secret must not resolve');
} catch (RuntimeException) {
}

$assets = new AssetRegistry();
$assets->register(new AssetDescriptor('wpe-admin-runtime', 31, AssetScope::Admin, AssetLoadStrategy::OnDemand));
$assets->register(new AssetDescriptor(
    'wpe-platform-screen',
    31,
    AssetScope::Admin,
    AssetLoadStrategy::AdminRoute,
    dependencies: ['wpe-admin-runtime'],
    adminRoutes: ['/platform'],
));
$assets->register(new AssetDescriptor('wpe-frontend-renderer', 9, AssetScope::Frontend, AssetLoadStrategy::RenderDiscovery));
$assets->validateGraph();

$routeAssets = array_map(static fn (AssetDescriptor $asset): string => $asset->handle, $assets->forAdminRoute('/platform'));
expect($routeAssets === ['wpe-admin-runtime', 'wpe-platform-screen'], 'route asset resolution must include dependencies in order');
expect($assets->forAdminRoute('/unrelated') === [], 'unrelated admin routes must not auto-load optional assets');

$badAssets = new AssetRegistry();
$badAssets->register(new AssetDescriptor('wpe-bad', 31, AssetScope::Admin, dependencies: ['wpe-missing']));
try {
    $badAssets->validateGraph();
    expect(false, 'missing asset dependency must fail');
} catch (RuntimeException) {
}

$integrations = new IntegrationRegistry();
$integration = new IntegrationDescriptor(
    key: 'provider.example',
    provider: 'example',
    ownerSurfaceId: 23,
    capabilities: ['send', 'verify'],
    credentialReferenceId: $reference->id,
);
$integrations->register($integration);
expect($integrations->get('provider.example')->transportSurfaceId === 23, 'external integration transport must resolve to Surface 23');
expect(count($integrations->supporting('verify')) === 1, 'integration capability lookup must be explicit');
expect($integration->externalAuthority && $integration->unknownOutcomeFirstClass, 'provider authority and unknown outcomes must remain first-class');

try {
    new IntegrationDescriptor('provider.invalid', 'example', 23, ['send'], transportSurfaceId: 22);
    expect(false, 'integration cannot bypass Surface 23 transport owner');
} catch (InvalidArgumentException) {
}

fwrite(STDOUT, "WPEssential vault/assets/integrations smoke PASS\n");
