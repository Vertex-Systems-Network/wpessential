<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php')) {
    fwrite(STDOUT, "WPEssential Listings reference application SKIP (WordPress fixture unavailable)\n");
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
define('AUTH_KEY',         'wpessential-listings-reference-auth-key');
define('SECURE_AUTH_KEY',  'wpessential-listings-reference-secure-auth-key');
define('LOGGED_IN_KEY',    'wpessential-listings-reference-logged-in-key');
define('NONCE_KEY',        'wpessential-listings-reference-nonce-key');
define('AUTH_SALT',        'wpessential-listings-reference-auth-salt');
define('SECURE_AUTH_SALT', 'wpessential-listings-reference-secure-auth-salt');
define('LOGGED_IN_SALT',   'wpessential-listings-reference-logged-in-salt');
define('NONCE_SALT',       'wpessential-listings-reference-nonce-salt');
$table_prefix = 'wpelistings_';
define('WP_DEBUG', false);
require_once ABSPATH . 'wp-settings.php';
PHP;
    if (file_put_contents($wpDir . '/wp-config.php', $config . "\n") === false) {
        fwrite(STDERR, "FAIL: unable to create WordPress Listings reference configuration\n");
        exit(1);
    }
}

require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

$root = dirname(__DIR__, 4);
require_once $root . '/vendor/autoload.php';

use InvalidArgumentException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\ComponentBlueprintRegistryInterface;
use WPEssential\Contracts\DynamicValueResolverInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\RendererInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Listings\Definition\ListingDefinitionCompiler;
use WPEssential\Modules\Listings\Presentation\ListingNoJsNavigation;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryBinding;
use WPEssential\Modules\Listings\QueryBinding\ListingQueryReader;
use WPEssential\Modules\Listings\Rendering\ListingServerRenderer;
use WPEssential\Modules\Listings\Scope\ListingScopeGuard;
use WPEssential\Modules\Listings\State\ListingPublicStateCodec;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Assets\AssetRegistry;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Components\ComponentBlueprintDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\DynamicValues\DynamicValueRequest;
use WPEssential\Platform\DynamicValues\DynamicValueResult;
use WPEssential\Platform\Rendering\RenderFailureCode;
use WPEssential\Platform\Rendering\RenderInput;
use WPEssential\Platform\Rendering\RenderOutput;

function listingsReferenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

if (!is_blog_installed()) {
    $installed = wp_install(
        'WPEssential Listings Reference',
        'wpessential_listings_admin',
        'listings-admin@example.test',
        false,
        '',
        'test-password-strong',
    );
    listingsReferenceExpect(!is_wp_error($installed), 'WordPress fixture installation must succeed');
}

$admin = get_user_by('login', 'wpessential_listings_admin');
if (!$admin instanceof WP_User) {
    $userId = wp_create_user('wpessential_listings_admin', 'test-password-strong', 'listings-admin@example.test');
    listingsReferenceExpect(is_int($userId) && $userId > 0, 'reference administrator must be created');
    $admin = get_user_by('id', $userId);
}
listingsReferenceExpect($admin instanceof WP_User, 'reference administrator must exist');
$admin->set_role('administrator');
wp_set_current_user($admin->ID);

$posts = [];
foreach ([
    ['Alpha Listing', 'publish', 'Gold & Verified'],
    ['Beta Listing', 'publish', 'Silver'],
    ['Draft Listing', 'draft', 'Hidden'],
] as [$title, $status, $badge]) {
    $id = wp_insert_post([
        'post_title' => $title,
        'post_content' => 'Listings reference content for ' . $title,
        'post_status' => $status,
        'post_type' => 'post',
        'post_author' => $admin->ID,
    ], true);
    listingsReferenceExpect(is_int($id) && $id > 0, 'reference post must be created: ' . $title);
    update_post_meta($id, 'wpe_listing_badge', $badge);
    $posts[$title] = $id;
}

$policyState = new class {
    public bool $allow = true;
    /** @var list<string> */
    public array $events = [];
};
$checker = new class($policyState) implements CapabilityCheckerInterface {
    public function __construct(private object $state) {}

    public function can(ExecutionContext $context, string $capability): bool
    {
        $this->state->events[] = 'policy:' . $capability;
        return $this->state->allow && current_user_can($capability);
    }
};

$services = new ServiceRegistry();
$dataSources = new DataSourceRegistry();
$services->set('platform.data-sources', $dataSources);
$services->set('platform.abilities.policy', new PolicyEngine($checker));
(new QueryModule())->register($services);

$realQuery = $services->get(QueryModule::SERVICE_READ_CONSUMER);
listingsReferenceExpect($realQuery instanceof QueryReadConsumerInterface, 'canonical Query read consumer must register');
$trackedQuery = new class($realQuery) implements QueryReadConsumerInterface {
    public int $readCalls = 0;

    public function __construct(private QueryReadConsumerInterface $inner) {}

    public function describe(string $sourceRef, ExecutionContext $context): array
    {
        return $this->inner->describe($sourceRef, $context);
    }

    public function read(array $request, ExecutionContext $context): array
    {
        ++$this->readCalls;
        return $this->inner->read($request, $context);
    }
};

$blueprint = new ComponentBlueprintDescriptor(
    id: '22222222-2222-4222-8222-222222222222',
    revision: 2,
    ownerSurfaceId: 9,
    componentType: 'listing.card',
    bindingSchema: [
        'title' => 'string',
        'badge' => 'string',
    ],
);
$blueprints = new class($blueprint) implements ComponentBlueprintRegistryInterface {
    public function __construct(private ComponentBlueprintDescriptor $blueprint) {}

    public function get(string $blueprintId, int $revision): ?ComponentBlueprintDescriptor
    {
        return $blueprintId === $this->blueprint->id && $revision === $this->blueprint->revision
            ? $this->blueprint
            : null;
    }
};

$dynamicValues = new class implements DynamicValueResolverInterface {
    public bool $fail = false;
    public int $calls = 0;

    public function resolve(DynamicValueRequest $request, ExecutionContext $context): DynamicValueResult
    {
        ++$this->calls;
        if ($this->fail) {
            return new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource);
        }
        if (
            $request->sourceRef !== 'wordpress.post-meta'
            || $request->valueRef !== 'wpe_listing_badge'
            || $request->resourceType !== 'post'
        ) {
            return new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource);
        }

        $value = get_post_meta((int) $request->resourceId, 'wpe_listing_badge', true);
        if (!is_string($value) || $value === '') {
            return new DynamicValueResult(false, null, RenderFailureCode::UnsupportedValueSource);
        }
        return new DynamicValueResult(true, $value, sourceEvidence: 'wordpress.post-meta');
    }
};

$renderer = new class implements RendererInterface {
    public bool $fail = false;
    public int $calls = 0;

    public function render(RenderInput $input, ExecutionContext $context): RenderOutput
    {
        ++$this->calls;
        if ($this->fail) {
            return new RenderOutput(false, '', [], RenderFailureCode::DependencyMismatch);
        }
        $title = is_string($input->bindings['title'] ?? null) ? $input->bindings['title'] : '';
        $badge = is_string($input->bindings['badge'] ?? null) ? $input->bindings['badge'] : '';
        return new RenderOutput(
            true,
            '<article class="wpe-card"><h2>' . esc_html($title) . '</h2><span>' . esc_html($badge) . '</span></article>',
            ['wpe-card'],
        );
    }
};

$definition = new Definition(
    id: '11111111-1111-4111-8111-111111111111',
    slug: 'posts-reference',
    type: 'listing',
    schemaVersion: 1,
    ownerSurfaceId: 9,
    status: DefinitionStatus::Published,
    payload: [
        'query_source_ref' => 'wordpress.posts',
        'blueprint' => ['id' => $blueprint->id, 'revision' => $blueprint->revision],
        'layout' => ['mode' => 'list', 'columns' => 1],
        'assets' => [],
        'bindings' => [
            [
                'kind' => 'query_field',
                'binding_key' => 'title',
                'query_field_ref' => 'post.title',
            ],
            [
                'kind' => 'dynamic_value',
                'binding_key' => 'badge',
                'source_ref' => 'wordpress.post-meta',
                'value_ref' => 'wpe_listing_badge',
                'resource_type' => 'post',
                'resource_id_field_ref' => 'post.id',
            ],
        ],
    ],
    revision: 3,
);
$compiled = (new ListingDefinitionCompiler($blueprints, new AssetRegistry()))->compile($definition);
listingsReferenceExpect(count($compiled->renderBindings) === 2, 'Published Listing must compile an explicit two-binding plan');
listingsReferenceExpect(strlen($compiled->compatibilityFingerprint) === 64, 'compiled Listing fingerprint must be deterministic SHA-256 evidence');

$binding = new ListingQueryBinding(
    sourceRef: 'wordpress.posts',
    projection: ['post.id', 'post.title'],
    filterParameters: ['status' => 'post.status'],
    orderBy: [['field_ref' => 'post.id', 'direction' => 'asc']],
    pageSize: 10,
);
$state = (new ListingPublicStateCodec())->normalize(
    $binding,
    'posts',
    ['wpe_posts_status' => 'publish', 'unrelated' => 'ignored'],
);
listingsReferenceExpect($state->parameters === ['status' => 'publish'], 'canonical public state must expose only declared Listing parameters');
listingsReferenceExpect($state->queryString === 'wpe_posts_status=publish', 'canonical public state serialization must be deterministic');
listingsReferenceExpect(
    (new ListingNoJsNavigation())->href('/archive', $state) === '/archive?wpe_posts_status=publish',
    'no-JS navigation must preserve canonical Listing public state',
);

$context = new ExecutionContext(new Principal($admin->ID), max(1, get_current_blog_id()));
$scopeGuard = new ListingScopeGuard();
$scope = $scopeGuard->fromContext($context, $state->parameters);
$scopeGuard->assertMatches($scope, $context);

$serverRenderer = new ListingServerRenderer(
    new ListingQueryReader($trackedQuery),
    $blueprints,
    $renderer,
    $dynamicValues,
);
$result = $serverRenderer->render($compiled, $binding, $state->parameters, $context);
listingsReferenceExpect($result->success, 'real WordPress Listings composed SSR must succeed');
listingsReferenceExpect($result->returned === 2, 'published filter must return exactly the two published reference posts');
listingsReferenceExpect(str_contains($result->html, 'Alpha Listing'), 'SSR must include the first published WordPress post');
listingsReferenceExpect(str_contains($result->html, 'Beta Listing'), 'SSR must include the second published WordPress post');
listingsReferenceExpect(!str_contains($result->html, 'Draft Listing'), 'SSR must exclude draft WordPress rows through canonical Query authorization/filtering');
listingsReferenceExpect(str_contains($result->html, 'Gold &amp; Verified'), 'shared Renderer must escape real WordPress Dynamic Value content');
listingsReferenceExpect($result->assetHandles === ['wpe-card'], 'renderer asset evidence must remain deterministic');
listingsReferenceExpect($renderer->calls === 2 && $dynamicValues->calls === 2, 'each authorized row must resolve and render exactly once');
listingsReferenceExpect($trackedQuery->readCalls === 1, 'one Listing render must execute exactly one canonical Query consumer read');

$beforeScopeRead = $trackedQuery->readCalls;
$scopeRejected = false;
try {
    $scopeGuard->fromContext($context, ['site_id' => 999]);
} catch (InvalidArgumentException) {
    $scopeRejected = true;
}
listingsReferenceExpect($scopeRejected, 'public site scope injection must fail closed');
listingsReferenceExpect($trackedQuery->readCalls === $beforeScopeRead, 'scope injection must fail before Query execution');

$policyState->allow = false;
$policyState->events = [];
$denied = $serverRenderer->render($compiled, $binding, $state->parameters, $context);
listingsReferenceExpect(!$denied->success && $denied->html === '' && $denied->returned === 0, 'authorization denial must return no protected Listing HTML');
listingsReferenceExpect(in_array('policy:read', $policyState->events, true), 'canonical Query Policy must be consulted on denied Listing execution');
$policyState->allow = true;

$dynamicValues->fail = true;
$rendererCallsBeforeDynamicFailure = $renderer->calls;
$dynamicFailure = $serverRenderer->render($compiled, $binding, $state->parameters, $context);
listingsReferenceExpect(!$dynamicFailure->success && $dynamicFailure->html === '' && $dynamicFailure->returned === 0, 'unresolved Dynamic Value must fail the whole Listing closed');
listingsReferenceExpect($renderer->calls === $rendererCallsBeforeDynamicFailure, 'unresolved Dynamic Value must fail before shared Renderer invocation');
$dynamicValues->fail = false;

$renderer->fail = true;
$renderFailure = $serverRenderer->render($compiled, $binding, $state->parameters, $context);
listingsReferenceExpect(!$renderFailure->success && $renderFailure->html === '' && $renderFailure->returned === 0, 'shared Renderer failure must not leak partial Listing HTML');
$renderer->fail = false;

fwrite(STDOUT, "WPEssential Listings real-WordPress reference application PASS\n");
