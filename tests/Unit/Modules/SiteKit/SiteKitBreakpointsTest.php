<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\SiteKit;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\SiteKit\SiteKitBreakpoints;
use WPEssential\Modules\SiteKit\SiteKitCompiler;

final class SiteKitBreakpointsTest extends TestCase {

	public function testDefaultsKeepDesktopTabletPhone(): void {
		$enabled = SiteKitBreakpoints::enabled();
		self::assertSame( [ 'desktop', 'tablet', 'mobile' ], array_keys( $enabled ) );
		self::assertSame( '@media (max-width: 1023px)', $enabled['tablet']['media'] );
		self::assertSame( '@media (max-width: 767px)', $enabled['mobile']['media'] );
	}

	public function testEnabledOrderAndCustomWidth(): void {
		$enabled = SiteKitBreakpoints::enabled(
			[
				'widescreen'   => [ 'on' => true ],
				'mobile_small' => [
					'on' => true,
					'w'  => 400,
				],
				'tablet'       => [ 'on' => false ],
			]
		);
		self::assertSame( [ 'desktop', 'widescreen', 'mobile', 'mobile_small' ], array_keys( $enabled ) );
		self::assertSame( '@media (min-width: 1920px)', $enabled['widescreen']['media'] );
		self::assertSame( '@media (max-width: 400px)', $enabled['mobile_small']['media'] );
	}

	public function testCompilerEmitsOnlyEnabledBreakpoints(): void {
		$css = SiteKitCompiler::css(
			[
				'breakpoints'   => [
					'tablet'       => [ 'on' => false ],
					'mobile_small' => [ 'on' => true ],
				],
				'section_space' => [
					'desktop'      => 120,
					'tablet'       => 80,
					'mobile_small' => 40,
				],
			]
		);
		self::assertStringContainsString( '@media (max-width: 480px)', $css );
		self::assertStringNotContainsString( '1023px', $css );
	}
}
