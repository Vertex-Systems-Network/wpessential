<?php

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	foreach ( [ 'esc_attr', 'esc_html' ] as $wpeFn ) {
		if ( ! function_exists( $wpeFn ) ) {
			eval( 'function ' . $wpeFn . '( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }' ); // phpcs:ignore
		}
	}
	if ( ! function_exists( 'selected' ) ) {
		function selected( $a, $b, $echo = true ) { return (string) $a === (string) $b ? ' selected="selected"' : ''; } // phpcs:ignore
	}
}

namespace WPEssential\Tests\Unit\Platform\Admin\Ui {

	use PHPUnit\Framework\TestCase;
	use WPEssential\Platform\Admin\Ui\Button;
	use WPEssential\Platform\Admin\Ui\Check;
	use WPEssential\Platform\Admin\Ui\Field;

	/** The PHP twins of the Figma UI kit form components (UI/Button, UI/Input, UI/Check chip). */
	final class FormComponentsTest extends TestCase
	{
		public function testButtonRendersVariantAttributesAndEscapes(): void
		{
			$html = Button::button( 'Save <now>', 'primary', [ 'type' => 'submit', 'id' => 'x', 'hidden' => true, 'data-off' => false ] );

			self::assertSame( '<button type="submit" id="x" hidden class="wpe-ui-button wpe-ui-button--primary">Save &lt;now&gt;</button>', $html );
		}

		public function testUnknownVariantFallsBackToGhost(): void
		{
			self::assertStringContainsString( 'wpe-ui-button--ghost', Button::button( 'A', 'nope' ) );
		}

		public function testFieldInputHasLabelForIdAndHelp(): void
		{
			$html = Field::input( 'f1', 'Key', [ 'placeholder' => 'e.g. "book"', 'required' => true ], 'Lowercase.' );

			self::assertStringContainsString( '<label for="f1">Key</label>', $html );
			self::assertStringContainsString( 'type="password"', Field::input( 'p', 'Password', [ 'type' => 'password' ] ) );
			self::assertStringContainsString( '<input type="text" placeholder="e.g. &quot;book&quot;" required id="f1">', $html );
			self::assertStringContainsString( 'Lowercase.</span></p>', $html );
		}

		public function testSelectMarksTheSelectedOption(): void
		{
			$html = Field::select( 's', 'Status', [ 'draft' => 'Draft', 'published' => 'Published' ], 'published' );

			self::assertStringContainsString( '<option value="published" selected="selected">Published</option>', $html );
		}

		public function testCheckChip(): void
		{
			self::assertSame(
				'<label class="wpe-ui-check" for="c"><input type="checkbox" id="c" checked> Public</label>',
				Check::render( 'Public', [ 'id' => 'c' ], true )
			);
		}
	}
}
