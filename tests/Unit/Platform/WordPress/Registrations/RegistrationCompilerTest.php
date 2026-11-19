<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\WordPress\Registrations;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationManifest;
use WPEssential\Platform\WordPress\Registrations\CompiledRegistrationStoreInterface;
use WPEssential\Platform\WordPress\Registrations\InMemoryCompiledRegistrationStore;
use WPEssential\Platform\WordPress\Registrations\RegistrationCompiler;
use WPEssential\Platform\WordPress\Registrations\RegistrationDefinition;
use WPEssential\Platform\WordPress\Registrations\RegistrationKind;

final class RegistrationCompilerTest extends TestCase
{
    public function testChangeAwareCompileDoesNotCreateGenerationForIdenticalManifest(): void
    {
        $store = new InMemoryCompiledRegistrationStore();
        $compiler = new RegistrationCompiler($store);
        $definition = new RegistrationDefinition(
            'definition-1',
            RegistrationKind::PostType,
            'book',
            ['public' => true],
        );

        $first = $compiler->compileAndPublishIfChanged([$definition]);
        $same = $compiler->compileAndPublishIfChanged([$definition]);
        $changed = $compiler->compileAndPublishIfChanged([
            new RegistrationDefinition('definition-1', RegistrationKind::PostType, 'book', ['public' => false], true, 2),
        ]);
        $empty = $compiler->compileAndPublishIfChanged([]);
        $sameEmpty = $compiler->compileAndPublishIfChanged([]);

        self::assertSame(1, $first?->generation);
        self::assertSame(1, $same?->generation);
        self::assertSame(2, $changed?->generation);
        self::assertSame(3, $empty?->generation);
        self::assertSame(3, $sameEmpty?->generation);
    }

    public function testStoreReturningSortedKeysDoesNotCreateGenerationPerRequest(): void
    {
        // The database store hands manifests back with every object's keys sorted.
        $store = new class implements CompiledRegistrationStoreInterface {
            public ?CompiledRegistrationManifest $manifest = null;

            public function active(): ?CompiledRegistrationManifest
            {
                if ($this->manifest === null) {
                    return null;
                }
                $sort = static function (array $value) use (&$sort): array {
                    foreach ($value as $key => $item) {
                        if (is_array($item)) {
                            $value[$key] = $sort($item);
                        }
                    }
                    if (!array_is_list($value)) {
                        ksort($value, SORT_STRING);
                    }
                    return $value;
                };
                return new CompiledRegistrationManifest($this->manifest->generation, $sort($this->manifest->entries), $this->manifest->checksum);
            }

            public function publish(CompiledRegistrationManifest $manifest): void
            {
                $this->manifest = $manifest;
            }
        };
        $compiler = new RegistrationCompiler($store);
        $definition = new RegistrationDefinition('definition-1', RegistrationKind::PostType, 'book', ['public' => true]);

        self::assertSame(1, $compiler->compileAndPublishIfChanged([$definition])?->generation);
        self::assertSame(1, $compiler->compileAndPublishIfChanged([$definition])?->generation);
    }

    public function testEmptyInitialManifestDoesNotCreateUnnecessaryGeneration(): void
    {
        $store = new InMemoryCompiledRegistrationStore();
        $compiler = new RegistrationCompiler($store);

        self::assertNull($compiler->compileAndPublishIfChanged([]));
        self::assertNull($store->active());
    }
}
