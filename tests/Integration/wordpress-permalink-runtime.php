<?php

declare(strict_types=1);

/*
 * URL formats on real WordPress (PermalinkRuntime): a builder post type with its base hidden
 * (/pl-hidden-item/) and one with a custom pattern (films/%category%/%postname%). Runs in two
 * requests through wp eval: "seed" saves the definitions and pretty permalinks, "verify" checks
 * the links, rewrite rules and request routing on the next request.
 */

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
if (!function_exists('get_bloginfo')) {
    fwrite(STDERR, "FAIL: WordPress must be loaded before the permalink runtime integration test.\n");
    exit(1);
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

function permalinkExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @param array<string,mixed> $payload */
function permalinkDefinition(string $id, string $slug, array $payload): Definition
{
    return new Definition(
        id: $id,
        slug: $slug,
        type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
        schemaVersion: 1,
        ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
        status: DefinitionStatus::Published,
        payload: $payload,
    );
}

$definitions = \WPEssential\Bootstrap\Plugin::kernel()->services()->get('platform.definitions');
permalinkExpect($definitions instanceof DefinitionRepositoryInterface, 'shared Definition Repository must be available');

$hiddenId = '33333333-3333-4333-8333-333333333331';
$customId = '33333333-3333-4333-8333-333333333332';
$mode = getenv('WPE_PERMALINK_TEST_MODE') ?: '';

if ($mode === 'seed') {
    update_option('permalink_structure', '/%postname%/');
    $definitions->save(permalinkDefinition($hiddenId, 'pl-hidden', [
        'post_type_key' => 'pl_hidden',
        'name' => 'Hidden Items',
        'singular_name' => 'Hidden Item',
        'public' => true,
        'permalink' => ['mode' => 'hide_base'],
    ]));
    $definitions->save(permalinkDefinition($customId, 'pl-film', [
        'post_type_key' => 'pl_film',
        'name' => 'Films',
        'singular_name' => 'Film',
        'public' => true,
        'taxonomies' => ['category'],
        'permalink' => ['mode' => 'custom', 'pattern' => 'films/%category%/%postname%'],
    ]));
    fwrite(STDOUT, "permalink seed PASS\n");
    return;
}

if ($mode === 'verify') {
    permalinkExpect(post_type_exists('pl_hidden') && post_type_exists('pl_film'), 'both builder post types must register');
    $hidden = wp_insert_post(['post_type' => 'pl_hidden', 'post_title' => 'Hidden Item One', 'post_name' => 'pl-hidden-item', 'post_status' => 'publish']);
    $term = wp_insert_term('Drama', 'category', ['slug' => 'drama']);
    $termId = is_array($term) ? (int) $term['term_id'] : (int) (get_term_by('slug', 'drama', 'category')->term_id ?? 0);
    $film = wp_insert_post(['post_type' => 'pl_film', 'post_title' => 'Film One', 'post_name' => 'pl-film-one', 'post_status' => 'publish', 'post_category' => [$termId]]);
    permalinkExpect(is_int($hidden) && $hidden > 0 && is_int($film) && $film > 0, 'fixture posts must be created');

    $hiddenLink = (string) get_permalink($hidden);
    permalinkExpect($hiddenLink === home_url('/pl-hidden-item/'), "hidden base link must be /pl-hidden-item/, got {$hiddenLink}");
    $routed = apply_filters('request', ['name' => 'pl-hidden-item']);
    permalinkExpect(($routed['post_type'] ?? null) === 'pl_hidden', 'a hidden-base URL must route to the builder post type');
    $page = wp_insert_post(['post_type' => 'page', 'post_title' => 'About', 'post_name' => 'pl-about', 'post_status' => 'publish']);
    permalinkExpect(is_int($page) && $page > 0, 'fixture page must be created');
    $pageRequest = apply_filters('request', ['pagename' => 'pl-about']);
    permalinkExpect(!isset($pageRequest['post_type']), 'a real page must win over a hidden-base URL');

    $filmLink = (string) get_permalink($film);
    permalinkExpect($filmLink === home_url('/films/drama/pl-film-one/'), "custom pattern link must be /films/drama/pl-film-one/, got {$filmLink}");
    permalinkExpect(url_to_postid($filmLink) === $film, 'rewrite rules must resolve the custom pattern URL back to the post');
    permalinkExpect(get_option(\WPEssential\Platform\WordPress\Registrations\PermalinkRuntime::OPTION) !== false, 'rewrite rules must have been flushed for the URL settings');

    fwrite(STDOUT, "permalink verify PASS\n");
    return;
}

fwrite(STDERR, "FAIL: unknown WPE_PERMALINK_TEST_MODE {$mode}\n");
exit(1);
