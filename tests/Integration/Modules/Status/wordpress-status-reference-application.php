<?php

declare(strict_types=1);

$wpDir = rtrim((string) getenv('WPE_TEST_WORDPRESS_DIR'), '/\\');
if ($wpDir === '' || !is_file($wpDir . '/wp-load.php') || !is_file($wpDir . '/wp-config.php')) {
    fwrite(STDOUT, "WPEssential Status reference application SKIP (prepared WordPress fixture unavailable)\n");
    exit(0);
}

$root = dirname(__DIR__, 4);
$muDir = $wpDir . '/wp-content/mu-plugins';
if (!is_dir($muDir) && !mkdir($muDir, 0777, true) && !is_dir($muDir)) {
    fwrite(STDERR, "FAIL: unable to create Status reference mu-plugin directory\n");
    exit(1);
}

$rootLiteral = var_export($root, true);
$muPlugin = str_replace('__WPE_ROOT__', $rootLiteral, <<<'PHP'
<?php
$wpeRoot = __WPE_ROOT__;
require_once $wpeRoot . '/vendor/autoload.php';

\WPEssential\Bootstrap\Plugin::setModuleActivationPolicy(
    new class implements \WPEssential\Contracts\ModuleActivationPolicyInterface {
        public function allows(\WPEssential\Platform\Modules\ModuleManifest $manifest): bool
        {
            return $manifest->edition === 'free' || $manifest->id === 'status';
        }
    },
);
\WPEssential\Bootstrap\Plugin::registerModule(new \WPEssential\Modules\Status\StatusModule());

add_action('plugins_loaded', static function (): void {
    $database = new \WPEssential\Platform\Database\NativeWpdbAdapter($GLOBALS['wpdb']);
    (new \WPEssential\Platform\Definitions\Migrations\CreateDefinitionTablesMigration($database))->apply();

    $networkId = function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : 1;
    $siteId = max(1, (int) get_current_blog_id());
    $repository = new \WPEssential\Platform\Definitions\PersistentDefinitionRepository(
        new \WPEssential\Platform\Definitions\WpdbDefinitionTableGateway(
            $database,
            \WPEssential\Platform\Definitions\DefinitionScope::site($networkId, $siteId),
        ),
    );

    $statusId = '51111111-1111-4111-8111-111111111111';
    if ($repository->get($statusId) === null) {
        $repository->save(new \WPEssential\Platform\Definitions\Definition(
            id: $statusId,
            slug: 'review-ready',
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: \WPEssential\Platform\Definitions\DefinitionStatus::Published,
            payload: [
                'key' => 'review-ready',
                'labels' => [
                    'label' => 'Review Ready',
                    'count_singular' => 'Review Ready',
                    'count_plural' => 'Review Ready',
                ],
                'visibility' => ['protected' => true],
                'post_types' => ['post'],
            ],
            revision: 1,
        ));
    }

    $policyId = '52222222-2222-4222-8222-222222222222';
    if ($repository->get($policyId) === null) {
        $repository->save(new \WPEssential\Platform\Definitions\Definition(
            id: $policyId,
            slug: 'editorial-status-policy',
            type: 'status-transition-policy',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: \WPEssential\Platform\Definitions\DefinitionStatus::Published,
            payload: [
                'edges' => [[
                    'from' => 'draft',
                    'to' => 'review-ready',
                    'capability' => 'edit_posts',
                    'reason_required' => true,
                    'bulk_allowed' => false,
                    'programmatic_allowed' => false,
                ]],
            ],
            revision: 1,
        ));
    }
}, -200);

require_once $wpeRoot . '/wpessential.php';
PHP);

$muPath = $muDir . '/wpessential-status-reference.php';
if (file_put_contents($muPath, $muPlugin . "\n") === false) {
    fwrite(STDERR, "FAIL: unable to install Status reference mu-plugin\n");
    exit(1);
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $wpDir . '/');
}
require $wpDir . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/user.php';

use RuntimeException;
use WPEssential\Bootstrap\Plugin;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Kernel\Kernel;
use WPEssential\Modules\Status\Events\StatusTransitionEvidencePublisher;
use WPEssential\Modules\Status\StatusModule;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Audit\AuditServices;
use WPEssential\Platform\Audit\AuditTableNames;
use WPEssential\Platform\Audit\PersistentAuditLogger;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\NativeWpdbAdapter;
use WPEssential\Platform\Definitions\PersistentDefinitionRepository;
use WPEssential\Platform\Events\DomainEvent;
use WPEssential\Platform\Events\EventBus;
use WPEssential\Platform\Events\EventServices;
use WPEssential\Platform\Modules\ModuleState;
use WPEssential\Platform\WordPress\Auth\WordPressAuthorizationServices;
use WPEssential\Platform\WordPress\Auth\WordPressPostResourceAuthorizer;

function statusReferenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function statusReferenceThrows(callable $callback, string $message): void
{
    try {
        $callback();
    } catch (Throwable) {
        return;
    }
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

/** @return list<array<string,mixed>> */
function statusReferenceTransitionAuditRows(NativeWpdbAdapter $database, string $auditTable): array
{
    return $database->getResults($database->prepare(
        "SELECT event_uuid, network_id, site_id, actor_type, actor_user_id, channel, correlation_id, owner_surface_id, action, outcome, resource_type, resource_id, reason, metadata_json FROM {$auditTable} WHERE action = %s ORDER BY recorded_at ASC, event_uuid ASC",
        StatusTransitionEvidencePublisher::AUDIT_ACTION,
    ));
}

$admin = get_user_by('login', 'wpessential_status_admin');
$contributor = get_user_by('login', 'wpessential_status_contributor');
statusReferenceExpect($admin instanceof WP_User, 'reference administrator must exist');
statusReferenceExpect($contributor instanceof WP_User, 'reference contributor must exist');
wp_set_current_user($admin->ID);

$kernel = Plugin::kernel();
statusReferenceExpect($kernel instanceof Kernel, 'production Plugin bootstrap must create the Kernel');
statusReferenceExpect($kernel->modules()->state('status') === ModuleState::Booted, 'Pro Status module must boot through explicit activation policy');
$services = $kernel->services();

$definitions = $services->get('platform.definitions');
$abilities = $services->get('platform.abilities');
$capabilityChecker = $services->get(WordPressAuthorizationServices::CAPABILITY_CHECKER);
$postResources = $services->get(WordPressAuthorizationServices::POST_RESOURCES);
$events = $services->get(EventServices::BUS);
$audit = $services->get(AuditServices::LOGGER);
$database = $services->get('platform.database');
statusReferenceExpect($definitions instanceof PersistentDefinitionRepository, 'production persistent Definition repository must back Status');
statusReferenceExpect($abilities instanceof AbilityRegistry, 'production Ability registry must be published');
statusReferenceExpect($capabilityChecker instanceof CapabilityCheckerInterface, 'production capability checker must be published');
statusReferenceExpect($postResources instanceof WordPressPostResourceAuthorizer, 'production post-resource authorizer must be published');
statusReferenceExpect($events instanceof EventBus, 'production canonical EventBus must be published for Status');
statusReferenceExpect($audit instanceof PersistentAuditLogger, 'production persistent Audit logger must be published for Status');
statusReferenceExpect($database instanceof NativeWpdbAdapter, 'production database adapter must be available for Audit evidence verification');
statusReferenceExpect($services->has(StatusModule::SERVICE_TRANSITION_EXECUTOR), 'Status module must publish the production transition executor');
statusReferenceExpect($abilities->descriptor(StatusModule::ABILITY_TRANSITION) !== null, 'Status transition Ability must be registered');

$auditTable = (new AuditTableNames($database))->events;
$baselineAuditRows = statusReferenceTransitionAuditRows($database, $auditTable);
$transitionEvents = [];
$events->listen(
    StatusTransitionEvidencePublisher::EVENT_NAME,
    static function (DomainEvent $event) use (&$transitionEvents): void {
        $postId = $event->payload['post_id'] ?? null;
        $target = $event->payload['to_status'] ?? null;
        statusReferenceExpect(
            is_int($postId) && is_string($target) && get_post_status($postId) === $target,
            'transition Event must be emitted only after the WordPress mutation is committed and observable',
        );
        $transitionEvents[] = $event;
    },
);

$statusObject = get_post_status_object('review-ready');
statusReferenceExpect(is_object($statusObject), 'custom Status must register through the real WordPress init lifecycle');
statusReferenceExpect(($statusObject->label ?? null) === 'Review Ready', 'registered Status must preserve canonical label');
statusReferenceExpect(get_post_status_object('publish') !== null, 'Core publish Status must remain registered');
statusReferenceExpect(get_post_status_object('future') !== null, 'Core future Status must remain registered');
statusReferenceExpect(get_post_status_object('trash') !== null, 'Core trash Status must remain registered');
statusReferenceExpect(get_post_status_object('inherit') !== null, 'Core attachment inherit Status must remain registered');

$postId = wp_insert_post([
    'post_title' => 'Status Reference Draft',
    'post_content' => 'Status reference content',
    'post_status' => 'draft',
    'post_type' => 'post',
    'post_author' => $admin->ID,
], true);
statusReferenceExpect(is_int($postId) && $postId > 0, 'reference draft post must be created');

$siteId = max(1, get_current_blog_id());
$networkId = function_exists('get_current_network_id') ? max(1, (int) get_current_network_id()) : null;
$correlationId = 'status-production-reference-transition';
$context = new ExecutionContext(
    principal: new Principal($admin->ID),
    siteId: $siteId,
    channel: ExecutionChannel::Internal,
    networkId: $networkId,
    correlationId: $correlationId,
);
$result = $abilities->execute(StatusModule::ABILITY_TRANSITION, [
    'post_id' => $postId,
    'expected_current_status' => 'draft',
    'target_status' => 'review-ready',
    'reason' => 'Ready for editorial review.',
    'programmatic' => false,
], $context);
statusReferenceExpect(is_array($result), 'Status Ability must return the bounded result envelope');
statusReferenceExpect(($result['from_status'] ?? null) === 'draft', 'Status Ability must report server-read source status');
statusReferenceExpect(($result['to_status'] ?? null) === 'review-ready', 'Status Ability must report verified target status');
statusReferenceExpect(get_post_status($postId) === 'review-ready', 'WordPress mutation must persist and verify the custom status');

statusReferenceExpect(count($transitionEvents) === 1, 'verified Status transition must emit exactly one production Event');
$transitionEvent = $transitionEvents[0];
statusReferenceExpect($transitionEvent instanceof DomainEvent, 'transition Event must use the canonical DomainEvent contract');
statusReferenceExpect($transitionEvent->name === StatusTransitionEvidencePublisher::EVENT_NAME, 'transition Event must use the canonical Status event name');
statusReferenceExpect($transitionEvent->correlationId === $correlationId, 'transition Event must preserve the execution correlation id');
statusReferenceExpect(($transitionEvent->payload['owner_surface_id'] ?? null) === 5, 'transition Event must remain owned by Surface 5');
statusReferenceExpect(($transitionEvent->payload['post_id'] ?? null) === $postId, 'transition Event must identify the server-mutated post');
statusReferenceExpect(($transitionEvent->payload['post_type'] ?? null) === 'post', 'transition Event must report the server-read post type');
statusReferenceExpect(($transitionEvent->payload['from_status'] ?? null) === 'draft', 'transition Event must report the server-read source Status');
statusReferenceExpect(($transitionEvent->payload['to_status'] ?? null) === 'review-ready', 'transition Event must report the verified target Status');
statusReferenceExpect(($transitionEvent->payload['programmatic'] ?? null) === false, 'transition Event must report the bounded execution mode');
statusReferenceExpect(($transitionEvent->payload['reason_provided'] ?? null) === true, 'transition Event may report only the bounded reason-presence fact');
statusReferenceExpect(($transitionEvent->payload['actor_type'] ?? null) === 'user', 'transition Event must preserve the canonical actor type');
statusReferenceExpect(($transitionEvent->payload['actor_user_id'] ?? null) === $admin->ID, 'transition Event must preserve the server-derived actor id');
statusReferenceExpect(($transitionEvent->payload['site_id'] ?? null) === $siteId, 'transition Event must preserve the execution site scope');
statusReferenceExpect(($transitionEvent->payload['network_id'] ?? null) === $networkId, 'transition Event must preserve the execution network scope');
statusReferenceExpect(($transitionEvent->payload['channel'] ?? null) === ExecutionChannel::Internal->value, 'transition Event must preserve the execution channel');
statusReferenceExpect(!array_key_exists('reason', $transitionEvent->payload), 'transition Event payload must not expose the authored reason');

$afterSuccessAuditRows = statusReferenceTransitionAuditRows($database, $auditTable);
statusReferenceExpect(
    count($afterSuccessAuditRows) === count($baselineAuditRows) + 1,
    'verified Status transition must append exactly one production Audit record',
);
$auditRow = $afterSuccessAuditRows[array_key_last($afterSuccessAuditRows)];
statusReferenceExpect((int) ($auditRow['owner_surface_id'] ?? 0) === 5, 'transition Audit record must remain owned by Surface 5');
statusReferenceExpect(($auditRow['action'] ?? null) === StatusTransitionEvidencePublisher::AUDIT_ACTION, 'transition Audit record must use the canonical action');
statusReferenceExpect(($auditRow['outcome'] ?? null) === 'success', 'transition Audit record must persist the success outcome');
statusReferenceExpect(($auditRow['resource_type'] ?? null) === 'post', 'transition Audit record must use the bounded post resource type');
statusReferenceExpect((string) ($auditRow['resource_id'] ?? '') === (string) $postId, 'transition Audit record must identify the mutated post');
statusReferenceExpect(($auditRow['reason'] ?? null) === 'Ready for editorial review.', 'transition Audit record must preserve the bounded normalized reason');
statusReferenceExpect(($auditRow['correlation_id'] ?? null) === $correlationId, 'transition Audit record must preserve the execution correlation id');
statusReferenceExpect(($auditRow['actor_type'] ?? null) === 'user', 'transition Audit record must preserve the canonical actor type');
statusReferenceExpect((int) ($auditRow['actor_user_id'] ?? 0) === $admin->ID, 'transition Audit record must preserve the actor id');
statusReferenceExpect((int) ($auditRow['site_id'] ?? 0) === $siteId, 'transition Audit record must preserve the execution site id');
statusReferenceExpect((int) ($auditRow['network_id'] ?? 0) === ($networkId ?? 0), 'transition Audit record must preserve the execution network id');
statusReferenceExpect(($auditRow['channel'] ?? null) === ExecutionChannel::Internal->value, 'transition Audit record must preserve the execution channel');
$auditMetadata = json_decode((string) ($auditRow['metadata_json'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
statusReferenceExpect(is_array($auditMetadata), 'transition Audit metadata must decode to a bounded object');
statusReferenceExpect(($auditMetadata['post_type'] ?? null) === 'post', 'transition Audit metadata must contain the server-read post type');
statusReferenceExpect(($auditMetadata['from_status'] ?? null) === 'draft', 'transition Audit metadata must contain the source Status');
statusReferenceExpect(($auditMetadata['to_status'] ?? null) === 'review-ready', 'transition Audit metadata must contain the target Status');
statusReferenceExpect(($auditMetadata['programmatic'] ?? null) === false, 'transition Audit metadata must contain the bounded execution mode');
statusReferenceExpect(($auditMetadata['reason_provided'] ?? null) === true, 'transition Audit metadata must contain only the reason-presence fact');
statusReferenceExpect(!array_key_exists('reason', $auditMetadata), 'transition Audit metadata must not duplicate the authored reason');

statusReferenceThrows(
    static fn () => $abilities->execute(StatusModule::ABILITY_TRANSITION, [
        'post_id' => $postId,
        'expected_current_status' => 'draft',
        'target_status' => 'review-ready',
        'reason' => 'Stale request.',
    ], $context),
    'stale expected status must fail closed',
);
statusReferenceThrows(
    static fn () => $abilities->execute(StatusModule::ABILITY_TRANSITION, [
        'post_id' => $postId,
        'expected_current_status' => 'review-ready',
        'target_status' => 'draft',
        'reason' => 'Undeclared reverse edge.',
    ], $context),
    'undeclared reverse transition must fail closed',
);
statusReferenceThrows(
    static fn () => $abilities->execute(StatusModule::ABILITY_TRANSITION, [
        'post_id' => $postId,
        'expected_current_status' => 'review-ready',
        'target_status' => 'trash',
        'reason' => 'Generic trash attempt.',
    ], $context),
    'Core trash lifecycle must not be reimplemented by the generic Status Ability',
);
statusReferenceExpect(get_post_status($postId) === 'review-ready', 'failed transitions must not mutate the protected post state');

$pageId = wp_insert_post([
    'post_title' => 'Status Reference Page',
    'post_status' => 'draft',
    'post_type' => 'page',
    'post_author' => $admin->ID,
], true);
statusReferenceExpect(is_int($pageId) && $pageId > 0, 'reference page must be created');
statusReferenceThrows(
    static fn () => $abilities->execute(StatusModule::ABILITY_TRANSITION, [
        'post_id' => $pageId,
        'expected_current_status' => 'draft',
        'target_status' => 'review-ready',
        'reason' => 'Wrong post type.',
    ], $context),
    'Status post-type applicability must fail closed',
);
statusReferenceExpect(get_post_status($pageId) === 'draft', 'applicability failure must not mutate the page');

$foreignPostId = wp_insert_post([
    'post_title' => 'Contributor Cannot Edit This',
    'post_status' => 'draft',
    'post_type' => 'post',
    'post_author' => $admin->ID,
], true);
statusReferenceExpect(is_int($foreignPostId) && $foreignPostId > 0, 'authorization reference post must be created');
wp_set_current_user($contributor->ID);
$contributorContext = new ExecutionContext(
    principal: new Principal($contributor->ID),
    siteId: $siteId,
    channel: ExecutionChannel::Internal,
    networkId: $networkId,
);
statusReferenceThrows(
    static fn () => $abilities->execute(StatusModule::ABILITY_TRANSITION, [
        'post_id' => $foreignPostId,
        'expected_current_status' => 'draft',
        'target_status' => 'review-ready',
        'reason' => 'Unauthorized attempt.',
    ], $contributorContext),
    'object-level authorization denial must fail before transition state mutation',
);
statusReferenceExpect(get_post_status($foreignPostId) === 'draft', 'authorization denial must preserve server state');

wp_set_current_user($admin->ID);
$scopeMismatch = new ExecutionContext(
    principal: new Principal($admin->ID),
    siteId: $siteId + 1,
    channel: ExecutionChannel::Internal,
    networkId: $networkId,
);
statusReferenceThrows(
    static fn () => $abilities->execute(StatusModule::ABILITY_TRANSITION, [
        'post_id' => $foreignPostId,
        'expected_current_status' => 'draft',
        'target_status' => 'review-ready',
        'reason' => 'Scope widening attempt.',
    ], $scopeMismatch),
    'site scope mismatch must fail through the canonical WordPress authorization services',
);
statusReferenceExpect(get_post_status($foreignPostId) === 'draft', 'scope mismatch must preserve server state');

$finalAuditRows = statusReferenceTransitionAuditRows($database, $auditTable);
statusReferenceExpect(count($transitionEvents) === 1, 'all failed Status reference paths must emit zero additional transition Events');
statusReferenceExpect(
    count($finalAuditRows) === count($baselineAuditRows) + 1,
    'all failed Status reference paths must append zero additional transition Audit records',
);

fwrite(STDOUT, "WPEssential Status production Event/Audit reference PASS\n");
