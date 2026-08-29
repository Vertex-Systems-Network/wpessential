<?php

declare(strict_types=1);


if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
spl_autoload_register(static function (string $class) use ($root): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) return;
    $relative = substr($class, strlen($prefix));
    $path = $root . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) require $path;
});

use WPEssential\Platform\WordPress\Registrations\AtomicCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifest;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifestIntegrity;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationScope;
use WPEssential\Platform\WordPress\Registrations\InMemoryCompiledRegistrationPersistenceGateway;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;

function compiledPersistenceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$gateway = new InMemoryCompiledRegistrationPersistenceGateway();
$scope = CompiledRegistrationScope::site(1, 11);
$store = new AtomicCompiledRegistrationStore($gateway, $scope);
$compiler = new RegistrationCompiler($store);
$runtime = new RegistrationRuntimeLoader($store);

$generation1 = $compiler->compileAndPublish([
    new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true]),
]);
compiledPersistenceExpect($generation1->generation === 1, 'first atomic generation must be 1');

$generation2 = $compiler->compileAndPublish([
    new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true], true, 2),
    new RegistrationDefinition('tax-1', RegistrationKind::Taxonomy, 'genre', ['object_type' => ['book']]),
]);
$pointer = $gateway->pointer($scope);
compiledPersistenceExpect($pointer->activeGeneration === 2 && $pointer->fallbackGeneration === 1, 'publish must retain previous generation as last-known-good');
compiledPersistenceExpect(isset($runtime->forKind(RegistrationKind::Taxonomy)['genre']), 'runtime must read active compiled generation only');

$otherSite = new AtomicCompiledRegistrationStore($gateway, CompiledRegistrationScope::site(1, 12));
$networkScope = new AtomicCompiledRegistrationStore($gateway, CompiledRegistrationScope::network(1));
compiledPersistenceExpect($otherSite->active() === null, 'compiled registrations must be site isolated');
compiledPersistenceExpect($networkScope->active() === null, 'network scope must not alias a site scope');

$gateway->failNextPublishAfterStageForTesting();
try {
    $compiler->compileAndPublish([
        new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => false], true, 3),
    ]);
    compiledPersistenceExpect(false, 'simulated staged publication failure must surface');
} catch (RuntimeException) {
}
compiledPersistenceExpect($gateway->pointer($scope)->activeGeneration === 2, 'failed staged publication must not move active pointer');
compiledPersistenceExpect(!$gateway->hasGenerationForTesting($scope, 3), 'failed staged publication must roll back staged generation');
compiledPersistenceExpect($gateway->latestGeneration($scope) === 2, 'rolled-back staged generation must not advance historical high-watermark');

$generation3 = $compiler->compileAndPublish([
    new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => false], true, 3),
]);
compiledPersistenceExpect($generation3->generation === 3, 'successful retry after rollback must publish generation 3');
$gateway->tamperGenerationChecksumForTesting($scope, 3);
$recovered = $store->active();
compiledPersistenceExpect($recovered?->generation === 2, 'corrupt active generation must recover to verified last-known-good');
$recoveredPointer = $gateway->pointer($scope);
compiledPersistenceExpect($recoveredPointer->activeGeneration === 2 && $recoveredPointer->fallbackGeneration === 1, 'recovery must atomically move pointer backward and preserve older fallback');
compiledPersistenceExpect(isset($gateway->corruptionsForTesting($scope)[3]), 'corrupt active generation must be quarantined');
compiledPersistenceExpect($store->nextGeneration() === 4, 'recovery must not reuse quarantined generation number 3');

$generation4 = $compiler->compileAndPublish([
    new RegistrationDefinition('pt-1', RegistrationKind::PostType, 'book', ['public' => true], true, 4),
]);
compiledPersistenceExpect($generation4->generation === 4, 'post-recovery compilation must advance historical high-watermark to generation 4');
compiledPersistenceExpect($gateway->pointer($scope)->fallbackGeneration === 2, 'post-recovery publication must retain recovered active generation as fallback');

$entries = ['post_type' => ['movie' => ['id' => 'pt-2', 'revision' => 1, 'payload' => ['public' => true]]]];
$staleManifest = new CompiledRegistrationManifest(5, $entries, CompiledRegistrationManifestIntegrity::checksum(5, $entries));
compiledPersistenceExpect(!$gateway->publishAtomically($scope, 2, $staleManifest), 'gateway compare-and-swap must reject stale active-pointer expectations before publication');
compiledPersistenceExpect($gateway->latestGeneration($scope) === 4, 'failed stale CAS must not consume generation 5');

$freshScope = CompiledRegistrationScope::site(1, 22);
$freshStore = new AtomicCompiledRegistrationStore($gateway, $freshScope);
$freshCompiler = new RegistrationCompiler($freshStore);
$freshCompiler->compileAndPublish([new RegistrationDefinition('pt-a', RegistrationKind::PostType, 'alpha', ['public' => true])]);
$freshCompiler->compileAndPublish([new RegistrationDefinition('pt-a', RegistrationKind::PostType, 'alpha', ['public' => false], true, 2)]);
$gateway->tamperGenerationChecksumForTesting($freshScope, 2);
$gateway->tamperGenerationChecksumForTesting($freshScope, 1);
try {
    $freshStore->active();
    compiledPersistenceExpect(false, 'corrupt active and fallback generations must fail closed');
} catch (RuntimeException) {
}
compiledPersistenceExpect($freshStore->nextGeneration() === 3, 'failed recovery must still preserve immutable high-watermark sequencing');

fwrite(STDOUT, "WPEssential compiled registration persistence smoke PASS\n");
