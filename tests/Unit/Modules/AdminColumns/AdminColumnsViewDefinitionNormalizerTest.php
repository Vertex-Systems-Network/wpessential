<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsViewDefinitionNormalizer;

final class AdminColumnsViewDefinitionNormalizerTest extends TestCase
{
    public function testNormalizesBoundedAuthoredViewDefinition(): void
    {
        $normalized = (new AdminColumnsViewDefinitionNormalizer())->normalize($this->payload());

        self::assertSame('posts_overview', $normalized['view_key']);
        self::assertSame('Posts overview', $normalized['name']);
        self::assertSame(['type' => 'post_type', 'key' => 'post'], $normalized['target']);
        self::assertCount(2, $normalized['columns']);
        self::assertTrue($normalized['columns'][0]['primary']);
        self::assertSame('fields', $normalized['columns'][1]['source']['owner']);
        self::assertSame('hidden', $normalized['visibility']['mode']);
    }

    public function testRejectsDuplicateColumnKeys(): void
    {
        $payload = $this->payload();
        $payload['columns'][1]['key'] = 'title';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate Column key');
        (new AdminColumnsViewDefinitionNormalizer())->normalize($payload);
    }

    public function testRejectsMultiplePrimaryColumns(): void
    {
        $payload = $this->payload();
        $payload['columns'][1]['primary'] = true;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at most one primary');
        (new AdminColumnsViewDefinitionNormalizer())->normalize($payload);
    }

    public function testRejectsPersonalOrEffectiveStateInsideSharedDefinition(): void
    {
        $payload = $this->payload();
        $payload['personal_preferences'] = ['density' => 'compact'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unsupported option "personal_preferences"');
        (new AdminColumnsViewDefinitionNormalizer())->normalize($payload);
    }

    public function testRejectsExecutableOrPrivateSourceOwnerFallback(): void
    {
        $payload = $this->payload();
        $payload['columns'][1]['source']['owner'] = 'callback';
        $payload['columns'][1]['source']['reference'] = 'php.callback';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('source owner is not supported');
        (new AdminColumnsViewDefinitionNormalizer())->normalize($payload);
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'view_key' => 'posts_overview',
            'name' => 'Posts overview',
            'enabled' => true,
            'target' => ['type' => 'post_type', 'key' => 'post'],
            'assignment' => [
                'roles' => ['editor'],
                'users' => [7],
                'capabilities' => ['edit_posts'],
            ],
            'layout' => [
                'density' => 'comfortable',
                'sticky_header' => true,
                'horizontal_scroll' => true,
            ],
            'columns' => [
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000301',
                    'key' => 'title',
                    'label' => 'Title',
                    'enabled' => true,
                    'source' => ['owner' => 'native', 'reference' => 'post.title'],
                    'format' => 'text',
                    'primary' => true,
                    'layout' => [
                        'width' => 320,
                        'min_width' => 160,
                        'max_width' => 640,
                        'alignment' => 'left',
                        'responsive_priority' => 100,
                        'sticky' => true,
                    ],
                ],
                [
                    'uuid' => '01990f6e-1f30-4000-8000-000000000302',
                    'key' => 'rating',
                    'label' => 'Rating',
                    'source' => [
                        'owner' => 'fields',
                        'reference' => 'fields.01990f6e-1f30-4000-8000-000000000200.01990f6e-1f30-4000-8000-000000000202',
                    ],
                    'format' => 'number',
                ],
            ],
            'visibility' => ['mode' => 'hidden', 'reason' => 'Presentation preference only'],
        ];
    }
}
