<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential WordPress AJAX/nonce/Policy integration SKIP (WordPress fixture unavailable)\n");
    exit(0);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
}
if (!defined('DOING_AJAX')) {
    define('DOING_AJAX', true);
}
if (!defined('WPE_AJAX_ACTION')) {
    define('WPE_AJAX_ACTION', 'wpessential_dispatch');
}
if (!defined('WPE_NONCE_ACTION')) {
    define('WPE_NONCE_ACTION', 'wpessential_request');
}

$dbName = getenv('WPE_TEST_WP_DB') ?: 'wpessential_test';
$dbUser = getenv('WPE_TEST_MYSQL_USER') ?: 'root';
$dbPassword = getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root';
$dbHost = getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306';
$config = <<<'PHP'
<?php
define('DB_NAME', getenv('WPE_TEST_WP_DB') ?: 'wpessential_test');
define('DB_USER', getenv('WPE_TEST_MYSQL_USER') ?: 'root');
define('DB_PASSWORD', getenv('WPE_TEST_MYSQL_PASSWORD') ?: 'root');
define('DB_HOST', getenv('WPE_TEST_WP_DB_HOST') ?: '127.0.0.1:3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY',         'wpessential-test-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-test-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-test-logged-in-key');
define('NONCE_KEY',        'wpessential-test-nonce-key');
define('AUTH_SALT',        'wpessential-test-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-test-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-test-logged-in-salt');
define('NONCE_SALT',       'wpessential-test-nonce-salt');
$table_prefix = 'wpcore_';
define('WP_DEBUG', false);
PHP;
if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
    fwrite(STDERR, "FAIL: unable to create WordPress test configuration\n");
    exit(1);
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

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

use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\WordPress\Abilities\NativeWordPressAbilityEnvironment;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AbilityAjaxHandler;
use WPEssential\Platform\WordPress\Ajax\AjaxDispatcher;
use WPEssential\Platform\WordPress\Ajax\AjaxRoute;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;
use WPEssential\Platform\WordPress\Ajax\NativeWordPressAjaxEnvironment;
use WPEssential\Platform\WordPress\Ajax\WordPressAjaxGateway;
use WPEssential\Platform\WordPress\Security\NativeWordPressNonceEnvironment;
use WPEssential\Platform\WordPress\Security\NonceManager;
use WPEssential\Platform\WordPress\Security\NonceOperation;

function wpAjaxPolicyExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Integration',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    $adminId = (int) ($installed['user_id'] ?? 0);
} else {
    $admin = get_user_by('login', 'wpessential_admin');
    $adminId = $admin instanceof WP_User ? (int) $admin->ID : 0;
}
wpAjaxPolicyExpect($adminId > 0, 'WordPress fixture must provide an administrator user');
wp_set_current_user($adminId);
wpAjaxPolicyExpect(is_user_logged_in(), 'WordPress current principal must be authenticated');
wpAjaxPolicyExpect(current_user_can('manage_options'), 'WordPress administrator must have manage_options');

$abilityEnvironment = new NativeWordPressAbilityEnvironment();
$capabilities = new WordPressCapabilityChecker($abilityEnvironment);
$policy = new PolicyEngine($capabilities);
$abilities = new AbilityRegistry($policy);
$abilities->register(
    new AbilityDescriptor(
        name: 'wpessential/platform/ajax-fixture',
        ownerSurfaceId: 31,
        capability: 'manage_options',
        mutates: true,
        channels: [ExecutionChannel::Ui],
    ),
    new class implements AbilityHandlerInterface {
        public function handle(array $input, ExecutionContext $context): mixed
        {
            return [
                'executed' => true,
                'user_id' => $context->principal->userId,
                'site_id' => $context->siteId,
                'network_id' => $context->networkId,
                'channel' => $context->channel->value,
                'echo' => $input['echo'] ?? null,
            ];
        }
    },
);

$contexts = new WordPressExecutionContextFactory($abilityEnvironment);
$nonces = new NonceManager(new NativeWordPressNonceEnvironment(), WPE_NONCE_ACTION);
$routes = new AjaxRouteRegistry();
$routes->register(new AjaxRoute(
    type: 'platform.fixture',
    handler: new AbilityAjaxHandler($abilities, 'wpessential/platform/ajax-fixture', $contexts),
    operation: NonceOperation::Update,
    capability: null,
    allowGuests: false,
    requiresNonce: true,
));
$ajaxEnvironment = new NativeWordPressAjaxEnvironment();
$dispatcher = new AjaxDispatcher($routes, $nonces, static fn (string $capability): bool => current_user_can($capability));
$gateway = new WordPressAjaxGateway(WPE_AJAX_ACTION, $dispatcher, $ajaxEnvironment);
$gateway->register();

wpAjaxPolicyExpect(has_action('wp_ajax_' . WPE_AJAX_ACTION, [$gateway, 'handle']) !== false, 'canonical authenticated wp_ajax hook must be registered');
wpAjaxPolicyExpect(has_action('wp_ajax_nopriv_' . WPE_AJAX_ACTION, [$gateway, 'handle']) !== false, 'canonical nopriv wp_ajax hook must be registered');
wpAjaxPolicyExpect($routes->types() === ['platform.fixture'], 'AJAX type registry must contain only the explicit allowlisted route');

$unknown = $dispatcher->dispatch(['type' => 'platform.unknown'], true);
wpAjaxPolicyExpect(!$unknown->success && $unknown->status === 404 && $unknown->errorCode === 'unknown_request_type', 'unknown AJAX type must fail closed');
$missingNonce = $dispatcher->dispatch(['type' => 'platform.fixture', 'payload' => []], true);
wpAjaxPolicyExpect(!$missingNonce->success && $missingNonce->status === 403 && $missingNonce->errorCode === 'invalid_nonce', 'missing nonce must fail before handler execution');

$adminNonce = $nonces->create(NonceOperation::Update, 'platform.fixture');
wpAjaxPolicyExpect($nonces->verify($adminNonce, NonceOperation::Update, 'platform.fixture'), 'nonce manager must verify a real WordPress nonce for the same operation/scope');
wpAjaxPolicyExpect(!$nonces->verify($adminNonce, NonceOperation::Delete, 'platform.fixture'), 'nonce must be operation scoped');

final class WPEAjaxFixtureTermination extends RuntimeException {}
$dieHandler = static fn (): callable => static function (): void {
    throw new WPEAjaxFixtureTermination('wp_die intercepted');
};
add_filter('wp_die_ajax_handler', $dieHandler);
add_filter('wp_die_handler', $dieHandler);

/** @param array<string,mixed> $post @return array<string,mixed> */
function runWpeAjaxGateway(WordPressAjaxGateway $gateway, array $post, bool $throughHook = false): array
{
    $_POST = $post;
    ob_start();
    try {
        if ($throughHook) {
            do_action('wp_ajax_' . WPE_AJAX_ACTION);
        } else {
            $gateway->handle();
        }
    } catch (WPEAjaxFixtureTermination) {
    }
    $output = (string) ob_get_clean();
    $decoded = json_decode($output, true);
    if (!is_array($decoded)) {
        fwrite(STDERR, "FAIL: WordPress AJAX response was not JSON: {$output}\n");
        exit(1);
    }
    return $decoded;
}

$success = runWpeAjaxGateway($gateway, [
    'action' => WPE_AJAX_ACTION,
    'type' => 'platform.fixture',
    'nonce' => $adminNonce,
    'payload' => ['echo' => 'hello'],
], true);
wpAjaxPolicyExpect(($success['success'] ?? false) === true, 'real WordPress wp_ajax action must execute authorized request');
wpAjaxPolicyExpect(($success['data']['channel'] ?? null) === 'ui', 'AJAX Ability execution must be bound to UI channel');
wpAjaxPolicyExpect((int) ($success['data']['user_id'] ?? 0) === $adminId, 'Ability context must use actual WordPress current user');
wpAjaxPolicyExpect((int) ($success['data']['site_id'] ?? 0) === get_current_blog_id(), 'Ability context must use actual WordPress site id');

$subscriberId = wp_create_user('wpessential_subscriber', 'test-password-strong', 'subscriber@example.test');
wpAjaxPolicyExpect(!is_wp_error($subscriberId), 'WordPress fixture must create a low-privilege user');
$subscriber = new WP_User((int) $subscriberId);
$subscriber->set_role('subscriber');
wp_set_current_user((int) $subscriberId);
wpAjaxPolicyExpect(is_user_logged_in() && !current_user_can('manage_options'), 'low-privilege WordPress user must lack manage_options');
$subscriberNonce = $nonces->create(NonceOperation::Update, 'platform.fixture');
$denied = runWpeAjaxGateway($gateway, [
    'action' => WPE_AJAX_ACTION,
    'type' => 'platform.fixture',
    'nonce' => $subscriberNonce,
    'payload' => ['echo' => 'denied'],
]);
wpAjaxPolicyExpect(($denied['success'] ?? true) === false, 'Policy-denied AJAX request must fail');
wpAjaxPolicyExpect(($denied['error']['code'] ?? null) === 'policy_denied', 'Policy denial must remain distinct from handler failure');
wpAjaxPolicyExpect(($denied['meta']['reason'] ?? null) === 'capability_denied', 'Policy denial must expose a stable safe reason code');

wp_set_current_user(0);
$guest = $dispatcher->dispatch([
    'type' => 'platform.fixture',
    'nonce' => 'invalid',
    'payload' => [],
], false);
wpAjaxPolicyExpect(!$guest->success && $guest->status === 401 && $guest->errorCode === 'authentication_required', 'guest request must fail authentication before nonce/handler processing');

fwrite(STDOUT, "WPEssential real WordPress AJAX/nonce/Policy integration PASS\n");
