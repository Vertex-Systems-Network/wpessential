<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields core Ability integration SKIP (WordPress fixture unavailable)\n");
    exit(0);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
}

if (!is_file($wpDir . '/wp-config.php')) {
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
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress test configuration\n");
        exit(1);
    }
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

use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldsModule;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;
use WPEssential\Platform\WordPress\Abilities\NativeWordPressAbilityEnvironment;
use WPEssential\Platform\WordPress\Abilities\WordPressAbilityBridge;
use WPEssential\Platform\WordPress\Abilities\WordPressCapabilityChecker;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;
use WPEssential\Platform\WordPress\Ajax\AjaxRouteRegistry;

function fieldsCoreAbilityExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @template T @param callable():T $callback @return T */
function fieldsCoreAbilityWithinHook(string $hook, callable $callback): mixed
{
    global $wp_current_filter;
    if (!is_array($wp_current_filter)) {
        $wp_current_filter = [];
    }
    $wp_current_filter[] = $hook;
    try {
        return $callback();
    } finally {
        array_pop($wp_current_filter);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Fields Core Ability Integration',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldsCoreAbilityExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_admin', 'test-password-strong', 'admin@example.test');
    fieldsCoreAbilityExpect(is_int($userId) && $userId > 0, 'integration administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldsCoreAbilityExpect($admin instanceof WP_User, 'integration administrator must exist');
$admin->set_role('administrator');

$subscriber = get_user_by('login', 'wpessential_reader');
if (!$subscriber instanceof WP_User) {
    $userId = wp_create_user('wpessential_reader', 'test-password-strong', 'reader@example.test');
    fieldsCoreAbilityExpect(is_int($userId) && $userId > 0, 'integration reader must be created');
    $subscriber = get_user_by('id', $userId);
}
fieldsCoreAbilityExpect($subscriber instanceof WP_User, 'integration reader must exist');
$subscriber->set_role('subscriber');

fieldsCoreAbilityExpect(class_exists('WP_Ability'), 'WordPress 6.9+ Ability class must be available');
fieldsCoreAbilityExpect(function_exists('wp_register_ability'), 'WordPress Ability registration function must be available');
fieldsCoreAbilityExpect(function_exists('wp_get_ability'), 'WordPress Ability lookup function must be available');

register_post_type('wpe_core_book', [
    'public' => true,
    'show_ui' => false,
    'show_in_rest' => true,
    'supports' => ['title', 'custom-fields'],
]);

$groupId = '71111111-1111-4111-8111-111111111111';
$fieldUuid = '72222222-2222-4222-8222-222222222222';
$groupNormalizer = new FieldGroupDefinitionNormalizer();
$payload = $groupNormalizer->normalize([
    'group_key' => 'core_ability_book_meta',
    'title' => 'Core Ability Book Meta',
    'fields' => [[
        'uuid' => $fieldUuid,
        'key' => 'wpe_core_headline',
        'label' => 'Headline',
        'type' => 'text',
    ]],
    'locations' => [[
        ['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_core_book'],
        ['source' => 'post_status', 'operator' => 'equals', 'value' => 'publish'],
    ]],
], true);
$repository = new InMemoryDefinitionRepository();
$repository->save(new Definition(
    id: $groupId,
    slug: 'field-group-core-ability-book-meta',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $payload,
    revision: 5,
));

$environment = new NativeWordPressAbilityEnvironment();
$contexts = new WordPressExecutionContextFactory($environment);
$abilities = new AbilityRegistry(new PolicyEngine(new WordPressCapabilityChecker($environment)));
$bridge = new WordPressAbilityBridge($abilities, $environment, $contexts);
$routes = new AjaxRouteRegistry();
$services = new ServiceRegistry();
$services->set('platform.definitions', $repository);
$services->set('platform.abilities', $abilities);
$services->set('platform.abilities.wordpress', $bridge);
$services->set('platform.abilities.contexts', $contexts);
$services->set('platform.ajax.routes', $routes);
(new FieldsModule())->register($services);

$compiler = $services->get('module.custom-fields.storage.post-meta.compiler');
$registrar = $services->get('module.custom-fields.storage.post-meta.registrar');
fieldsCoreAbilityExpect($compiler instanceof PostMetaRegistrationCompiler, 'Fields module must expose the certified post-meta compiler');
fieldsCoreAbilityExpect($registrar instanceof WordPressPostMetaRegistrar, 'Fields module must expose the certified post-meta registrar');
$registrar->register($compiler->compile($payload['fields'][0], 'wpe_core_book', showInRest: true));

$categoryRegistered = fieldsCoreAbilityWithinHook(
    'wp_abilities_api_categories_init',
    static fn (): bool => $bridge->registerCategory(),
);
fieldsCoreAbilityExpect($categoryRegistered === true, 'WPEssential Ability category must register in native WordPress');
$registeredNames = fieldsCoreAbilityWithinHook(
    'wp_abilities_api_init',
    static fn (): array => $bridge->registerAbilities(),
);
fieldsCoreAbilityExpect(in_array('wpessential/fields-read-value', $registeredNames, true), 'read-value must register in native WordPress Abilities API');
fieldsCoreAbilityExpect(in_array('wpessential/fields-write-value', $registeredNames, true), 'write-value must register in native WordPress Abilities API');

$readAbility = wp_get_ability('wpessential/fields-read-value');
$writeAbility = wp_get_ability('wpessential/fields-write-value');
fieldsCoreAbilityExpect($readAbility instanceof WP_Ability, 'native read-value Ability must be retrievable');
fieldsCoreAbilityExpect($writeAbility instanceof WP_Ability, 'native write-value Ability must be retrievable');

$writeSchema = $writeAbility->get_input_schema();
fieldsCoreAbilityExpect(is_array($writeSchema), 'write Ability must retain its input schema');
$required = $writeSchema['required'] ?? [];
fieldsCoreAbilityExpect(is_array($required) && in_array('expected_group_revision', $required, true), 'write Ability schema must require group revision CAS');
$writeMeta = $writeAbility->get_meta();
$readMeta = $readAbility->get_meta();
fieldsCoreAbilityExpect(($writeMeta['show_in_rest'] ?? null) === true, 'write Ability must retain explicit REST exposure');
fieldsCoreAbilityExpect(($readMeta['annotations']['readonly'] ?? null) === true, 'read Ability must be annotated readonly');
fieldsCoreAbilityExpect(($writeMeta['annotations']['readonly'] ?? null) === false, 'write Ability must be annotated mutating');

wp_set_current_user($admin->ID);
$postId = wp_insert_post([
    'post_type' => 'wpe_core_book',
    'post_status' => 'publish',
    'post_title' => 'Core Ability Target',
], true);
fieldsCoreAbilityExpect(is_int($postId) && $postId > 0, 'core Ability target post must be created');

$writeInput = [
    'group_id' => $groupId,
    'field_uuid' => $fieldUuid,
    'post_id' => $postId,
    'expected_group_revision' => 5,
    'value' => '  Core Ability Value  ',
];
fieldsCoreAbilityExpect($writeAbility->check_permissions($writeInput) === true, 'administrator resource permission must pass through native Ability callback');
$written = $writeAbility->execute($writeInput);
fieldsCoreAbilityExpect(!is_wp_error($written), 'native write Ability execution must succeed for authorized administrator');
fieldsCoreAbilityExpect(is_array($written) && ($written['value'] ?? null) === 'Core Ability Value', 'native write Ability must return canonical value');
fieldsCoreAbilityExpect(get_post_meta($postId, 'wpe_core_headline', true) === 'Core Ability Value', 'native Ability execution must persist through registered post meta');

$readInput = [
    'group_id' => $groupId,
    'field_uuid' => $fieldUuid,
    'post_id' => $postId,
];
$readBack = $readAbility->execute($readInput);
fieldsCoreAbilityExpect(!is_wp_error($readBack) && is_array($readBack) && ($readBack['value'] ?? null) === 'Core Ability Value', 'native read Ability must return canonical stored value');

wp_set_current_user($subscriber->ID);
fieldsCoreAbilityExpect($readAbility->check_permissions($readInput) === true, 'subscriber with read_post authority must pass read Ability permission');
fieldsCoreAbilityExpect($writeAbility->check_permissions($writeInput) === false, 'subscriber without edit_post authority must fail native write Ability permission');
$denied = $writeAbility->execute($writeInput);
fieldsCoreAbilityExpect(is_wp_error($denied) && $denied->get_error_code() === 'ability_invalid_permissions', 'native write Ability must return permission WP_Error before handler execution');
fieldsCoreAbilityExpect(get_post_meta($postId, 'wpe_core_headline', true) === 'Core Ability Value', 'denied native Ability write must not mutate metadata');

wp_set_current_user($admin->ID);
$staleInput = $writeInput;
$staleInput['expected_group_revision'] = 4;
fieldsCoreAbilityExpect($writeAbility->check_permissions($staleInput) === true, 'authorized stale-revision request must pass resource permission before schema CAS');
$stale = $writeAbility->execute($staleInput);
fieldsCoreAbilityExpect(is_wp_error($stale) && $stale->get_error_code() === 'wpessential_ability_execution_failed', 'stale schema revision must translate to safe WordPress Ability error');
fieldsCoreAbilityExpect(get_post_meta($postId, 'wpe_core_headline', true) === 'Core Ability Value', 'stale schema revision must not mutate metadata');

wp_update_post(['ID' => $postId, 'post_status' => 'draft']);
fieldsCoreAbilityExpect($readAbility->check_permissions($readInput) === true, 'administrator retains native read_post authority after target status changes');
$targetMismatch = $readAbility->execute($readInput);
fieldsCoreAbilityExpect(is_wp_error($targetMismatch) && $targetMismatch->get_error_code() === 'wpessential_ability_execution_failed', 'location mismatch must fail through safe native Ability error boundary');

fwrite(STDOUT, sprintf(
    "Fields core Ability integration PASS (WP %s / PHP %s)\n",
    get_bloginfo('version'),
    PHP_VERSION,
));
