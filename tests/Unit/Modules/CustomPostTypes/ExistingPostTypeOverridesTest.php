<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomPostTypes\ExistingPostTypeOverrides;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class ExistingPostTypeOverridesTest extends TestCase
{
    public function testSanitizeKeepsOnlyOfferedSettings(): void
    {
        $clean = ExistingPostTypeOverrides::sanitize([
            'labels' => ['name' => ' Articles <b>x</b> ', 'singular_name' => ''],
            'menu_icon' => 'dashicons-media-text',
            'menu_position' => '7',
            'flags' => ['show_in_rest' => '0', 'public' => '', 'capability_type' => '1'],
            'rewrite_slug' => 'articles',
            'supports_remove' => ['comments', 'not-a-feature'],
            'evil' => 'x',
        ], 'post');

        self::assertSame([
            'labels' => ['name' => 'Articles x'],
            'menu_icon' => 'dashicons-media-text',
            'menu_position' => 7,
            'show_in_rest' => false,
            'rewrite_slug' => 'articles',
            'supports_remove' => ['comments'],
        ], $clean);
    }

    public function testCoreTypesWordPressNeedsCannotBeHidden(): void
    {
        self::assertTrue(ExistingPostTypeOverrides::sanitize(['disabled' => '1'], 'post')['disabled']);
        $this->expectException(InvalidArgumentException::class);
        ExistingPostTypeOverrides::sanitize(['disabled' => '1'], 'attachment');
    }

    public function testRejectsUnsafeIconAndConflictingSupports(): void
    {
        foreach ([['menu_icon' => 'javascript:alert(1)'], ['supports_add' => ['excerpt'], 'supports_remove' => ['excerpt']], ['rewrite_slug' => '../x']] as $input) {
            try {
                ExistingPostTypeOverrides::sanitize($input, 'post');
                self::fail('Expected rejection for ' . json_encode($input));
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testMergeLayersOverrideAndHidesWithoutDroppingArgs(): void
    {
        $args = ['labels' => ['name' => 'Posts', 'add_new' => 'Add'], 'public' => true, 'rewrite' => ['with_front' => false], 'show_in_rest' => true];
        $merged = ExistingPostTypeOverrides::merge($args, ['labels' => ['name' => 'Articles'], 'rewrite_slug' => 'news', 'show_in_rest' => false], 'post');
        self::assertSame('Articles', $merged['labels']['name']);
        self::assertSame('Add', $merged['labels']['add_new']);
        self::assertSame(['with_front' => false, 'slug' => 'news'], $merged['rewrite']);
        self::assertFalse($merged['show_in_rest']);

        $hidden = ExistingPostTypeOverrides::merge($args, ['disabled' => true], 'post');
        self::assertFalse($hidden['public']);
        self::assertFalse($hidden['show_ui']);
        self::assertTrue($hidden['exclude_from_search']);
    }

    public function testSaveThenRevertThroughTheAbility(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $overrides = new ExistingPostTypeOverrides($repository);
        $context = new ExecutionContext(new Principal(1), 1);

        $saved = $overrides->handle(['post_type' => 'post', 'override' => ['labels' => ['name' => 'Articles']]], $context);
        self::assertSame('published', $saved['status']);
        self::assertSame('Articles', $overrides->filterArgs(['labels' => ['name' => 'Posts']], 'post')['labels']['name']);
        self::assertSame(['labels' => ['name' => 'Pages']], $overrides->filterArgs(['labels' => ['name' => 'Pages']], 'page'));

        $reverted = $overrides->handle(['post_type' => 'post', 'revert' => true], $context);
        self::assertSame('archived', $reverted['status']);
        self::assertSame('Posts', $overrides->filterArgs(['labels' => ['name' => 'Posts']], 'post')['labels']['name']);
        self::assertCount(1, $repository->byType(ExistingPostTypeOverrides::DEFINITION_TYPE));
    }
}
