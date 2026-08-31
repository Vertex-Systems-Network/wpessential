<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueNormalizer;

final class FieldValueNormalizerHardeningTest extends TestCase
{
    public function testRequiredMultipleChoiceRejectsEmptyList(): void
    {
        $field = (new FieldDefinitionNormalizer())->normalize([
            'key' => 'regions',
            'label' => 'Regions',
            'type' => 'multi_select',
            'settings' => [
                'required' => true,
                'choices' => ['antalya' => 'Antalya'],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        (new FieldValueNormalizer())->normalize($field, []);
    }

    public function testRgbAndHslWithoutAlphaRejectFourthComponent(): void
    {
        $field = (new FieldDefinitionNormalizer())->normalize([
            'key' => 'brand_color',
            'label' => 'Brand Color',
            'type' => 'color',
        ]);
        $normalizer = new FieldValueNormalizer();

        foreach (['rgb(10, 20, 30, 0.5)', 'hsl(120, 50%, 50%, 0.5)'] as $invalid) {
            try {
                $normalizer->normalize($field, $invalid);
                self::fail(sprintf('Invalid color syntax "%s" should have been rejected.', $invalid));
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }

        self::assertSame('rgba(10, 20, 30, 0.5)', $normalizer->normalize($field, 'rgba(10, 20, 30, 0.5)'));
        self::assertSame('hsla(120, 50%, 50%, 0.5)', $normalizer->normalize($field, 'hsla(120, 50%, 50%, 0.5)'));
    }
}
