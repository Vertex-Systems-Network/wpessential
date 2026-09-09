<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\WordPress\Registrations;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Platform\WordPress\Registrations\TaxonomyRuntimeProviderRegistry;

final class TaxonomyRuntimeProviderRegistryTest extends TestCase
{
    public function testBuiltInRegistryIsSafeToConstructBeforeAdminCallbacksAreLoaded(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();

        self::assertTrue($registry->hasRestController('wordpress.terms'));
        self::assertTrue($registry->hasMetaBoxProvider('wordpress.disabled'));
        self::assertFalse($registry->hasMetaBoxProvider('wordpress.categories'));
        self::assertFalse($registry->hasTermCountProvider('wordpress.post_terms'));
    }

    public function testAppliesRegisteredProviderIdsOnlyAtRuntimeBoundary(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();
        $editor = static function (): void {};
        $sanitize = static fn (mixed $terms): mixed => $terms;
        $counter = static function (): void {};

        $registry->registerRestController('test.rest', TaxonomyRuntimeProviderTestController::class);
        $registry->registerMetaBoxProvider('test.editor', $editor);
        $registry->registerMetaBoxSanitizeProvider('test.sanitize', $sanitize);
        $registry->registerTermCountProvider('test.counter', $counter);

        $args = $registry->apply(
            ['show_in_rest' => true],
            [
                'rest_controller' => 'test.rest',
                'meta_box' => 'test.editor',
                'meta_box_sanitize' => 'test.sanitize',
                'term_count' => 'test.counter',
            ],
        );

        self::assertSame(TaxonomyRuntimeProviderTestController::class, $args['rest_controller_class']);
        self::assertSame($editor, $args['meta_box_cb']);
        self::assertSame($sanitize, $args['meta_box_sanitize_cb']);
        self::assertSame($counter, $args['update_count_callback']);
    }

    public function testDisabledMetaBoxProviderResolvesToExplicitFalse(): void
    {
        $args = (new TaxonomyRuntimeProviderRegistry())->apply([], ['meta_box' => 'wordpress.disabled']);

        self::assertArrayHasKey('meta_box_cb', $args);
        self::assertFalse($args['meta_box_cb']);
    }

    public function testDuplicateDisabledProviderIdIsRejected(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();

        $this->expectException(InvalidArgumentException::class);
        $registry->registerMetaBoxProvider('wordpress.disabled', false);
    }

    public function testInvalidTrustedCallbackDescriptorIsRejected(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();

        $this->expectException(InvalidArgumentException::class);
        $registry->registerTermCountProvider('vendor.bad', 'function name with spaces');
    }

    public function testUnknownPersistedProviderFailsClosed(): void
    {
        $this->expectException(RuntimeException::class);
        (new TaxonomyRuntimeProviderRegistry())->apply([], ['term_count' => 'missing.provider']);
    }

    public function testUnknownProviderSlotIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new TaxonomyRuntimeProviderRegistry())->apply([], ['raw_callback' => 'anything']);
    }
}

final class TaxonomyRuntimeProviderTestController
{
}
