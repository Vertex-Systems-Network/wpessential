<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Kit;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Elements\ElementRegistry;
use WPEssential\Platform\Kit\Kit;

/**
 * The kit moved from the Hi WPEssential theme (ADR-0238): Free ships the engine and the basic
 * elements, WPEssential Pro adds the advanced ones through `wpessential_kit_roots`.
 */
final class KitTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    protected function setUp(): void
    {
        Kit::boot( self::ROOT, 'https://example.test/wp-content/plugins/wpessential/', '3.0.0', new ElementRegistry() );
    }

    public function testFreeShipsTheBasicElementsWithAWidgetEach(): void
    {
        $defs = Kit::defs();
        self::assertCount( 49, $defs );
        foreach ( $defs as $name => $def ) {
            self::assertSame( 'free', $def['edition'], $name );
            self::assertFileExists( $def['dir'] . '/render.php', $name );
            self::assertFileExists( $def['dir'] . '/style.css', $name );
            self::assertSame( 'https://example.test/wp-content/plugins/wpessential/kit/' . $name, $def['url'] );
            $class = $def['namespace'] . '\\' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $name ) ) );
            self::assertFileExists( self::ROOT . '/frameworks/Platform/Kit/Elements/' . substr( strrchr( $class, '\\' ), 1 ) . '.php', $name );
        }
    }

    public function testProElementsAreListedAsProButNotShippedInFree(): void
    {
        $catalog = json_decode( (string) file_get_contents( self::ROOT . '/kit/pro-catalog.json' ), true );
        self::assertIsArray( $catalog );
        self::assertCount( 17, $catalog );
        foreach ( array_keys( $catalog ) as $name ) {
            self::assertDirectoryDoesNotExist( self::ROOT . '/kit/' . $name, 'Pro element shipped in Free: ' . $name );
            self::assertArrayNotHasKey( $name, Kit::defs() );
        }

        $byKey = [];
        foreach ( Kit::elements( [] ) as $element ) {
            $byKey[ $element['type'] . ':' . $element['id'] ] = $element['edition'];
        }
        self::assertCount( 2 * ( 49 + 17 ), $byKey, 'One Gutenberg block and one Elementor widget per element.' );
        self::assertSame( 'free', $byKey['gutenberg:accordion'] );
        self::assertSame( 'pro', $byKey['elementor:countdown'] );
    }

    public function testEveryElementDefinitionIsValid(): void
    {
        foreach ( Kit::defs() as $name => $def ) {
            self::assertSame( $name, basename( $def['dir'] ) );
            self::assertNotSame( '', (string) $def['title'] );
            self::assertIsArray( $def['attributes'] );
        }
    }
}
