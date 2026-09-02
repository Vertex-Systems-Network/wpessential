<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'WPEssential\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = dirname(__DIR__, 2) . '/frameworks/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

use WPEssential\Platform\DataSources\DataSourceAvailability;
use WPEssential\Platform\DataSources\DataSourceDescriptor;
use WPEssential\Platform\DataSources\DataSourceRegistry;

function dataSourceExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$registry = new DataSourceRegistry();
$descriptor = new DataSourceDescriptor(
    id: 'wordpress.posts',
    sourceType: 'wordpress.posts',
    capabilityVersion: 1,
    fieldSchema: ['post.id' => 'integer', 'post.title' => 'string'],
    predicates: ['eq', 'contains'],
    sortModes: ['field'],
    paginationModes: ['offset'],
    scopes: ['site'],
    maxPageSize: 100,
    maxBatchSize: 100,
    cacheable: true,
    cacheGenerationKeys: ['posts.generation'],
);
$registry->register($descriptor);

dataSourceExpect($registry->require('wordpress.posts') === $descriptor, 'registered source must resolve by stable id');
dataSourceExpect($descriptor->policyRequired, 'data source must require canonical Policy authorization');
dataSourceExpect($descriptor->isAvailable(), 'available source must report available');

try {
    $registry->register($descriptor);
    dataSourceExpect(false, 'duplicate data source registration must fail closed');
} catch (RuntimeException) {
    // expected
}

$degraded = new DataSourceDescriptor(
    id: 'remote.crm',
    sourceType: 'remote.provider',
    capabilityVersion: 1,
    fieldSchema: ['record.id' => 'integer'],
    availability: DataSourceAvailability::Degraded,
    degradedReason: 'provider_not_connected',
);
dataSourceExpect(!$degraded->isAvailable(), 'degraded optional provider must not be executable as available');

$bootstrap = file_get_contents(dirname(__DIR__, 2) . '/frameworks/Bootstrap/Plugin.php');
dataSourceExpect(is_string($bootstrap), 'bootstrap source must be readable');
dataSourceExpect(str_contains($bootstrap, 'new DataSourceRegistry()'), 'bootstrap must construct canonical data source registry');
dataSourceExpect(str_contains($bootstrap, "set('platform.data-sources', \$dataSources)"), 'bootstrap must expose canonical data source registry service');

fwrite(STDOUT, "WPEssential data source contract smoke PASS\n");
