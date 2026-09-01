<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields value Ability integration SKIP (WordPress fixture unavailable)\n");
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

use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueAbilityHandler;
use WPEssential\Modules\Fields\FieldValueTargetResolver;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;
use WPEssential\Modules\Fields\WordPressPostResourceAuthorizer;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

function fieldsValueAbilityExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Fields Value Ability Integration',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldsValueAbilityExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_admin', 'test-password-strong', 'admin@example.test');
    fieldsValueAbilityExpect(is_int($userId) && $userId > 0, 'integration administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldsValueAbilityExpect($admin instanceof WP_User, 'integration administrator must exist');
$admin->set_role('administrator');

$subscriber = get_user_by('login', 'wpessential_reader');
if (!$subscriber instanceof WP_User) {
    $userId = wp_create_user('wpessential_reader', 'test-password-strong', 'reader@example.test');
    fieldsValueAbilityExpect(is_int($userId) && $userId > 0, 'integration reader must be created');
    $subscriber = get_user_by('id', $userId);
}
fieldsValueAbilityExpect($subscriber instanceof WP_User, 'integration reader must exist');
$subscriber->set_role('subscriber');

register_post_type('wpe_ability_book', [
    'public' => true,
    'show_ui' => false,
    'show_in_rest' => true,
    'supports' => ['title', 'custom-fields'],
]);

$groupId = '61111111-1111-4111-8111-111111111111';
$fieldUuid = '62222222-2222-4222-8222-222222222222';
$normalizer = new FieldGroupDefinitionNormalizer();
$payload = $normalizer->normalize([
    'group_key' => 'ability_book_meta',
    'title' => 'Ability Book Meta',
    'fields' => [[
        'uuid' => $fieldUuid,
        'key' => 'wpe_ability_headline',
        'label' => 'Headline',
        'type' => 'text',
    ]],
    'locations' => [[
        ['source' => 'post_type', 'operator' => 'equals', 'value' => 'wpe_ability_book'],
        ['source' => 'post_status', 'operator' => 'equals', 'value' => 'publish'],
    ]],
], true);
$repository = new InMemoryDefinitionRepository();
$repository->save(new Definition(
    id: $groupId,
    slug: 'field-group-ability-book-meta',
    type: FieldGroupDefinitionNormalizer::DEFINITION_TYPE,
    schemaVersion: 1,
    ownerSurfaceId: FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID,
    status: DefinitionStatus::Published,
    payload: $payload,
    revision: 4,
));

$field = $payload['fields'][0];
$compiler = new PostMetaRegistrationCompiler();
(new WordPressPostMetaRegistrar())->register($compiler->compile($field, 'wpe_ability_book', showInRest: true));

wp_set_current_user($admin->ID);
$postId = wp_insert_post([
    'post_type' => 'wpe_ability_book',
    'post_status' => 'publish',
    'post_title' => 'Ability Target',
], true);
fieldsValueAbilityExpect(is_int($postId) && $postId > 0, 'Ability target post must be created');

$targets = new FieldValueTargetResolver($repository, $normalizer);
$store = new PostMetaValueStore($compiler);
$authorization = new WordPressPostResourceAuthorizer();
$read = new FieldValueAbilityHandler($targets, $store, $authorization, FieldValueAbilityHandler::READ);
$write = new FieldValueAbilityHandler($targets, $store, $authorization, FieldValueAbilityHandler::WRITE);

$networkId = function_exists('get_current_network_id') ? (int) get_current_network_id() : null;
$adminContext = new ExecutionContext(
    new Principal($admin->ID),
    (int) get_current_blog_id(),
    networkId: is_int($networkId) && $networkId > 0 ? $networkId : null,
);

$written = $write->handle([
    'group_id' => $groupId,
    'field_uuid' => $fieldUuid,
    'post_id' => $postId,
    'value' => '  Ability Value  ',
], $adminContext);
fieldsValueAbilityExpect($written['status'] === 'written', 'authorized value Ability write must report written');
fieldsValueAbilityExpect($written['value'] === 'Ability Value', 'authorized value Ability write must return canonical value');
fieldsValueAbilityExpect($written['group_revision'] === 4, 'Ability response must retain resolved group revision');
fieldsValueAbilityExpect($written['field_uuid'] === $fieldUuid, 'Ability response must retain stable Field UUID');
fieldsValueAbilityExpect(get_post_meta($postId, 'wpe_ability_headline', true) === 'Ability Value', 'Ability write must use native registered post meta');

$readBack = $read->handle([
    'group_id' => $groupId,
    'field_uuid' => $fieldUuid,
    'post_id' => $postId,
], $adminContext);
fieldsValueAbilityExpect($readBack['status'] === 'read' && $readBack['value'] === 'Ability Value', 'authorized read Ability must return canonical stored value');

wp_set_current_user($subscriber->ID);
$readerContext = new ExecutionContext(
    new Principal($subscriber->ID),
    (int) get_current_blog_id(),
    networkId: is_int($networkId) && $networkId > 0 ? $networkId : null,
);
$readerValue = $read->handle([
    'group_id' => $groupId,
    'field_uuid' => $fieldUuid,
    'post_id' => $postId,
], $readerContext);
fieldsValueAbilityExpect($readerValue['value'] === 'Ability Value', 'reader with read_post authority must read the published target');
try {
    $write->handle([
        'group_id' => $groupId,
        'field_uuid' => $fieldUuid,
        'post_id' => $postId,
        'value' => 'Denied Mutation',
    ], $readerContext);
    fieldsValueAbilityExpect(false, 'reader without edit_post authority must not mutate Field value');
} catch (RuntimeException) {
    fieldsValueAbilityExpect(get_post_meta($postId, 'wpe_ability_headline', true) === 'Ability Value', 'denied mutation must leave stored value unchanged');
}

try {
    $read->handle([
        'group_id' => $groupId,
        'field_uuid' => $fieldUuid,
        'post_id' => $postId,
    ], $adminContext);
    fieldsValueAbilityExpect(false, 'stale execution context principal must not be reused under a different active WordPress user');
} catch (RuntimeException) {
    fieldsValueAbilityExpect(true, 'context/user binding denial is expected');
}

wp_set_current_user($admin->ID);
wp_update_post(['ID' => $postId, 'post_status' => 'draft']);
try {
    $read->handle([
        'group_id' => $groupId,
        'field_uuid' => $fieldUuid,
        'post_id' => $postId,
    ], $adminContext);
    fieldsValueAbilityExpect(false, 'location mismatch after post status transition must fail closed');
} catch (RuntimeException) {
    fieldsValueAbilityExpect(true, 'post status location mismatch denial is expected');
}

fwrite(STDOUT, sprintf(
    "Fields value Ability integration PASS (WP %s / PHP %s)\n",
    get_bloginfo('version'),
    PHP_VERSION,
));
