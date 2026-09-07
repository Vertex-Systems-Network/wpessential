<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDependencyManifest;

final class AdminColumnsViewDependencyManifestTest extends TestCase
{
    public function testProjectsDeterministicTypedDependenciesAndConsolidatesSources(): void
    {
        $manifest = (new AdminColumnsViewDependencyManifest(
            new AdminColumnsViewDefinitionNormalizer(),
        ))->project($this->payload());

        self::assertSame(AdminColumnsViewDependencyManifest::CONTRACT_VERSION, $manifest['contract_version']);
        self::assertSame('editorial_posts', $manifest['view_key']);
        self::assertSame(['type' => 'post_type', 'key' => 'post'], $manifest['target']);
        self::assertSame(
            [
                [
                    'owner' => 'native',
                    'reference' => 'post.id',
                    'column_keys' => ['id'],
                ],
                [
                    'owner' => 'native',
                    'reference' => 'post.title',
                    'column_keys' => ['title', 'title_copy'],
                ],
                [
                    'owner' => 'fields',
                    'reference' => 'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222',
                    'column_keys' => ['subtitle'],
                ],
            ],
            $manifest['sources'],
        );
        self::assertSame(
            [
                'roles' => ['editor', 'administrator'],
                'users' => [7, 19],
                'capabilities' => ['edit_posts'],
            ],
            $manifest['assignments'],
        );
    }

    public function testManifestWithoutAssignmentUsesExplicitEmptyTypedSets(): void
    {
        $payload = $this->payload();
        unset($payload['assignment']);

        $manifest = (new AdminColumnsViewDependencyManifest(
            new AdminColumnsViewDefinitionNormalizer(),
        ))->project($payload);

        self::assertSame(
            ['roles' => [], 'users' => [], 'capabilities' => []],
            $manifest['assignments'],
        );
    }

    public function testCanonicalNormalizerRemainsAuthoritativeForMalformedPayloads(): void
    {
        $payload = $this->payload();
        $payload['target'] = ['type' => 'private_engine', 'key' => 'post'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('target type is not supported');

        (new AdminColumnsViewDependencyManifest(
            new AdminColumnsViewDefinitionNormalizer(),
        ))->project($payload);
    }

    public function testSourceManifestPreservesFirstUseOrderRatherThanSorting(): void
    {
        $payload = $this->payload();
        $payload['columns'] = [
            $payload['columns'][3],
            $payload['columns'][1],
            $payload['columns'][0],
            $payload['columns'][2],
        ];

        $manifest = (new AdminColumnsViewDependencyManifest(
            new AdminColumnsViewDefinitionNormalizer(),
        ))->project($payload);

        self::assertSame(
            [
                'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222',
                'post.title',
                'post.id',
            ],
            array_column($manifest['sources'], 'reference'),
        );
        self::assertSame(['title', 'title_copy'], $manifest['sources'][1]['column_keys']);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'view_key' => 'editorial_posts',
            'name' => 'Editorial Posts',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'assignment' => [
                'roles' => ['editor', 'administrator'],
                'users' => [7, 19],
                'capabilities' => ['edit_posts'],
            ],
            'columns' => [
                [
                    'uuid' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
                    'key' => 'id',
                    'label' => 'ID',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.id'],
                    'format' => 'number',
                    'primary' => true,
                ],
                [
                    'uuid' => 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
                    'key' => 'title',
                    'label' => 'Title',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                    'primary' => false,
                ],
                [
                    'uuid' => 'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
                    'key' => 'title_copy',
                    'label' => 'Title copy',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                    'primary' => false,
                ],
                [
                    'uuid' => 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'enabled' => true,
                    'source' => [
                        'owner' => 'fields',
                        'reference' => 'fields.11111111-1111-4111-8111-111111111111.22222222-2222-4222-8222-222222222222',
                    ],
                    'format' => 'text',
                    'primary' => false,
                ],
            ],
        ];
    }
}
