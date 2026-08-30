<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = $root . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Platform\Observability\FlowTrace;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Hooks\HookNames;
use WPEssential\Platform\WordPress\Registrations\InMemoryCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;
use WPEssential\Platform\WordPress\Security\NonceEnvironmentInterface;
use WPEssential\Platform\WordPress\Security\NonceManager;
use WPEssential\Platform\WordPress\Security\NonceOperation;

function engineeringExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

engineeringExpect(HookNames::filter('definition_saved') === 'wpesential/apply_definition_saved', 'filter prefix contract must remain exact');
engineeringExpect(HookNames::action('definition_saved') === 'wpessential/hook_definition_saved', 'action prefix contract must remain exact');

$nonceEnvironment = new class implements NonceEnvironmentInterface {
    public function create(string $action): string { return hash('sha256', $action); }
    public function verify(string $nonce, string $action): bool { return hash_equals(hash('sha256', $action), $nonce); }
};
$nonces = new NonceManager($nonceEnvironment, 'wpessential_request');
$createNonce = $nonces->create(NonceOperation::Create, 'definitions.create');
engineeringExpect($nonces->verify($createNonce, NonceOperation::Create, 'definitions.create'), 'matching nonce operation/scope must verify');
engineeringExpect(!$nonces->verify($createNonce, NonceOperation::Delete, 'definitions.create'), 'nonce cannot cross operation boundaries');

$routes = new AjaxRouteRegistry();
$handler = new class implements AjaxHandlerInterface {
    public function handle(array $payload): mixed { return ['echo' => $payload['value'] ?? null]; }
};
$routes->register(new AjaxRoute('definitions.create', $handler, NonceOperation::Create, 'wpe_manage_definitions'));
$dispatcher = new AjaxDispatcher($routes, $nonces, static fn (string $capability): bool => $capability === 'wpe_manage_definitions');
$response = $dispatcher->dispatch([
    'type' => 'definitions.create',
    'nonce' => $createNonce,
    'payload' => ['value' => 'ok'],
], true);
engineeringExpect($response->success && $response->data['echo'] === 'ok', 'allowlisted AJAX request must dispatch');
engineeringExpect($dispatcher->dispatch(['type' => 'unknown'], true)->errorCode === 'unknown_request_type', 'unknown AJAX type must fail closed');
engineeringExpect($dispatcher->dispatch(['type' => 'definitions.create', 'nonce' => 'bad'], true)->errorCode === 'invalid_nonce', 'invalid nonce must fail before handler');

$store = new InMemoryCompiledRegistrationStore();
$compiler = new RegistrationCompiler($store);
$runtime = new RegistrationRuntimeLoader($store);
$compiler->compileAndPublish([
    new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true]),
    new RegistrationDefinition('tax-1', RegistrationKind::Taxonomy, 'genre', ['object_type' => ['book']]),
]);
engineeringExpect($store->active()?->generation === 1, 'first compile must publish generation 1');
engineeringExpect(isset($runtime->forKind(RegistrationKind::PostType)['book']), 'runtime must read compiled post type manifest');
$compiler->compileAndPublish([
    new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true], false, 2),
]);
engineeringExpect($store->active()?->generation === 2, 'second compile must advance generation');
engineeringExpect($runtime->forKind(RegistrationKind::PostType) === [], 'disabled definition must disappear at compile time');

$trace = new FlowTrace('corr-engineering');
$trace->node('ajax', 'AjaxDispatcher', ['api_token' => 'must-not-leak']);
$trace->checkpoint('AjaxDispatcher', 'route_resolved');
$trace->checkpoint('NonceManager', 'nonce_verified');
$trace->fail('DefinitionHandler', 'persist', 'simulated_failure', ['Authorization' => 'Bearer secret']);
$snapshot = $trace->snapshot();
engineeringExpect(($snapshot['failure_boundary']['last_successful']['operation'] ?? null) === 'nonce_verified', 'trace must expose last successful boundary');
engineeringExpect(($snapshot['failure_boundary']['failed']['component'] ?? null) === 'DefinitionHandler', 'trace must identify failed component');
$encoded = json_encode($snapshot, JSON_THROW_ON_ERROR);
engineeringExpect(!str_contains($encoded, 'must-not-leak') && !str_contains($encoded, 'Bearer secret'), 'trace metadata must redact secrets');

$composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
engineeringExpect(($composer['autoload']['psr-4']['WPEssential\\'] ?? null) === 'frameworks/', 'Composer must map WPEssential to frameworks/');
engineeringExpect(!is_dir($root . '/src'), 'legacy src/ root must be absent');

$entrypoint = (string) file_get_contents($root . '/wpessential.php');
foreach (['WPE_VERSION', 'WPE_AJAX_ACTION', 'WPE_NONCE_ACTION'] as $constant) {
    engineeringExpect(str_contains($entrypoint, $constant), "entrypoint must define {$constant}");
}

fwrite(STDOUT, "WPEssential engineering contracts smoke PASS\n");
