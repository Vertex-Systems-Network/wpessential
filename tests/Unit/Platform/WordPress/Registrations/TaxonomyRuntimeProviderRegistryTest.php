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

    public function testAcceptsQualifiedClassAndStaticCallbackDescriptorShapesWithoutResolvingThemEarly(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();

        $registry->registerRestController('vendor.rest', 'Vendor\\Package\\TermsController');
        $registry->registerTermCountProvider('vendor.counter', 'Vendor\\Package\\TermCounter::update');

        self::assertTrue($registry->hasRestController('vendor.rest'));
        self::assertTrue($registry->hasTermCountProvider('vendor.counter'));
    }

    public function testProviderCatalogIsDeterministicJsonSafeAndDescriptorRedacted(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();
        $registry->registerRestController('test.rest', TaxonomyRuntimeProviderTestController::class);
        $registry->registerMetaBoxProvider('test.editor', static function (): void {});
        $registry->registerMetaBoxSanitizeProvider('test.sanitize', static fn (mixed $terms): mixed => $terms);
        $registry->registerTermCountProvider('vendor.counter', 'Vendor\\Package\\TermCounter::update');

        $catalog = $registry->catalog();

        self::assertSame(['rest_controller', 'meta_box', 'meta_box_sanitize', 'term_count'], array_keys($catalog));
        self::assertContains(['id' => 'test.rest', 'available' => true], $catalog['rest_controller']);
        self::assertContains(['id' => 'wordpress.disabled', 'available' => true], $catalog['meta_box']);
        self::assertContains(['id' => 'test.editor', 'available' => true], $catalog['meta_box']);
        self::assertContains(['id' => 'test.sanitize', 'available' => true], $catalog['meta_box_sanitize']);
        self::assertSame([['id' => 'vendor.counter', 'available' => false]], $catalog['term_count']);

        $encoded = json_encode($catalog, JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString('TaxonomyRuntimeProviderTestController', $encoded);
        self::assertStringNotContainsString('Vendor\\Package\\TermCounter::update', $encoded);
    }

    public function testProviderCatalogSortsIdsWithinEachSlot(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();
        $registry->registerMetaBoxProvider('vendor.zeta', false);
        $registry->registerMetaBoxProvider('vendor.alpha', false);

        self::assertSame(
            ['vendor.alpha', 'vendor.zeta', 'wordpress.disabled'],
            array_column($registry->catalog()['meta_box'], 'id'),
        );
    }

    public function testRejectsMalformedQualifiedClassName(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();

        $this->expectException(InvalidArgumentException::class);
        $registry->registerRestController('vendor.bad-rest', 'Vendor\\\\BrokenController');
    }

    public function testRejectsMalformedStaticCallbackDescriptor(): void
    {
        $registry = new TaxonomyRuntimeProviderRegistry();

        $this->expectException(InvalidArgumentException::class);
        $registry->registerTermCountProvider('vendor.bad-counter', 'Vendor\\Counter::bad-method!');
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
