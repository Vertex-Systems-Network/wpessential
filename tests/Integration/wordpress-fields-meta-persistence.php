<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields post-meta persistence integration SKIP (WordPress fixture unavailable)\n");
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

use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\PostMetaValueStore;
use WPEssential\Modules\Fields\PostMetaValueWriteResult;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;

function fieldsPersistenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Fields Persistence Integration',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldsPersistenceExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_admin', 'test-password-strong', 'admin@example.test');
    fieldsPersistenceExpect(is_int($userId) && $userId > 0, 'integration administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldsPersistenceExpect($admin instanceof WP_User, 'integration administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

register_post_type('wpe_value_book', [
    'public' => false,
    'show_ui' => false,
    'show_in_rest' => true,
    'supports' => ['title', 'custom-fields', 'revisions'],
]);

$definitions = new FieldDefinitionNormalizer();
$compiler = new PostMetaRegistrationCompiler();
$registrar = new WordPressPostMetaRegistrar();
$store = new PostMetaValueStore($compiler);

$fields = [
    'headline' => $definitions->normalize([
        'uuid' => '51111111-1111-4111-8111-111111111111',
        'key' => 'wpe_value_headline',
        'label' => 'Headline',
        'type' => 'text',
    ]),
    'enabled' => $definitions->normalize([
        'uuid' => '52222222-2222-4222-8222-222222222222',
        'key' => 'wpe_value_enabled',
        'label' => 'Enabled',
        'type' => 'true_false',
    ]),
    'count' => $definitions->normalize([
        'uuid' => '53333333-3333-4333-8333-333333333333',
        'key' => 'wpe_value_count',
        'label' => 'Count',
        'type' => 'number',
        'settings' => ['integer' => true],
    ]),
    'ratio' => $definitions->normalize([
        'uuid' => '54444444-4444-4444-8444-444444444444',
        'key' => 'wpe_value_ratio',
        'label' => 'Ratio',
        'type' => 'number',
    ]),
    'aliases' => $definitions->normalize([
        'uuid' => '55555555-5555-4555-8555-555555555555',
        'key' => 'wpe_value_aliases',
        'label' => 'Aliases',
        'type' => 'text',
        'cloneable' => true,
        'max_clones' => 3,
    ]),
    'rows' => $definitions->normalize([
        'uuid' => '56666666-6666-4666-8666-666666666666',
        'key' => 'wpe_value_rows',
        'label' => 'Rows',
        'type' => 'text',
        'cloneable' => true,
        'clone_as_multiple' => true,
    ]),
];

foreach ($fields as $field) {
    $registrar->register($compiler->compile($field, 'wpe_value_book', showInRest: true));
}

$postId = wp_insert_post([
    'post_type' => 'wpe_value_book',
    'post_status' => 'publish',
    'post_title' => 'Fields Persistence Fixture',
], true);
fieldsPersistenceExpect(is_int($postId) && $postId > 0, 'fixture post must be created');

$slashSensitive = 'Quote "A\\B"';
$written = $store->write($fields['headline'], 'wpe_value_book', $postId, '  ' . $slashSensitive . '  ');
fieldsPersistenceExpect($written->status === PostMetaValueWriteResult::WRITTEN, 'scalar write must report written');
fieldsPersistenceExpect($written->changed(), 'scalar write must report a changed state');
fieldsPersistenceExpect($store->read($fields['headline'], 'wpe_value_book', $postId) === $slashSensitive, 'scalar read must round-trip canonical slash-sensitive text');

$unchanged = $store->write($fields['headline'], 'wpe_value_book', $postId, $slashSensitive);
fieldsPersistenceExpect($unchanged->status === PostMetaValueWriteResult::UNCHANGED, 'idempotent scalar write must report unchanged');
fieldsPersistenceExpect(!$unchanged->changed(), 'unchanged write must not report mutation');

$boolWrite = $store->write($fields['enabled'], 'wpe_value_book', $postId, false);
fieldsPersistenceExpect($boolWrite->status === PostMetaValueWriteResult::WRITTEN, 'boolean false must be persistable as a present value');
fieldsPersistenceExpect(metadata_exists('post', $postId, 'wpe_value_enabled'), 'boolean false must remain distinguishable from absent metadata');
fieldsPersistenceExpect($store->read($fields['enabled'], 'wpe_value_book', $postId) === false, 'boolean false must round-trip as bool false');

$store->write($fields['count'], 'wpe_value_book', $postId, '7');
fieldsPersistenceExpect($store->read($fields['count'], 'wpe_value_book', $postId) === 7, 'integer meta must read back as canonical int');

$store->write($fields['ratio'], 'wpe_value_book', $postId, '7.5');
fieldsPersistenceExpect($store->read($fields['ratio'], 'wpe_value_book', $postId) === 7.5, 'number meta must read back as canonical float');

try {
    $store->write($fields['ratio'], 'wpe_value_book', $postId, INF);
    fieldsPersistenceExpect(false, 'non-finite store write must fail closed before native mutation');
} catch (\InvalidArgumentException) {
    fieldsPersistenceExpect($store->read($fields['ratio'], 'wpe_value_book', $postId) === 7.5, 'failed non-finite write must retain previous canonical value');
}

try {
    update_post_meta($postId, 'wpe_value_ratio', '1e309');
    fieldsPersistenceExpect(false, 'registered-meta sanitizer must reject non-finite numeric input outside the store path');
} catch (\InvalidArgumentException) {
    fieldsPersistenceExpect($store->read($fields['ratio'], 'wpe_value_book', $postId) === 7.5, 'alternate native write rejection must retain previous canonical value');
}

$aliases = [' One ', 'Two\\Three'];
$arrayWrite = $store->write($fields['aliases'], 'wpe_value_book', $postId, $aliases);
fieldsPersistenceExpect($arrayWrite->status === PostMetaValueWriteResult::WRITTEN, 'single-array write must report written');
fieldsPersistenceExpect($store->read($fields['aliases'], 'wpe_value_book', $postId) === ['One', 'Two\\Three'], 'single-array read must normalize and preserve slash-sensitive items');

add_post_meta($postId, 'wpe_value_rows', 'One');
add_post_meta($postId, 'wpe_value_rows', 'Two');
fieldsPersistenceExpect($store->read($fields['rows'], 'wpe_value_book', $postId) === ['One', 'Two'], 'multi-row metadata must remain readable as a typed list');

$rowsWrite = $store->write($fields['rows'], 'wpe_value_book', $postId, ['Three', 'Four', 'Three']);
fieldsPersistenceExpect($rowsWrite->status === PostMetaValueWriteResult::WRITTEN, 'multi-row replacement must report written');
fieldsPersistenceExpect($store->read($fields['rows'], 'wpe_value_book', $postId) === ['Three', 'Four', 'Three'], 'multi-row read must preserve replacement order and duplicates');
fieldsPersistenceExpect(get_post_meta($postId, 'wpe_value_rows', false) === ['Three', 'Four', 'Three'], 'native multi-row storage must match canonical replacement order and duplicates');

$rowsUnchanged = $store->write($fields['rows'], 'wpe_value_book', $postId, ['Three', 'Four', 'Three']);
fieldsPersistenceExpect($rowsUnchanged->status === PostMetaValueWriteResult::UNCHANGED, 'idempotent multi-row replacement must report unchanged');

$rowsReordered = $store->write($fields['rows'], 'wpe_value_book', $postId, ['Three', 'Three', 'Four']);
fieldsPersistenceExpect($rowsReordered->status === PostMetaValueWriteResult::WRITTEN, 'reorder-only multi-row replacement must be persisted');
fieldsPersistenceExpect(get_post_meta($postId, 'wpe_value_rows', false) === ['Three', 'Three', 'Four'], 'reorder-only multi-row replacement must rebuild native row order');

$rowsDeleted = $store->write($fields['rows'], 'wpe_value_book', $postId, []);
fieldsPersistenceExpect($rowsDeleted->status === PostMetaValueWriteResult::DELETED, 'empty multi-row list must delete all native rows');
fieldsPersistenceExpect(!metadata_exists('post', $postId, 'wpe_value_rows'), 'empty multi-row list must leave metadata absent');
fieldsPersistenceExpect($store->read($fields['rows'], 'wpe_value_book', $postId) === null, 'deleted multi-row metadata must read as null/absent');

$deleted = $store->write($fields['headline'], 'wpe_value_book', $postId, null);
fieldsPersistenceExpect($deleted->status === PostMetaValueWriteResult::DELETED, 'optional null must delete existing metadata');
fieldsPersistenceExpect(!metadata_exists('post', $postId, 'wpe_value_headline'), 'deleted metadata must be absent natively');
fieldsPersistenceExpect($store->read($fields['headline'], 'wpe_value_book', $postId) === null, 'deleted metadata must read as null/absent');

$absent = $store->write($fields['headline'], 'wpe_value_book', $postId, null);
fieldsPersistenceExpect($absent->status === PostMetaValueWriteResult::ABSENT, 'repeated null delete must report already absent');
fieldsPersistenceExpect(!$absent->changed(), 'already-absent delete must be idempotent');

fwrite(STDOUT, sprintf(
    "Fields post-meta persistence integration PASS (WP %s / PHP %s)\n",
    get_bloginfo('version'),
    PHP_VERSION,
));
