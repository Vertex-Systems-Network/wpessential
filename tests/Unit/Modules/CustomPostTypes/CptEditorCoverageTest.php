<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

/**
 * The editor must offer every option the CPT definition supports, so no option can silently
 * disappear from the screen again (owner rule: never remove an option).
 */
final class CptEditorCoverageTest extends TestCase
{
    private const ROOT = __DIR__ . '/../../../..';

    private function editorSource(): string
    {
        return (string) file_get_contents(self::ROOT . '/frameworks/Modules/CustomPostTypes/CustomPostTypeAdminController.php')
            . (string) file_get_contents(self::ROOT . '/admin-ui/src/main.ts')
            . (string) file_get_contents(self::ROOT . '/admin-ui/src/cpt-options.ts');
    }

    public function testEveryTopLevelOptionHasAnEditorControl(): void
    {
        $source  = $this->editorSource();
        $missing = [];
        foreach (CustomPostTypeDefinitionProjector::TOP_LEVEL_KEYS as $key) {
            if (! str_contains($source, "'" . $key . "'") && ! str_contains($source, $key . ':')) {
                $missing[] = $key;
            }
        }

        self::assertSame([], $missing, 'CPT options without an editor control.');
    }

    public function testEverySupportHasACheckChip(): void
    {
        $controller = (string) file_get_contents(self::ROOT . '/frameworks/Modules/CustomPostTypes/CustomPostTypeAdminController.php');
        foreach (CustomPostTypeDefinitionProjector::SUPPORT_KEYS as $support) {
            self::assertStringContainsString("'" . $support . "'", $controller, $support);
        }
    }

    public function testEveryLabelIsEditable(): void
    {
        self::assertStringContainsString('CustomPostTypeDefinitionProjector::LABEL_KEYS', $this->editorSource());
    }

    /** The exact payload the editor posts with every advanced option set (captured from the screen). */
    public function testAFullEditorPayloadProjects(): void
    {
        $payload = json_decode('{"show_ui":true,"show_in_menu":"tools.php","menu_position":25,"menu_icon":"dashicons-book","exclude_from_search":false,"rewrite":{"slug":"library/books","with_front":false},"query_var":"book_q","rest_base":"books","capability_type":["book","books"],"map_meta_cap":true,"capabilities":{"edit_posts":"edit_books"},"labels":{"add_new_item":"Add new book"},"taxonomies":["category"],"post_type_key":"book","name":"Books","singular_name":"Book","description":"","public":true,"show_in_rest":true,"hierarchical":false,"has_archive":"library","supports":["title","editor","author"]}', true);
        self::assertIsArray($payload);

        $registration = (new CustomPostTypeDefinitionProjector())->project(new Definition(
            id: '0f8fad5b-d9cb-469f-a165-70867728950e',
            slug: 'book',
            type: CustomPostTypeDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: CustomPostTypeDefinitionProjector::OWNER_SURFACE_ID,
            status: DefinitionStatus::Published,
            payload: $payload,
        ));

        self::assertSame('tools.php', $registration->payload['show_in_menu']);
        self::assertSame(25, $registration->payload['menu_position']);
        self::assertSame(['book', 'books'], $registration->payload['capability_type']);
        self::assertSame('library', $registration->payload['has_archive']);
        self::assertSame('Add new book', $registration->payload['labels']['add_new_item']);
    }
}
