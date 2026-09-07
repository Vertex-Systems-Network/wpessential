<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;

final class AdminColumnsFrontendFieldsBulkEditContractTest extends TestCase
{
    public function testBulkUiReusesCertifiedSingleRowOwnerRouteWithHardVisibleRowBudget(): void
    {
        $path = dirname(__DIR__, 4) . '/admin-ui/src/columns-runtime.ts';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString('const MAX_BULK_FIELD_ROWS = 20;', $source);
        self::assertStringContainsString('const selectedPostIds = new Set< number >();', $source);
        self::assertStringContainsString('selectedPostIds.size >= MAX_BULK_FIELD_ROWS', $source);
        self::assertStringContainsString('Array.from( selectedPostIds )', $source);
        self::assertStringContainsString('for ( const postId of selectedIds )', $source);
        self::assertStringContainsString('writeBootstrap.routes.writeFieldValue', $source);
        self::assertStringNotContainsString("'admin-columns.bulk", $source);
    }

    public function testBulkMutationKeepsOwnerAndSavedViewGuardsForEverySelectedRow(): void
    {
        $path = dirname(__DIR__, 4) . '/admin-ui/src/columns-runtime.ts';
        $source = file_get_contents($path);

        self::assertIsString($source);
        foreach ([
            "source.owner === 'fields'",
            'metadata.postTypes.includes( targetKey )',
            "session.status === 'published'",
            'session.viewEnabled',
            '! session.authoredDirty',
            'view_id: expectedViewId',
            'column_key: target.column.key',
            'post_id: postId',
            'expected_group_revision: target.metadata.groupRevision',
            'parseFieldWriteResult(',
        ] as $needle) {
            self::assertStringContainsString($needle, $source);
        }
    }

    public function testAttemptedBulkMutationReportsPartialFailuresAndInvalidatesPreview(): void
    {
        $path = dirname(__DIR__, 4) . '/admin-ui/src/columns-runtime.ts';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString('const failedPostIds: number[] = [];', $source);
        self::assertStringContainsString('failedPostIds.push( postId );', $source);
        self::assertStringContainsString('const notAttemptedIds = selectedIds.slice( attempted );', $source);
        self::assertStringContainsString('if ( attempted > 0 ) {', $source);
        self::assertStringContainsString('resetPreview();', $source);
        self::assertStringContainsString('Bulk Fields edit finished:', $source);
        self::assertStringContainsString('Preview was invalidated; choose Preview rows to read authoritative owner state again.', $source);
    }
}
