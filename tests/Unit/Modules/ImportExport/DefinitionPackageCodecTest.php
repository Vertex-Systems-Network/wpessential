<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\ImportExport;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\ImportExport\DefinitionPackageCodec;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class DefinitionPackageCodecTest extends TestCase
{
    public function testRoundTripPreservesPortableDefinitionContract(): void
    {
        $codec = new DefinitionPackageCodec();
        $record = $this->record();

        $package = $codec->create([$record]);
        $json = $codec->encode($package);
        $decoded = $codec->decode($json);

        self::assertSame(DefinitionPackageCodec::FORMAT, $decoded['manifest']['format']);
        self::assertSame(DefinitionPackageCodec::FORMAT_VERSION, $decoded['manifest']['format_version']);
        self::assertSame('definition', $decoded['manifest']['package_type']);
        self::assertSame('excluded', $decoded['manifest']['secret_policy']);
        self::assertFalse($decoded['manifest']['runtime_data_included']);
        self::assertSame(1, $decoded['manifest']['definition_count']);
        self::assertSame($record['id'], $decoded['definitions'][0]['id']);
        self::assertSame($record['checksum'], $decoded['definitions'][0]['checksum']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $codec->packageChecksum($decoded));
    }

    public function testTamperedPayloadIsRejectedEvenWhenAggregateChecksumIsRecomputed(): void
    {
        $codec = new DefinitionPackageCodec();
        $package = $codec->create([$this->record()]);
        $package['definitions'][0]['payload']['name'] = 'Tampered Books';
        $package['manifest']['definitions_checksum'] = $this->checksum($package['definitions']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('checksum-invalid');
        $codec->verify($package);
    }

    public function testDuplicateDefinitionUuidIsRejected(): void
    {
        $codec = new DefinitionPackageCodec();
        $package = $codec->create([$this->record()]);
        $package['definitions'][] = $package['definitions'][0];
        $package['manifest']['definition_count'] = 2;
        $package['manifest']['definitions_checksum'] = $this->checksum($package['definitions']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate definition UUIDs');
        $codec->verify($package);
    }

    /** @return array<string,mixed> */
    private function record(): array
    {
        $definition = new Definition(
            id: '11111111-1111-4111-8111-111111111111',
            slug: 'cpt-library-book',
            type: 'post_type',
            schemaVersion: 1,
            ownerSurfaceId: 1,
            status: DefinitionStatus::Draft,
            payload: [
                'post_type_key' => 'library_book',
                'name' => 'Books',
                'singular_name' => 'Book',
                'public' => true,
                'show_in_rest' => true,
                'supports' => ['title', 'editor'],
            ],
            revision: 3,
        );

        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->computedChecksum(),
        ];
    }

    private function checksum(mixed $value): string
    {
        return hash('sha256', json_encode(
            $this->canonicalize($value),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }

    private function canonicalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (array_is_list($value)) {
            return array_map($this->canonicalize(...), $value);
        }
        ksort($value, SORT_STRING);
        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }
        return $value;
    }
}
