<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;

final class FieldGroupDefinitionNormalizerTest extends TestCase
{
    public function testNormalizesNestedFieldsLocationsAndPresentation(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'speaker_profile',
            'title' => 'Speaker Profile',
            'fields' => [
                ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
                [
                    'key' => 'socials',
                    'label' => 'Socials',
                    'type' => 'group',
                    'cloneable' => true,
                    'sortable' => true,
                    'subfields' => [
                        ['key' => 'network', 'label' => 'Network', 'type' => 'select'],
                        ['key' => 'url', 'label' => 'URL', 'type' => 'url'],
                    ],
                ],
            ],
            'locations' => [[
                ['source' => 'post_type', 'operator' => 'in', 'value' => ['speaker']],
                ['source' => 'post_status', 'operator' => 'not_equals', 'value' => 'trash'],
            ]],
            'presentation' => [
                'panel_style' => 'sectioned',
                'label_placement' => 'left',
                'instruction_placement' => 'below_input',
                'collapsible' => true,
                'collapsed' => true,
            ],
        ], true);

        self::assertSame('speaker_profile', $group['group_key']);
        self::assertCount(2, $group['fields']);
        self::assertTrue($group['fields'][1]['repeatability']['enabled']);
        self::assertTrue($group['fields'][1]['repeatability']['sortable']);
        self::assertCount(2, $group['fields'][1]['subfields']);
        self::assertSame('post_type', $group['locations'][0][0]['source']);
        self::assertSame('sectioned', $group['presentation']['panel_style']);
        self::assertTrue($group['presentation']['collapsed']);
    }

    public function testDraftMayBeEmptyButPublishedGroupMustContainAField(): void
    {
        $normalizer = new FieldGroupDefinitionNormalizer();
        $draft = $normalizer->normalize(['group_key' => 'empty_draft', 'title' => 'Empty Draft']);
        self::assertSame([], $draft['fields']);

        $this->expectException(InvalidArgumentException::class);
        $normalizer->normalize(['group_key' => 'empty_publish', 'title' => 'Empty Publish'], true);
    }

    public function testRejectsDuplicateTopLevelFieldKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'duplicate_keys',
            'title' => 'Duplicate Keys',
            'fields' => [
                ['key' => 'name', 'label' => 'First', 'type' => 'text'],
                ['key' => 'name', 'label' => 'Second', 'type' => 'textarea'],
            ],
        ]);
    }

    public function testRejectsArbitraryTopLevelExecutableConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'unsafe',
            'title' => 'Unsafe',
            'php_callback' => 'dangerous_callback',
        ]);
    }
}
