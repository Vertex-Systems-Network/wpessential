<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValueNormalizer;

final class FieldValueNormalizerTest extends TestCase
{
    private FieldDefinitionNormalizer $definitions;
    private FieldValueNormalizer $values;

    protected function setUp(): void
    {
        $this->definitions = new FieldDefinitionNormalizer();
        $this->values = new FieldValueNormalizer();
    }

    public function testNormalizesPrimitiveAndDatePresetValues(): void
    {
        $text = $this->field([
            'key' => 'headline',
            'type' => 'text',
            'settings' => ['required' => true, 'max_length' => 20],
        ]);
        self::assertSame('Hello Fields', $this->values->normalize($text, '  Hello Fields  '));

        $week = $this->field(['key' => 'week', 'type' => 'week']);
        self::assertSame('2026-W36', $this->values->normalize($week, '2026-W36'));

        $month = $this->field(['key' => 'month', 'type' => 'month']);
        self::assertSame('2026-09', $this->values->normalize($month, '2026-09'));

        $this->expectException(InvalidArgumentException::class);
        $this->values->normalize($text, null);
    }

    public function testRejectsInvalidEmailAndUnsafeUrlScheme(): void
    {
        $email = $this->field(['key' => 'email', 'type' => 'email']);
        try {
            $this->values->normalize($email, 'not-an-email');
            self::fail('Invalid email should have been rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $url = $this->field(['key' => 'website', 'type' => 'url']);
        $this->expectException(InvalidArgumentException::class);
        $this->values->normalize($url, 'ftp://example.com/file.txt');
    }

    public function testNormalizesMultiSelectAndRejectsUnknownChoice(): void
    {
        $field = $this->field([
            'key' => 'regions',
            'type' => 'multi_select',
            'settings' => [
                'choices' => [
                    'antalya' => 'Antalya',
                    'dubai' => 'Dubai',
                ],
            ],
        ]);

        self::assertSame(['antalya', 'dubai'], $this->values->normalize($field, ['antalya', 'dubai']));

        $this->expectException(InvalidArgumentException::class);
        $this->values->normalize($field, ['istanbul']);
    }

    public function testNormalizesCloneableValuesAndEnforcesBounds(): void
    {
        $field = $this->field([
            'key' => 'aliases',
            'type' => 'text',
            'cloneable' => true,
            'sortable' => true,
            'min_clones' => 1,
            'max_clones' => 2,
        ]);

        self::assertSame(['One', 'Two'], $this->values->normalize($field, [' One ', ' Two ']));

        $this->expectException(InvalidArgumentException::class);
        $this->values->normalize($field, ['One', 'Two', 'Three']);
    }

    public function testNormalizesNestedGroupAndRepeaterRows(): void
    {
        $group = $this->field([
            'key' => 'contact',
            'type' => 'group',
            'subfields' => [
                ['key' => 'name', 'type' => 'text', 'settings' => ['required' => true]],
                ['key' => 'email', 'type' => 'email'],
            ],
        ]);

        self::assertSame(
            ['name' => 'Hanan', 'email' => 'hello@example.com'],
            $this->values->normalize($group, ['name' => ' Hanan ', 'email' => 'hello@example.com']),
        );

        $repeater = $this->field([
            'key' => 'team',
            'type' => 'repeater',
            'settings' => ['min_rows' => 1, 'max_rows' => 2],
            'subfields' => [
                ['key' => 'name', 'type' => 'text', 'settings' => ['required' => true]],
                ['key' => 'days', 'type' => 'duration_days'],
            ],
        ]);

        self::assertSame(
            [
                ['name' => 'A', 'days' => 5],
                ['name' => 'B', 'days' => 7],
            ],
            $this->values->normalize($repeater, [
                ['name' => 'A', 'days' => '5'],
                ['name' => 'B', 'days' => 7],
            ]),
        );
    }

    public function testNormalizesMediaColorAndDatetime(): void
    {
        $image = $this->field(['key' => 'hero_image', 'type' => 'image']);
        self::assertSame(42, $this->values->normalize($image, '42'));

        $color = $this->field(['key' => 'brand', 'type' => 'color_alpha']);
        self::assertSame('rgba(10, 20, 30, 0.5)', $this->values->normalize($color, 'RGBA(10, 20, 30, 0.5)'));

        $datetime = $this->field(['key' => 'starts_at', 'type' => 'datetime']);
        self::assertSame('2026-09-01T07:00:00Z', $this->values->normalize($datetime, '2026-09-01T12:00:00+05:00'));
    }

    public function testFailsClosedForUiOnlyAndRelationsOwnedValues(): void
    {
        $section = $this->field(['key' => 'section', 'type' => 'section']);
        try {
            $this->values->normalize($section, 'should-not-store');
            self::fail('UI-only field should have rejected persisted content.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $relation = $this->field(['key' => 'related', 'type' => 'relationship']);
        $this->expectException(InvalidArgumentException::class);
        $this->values->normalize($relation, 123);
    }

    /** @param array<string,mixed> $definition @return array<string,mixed> */
    private function field(array $definition): array
    {
        if (!isset($definition['label'])) {
            $definition['label'] = ucfirst(str_replace('_', ' ', (string) ($definition['key'] ?? 'field')));
        }
        return $this->definitions->normalize($definition);
    }
}
