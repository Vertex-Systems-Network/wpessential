<?php
/**
 * Validates the WordPress.org Live Preview blueprint (wordpress-org/assets/blueprints/blueprint.json).
 * WordPress.org shows "Missing or invalid blueprint.json file" when the SVN copy is absent or broken.
 *
 * Usage: php tools/release/check-blueprint.php [path]
 */

declare(strict_types=1);

$path = $argv[1] ?? dirname(__DIR__, 2) . '/wordpress-org/assets/blueprints/blueprint.json';
$fail = static function (string $message): never {
    fwrite(STDERR, "blueprint.json: {$message}\n");
    exit(1);
};

if (!is_readable($path)) {
    $fail("missing at {$path}");
}
try {
    $blueprint = json_decode((string) file_get_contents($path), true, 64, JSON_THROW_ON_ERROR);
} catch (JsonException $error) {
    $fail('invalid JSON: ' . $error->getMessage());
}
if (!is_array($blueprint)) {
    $fail('must be a JSON object');
}
if (($blueprint['$schema'] ?? '') !== 'https://playground.wordpress.net/blueprint-schema.json') {
    $fail('$schema must be https://playground.wordpress.net/blueprint-schema.json');
}
if (!is_string($blueprint['landingPage'] ?? null) || !str_starts_with($blueprint['landingPage'], '/')) {
    $fail('landingPage must be a site path such as /wp-admin/admin.php?page=wpessential');
}
$installs = array_filter(
    (array) ($blueprint['steps'] ?? []),
    static fn (mixed $step): bool => is_array($step)
        && ($step['step'] ?? '') === 'installPlugin'
        && ($step['pluginData']['resource'] ?? '') === 'wordpress.org/plugins'
        && ($step['pluginData']['slug'] ?? '') === 'wpessential'
        && ($step['options']['activate'] ?? false) === true,
);
if ($installs === []) {
    $fail('steps must install and activate the wordpress.org/plugins "wpessential" plugin');
}

echo "blueprint.json OK\n";
