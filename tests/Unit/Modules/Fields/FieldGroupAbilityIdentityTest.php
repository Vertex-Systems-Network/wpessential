<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupAbilityHandler;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupValidationService;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class FieldGroupAbilityIdentityTest extends TestCase
{
    public function testSaveSeedsIdentityAndUpdateWithoutUuidPreservesIt(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, FieldGroupAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];

        $uuid = $created['payload']['fields'][0]['uuid'];
        self::assertIsString($uuid);
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $uuid);
        self::assertTrue($created['payload']['fields'][0]['repeatability']['enabled']);
        self::assertTrue($created['payload']['fields'][0]['repeatability']['sortable']);

        $updatedPayload = $this->payload();
        $updatedPayload['fields'][0]['label'] = 'Updated Speaker';
        $updated = $this->handler($repository, FieldGroupAbilityHandler::SAVE)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'payload' => $updatedPayload,
        ], $this->context())['definition'];

        self::assertSame($uuid, $updated['payload']['fields'][0]['uuid']);
        self::assertTrue($updated['payload']['fields'][0]['repeatability']['enabled']);
        self::assertTrue($updated['payload']['fields'][0]['repeatability']['sortable']);
    }

    public function testStatusTransitionPreservesCanonicalRepeatabilityAndIdentity(): void
    {
        $repository = new InMemoryDefinitionRepository();
        $created = $this->handler($repository, FieldGroupAbilityHandler::SAVE)->handle([
            'payload' => $this->payload(),
        ], $this->context())['definition'];
        $uuid = $created['payload']['fields'][0]['uuid'];

        $published = $this->handler($repository, FieldGroupAbilityHandler::STATUS)->handle([
            'id' => $created['id'],
            'expected_revision' => 1,
            'status' => 'published',
        ], $this->context())['definition'];

        self::assertSame($uuid, $published['payload']['fields'][0]['uuid']);
        self::assertTrue($published['payload']['fields'][0]['repeatability']['enabled']);
        self::assertTrue($published['payload']['fields'][0]['repeatability']['sortable']);
        self::assertSame('published', $published['status']);
    }

    private function handler(InMemoryDefinitionRepository $repository, string $action): FieldGroupAbilityHandler
    {
        $normalizer = new FieldGroupDefinitionNormalizer();
        return new FieldGroupAbilityHandler(
            $repository,
            $normalizer,
            new FieldGroupValidationService($repository, $normalizer),
            $action,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(1), 1);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'group_key' => 'event_speakers',
            'title' => 'Event Speakers',
            'fields' => [[
                'key' => 'speaker_name',
                'label' => 'Speaker',
                'type' => 'text',
                'cloneable' => true,
                'sortable' => true,
                'min_clones' => 1,
                'max_clones' => 8,
            ]],
        ];
    }
}
