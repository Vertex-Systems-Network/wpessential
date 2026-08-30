<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\WordPress\Registrations;

use PHPUnit\Framework\TestCase;
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

    public function testEmptyInitialManifestDoesNotCreateUnnecessaryGeneration(): void
    {
        $store = new InMemoryCompiledRegistrationStore();
        $compiler = new RegistrationCompiler($store);

        self::assertNull($compiler->compileAndPublishIfChanged([]));
        self::assertNull($store->active());
    }
}
