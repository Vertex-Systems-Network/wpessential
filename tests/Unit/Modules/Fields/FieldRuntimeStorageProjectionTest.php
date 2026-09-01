<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldGroupDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldGroupRuntimeStorageProjection;

final class FieldRuntimeStorageProjectionTest extends TestCase
{
    public function testLegacyGroupDefaultsRemainFailClosedForRuntimeBinding(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'legacy_group',
            'title' => 'Legacy Group',
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
        ], true);

        self::assertSame(['mode' => 'unconfigured'], $group['storage']);
        self::assertFalse($group['show_in_rest']);
        self::assertSame('disabled', $group['revision_policy']);
        self::assertFalse($group['fields'][0]['show_in_rest']);
        self::assertSame('auto', $group['fields'][0]['rest_schema']);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupRuntimeStorageProjection())->projectGroup($group);
    }

    public function testNativePostMetaProjectionCompilesExplicitRestAndRevisionIntent(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'speaker_profile',
            'title' => 'Speaker Profile',
            'storage' => ['mode' => 'native_post_meta'],
            'show_in_rest' => true,
            'revision_policy' => 'enabled',
            'fields' => [
                [
                    'key' => 'subtitle',
                    'label' => 'Subtitle',
                    'type' => 'text',
                    'show_in_rest' => true,
                    'rest_schema' => 'auto',
                ],
            ],
        ], true);

        $projection = new FieldGroupRuntimeStorageProjection();
        $groupRuntime = $projection->projectGroup($group);
        $fieldRuntime = $projection->projectField($group['fields'][0], $groupRuntime['show_in_rest']);

        self::assertSame([
            'mode' => 'native_post_meta',
            'show_in_rest' => true,
            'revisions_enabled' => true,
        ], $groupRuntime);
        self::assertSame([
            'show_in_rest' => true,
            'rest_schema' => 'auto',
        ], $fieldRuntime);
    }

    public function testGroupRestPolicyIsHardUpperBoundForFieldExposure(): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'private_group',
            'title' => 'Private Group',
            'storage' => ['mode' => 'native_post_meta'],
            'show_in_rest' => false,
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text', 'show_in_rest' => true],
            ],
        ], true);

        $projection = new FieldGroupRuntimeStorageProjection();
        $groupRuntime = $projection->projectGroup($group);
        $fieldRuntime = $projection->projectField($group['fields'][0], $groupRuntime['show_in_rest']);

        self::assertFalse($fieldRuntime['show_in_rest']);
    }

    /** @dataProvider unsupportedStorageModeProvider */
    public function testRuntimePostMetaProjectionRejectsNonNativePostMetaModes(string $mode): void
    {
        $group = (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'provider_group',
            'title' => 'Provider Group',
            'storage' => ['mode' => $mode],
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
        ], true);

        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupRuntimeStorageProjection())->projectGroup($group);
    }

    /** @return iterable<string,array{string}> */
    public static function unsupportedStorageModeProvider(): iterable
    {
        yield 'unconfigured' => ['unconfigured'];
        yield 'options' => ['native_options'];
        yield 'term meta' => ['native_term_meta'];
        yield 'user meta' => ['native_user_meta'];
        yield 'comment meta' => ['native_comment_meta'];
        yield 'custom table' => ['custom_table'];
        yield 'registered provider' => ['registered_provider'];
    }

    public function testNormalizerRejectsArbitraryRestSchemaOverride(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'unsafe_schema',
            'title' => 'Unsafe Schema',
            'storage' => ['mode' => 'native_post_meta'],
            'fields' => [[
                'key' => 'subtitle',
                'label' => 'Subtitle',
                'type' => 'text',
                'show_in_rest' => true,
                'rest_schema' => '{"type":"object"}',
            ]],
        ], true);
    }

    public function testNormalizerRejectsUnknownStorageConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new FieldGroupDefinitionNormalizer())->normalize([
            'group_key' => 'unsafe_storage',
            'title' => 'Unsafe Storage',
            'storage' => ['mode' => 'native_post_meta', 'callback' => 'dangerous_callback'],
            'fields' => [
                ['key' => 'subtitle', 'label' => 'Subtitle', 'type' => 'text'],
            ],
        ], true);
    }
}
