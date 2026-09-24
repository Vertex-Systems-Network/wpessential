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

use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Kernel\ServiceRegistry;
use WPEssential\Modules\Query\QueryModule;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\DataSources\DataSourceRegistry;

final class QueryProActivationCapabilityChecker implements CapabilityCheckerInterface
{
    public function can(ExecutionContext $context, string $capability): bool
    {
        return true;
    }
}

function expect_query_pro_activation(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$root = dirname(__DIR__, 2);
$proBootstrap = $root . '/wpessential-pro.php';
$source = file_get_contents($proBootstrap);
expect_query_pro_activation(is_string($source), 'wpessential-pro.php must be readable');

$queryContribution = '\WPEssential\Modules\Query\QueryModule::class';
$dashboardContribution = '\WPEssential\Modules\DashboardWidgets\DashboardWidgetsModule::class';

expect_query_pro_activation(
    substr_count($source, $queryContribution) === 1,
    'QueryModule must be contributed exactly once',
);
expect_query_pro_activation(
    substr_count($source, $dashboardContribution) === 1,
    'DashboardWidgetsModule must be contributed exactly once',
);

$moduleListStart = strpos($source, '$moduleClasses = [');
$moduleListEnd = $moduleListStart === false ? false : strpos($source, '];', $moduleListStart);
expect_query_pro_activation(
    $moduleListStart !== false && $moduleListEnd !== false,
    'Pro module contribution list must remain explicit and bounded',
);

$moduleList = substr($source, $moduleListStart, ($moduleListEnd - $moduleListStart) + 2);
$queryPosition = strpos($moduleList, $queryContribution);
$dashboardPosition = strpos($moduleList, $dashboardContribution);
expect_query_pro_activation(
    $queryPosition !== false && $dashboardPosition !== false && $queryPosition < $dashboardPosition,
    'QueryModule must precede DashboardWidgetsModule in the Pro contribution list',
);

$compatibilityGate = strpos($source, "if ((\$compatibility['state'] ?? '') !== 'compatible')");
$registrationLoop = strpos(
    $source,
    '\WPEssential\Bootstrap\Plugin::registerModule(new $moduleClass())',
);
expect_query_pro_activation(
    $compatibilityGate !== false
        && $registrationLoop !== false
        && $compatibilityGate < $registrationLoop,
    'Pro module contribution must remain behind the compatible preflight gate',
);

$services = new ServiceRegistry();
$services->set('platform.data-sources', new DataSourceRegistry());
$services->set(
    'platform.abilities.policy',
    new PolicyEngine(new QueryProActivationCapabilityChecker()),
);

$query = new QueryModule();
$query->register($services);

expect_query_pro_activation(
    $services->has(QueryModule::SERVICE_READ_CONSUMER),
    'QueryModule registration must publish the canonical read-consumer service',
);
expect_query_pro_activation(
    $services->get(QueryModule::SERVICE_READ_CONSUMER) instanceof QueryReadConsumerInterface,
    'Query read-consumer service must implement the canonical public contract',
);

fwrite(STDOUT, "WPEssential Query Pro activation contract PASS\n");
