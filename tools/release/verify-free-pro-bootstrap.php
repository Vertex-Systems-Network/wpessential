<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This verifier is CLI-only.\n");
    exit(1);
}

$mode = $argv[1] ?? '';
$freeRoot = isset($argv[2]) ? realpath($argv[2]) : false;
$proRoot = isset($argv[3]) ? realpath($argv[3]) : false;
$order = $argv[4] ?? 'free-first';

if (!in_array($mode, ['free', 'free-pro'], true)) {
    fwrite(STDERR, "Usage: php verify-free-pro-bootstrap.php <free|free-pro> <free-root> [pro-root] [free-first|pro-first]\n");
    exit(1);
}

if ($freeRoot === false || !is_file($freeRoot . '/wpessential.php')) {
    fwrite(STDERR, "Free package root is invalid.\n");
    exit(1);
}

if ($mode === 'free-pro') {
    if ($proRoot === false || !is_file($proRoot . '/wpessential-pro.php')) {
        fwrite(STDERR, "Pro package root is invalid.\n");
        exit(1);
    }
    if (!in_array($order, ['free-first', 'pro-first'], true)) {
        fwrite(STDERR, "Load order must be free-first or pro-first.\n");
        exit(1);
    }
}

if (!defined('ABSPATH')) {
    define('ABSPATH', $freeRoot . '/');
}

$GLOBALS['wpe_bootstrap_verifier_actions'] = [];
$GLOBALS['wpe_bootstrap_verifier_sequence'] = 0;

if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $GLOBALS['wpe_bootstrap_verifier_actions'][$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs,
            'sequence' => $GLOBALS['wpe_bootstrap_verifier_sequence']++,
        ];
        return true;
    }
}

if (!function_exists('get_bloginfo')) {
    function get_bloginfo(string $show = ''): string
    {
        return $show === 'version' ? '6.9' : '';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url(string $file): string
    {
        return 'https://example.test/wp-content/plugins/' . basename(dirname($file)) . '/';
    }
}

if (!function_exists('esc_html')) {
    function esc_html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

$includeFree = static function () use ($freeRoot): void {
    require $freeRoot . '/wpessential.php';
};

$includePro = static function () use ($proRoot): void {
    require $proRoot . '/wpessential-pro.php';
};

if ($mode === 'free') {
    $includeFree();
} elseif ($order === 'free-first') {
    $includeFree();
    $includePro();
} else {
    $includePro();
    $includeFree();
}

$pluginsLoaded = $GLOBALS['wpe_bootstrap_verifier_actions']['plugins_loaded'] ?? [];
usort(
    $pluginsLoaded,
    static function (array $left, array $right): int {
        $priority = $left['priority'] <=> $right['priority'];
        return $priority !== 0 ? $priority : ($left['sequence'] <=> $right['sequence']);
    }
);

if ($pluginsLoaded === []) {
    fwrite(STDERR, "No plugins_loaded callbacks were registered.\n");
    exit(1);
}

foreach ($pluginsLoaded as $action) {
    ($action['callback'])();
}

if (!class_exists(\WPEssential\Bootstrap\Plugin::class)) {
    fwrite(STDERR, "Free Platform bootstrap class did not resolve.\n");
    exit(1);
}

$kernel = \WPEssential\Bootstrap\Plugin::kernel();
if (!$kernel instanceof \WPEssential\Kernel\Kernel || !$kernel->isBooted()) {
    fwrite(STDERR, "WPEssential kernel did not boot.\n");
    exit(1);
}

$modules = $kernel->modules();
foreach (['custom-post-types', 'taxonomies'] as $requiredFreeModule) {
    if (!$modules->has($requiredFreeModule)) {
        fwrite(STDERR, "Missing required Free module: {$requiredFreeModule}\n");
        exit(1);
    }
}

$implementedProModules = [
    'roles',
    'admin-menu',
    'settings',
    'dashboard',
    'profiles',
    'membership',
    'builder-widgets',
    'forms-workflows',
    'cron',
    'notifications',
    'emails',
    'chat',
];

if ($mode === 'free') {
    foreach ($implementedProModules as $proModule) {
        if ($modules->has($proModule)) {
            fwrite(STDERR, "Free-only boot registered Pro module: {$proModule}\n");
            exit(1);
        }
    }

    if (class_exists(\WPEssential\Modules\Membership\MembershipModule::class)) {
        fwrite(STDERR, "Free-only package unexpectedly resolves Pro Membership source.\n");
        exit(1);
    }

    if (defined('WPE_PRO_PACKAGE_ACTIVE')) {
        fwrite(STDERR, "Free-only boot unexpectedly marked the Pro package active.\n");
        exit(1);
    }
} else {
    if (!defined('WPE_PRO_PACKAGE_ACTIVE') || WPE_PRO_PACKAGE_ACTIVE !== true) {
        fwrite(STDERR, "Free+Pro boot did not mark the Pro package active.\n");
        exit(1);
    }

    foreach ($implementedProModules as $proModule) {
        if (!$modules->has($proModule)) {
            fwrite(STDERR, "Free+Pro boot did not register implemented Pro module: {$proModule}\n");
            exit(1);
        }
    }

    foreach ([
        \WPEssential\Modules\Membership\MembershipModule::class,
        \WPEssential\Modules\CustomTables\Migration\Run\Persistence\CreateMigrationRunStoreMigration::class,
    ] as $requiredProClass) {
        if (!class_exists($requiredProClass)) {
            fwrite(STDERR, "Free+Pro package could not autoload Pro class: {$requiredProClass}\n");
            exit(1);
        }
    }
}

fwrite(STDOUT, sprintf(
    "[bootstrap-verify] PASS mode=%s order=%s free=%s pro=%s\n",
    $mode,
    $mode === 'free' ? 'n/a' : $order,
    $freeRoot,
    $mode === 'free-pro' ? $proRoot : 'n/a',
));
