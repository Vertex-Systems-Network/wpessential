<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewPortabilityCodec;

final class AdminColumnsViewPortabilityCodecTest extends TestCase
{
    private const VIEW_ID = '01990f6e-1f30-4000-8000-000000000398';
    private const FIELD_OLD = 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202';
    private const FIELD_NEW = 'fields.01990f6e-1f30-4000-8000-000000000210.01990f6e-1f30-4000-8000-000000000212';

    public function testRoundTripPreservesStableIdentityAndRequiresExplicitEnvironmentRemaps(): void
    {
        $codec = $this->codec();
        $encoded = $codec->encode(self::VIEW_ID, 7, $this->payload());

        $decoded = $codec->decode($encoded, [
            'target_keys' => ['post' => 'article'],
            'source_references' => ['fields|' . self::FIELD_OLD => self::FIELD_NEW],
            'assignment_roles' => ['editor' => 'content_editor'],
            'assignment_users' => [7 => 70],
            'assignment_capabilities' => ['edit_posts' => 'edit_articles'],
        ]);

        self::assertSame(AdminColumnsViewPortabilityCodec::CONTRACT_VERSION, $decoded['contract_version']);
        self::assertSame(self::VIEW_ID, $decoded['view_id']);
        self::assertSame(7, $decoded['source_revision']);
        self::assertSame('article', $decoded['payload']['target']['key']);
        self::assertSame(
            '01990f6e-1f30-4000-8000-000000000301',
            $decoded['payload']['columns'][0]['uuid'],
        );
        self::assertSame(
            '01990f6e-1f30-4000-8000-000000000302',
            $decoded['payload']['columns'][1]['uuid'],
        );
        self::assertSame('post.id', $decoded['payload']['columns'][0]['source']['reference']);
        self::assertSame(self::FIELD_NEW, $decoded['payload']['columns'][1]['source']['reference']);
        self::assertSame(['content_editor'], $decoded['payload']['assignment']['roles']);
        self::assertSame([70], $decoded['payload']['assignment']['users']);
        self::assertSame(['edit_articles'], $decoded['payload']['assignment']['capabilities']);
    }

    public function testMissingSensitiveSourceRemapFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('explicit remap for source reference');

        $this->codec()->decode(
            $this->codec()->encode(self::VIEW_ID, 1, $this->payload()),
            [
                'target_keys' => ['post' => 'post'],
                'assignment_roles' => ['editor' => 'editor'],
                'assignment_users' => [7 => 7],
                'assignment_capabilities' => ['edit_posts' => 'edit_posts'],
            ],
        );
    }

    public function testUnusedForeignRemapFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unused or foreign mapping');

        $this->codec()->decode(
            $this->codec()->encode(self::VIEW_ID, 1, $this->nativeOnlyPayload()),
            [
                'target_keys' => ['post' => 'post', 'page' => 'page'],
            ],
        );
    }

    public function testCollapsingTwoMappingsToOneDestinationFailsClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot collapse');

        $this->codec()->decode(
            $this->codec()->encode(self::VIEW_ID, 1, $this->nativeOnlyPayload()),
            [
                'target_keys' => ['post' => 'article'],
                'assignment_roles' => [
                    'editor' => 'same_role',
                    'author' => 'same_role',
                ],
            ],
        );
    }

    public function testPortableDocumentCannotInjectTrustedExecutionScope(): void
    {
        $encoded = $this->codec()->encode(self::VIEW_ID, 1, $this->nativeOnlyPayload());
        $document = json_decode($encoded, true, 64, JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        $document['site_id'] = 99;
        $tampered = json_encode($document, JSON_THROW_ON_ERROR);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported key');
        $this->codec()->decode($tampered, ['target_keys' => ['post' => 'post']]);
    }

    public function testMalformedContractIdentityFailsClosed(): void
    {
        $encoded = $this->codec()->encode(self::VIEW_ID, 1, $this->nativeOnlyPayload());
        $document = json_decode($encoded, true, 64, JSON_THROW_ON_ERROR);
        self::assertIsArray($document);
        $document['owner_surface_id'] = 999;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contract identity');
        $this->codec()->decode(
            json_encode($document, JSON_THROW_ON_ERROR),
            ['target_keys' => ['post' => 'post']],
        );
    }

    private function codec(): AdminColumnsViewPortabilityCodec
    {
        return new AdminColumnsViewPortabilityCodec(new AdminColumnsViewDefinitionNormalizer());
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        $payload = $this->nativeOnlyPayload();
        $payload['columns'][] = [
            'uuid' => '01990f6e-1f30-4000-8000-000000000302',
            'key' => 'headline',
            'label' => 'Headline',
            'source' => ['owner' => 'fields', 'reference' => self::FIELD_OLD],
            'format' => 'text',
            'primary' => false,
        ];
        $payload['assignment'] = [
            'roles' => ['editor'],
            'users' => [7],
            'capabilities' => ['edit_posts'],
        ];
        return $payload;
    }

    /** @return array<string,mixed> */
    private function nativeOnlyPayload(): array
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
