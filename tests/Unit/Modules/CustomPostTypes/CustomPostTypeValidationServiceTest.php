<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomPostTypes;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeAbilityHandler;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeDefinitionProjector;
use WPEssential\Modules\CustomPostTypes\CustomPostTypeValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class CustomPostTypeValidationServiceTest extends TestCase
{
    public function testValidCandidateReturnsNonMutatingPassReport(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate(['payload' => $this->payload()]);

        self::assertTrue($report['valid']);
        self::assertSame([], $report['issues']);
        self::assertSame('library_book', $report['candidate']['post_type_key']);
        self::assertSame([], $repository->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE));
    }

    public function testReservedKeyIsReportedAsBlockedWithoutSaving(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $report = $this->validation($repository)->validate([
            'payload' => array_merge($this->payload(), ['post_type_key' => 'post']),
        ]);

        self::assertFalse($report['valid']);
        self::assertSame('registration_schema_invalid', $report['issues'][0]['id']);
        self::assertSame('blocked', $report['issues'][0]['severity']);
        self::assertSame([], $repository->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE));
    }

    public function testDuplicateCanonicalKeyIsReportedAcrossLifecycleStates(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, $this->payload());
        $this->status($repository, $created['id'], 1, 'archived');

        $report = $this->validation($repository)->validate(['payload' => $this->payload()]);

        self::assertFalse($report['valid']);
        self::assertContains('duplicate_definition', array_column($report['issues'], 'id'));
        self::assertCount(1, $repository->byType(CustomPostTypeDefinitionProjector::DEFINITION_TYPE));
    }

    public function testExistingRuntimeKeyRenameIsReportedAsBlocked(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->save($repository, $this->payload());

        $report = $this->validation($repository)->validate([
            'id' => $created['id'],
            'payload' => array_merge($this->payload(), ['post_type_key' => 'library_volume']),
        ]);

        self::assertFalse($report['valid']);
        self::assertContains('runtime_key_immutable', array_column($report['issues'], 'id'));
        self::assertSame(1, $repository->get($created['id'])?->revision);
    }

    private function validation(InMemoryDefinitionRepository $repository): CustomPostTypeValidationService
    {
        return new CustomPostTypeValidationService(
            $repository,
            new CustomPostTypeDefinitionProjector(),
        );
    }

    private function handler(InMemoryDefinitionRepository $repository, string $action): CustomPostTypeAbilityHandler
    {
        $projector = new CustomPostTypeDefinitionProjector();
        return new CustomPostTypeAbilityHandler(
            $repository,
            $projector,
            new CustomPostTypeValidationService($repository, $projector),
            $action,
        );
    }

    /** @return array<string,mixed> */
    private function save(InMemoryDefinitionRepository $repository, array $payload): array
    {
        return $this->handler($repository, CustomPostTypeAbilityHandler::SAVE)
            ->handle(['payload' => $payload], $this->context())['definition'];
    }

    private function status(
        InMemoryDefinitionRepository $repository,
        string $id,
        int $revision,
        string $status,
    ): void {
        $this->handler($repository, CustomPostTypeAbilityHandler::STATUS)->handle([
            'id' => $id,
            'expected_revision' => $revision,
            'status' => $status,
        ], $this->context());
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'post_type_key' => 'library_book',
            'name' => 'Books',
            'singular_name' => 'Book',
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor'],
        ];
    }
}
