<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Fields registered-meta integration SKIP (WordPress fixture unavailable)\n");
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
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;

function fieldsMetaExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Fields Integration',
        'wpessential_admin',
        'admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    fieldsMetaExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_admin', 'test-password-strong', 'admin@example.test');
    fieldsMetaExpect(is_int($userId) && $userId > 0, 'integration administrator must be created');
    $admin = get_user_by('id', $userId);
}
fieldsMetaExpect($admin instanceof WP_User, 'integration administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

register_post_type('wpe_meta_book', [
    'public' => false,
    'show_ui' => false,
    'show_in_rest' => true,
    'supports' => ['title', 'custom-fields', 'revisions'],
]);
register_post_type('wpe_no_revision', [
    'public' => false,
    'show_ui' => false,
    'show_in_rest' => true,
    'supports' => ['title', 'custom-fields'],
]);
register_post_type('wpe_no_meta', [
    'public' => false,
    'show_ui' => false,
    'show_in_rest' => true,
    'supports' => ['title', 'revisions'],
]);

$definitions = new FieldDefinitionNormalizer();
$compiler = new PostMetaRegistrationCompiler();
$registrar = new WordPressPostMetaRegistrar();

$text = $definitions->normalize([
    'uuid' => '11111111-1111-4111-8111-111111111111',
    'key' => 'wpe_headline',
    'label' => 'Headline',
    'type' => 'text',
]);
$textRegistration = $compiler->compile($text, 'wpe_meta_book', showInRest: true, revisionsEnabled: true);
$registrar->register($textRegistration);

$email = $definitions->normalize([
    'uuid' => '44444444-4444-4444-8444-444444444444',
    'key' => 'wpe_email',
    'label' => 'Email',
    'type' => 'email',
]);
$emailRegistration = $compiler->compile($email, 'wpe_meta_book');
$registrar->register($emailRegistration);

$aliases = $definitions->normalize([
    'uuid' => '22222222-2222-4222-8222-222222222222',
    'key' => 'wpe_aliases',
    'label' => 'Aliases',
    'type' => 'text',
    'cloneable' => true,
    'max_clones' => 3,
]);
$aliasesRegistration = $compiler->compile($aliases, 'wpe_meta_book', showInRest: true);
$registrar->register($aliasesRegistration);

$rows = $definitions->normalize([
    'uuid' => '33333333-3333-4333-8333-333333333333',
    'key' => 'wpe_tags',
    'label' => 'Tags',
    'type' => 'text',
    'cloneable' => true,
    'clone_as_multiple' => true,
]);
$rowsRegistration = $compiler->compile($rows, 'wpe_meta_book', showInRest: true);
$registrar->register($rowsRegistration);

$postObject = $definitions->normalize([
    'uuid' => '55555555-5555-4555-8555-555555555555',
    'key' => 'wpe_post_object',
    'label' => 'Post Object',
    'type' => 'post_object',
]);
$registrar->register($compiler->compile($postObject, 'wpe_meta_book', showInRest: true));

$posts = $definitions->normalize([
    'uuid' => '66666666-6666-4666-8666-666666666666',
    'key' => 'wpe_posts',
    'label' => 'Posts',
    'type' => 'posts',
]);
$registrar->register($compiler->compile($posts, 'wpe_meta_book', showInRest: true));

$ownerBoundTypes = [
    'relationship',
    'taxonomy',
    'user',
    'page_link',
    'nav_menu',
    'sidebar',
    'group',
    'repeater',
    'flexible_content',
    'clone',
    'accordion',
    'tab',
];
foreach ($ownerBoundTypes as $index => $type) {
    $uuidSuffix = str_pad((string) ($index + 1), 12, '0', STR_PAD_LEFT);
    $field = $definitions->normalize([
        'uuid' => '77777777-7777-4777-8777-' . $uuidSuffix,
        'key' => 'wpe_owner_' . $type,
        'label' => 'Owner Boundary ' . $type,
        'type' => $type,
    ]);

    try {
        $registrar->register($compiler->compile($field, 'wpe_meta_book'));
        fieldsMetaExpect(false, sprintf('owner-bound type %s must fail before native registration', $type));
    } catch (\InvalidArgumentException $exception) {
        fieldsMetaExpect($exception->getMessage() !== '', sprintf('owner-bound type %s must fail with an explicit reason', $type));
    }
}

$registered = get_registered_meta_keys('post', 'wpe_meta_book');
fieldsMetaExpect(isset($registered['wpe_headline']), 'scalar field must be registered for the exact post subtype');
fieldsMetaExpect($registered['wpe_headline']['type'] === 'string', 'scalar field must retain WordPress string type');
fieldsMetaExpect($registered['wpe_headline']['single'] === true, 'scalar field must be registered single');
fieldsMetaExpect($registered['wpe_headline']['revisions_enabled'] === true, 'revision-enabled scalar field must retain its native revision flag');
fieldsMetaExpect(isset($registered['wpe_email']), 'validated scalar field must be registered');
fieldsMetaExpect(isset($registered['wpe_aliases']), 'array-backed repeatable field must be registered');
fieldsMetaExpect($registered['wpe_aliases']['type'] === 'array' && $registered['wpe_aliases']['single'] === true, 'repeatable array storage shape must survive native registration');
fieldsMetaExpect(isset($registered['wpe_tags']), 'multiple-row field must be registered');
fieldsMetaExpect($registered['wpe_tags']['type'] === 'string' && $registered['wpe_tags']['single'] === false, 'multiple-row scalar shape must survive native registration');
fieldsMetaExpect(isset($registered['wpe_post_object']), 'certified post_object reference must register natively');
fieldsMetaExpect($registered['wpe_post_object']['type'] === 'integer' && $registered['wpe_post_object']['single'] === true, 'post_object must retain single integer native shape');
fieldsMetaExpect(isset($registered['wpe_posts']), 'certified posts reference list must register natively');
fieldsMetaExpect($registered['wpe_posts']['type'] === 'array' && $registered['wpe_posts']['single'] === true, 'posts must retain native integer-list storage shape');
foreach ($ownerBoundTypes as $type) {
    fieldsMetaExpect(!isset($registered['wpe_owner_' . $type]), sprintf('owner-bound type %s must not appear in native registered meta', $type));
}

$restMeta = new WP_REST_Post_Meta_Fields('wpe_meta_book');
$restSchema = $restMeta->get_field_schema();
$restFields = $restSchema['properties'] ?? [];
fieldsMetaExpect(is_array($restFields), 'public REST meta schema must expose a properties map');
fieldsMetaExpect(isset($restFields['wpe_headline']), 'REST meta schema must include scalar field');
fieldsMetaExpect($restFields['wpe_headline']['type'] === 'string', 'single scalar REST schema must remain scalar');
fieldsMetaExpect(isset($restFields['wpe_aliases']), 'REST meta schema must include array-backed repeatable field');
fieldsMetaExpect($restFields['wpe_aliases']['type'] === 'array', 'array-backed repeatable REST schema must be an array');
fieldsMetaExpect(($restFields['wpe_aliases']['items']['type'] ?? null) === 'string', 'array-backed repeatable REST schema must retain item type');
fieldsMetaExpect(isset($restFields['wpe_tags']), 'REST meta schema must include non-single scalar field');
fieldsMetaExpect($restFields['wpe_tags']['type'] === 'array', 'WordPress REST must wrap non-single scalar meta as an array');
fieldsMetaExpect(($restFields['wpe_tags']['items']['type'] ?? null) === 'string', 'WordPress REST must retain the scalar item schema for non-single meta');
fieldsMetaExpect(isset($restFields['wpe_post_object']), 'REST meta schema must include certified post_object reference');
fieldsMetaExpect($restFields['wpe_post_object']['type'] === 'integer', 'post_object REST schema must remain integer');
fieldsMetaExpect(isset($restFields['wpe_posts']), 'REST meta schema must include certified posts reference list');
fieldsMetaExpect($restFields['wpe_posts']['type'] === 'array', 'posts REST schema must remain an array');
fieldsMetaExpect(($restFields['wpe_posts']['items']['type'] ?? null) === 'integer', 'posts REST schema must retain integer items');

$postId = wp_insert_post([
    'post_type' => 'wpe_meta_book',
    'post_status' => 'publish',
    'post_title' => 'Fields Meta Fixture',
], true);
fieldsMetaExpect(is_int($postId) && $postId > 0, 'fixture post must be created');

$auth = $registered['wpe_headline']['auth_callback'] ?? null;
fieldsMetaExpect(is_callable($auth), 'registered scalar field must expose an explicit auth callback');
fieldsMetaExpect($auth(false, 'wpe_headline', $postId, $admin->ID, 'edit_post_meta', []) === true, 'administrator must be authorized through object-level edit_post policy');

$canonicalText = 'Quote "A\\B"';
$updated = update_post_meta($postId, 'wpe_headline', wp_slash('  ' . $canonicalText . '  '));
fieldsMetaExpect($updated !== false, 'native scalar metadata write must succeed');
fieldsMetaExpect(get_post_meta($postId, 'wpe_headline', true) === $canonicalText, 'native write boundary must preserve canonical slash-sensitive text after normalization');

$updated = update_post_meta($postId, 'wpe_aliases', wp_slash([' One ', 'Two\\Three']));
fieldsMetaExpect($updated !== false, 'native array metadata write must succeed');
fieldsMetaExpect(get_post_meta($postId, 'wpe_aliases', true) === ['One', 'Two\\Three'], 'native array write must preserve normalized slash-sensitive list items');

$updated = update_post_meta($postId, 'wpe_email', wp_slash('person@example.com'));
fieldsMetaExpect($updated !== false, 'valid registered email metadata write must succeed');
try {
    update_post_meta($postId, 'wpe_email', wp_slash('not-an-email'));
    fieldsMetaExpect(false, 'invalid registered email metadata write must fail closed');
} catch (\InvalidArgumentException) {
    fieldsMetaExpect(get_post_meta($postId, 'wpe_email', true) === 'person@example.com', 'failed sanitization must leave the previous metadata value intact');
}

try {
    $registrar->register($compiler->compile($text, 'wpe_no_revision', revisionsEnabled: true));
    fieldsMetaExpect(false, 'revision-enabled registration must reject a subtype without revisions support');
} catch (\InvalidArgumentException) {
    fieldsMetaExpect(true, 'revision support rejection is expected');
}

try {
    $registrar->register($compiler->compile($text, 'wpe_no_meta', showInRest: true));
    fieldsMetaExpect(false, 'REST-visible registration must reject a subtype without custom-fields support');
} catch (\InvalidArgumentException) {
    fieldsMetaExpect(true, 'custom-fields support rejection is expected');
}

fwrite(STDOUT, sprintf(
    "Fields registered-meta integration PASS (WP %s / PHP %s)\n",
    get_bloginfo('version'),
    PHP_VERSION,
));
