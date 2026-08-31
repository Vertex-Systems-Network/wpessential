<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;

final class FieldDefinitionNormalizerTest extends TestCase
{
    public function testMetaBoxStyleCloneAndSortNormalizeToSharedRepeatabilityContract(): void
    {
        $field = (new FieldDefinitionNormalizer())->normalize([
            'key' => 'speaker_name',
            'label' => 'Speaker',
            'type' => 'text',
            'cloneable' => true,
            'sortable' => true,
            'clone_default' => true,
            'clone_as_multiple' => true,
            'clone_empty_start' => true,
            'min_clones' => 1,
            'max_clones' => 8,
            'add_button_label' => 'Add speaker',
        ]);

        self::assertSame('text', $field['type']);
        self::assertTrue($field['repeatability']['enabled']);
        self::assertTrue($field['repeatability']['sortable']);
        self::assertTrue($field['repeatability']['clone_default']);
        self::assertTrue($field['repeatability']['store_as_multiple']);
        self::assertSame(8, $field['repeatability']['max']);
    }

    public function testPresetModesCompileToCanonicalTypes(): void
    {
        $normalizer = new FieldDefinitionNormalizer();

        $multi = $normalizer->normalize(['key' => 'topics', 'label' => 'Topics', 'type' => 'multi_select']);
        self::assertSame('select', $multi['type']);
        self::assertSame('multi_select', $multi['preset']);
        self::assertTrue($multi['settings']['multiple']);

        $week = $normalizer->normalize(['key' => 'week', 'label' => 'Week', 'type' => 'week']);
        self::assertSame('date', $week['type']);
        self::assertSame('week', $week['settings']['precision']);
        self::assertFalse($week['native_browser_picker']);

        $tinymce = $normalizer->normalize(['key' => 'body', 'label' => 'Body', 'type' => 'tinymce']);
        self::assertSame('wysiwyg', $tinymce['type']);
        self::assertSame('wordpress_editor', $tinymce['settings']['editor_adapter']);
    }

    public function testGroupSupportsSubfieldsAndCanBeCloneableAndSortable(): void
    {
        $group = (new FieldDefinitionNormalizer())->normalize([
            'key' => 'team',
            'label' => 'Team',
            'type' => 'group',
            'cloneable' => true,
            'sortable' => true,
            'subfields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                ['key' => 'email', 'label' => 'Email', 'type' => 'email'],
            ],
        ]);

        self::assertCount(2, $group['subfields']);
        self::assertTrue($group['repeatability']['enabled']);
        self::assertTrue($group['repeatability']['sortable']);
    }

    public function testRepeaterUsesItsOwnRowDuplicateAndSortContract(): void
    {
        $repeater = (new FieldDefinitionNormalizer())->normalize([
            'key' => 'items',
            'label' => 'Items',
            'type' => 'repeater',
            'subfields' => [
                ['key' => 'title', 'label' => 'Title', 'type' => 'text'],
            ],
        ]);

        self::assertSame('container_managed', $repeater['repeatability']['mode']);
        self::assertTrue($repeater['repeatability']['enabled']);
        self::assertTrue($repeater['repeatability']['sortable']);
    }

    public function testLayoutSectionCannotBecomeARepeatableStoredValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldDefinitionNormalizer())->normalize([
            'key' => 'details_section',
            'label' => 'Details',
            'type' => 'section',
            'cloneable' => true,
        ]);
    }

    public function testCodeFieldCannotEnableExecutionEvenForPhpSyntaxMode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldDefinitionNormalizer())->normalize([
            'key' => 'snippet',
            'label' => 'Snippet',
            'type' => 'code_editor',
            'settings' => ['control_mode' => 'php', 'execute' => true],
        ]);
    }

    public function testSortableCannotBeEnabledWithoutCloneable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldDefinitionNormalizer())->normalize([
            'key' => 'tagline',
            'label' => 'Tagline',
            'type' => 'text',
            'sortable' => true,
        ]);
    }
}
