<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use PHPUnit\Framework\TestCase;

final class AdminColumnsFrontendFieldsInlineEditContractTest extends TestCase
{
    public function testOptionalOwnerWriteRouteIsProjectedWithoutWideningCoreRoutes(): void
    {
        $path = dirname(__DIR__, 4) . '/frameworks/Modules/AdminColumns/AdminColumnsAdminController.php';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString(
            'private const WRITE_FIELD_ROUTE = AdminColumnsFieldValueWriteAbilityHandler::AJAX_TYPE;',
            $source,
        );
        self::assertStringContainsString("\$routes['writeFieldValue']", $source);
        self::assertStringContainsString('catch (InvalidArgumentException)', $source);
        self::assertStringContainsString(
            "'nonce' => \$this->ajax->createNonce(self::WRITE_FIELD_ROUTE)",
            $source,
        );
    }

    public function testFrontendMutationRemainsBoundedToExplicitPostIdAndFieldsOwner(): void
    {
        $path = dirname(__DIR__, 4) . '/admin-ui/src/columns-runtime.ts';
        $source = file_get_contents($path);

        self::assertIsString($source);
        self::assertStringContainsString("'admin-columns.write.field-value'", $source);
        self::assertStringContainsString("source.reference === 'post.id'", $source);
        self::assertStringContainsString("authoredSource?.owner === 'fields'", $source);
        self::assertStringContainsString('metadata.postTypes.includes( targetKey )', $source);
        self::assertStringContainsString('expected_group_revision: metadata.groupRevision', $source);
        self::assertStringContainsString('post_id: postId', $source);
        self::assertStringContainsString('column_key: column.key', $source);
        self::assertStringContainsString('view_id: expectedViewId', $source);
        self::assertStringContainsString('session.authoredDirty', $source);
        self::assertStringContainsString(
            'Preview was invalidated; choose Preview rows to read authoritative owner state again.',
            $source,
        );
        self::assertStringNotContainsString('admin-columns.bulk', $source);
        self::assertStringNotContainsString('admin-columns.export', $source);
    }

    public function testFrontendValidatesOwnerMutationEvidenceBeforeInvalidatingPreview(): void
    {
        $path = dirname(__DIR__, 4) . '/admin-ui/src/columns-runtime.ts';
        $source = file_get_contents($path);

        self::assertIsString($source);
        foreach ([
            'write.contract_version === 1',
            'write.field_ref === expectedSourceReference',
            'write.group_revision === metadata.groupRevision',
            'write.field_uuid === metadata.fieldUuid',
            "write.storage_owner === 'native_post_meta'",
            'write.post_id === expectedPostId',
            'write.post_type === expectedPostType',
            "value.source_owner !== 'fields'",
            'resetPreview();',
        ] as $needle) {
            self::assertStringContainsString($needle, $source);
        }
    }
}
