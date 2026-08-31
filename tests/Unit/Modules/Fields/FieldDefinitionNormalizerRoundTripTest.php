<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;

final class FieldDefinitionNormalizerRoundTripTest extends TestCase
{
    public function testCanonicalPresetRepeatabilityAndNestedIdentityRoundTripWithoutSemanticLoss(): void
    {
        $normalizer = new FieldDefinitionNormalizer();
        $first = $normalizer->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'regions',
            'label' => 'Regions',
            'type' => 'multi_select',
            'cloneable' => true,
            'sortable' => true,
            'clone_default' => true,
            'clone_as_multiple' => true,
            'clone_empty_start' => true,
            'min_clones' => 1,
            'max_clones' => 4,
            'add_button_label' => 'Add region set',
            'settings' => [
                'choices' => ['antalya' => 'Antalya', 'dubai' => 'Dubai'],
            ],
        ]);

        self::assertSame($first, $normalizer->normalize($first));
        self::assertSame('multi_select', $first['preset']);
        self::assertTrue($first['repeatability']['enabled']);
        self::assertTrue($first['repeatability']['sortable']);
        self::assertTrue($first['repeatability']['store_as_multiple']);
    }
}
