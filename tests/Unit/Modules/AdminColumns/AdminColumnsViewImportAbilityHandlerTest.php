<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Modules\AdminColumns\AdminColumnsViewImportAbilityHandler;
use WPEssential\Modules\AdminColumns\AdminColumnsViewImportService;
use WPEssential\Modules\AdminColumns\AdminColumnsViewPortabilityCodec;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class AdminColumnsViewImportAbilityHandlerTest extends TestCase
{
    public function testCommitsPortableViewCreateOnlyAsDraftWithStableIdentity(): void
    {
        [$handler, $codec] = $this->handler();
        $viewId = '01990f6e-1f30-4000-8000-000000000481';
        $document = $codec->encode($viewId, 7, $this->payload());

        $result = $handler->handle([
            'document' => $document,
            'remaps' => [
                'target_keys' => ['post' => 'post'],
            ],
        ], $this->context());

        self::assertIsArray($result);
        self::assertSame(1, $result['contract_version']);
        self::assertSame($viewId, $result['source_view_id']);
        self::assertSame(7, $result['source_revision']);
        self::assertSame($viewId, $result['destination']['id']);
        self::assertSame(1, $result['destination']['revision']);
        self::assertSame(DefinitionStatus::Draft->value, $result['destination']['status']);
        self::assertSame('portable_posts', $result['destination']['payload']['view_key']);
    }

    public function testRejectsCallerStatusAndDuplicateDestinationIdentity(): void
    {
        [$handler, $codec] = $this->handler();
        $viewId = '01990f6e-1f30-4000-8000-000000000482';
        $document = $codec->encode($viewId, 3, $this->payload());
        $input = [
            'document' => $document,
            'remaps' => ['target_keys' => ['post' => 'post']],
        ];

        $handler->handle($input, $this->context());

        try {
            $handler->handle($input, $this->context());
            self::fail('Expected duplicate portable View id to fail closed.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('already exists', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported key');
        $handler->handle($input + ['status' => 'published'], $this->context());
    }

    public function testRequiresBoundedDocumentAndObjectRemaps(): void
    {
        [$handler] = $this->handler();

        try {
            $handler->handle([
                'document' => [],
                'remaps' => [],
            ], $this->context());
            self::fail('Expected non-string document to fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('document must be a string', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('remaps must be an object/map');
        $handler->handle([
            'document' => '{}',
            'remaps' => ['not', 'a', 'map'],
        ], $this->context());
    }

    /** @return array{AdminColumnsViewImportAbilityHandler,AdminColumnsViewPortabilityCodec} */
    private function handler(): array
    {
        $normalizer = new AdminColumnsViewDefinitionNormalizer();
        $views = new AdminColumnsViewDefinitionService(
            new InMemoryDefinitionRepository(),
            $normalizer,
        );
        $codec = new AdminColumnsViewPortabilityCodec($normalizer);
        return [
            new AdminColumnsViewImportAbilityHandler(
                new AdminColumnsViewImportService($codec, $views),
            ),
            $codec,
        ];
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'view_key' => 'portable_posts',
            'name' => 'Portable posts',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000483',
                    'key' => 'title',
                    'label' => 'Title',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                ],
            ],
        ];
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(new Principal(7), 1);
    }
}
