<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\AiOAuth;

final class AiOAuthTest extends TestCase {

	public function test_pkce_s256_matches_rfc7636_example(): void {
		self::assertSame( 'E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM', AiOAuth::challengeFor( 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk' ) );
	}

	public function test_redirect_uris(): void {
		if ( ! function_exists( 'wp_parse_url' ) ) {
			self::markTestSkipped( 'wp_parse_url is not loaded.' );
		}
		self::assertTrue( AiOAuth::redirectAllowed( 'https://claude.ai/api/mcp/auth_callback' ) );
		self::assertTrue( AiOAuth::redirectAllowed( 'http://localhost:6274/callback' ) );
		self::assertFalse( AiOAuth::redirectAllowed( 'http://evil.example/cb' ) );
		self::assertFalse( AiOAuth::redirectAllowed( 'javascript:alert(1)' ) );
	}

	public function test_client_kind(): void {
		self::assertSame( 'claude-ai', AiOAuth::clientKind( 'Claude' ) );
		self::assertSame( 'chatgpt', AiOAuth::clientKind( 'ChatGPT' ) );
		self::assertSame( 'generic', AiOAuth::clientKind( 'Cursor' ) );
	}
}
