<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\FieldValuePersistenceGuard;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;

final class FieldValuePersistenceGuardTest extends TestCase
{
    public function testAcceptsCanonicalNestedScalarArrayData(): void
    {
        $guard = new FieldValuePersistenceGuard();
        $value = ['name' => 'A', 'count' => 4, 'ratio' => 4.5, 'enabled' => true, 'items' => [1, 2, null]];

        self::assertSame($value, $guard->assertSafe($value));
    }

    /** @dataProvider unsafeValueProvider */
    public function testRejectsNonFiniteAndNonCanonicalPersistenceValues(mixed $value): void
    {
        $guard = new FieldValuePersistenceGuard();

        $this->expectException(InvalidArgumentException::class);
        $guard->assertSafe($value);
    }

    /** @return iterable<string,array{mixed}> */
    public static function unsafeValueProvider(): iterable
    {
        yield 'positive infinity' => [INF];
        yield 'negative infinity' => [-INF];
        yield 'nan' => [NAN];
        yield 'nested infinity' => [['value' => INF]];
        yield 'object' => [new \stdClass()];
    }

    public function testRegisteredMetaSanitizerCannotBypassFiniteGuard(): void
    {
        $definitions = new FieldDefinitionNormalizer();
        $field = $definitions->normalize([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'key' => 'ratio',
            'label' => 'Ratio',
            'type' => 'number',
        ]);
        $registration = (new PostMetaRegistrationCompiler())->compile($field, 'post');
        $sanitizer = $registration['args']['sanitize_callback'];

        self::assertIsCallable($sanitizer);
        self::assertSame(7.5, $sanitizer('7.5'));

        $this->expectException(InvalidArgumentException::class);
        $sanitizer('1e309');
    }
}
