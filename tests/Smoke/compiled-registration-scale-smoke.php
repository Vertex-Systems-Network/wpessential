<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

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

use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifest;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifestIntegrity;
use WPEssential\Platform\WordPress\Registrations\InMemoryCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;
use WPEssential\Platform\WordPress\Registrations\RegistrationRuntimeLoader;

function scaleExpect(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @return Generator<int, RegistrationDefinition> */
function scaleDefinitions(int $count, bool $reverse = false): Generator
{
    $kinds = RegistrationKind::cases();
    for ($offset = 0; $offset < $count; $offset++) {
        $index = $reverse ? ($count - 1 - $offset) : $offset;
        $kind = $kinds[$index % count($kinds)];
        $key = sprintf('scale-%06d', $index);
        yield new RegistrationDefinition(
            'scale-' . $index,
            $kind,
            $key,
            [
                'label' => 'Scale ' . $index,
                'enabled' => true,
                'nested' => ['z' => $index % 17, 'a' => $index % 7],
            ],
            true,
            1,
        );
    }
}

/** @return array{count:int,generation:int,checksum:string,seconds:float,peak_bytes:int,kind_counts:array<string,int>} */
function certifyScale(int $count): array
{
    $store = new InMemoryCompiledRegistrationStore();
    $compiler = new RegistrationCompiler($store);
    $runtime = new RegistrationRuntimeLoader($store);

    $started = hrtime(true);
    $manifest = $compiler->compileAndPublish(scaleDefinitions($count));
    $elapsed = (hrtime(true) - $started) / 1_000_000_000;

    scaleExpect($manifest instanceof CompiledRegistrationManifest, "{$count} manifest must be produced");
    scaleExpect($manifest->generation === 1, "{$count} first generation must be 1");
    scaleExpect(CompiledRegistrationManifestIntegrity::verify($manifest), "{$count} manifest integrity must verify");
    scaleExpect($store->active()?->checksum === $manifest->checksum, "{$count} active manifest checksum mismatch");

    $kindCounts = [];
    $total = 0;
    foreach (RegistrationKind::cases() as $kind) {
        $entries = $runtime->forKind($kind);
        $kindCounts[$kind->value] = count($entries);
        $total += count($entries);
    }
    scaleExpect($total === $count, "{$count} runtime entry count mismatch: {$total}");

    $firstKey = 'scale-000000';
    $lastKey = sprintf('scale-%06d', $count - 1);
    $firstKind = RegistrationKind::cases()[0]->value;
    $lastKind = RegistrationKind::cases()[($count - 1) % count(RegistrationKind::cases())]->value;
    scaleExpect(isset($manifest->entries[$firstKind][$firstKey]), "{$count} first registration missing");
    scaleExpect(isset($manifest->entries[$lastKind][$lastKey]), "{$count} last registration missing");

    return [
        'count' => $count,
        'generation' => $manifest->generation,
        'checksum' => $manifest->checksum,
        'seconds' => round($elapsed, 6),
        'peak_bytes' => memory_get_peak_usage(true),
        'kind_counts' => $kindCounts,
    ];
}

$tenThousand = certifyScale(10_000);
scaleExpect($tenThousand['seconds'] <= 15.0, '10K compilation exceeded 15 second certification budget');
scaleExpect($tenThousand['peak_bytes'] <= 256 * 1024 * 1024, '10K compilation exceeded 256 MiB peak-memory budget');

// Canonical sorting must make the compiled payload stable regardless of definition iteration order.
$forwardStore = new InMemoryCompiledRegistrationStore();
$reverseStore = new InMemoryCompiledRegistrationStore();
$forward = (new RegistrationCompiler($forwardStore))->compileAndPublish(scaleDefinitions(10_000));
$reverse = (new RegistrationCompiler($reverseStore))->compileAndPublish(scaleDefinitions(10_000, true));
scaleExpect($forward->checksum === $reverse->checksum, '10K checksum must be deterministic across input ordering');
unset($forwardStore, $reverseStore, $forward, $reverse);
gc_collect_cycles();

$hundredThousand = certifyScale(100_000);
scaleExpect($hundredThousand['seconds'] <= 45.0, '100K compilation exceeded 45 second certification budget');
scaleExpect($hundredThousand['peak_bytes'] <= 768 * 1024 * 1024, '100K compilation exceeded 768 MiB peak-memory budget');

fwrite(STDOUT, json_encode([
    'status' => 'PASS',
    'contract' => 'compiled-registration-scale-v1',
    'cases' => [$tenThousand, $hundredThousand],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
