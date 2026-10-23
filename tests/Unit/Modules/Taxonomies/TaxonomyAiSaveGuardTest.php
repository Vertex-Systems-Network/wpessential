<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

/** AI may draft and edit taxonomies but never publish, disable or archive them. */
final class TaxonomyAiSaveGuardTest extends TestCase
{
    public function testAiSavesNewTaxonomiesAsDraftsAndKeepsExistingStatus(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $ai = new ExecutionContext(new Principal(1), 1, ExecutionChannel::Ai);
        $ui = new ExecutionContext(new Principal(1), 1, ExecutionChannel::Ui);

        $created = $this->handler($repository)->handle(['payload' => $this->payload(), 'status' => 'published'], $ai)['definition'];
        self::assertSame('draft', $created['status']);

        $published = $this->handler($repository)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $this->payload(),
            'status' => 'published',
        ], $ui)['definition'];
        self::assertSame('published', $published['status']);

        $edited = $this->handler($repository)->handle([
            'id' => $created['id'],
            'expected_revision' => 2,
            'payload' => array_merge($this->payload(), ['description' => 'Edited by AI']),
            'status' => 'disabled',
        ], $ai)['definition'];
        self::assertSame('published', $edited['status']);
    }

    private function handler(InMemoryDefinitionRepository $repository): TaxonomyAbilityHandler
    {
        $projector = new TaxonomyDefinitionProjector();

        return new TaxonomyAbilityHandler($repository, $projector, new TaxonomyValidationService($repository, $projector), TaxonomyAbilityHandler::SAVE);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'taxonomy_key' => 'ai_genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'public' => true,
            'show_in_rest' => true,
        ];
    }
}
