<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform\Admin\Connect;

use PHPUnit\Framework\TestCase;
use WPEssential\Platform\Admin\Connect\AiActivityLog;

final class AiActivityLogTest extends TestCase {

	public function test_csv_line_quotes_and_neutralises_formulas(): void {
		self::assertSame( "\"a\",\"'=1+1\",\"say \"\"hi\"\"\"\r\n", AiActivityLog::csvLine( [ 'a', '=1+1', 'say "hi"' ] ) );
	}

	public function test_without_database_log_is_empty(): void {
		$log = new AiActivityLog( null );
		self::assertFalse( $log->available() );
		self::assertSame( [], $log->recent() );
	}
}
