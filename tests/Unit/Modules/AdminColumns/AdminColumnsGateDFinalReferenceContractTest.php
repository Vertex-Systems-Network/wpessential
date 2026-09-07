<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;

final class AdminColumnsGateDFinalReferenceContractTest extends TestCase
{
    public function testComposedRuntimeReusesCanonicalViewQueryFieldsAndExportSeams(): void
    {
        $module = $this->source('frameworks/Modules/AdminColumns/AdminColumnsModule.php');

        self::assertStringContainsString(
            '$readAdapter = new AdminColumnsReadAdapter($views, $query, $fields);',
            $module,
        );
        self::assertStringContainsString(
            '$fieldWriteAdapter = new AdminColumnsFieldValueWriteAdapter($views, $query, $fieldWrites);',
            $module,
        );
        self::assertStringContainsString(
            '$csvExport = new AdminColumnsCsvExportService(',
            $module,
        );
        self::assertStringContainsString("type: 'admin-columns.read.rows'", $module);
        self::assertStringContainsString('AdminColumnsCsvExportAbilityHandler::AJAX_TYPE', $module);
        self::assertStringContainsString('AdminColumnsFieldValueWriteAbilityHandler::AJAX_TYPE', $module);
    }

    public function testReadsAndWritesRequireAuthoritativeViewAndOwnerContracts(): void
    {
        $read = $this->source('frameworks/Modules/AdminColumns/AdminColumnsReadAdapter.php');
        $write = $this->source('frameworks/Modules/AdminColumns/AdminColumnsFieldValueWriteAdapter.php');

        foreach ([
            'Admin Columns runtime reads require a published View.',
            'Admin Columns runtime reads require an enabled View.',
            'source owner is not readable in V1.',
            '$result = $this->query->read($request, $context);',
            '$result = $this->fields->readValues($fieldReference, $postIds, $context);',
        ] as $needle) {
            self::assertStringContainsString($needle, $read);
        }

        foreach ([
            'Admin Columns runtime mutation requires a published View.',
            'Admin Columns runtime mutation requires an enabled View.',
            'Admin Columns mutation column is not owned by Fields.',
            '$this->assertTargetRow($postId, $targetKey, $context);',
            '$write = $this->fields->writeValue(',
        ] as $needle) {
            self::assertStringContainsString($needle, $write);
        }

        foreach ([$read, $write] as $source) {
            self::assertStringNotContainsString('update_post_meta(', $source);
            self::assertStringNotContainsString('add_post_meta(', $source);
            self::assertStringNotContainsString('delete_post_meta(', $source);
            self::assertStringNotContainsString('$wpdb->', $source);
        }
    }

    public function testBulkMutationReusesSingleRowRouteAndRequiresAuthoritativeRepreview(): void
    {
        $frontend = $this->source('admin-ui/src/columns-runtime.ts');

        foreach ([
            'const MAX_BULK_FIELD_ROWS = 20;',
            'for ( const postId of selectedIds )',
            'writeBootstrap.routes.writeFieldValue',
            "source.owner === 'fields'",
            "session.status === 'published'",
            'session.viewEnabled',
            '! session.authoredDirty',
            'if ( attempted > 0 ) {',
            'resetPreview();',
            'Preview was invalidated; choose Preview rows to read authoritative owner state again.',
        ] as $needle) {
            self::assertStringContainsString($needle, $frontend);
        }

        self::assertStringNotContainsString("'admin-columns.bulk", $frontend);
    }

    public function testExportAndAuxiliaryFoundationsStayOutOfMutationAuthority(): void
    {
        $export = $this->source('frameworks/Modules/AdminColumns/AdminColumnsCsvExportService.php');
        $read = $this->source('frameworks/Modules/AdminColumns/AdminColumnsReadAdapter.php');
        $write = $this->source('frameworks/Modules/AdminColumns/AdminColumnsFieldValueWriteAdapter.php');

        self::assertStringContainsString(
            'This service owns neither source execution nor authorization.',
            $export,
        );
        self::assertStringContainsString('private AdminColumnsReadAdapter $reads,', $export);
        self::assertStringContainsString('$page = $this->readPage(', $export);

        foreach ([$read, $write, $export] as $source) {
            self::assertStringNotContainsString('AdminColumnsPersonalPreferenceStore', $source);
            self::assertStringNotContainsString('AdminColumnsViewImportService', $source);
            self::assertStringNotContainsString('AdminColumnsEffectiveStateProjector', $source);
            self::assertStringNotContainsString('AdminColumnsRowActionPolicy', $source);
        }

        foreach ([
            'tests/Unit/Modules/AdminColumns/AdminColumnsReadPerformanceTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsFrontendPreviewContractTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsFrontendFieldsInlineEditContractTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsFrontendFieldsBulkEditContractTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsCsvExportServiceTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsPersonalPreferenceAbilityHandlerTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsRowActionPolicyTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsEffectiveStateProjectorTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsViewImportAbilityHandlerTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsViewPortabilityCodecTest.php',
            'tests/Unit/Modules/AdminColumns/AdminColumnsViewDependencyManifestTest.php',
        ] as $path) {
            self::assertFileExists(dirname(__DIR__, 4) . '/' . $path);
        }
    }

    private function source(string $path): string
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/' . $path);
        self::assertIsString($source);

        return $source;
    }
}
