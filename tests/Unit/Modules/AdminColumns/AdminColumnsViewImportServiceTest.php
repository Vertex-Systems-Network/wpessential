<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionService;
use WPEssential\Modules\AdminColumns\AdminColumnsViewImportService;
use WPEssential\Modules\AdminColumns\AdminColumnsViewPortabilityCodec;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class AdminColumnsViewImportServiceTest extends TestCase
{
    private const VIEW_ID = '01990f6e-1f30-4000-8000-000000000398';
    private const OTHER_VIEW_ID = '01990f6e-1f30-4000-8000-000000000399';

    public function testCreateImportPreservesPortableIdentityAndStartsDestinationRevisionAtOne(): void
    {
        [$codec, $views, $imports, $repository] = $this->services();
        $document = $codec->encode(self::VIEW_ID, 7, $this->payload());

        $result = $imports->commitCreate($document, [
            'target_keys' => ['post' => 'article'],
        ]);

        self::assertSame(AdminColumnsViewImportService::CONTRACT_VERSION, $result['contract_version']);
        self::assertSame(self::VIEW_ID, $result['source_view_id']);
        self::assertSame(7, $result['source_revision']);

        $destination = $result['destination'];
        self::assertSame(self::VIEW_ID, $destination->id);
        self::assertSame(1, $destination->revision);
        self::assertSame(DefinitionStatus::Draft, $destination->status);
        self::assertSame('article', $destination->payload['target']['key']);
        self::assertSame('portable_posts', $destination->payload['view_key']);
        self::assertSame($destination->computedChecksum(), $destination->checksum);
        self::assertSame($destination, $repository->get(self::VIEW_ID));
        self::assertSame($destination, $views->get(self::VIEW_ID));
    }

    public function testExplicitImportedStatusIsPreservedWithoutChangingSourceRevisionSemantics(): void
    {
        [$codec, , $imports] = $this->services();

        $result = $imports->commitCreate(
            $codec->encode(self::VIEW_ID, 11, $this->payload()),
            ['target_keys' => ['post' => 'post']],
            DefinitionStatus::Published,
        );

        self::assertSame(11, $result['source_revision']);
        self::assertSame(1, $result['destination']->revision);
        self::assertSame(DefinitionStatus::Published, $result['destination']->status);
    }

    public function testExistingDestinationIdCannotBeOverwritten(): void
    {
        [$codec, , $imports] = $this->services();
        $document = $codec->encode(self::VIEW_ID, 1, $this->payload());
        $imports->commitCreate($document, ['target_keys' => ['post' => 'post']]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('id already exists');
        $imports->commitCreate($document, ['target_keys' => ['post' => 'post']]);
    }

    public function testDuplicateOwnedViewKeyCannotBeImportedUnderAnotherId(): void
    {
        [$codec, , $imports] = $this->services();
        $imports->commitCreate(
            $codec->encode(self::VIEW_ID, 1, $this->payload()),
            ['target_keys' => ['post' => 'post']],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already owned');
        $imports->commitCreate(
            $codec->encode(self::OTHER_VIEW_ID, 9, $this->payload()),
            ['target_keys' => ['post' => 'post']],
        );
    }

    public function testImportStillRequiresCompleteCodecRemaps(): void
    {
        [$codec, , $imports] = $this->services();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('explicit remap for target key');
        $imports->commitCreate($codec->encode(self::VIEW_ID, 1, $this->payload()), []);
    }

    /**
     * @return array{
     *   0:AdminColumnsViewPortabilityCodec,
     *   1:AdminColumnsViewDefinitionService,
     *   2:AdminColumnsViewImportService,
     *   3:InMemoryDefinitionRepository
     * }
     */
    private function services(): array
    {
        $normalizer = new AdminColumnsViewDefinitionNormalizer();
        $codec = new AdminColumnsViewPortabilityCodec($normalizer);
        $repository = new InMemoryDefinitionRepository();
        $views = new AdminColumnsViewDefinitionService($repository, $normalizer);

        return [
            $codec,
            $views,
            new AdminColumnsViewImportService($codec, $views),
            $repository,
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
            'columns' => [[
                'uuid' => '01990f6e-1f30-4000-8000-000000000301',
                'key' => 'id',
                'label' => 'ID',
                'source' => ['owner' => 'native', 'reference' => 'post.id'],
                'format' => 'number',
                'primary' => true,
            ]],
        ];
    }
}
