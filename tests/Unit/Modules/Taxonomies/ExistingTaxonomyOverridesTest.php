<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\ExistingTaxonomyOverrides;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class ExistingTaxonomyOverridesTest extends TestCase
{
    public function testSanitizeKeepsOfferedSettingsAndPostTypeLists(): void
    {
        self::assertSame([
            'labels' => ['name' => 'Topics'],
            'show_admin_column' => true,
            'attach' => ['page'],
            'detach' => ['post'],
        ], ExistingTaxonomyOverrides::sanitize([
            'labels' => ['name' => 'Topics'],
            'flags' => ['show_admin_column' => '1', 'hierarchical' => '1'],
            'attach' => ['page', 'page', 'Bad Type'],
            'detach' => ['post'],
            'menu_icon' => 'dashicons-tag',
        ], 'category'));
    }

    public function testCoreTaxonomiesCannotBeHiddenOrMoved(): void
    {
        foreach ([['disabled' => '1'], ['attach' => ['page']]] as $input) {
            try {
                ExistingTaxonomyOverrides::sanitize($input, 'nav_menu');
                self::fail('nav_menu must stay locked');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
        $this->expectException(InvalidArgumentException::class);
        ExistingTaxonomyOverrides::sanitize(['attach' => ['page'], 'detach' => ['page']], 'post_tag');
    }

    public function testHideKeepsTermsAndRemovesUi(): void
    {
        $hidden = ExistingTaxonomyOverrides::merge(['public' => true, 'show_ui' => true, 'labels' => []], ['disabled' => true], 'post_tag');
        self::assertFalse($hidden['show_ui']);
        self::assertFalse($hidden['show_in_rest']);
        self::assertFalse($hidden['meta_box_cb']);
    }

    public function testSaveThenRevertThroughTheAbility(): void
    {
        $overrides = new ExistingTaxonomyOverrides(new InMemoryDefinitionRepository());
        $context = new ExecutionContext(new Principal(1), 1);
        $overrides->handle(['taxonomy' => 'category', 'override' => ['labels' => ['name' => 'Topics']]], $context);
        self::assertSame('Topics', $overrides->filterArgs(['labels' => ['name' => 'Categories']], 'category')['labels']['name']);
        $overrides->handle(['taxonomy' => 'category', 'revert' => true], $context);
        self::assertSame('Categories', $overrides->filterArgs(['labels' => ['name' => 'Categories']], 'category')['labels']['name']);
    }
}
