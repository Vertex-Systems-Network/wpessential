<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Taxonomies;

use Closure;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Taxonomies\TaxonomyAbilityHandler;
use WPEssential\Modules\Taxonomies\TaxonomyDefinitionProjector;
use WPEssential\Modules\Taxonomies\TaxonomyRewriteRefreshCoordinator;
use WPEssential\Modules\Taxonomies\TaxonomyValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class TaxonomyRewriteRefreshCoordinatorTest extends TestCase
{
    public function testDraftAndPublishedNonRoutingEditsDoNotScheduleRefresh(): void
    {
        $state = [];
        $writes = 0;
        $flushModes = [];
        $coordinator = $this->coordinator($state, $writes, $flushModes);

        $draftBefore = $this->definition(DefinitionStatus::Draft, ['name' => 'Genres']);
        $draftAfter = $this->definition(DefinitionStatus::Draft, ['name' => 'Book Genres'], revision: 2);
        self::assertFalse($coordinator->scheduleForMutation($draftBefore, $draftAfter));

        $publishedBefore = $this->definition(DefinitionStatus::Published, ['name' => 'Genres']);
        $publishedAfter = $this->definition(DefinitionStatus::Published, ['name' => 'Book Genres'], revision: 2);
        self::assertFalse($coordinator->scheduleForMutation($publishedBefore, $publishedAfter));
        self::assertSame(0, $writes);
        self::assertSame([], $state);
    }

    public function testEquivalentDefaultRewriteMapDoesNotScheduleRefresh(): void
    {
        $state = [];
        $writes = 0;
        $flushModes = [];
        $coordinator = $this->coordinator($state, $writes, $flushModes);
        $before = $this->definition(DefinitionStatus::Published);
        $after = $this->definition(DefinitionStatus::Published, [
            'rewrite' => [
                'slug' => 'genre',
                'with_front' => true,
                'hierarchical' => false,
                'ep_mask' => 0,
            ],
        ], revision: 2);

        self::assertFalse($coordinator->scheduleForMutation($before, $after));
        self::assertSame(0, $writes);
    }

    public function testPublishedRoutingChangeSchedulesOneSoftRefresh(): void
    {
        $state = [];
        $writes = 0;
        $flushModes = [];
        $coordinator = $this->coordinator($state, $writes, $flushModes);
        $before = $this->definition(DefinitionStatus::Published);
        $after = $this->definition(DefinitionStatus::Published, [
            'rewrite' => ['slug' => 'library/genre'],
        ], revision: 2);

        self::assertTrue($coordinator->scheduleForMutation($before, $after));
        self::assertTrue($coordinator->scheduleForMutation($before, $after));
        self::assertSame(1, $writes);
        self::assertSame(1, $state[TaxonomyRewriteRefreshCoordinator::OPTION_KEY] ?? null);

        self::assertTrue($coordinator->flushPending());
        self::assertSame([false], $flushModes);
        self::assertSame([], $state);
        self::assertFalse($coordinator->flushPending());
        self::assertSame([false], $flushModes);
    }

    public function testLifecycleAndEffectiveQueryVarChangesScheduleRefresh(): void
    {
        $state = [];
        $writes = 0;
        $flushModes = [];
        $coordinator = $this->coordinator($state, $writes, $flushModes);
        $draft = $this->definition(DefinitionStatus::Draft);
        $published = $this->definition(DefinitionStatus::Published, revision: 2);

        self::assertTrue($coordinator->scheduleForMutation($draft, $published));
        self::assertTrue($coordinator->flushPending());

        $queryVarDisabled = $this->definition(DefinitionStatus::Published, [
            'publicly_queryable' => false,
        ], revision: 3);
        self::assertTrue($coordinator->scheduleForMutation($published, $queryVarDisabled));
        self::assertTrue($coordinator->flushPending());

        $disabled = $this->definition(DefinitionStatus::Disabled, revision: 4);
        self::assertTrue($coordinator->scheduleForMutation($queryVarDisabled, $disabled));
        self::assertSame(3, $writes);
    }

    public function testFailedFlushRetainsPendingMarkerForRetry(): void
    {
        $state = [];
        $writes = 0;
        $flushModes = [];
        $flushSucceeds = false;
        $coordinator = $this->coordinator(
            $state,
            $writes,
            $flushModes,
            static function (bool $hard) use (&$flushSucceeds): bool {
                return $flushSucceeds;
            },
        );
        $before = $this->definition(DefinitionStatus::Published);
        $after = $this->definition(DefinitionStatus::Published, [
            'rewrite' => false,
        ], revision: 2);

        self::assertTrue($coordinator->scheduleForMutation($before, $after));
        self::assertFalse($coordinator->flushPending());
        self::assertSame(1, $state[TaxonomyRewriteRefreshCoordinator::OPTION_KEY] ?? null);

        $flushSucceeds = true;
        self::assertTrue($coordinator->flushPending());
        self::assertSame([false, false], $flushModes);
        self::assertSame([], $state);
    }

    public function testCanonicalSaveAndStatusHandlersScheduleOnlyRequiredRefreshes(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $projector = new TaxonomyDefinitionProjector();
        $validation = new TaxonomyValidationService($repository, $projector);
        $state = [];
        $writes = 0;
        $flushModes = [];
        $coordinator = $this->coordinator($state, $writes, $flushModes);
        $save = new TaxonomyAbilityHandler(
            $repository,
            $projector,
            $validation,
            TaxonomyAbilityHandler::SAVE,
            $coordinator,
        );
        $status = new TaxonomyAbilityHandler(
            $repository,
            $projector,
            $validation,
            TaxonomyAbilityHandler::STATUS,
            $coordinator,
        );
        $context = new ExecutionContext(new Principal(1), 1);

        $created = $save->handle([
            'payload' => $this->payload(),
            'status' => 'draft',
        ], $context);
        self::assertSame(0, $writes);
        $id = $created['definition']['id'];
        self::assertIsString($id);

        $published = $status->handle([
            'id' => $id,
            'expected_revision' => 1,
            'status' => 'published',
        ], $context);
        self::assertSame(1, $writes);
        self::assertTrue($coordinator->flushPending());

        $payload = $published['definition']['payload'];
        self::assertIsArray($payload);
        $payload['name'] = 'Book Genres';
        $renamed = $save->handle([
            'id' => $id,
            'expected_revision' => 2,
            'payload' => $payload,
        ], $context);
        self::assertSame(1, $writes);

        $payload = $renamed['definition']['payload'];
        self::assertIsArray($payload);
        $payload['rewrite'] = ['slug' => 'library/genre'];
        $save->handle([
            'id' => $id,
            'expected_revision' => 3,
            'payload' => $payload,
        ], $context);
        self::assertSame(2, $writes);
    }

    /**
     * @param array<string,mixed> $state
     * @param list<bool> $flushModes
     */
    private function coordinator(
        array &$state,
        int &$writes,
        array &$flushModes,
        ?Closure $flushDecision = null,
    ): TaxonomyRewriteRefreshCoordinator {
        return new TaxonomyRewriteRefreshCoordinator(
            readPending: static function (string $key) use (&$state): mixed {
                return $state[$key] ?? false;
            },
            writePending: static function (string $key) use (&$state, &$writes): bool {
                ++$writes;
                $state[$key] = 1;

                return true;
            },
            deletePending: static function (string $key) use (&$state): bool {
                unset($state[$key]);

                return true;
            },
            flushRewriteRules: static function (bool $hard) use (&$flushModes, $flushDecision): bool {
                $flushModes[] = $hard;

                return $flushDecision instanceof Closure ? $flushDecision($hard) : true;
            },
        );
    }

    /** @param array<string,mixed> $overrides */
    private function definition(
        DefinitionStatus $status,
        array $overrides = [],
        int $revision = 1,
    ): Definition {
        return new Definition(
            id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            slug: 'taxonomy-genre',
            type: TaxonomyDefinitionProjector::DEFINITION_TYPE,
            schemaVersion: 1,
            ownerSurfaceId: TaxonomyDefinitionProjector::OWNER_SURFACE_ID,
            status: $status,
            payload: array_replace($this->payload(), $overrides),
            revision: $revision,
        );
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'taxonomy_key' => 'genre',
            'object_types' => ['post'],
            'name' => 'Genres',
            'singular_name' => 'Genre',
            'public' => true,
            'hierarchical' => false,
            'show_in_rest' => true,
        ];
    }
}
