<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;

final class AdminColumnsFrontendPreviewContractTest extends TestCase
{
    public function testPreviewParserAcceptsCertifiedFieldsOwnerAndRemainsFinite(): void
    {
        $path = dirname(__DIR__, 4) . '/admin-ui/src/columns-runtime.ts';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertMatchesRegularExpression(
            "/!\\s*\\[\\s*'native',\\s*'query',\\s*'fields'\\s*\\]\\.includes\\(\\s*candidate\\.source_owner\\s*\\)/",
            $source,
            'Preview source-owner parser must accept exactly the certified native/query/fields owner set.',
        );
        self::assertStringNotContainsString(
            "[ 'native', 'query' ].includes( candidate.source_owner )",
            $source,
            'The pre-Fields frontend allowlist must not remain active.',
        );
    }
}
