<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\SiteKit;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\SiteKit\SiteKitCompiler;
use WPEssential\Modules\SiteKit\SiteKitUnits;

final class SiteKitUnitsTest extends TestCase {

	public function testDefaultsAndSelection(): void {
		self::assertSame( SiteKitUnits::DEFAULT_ON, SiteKitUnits::enabled() );
		self::assertSame( [ 'px', 'vh' ], SiteKitUnits::enabled( [ 'on' => 'vh,px,bogus' ] ) );
	}

	public function testCustomUnitConvertsToItsBase(): void {
		$units = [
			'custom' => [
				[
					'n' => 'hm',
					'f' => 2,
					'b' => 'px',
				],
				[
					'n' => 'px',
					'f' => 3,
					'b' => 'rem',
				],
			],
		];
		self::assertSame( [ 'hm' ], array_keys( SiteKitUnits::custom( $units ) ) );
		self::assertSame( '6px', SiteKitUnits::css( 3, 'hm', $units ) );
		self::assertSame( '3px', SiteKitUnits::css( 3, 'bogus', $units ) );
	}

	public function testCompilerUsesCustomUnits(): void {
		$css = SiteKitCompiler::css(
			[
				'units'         => [
					'custom' => [
						[
							'n' => 'hm',
							'f' => 2,
							'b' => 'px',
						],
					],
				],
				'section_space' => [
					'desktop' => [
						'size' => 40,
						'unit' => 'hm',
					],
				],
			]
		);
		self::assertStringContainsString( '80px', $css );
		self::assertStringNotContainsString( 'hm', $css );
	}
}
