<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;

final class PostMetaRegistrationCompilerTest extends TestCase
{
    private FieldDefinitionNormalizer $definitions;
    private PostMetaRegistrationCompiler $compiler;

    protected function setUp(): void
    {
        $this->definitions = new FieldDefinitionNormalizer();
        $this->compiler = new PostMetaRegistrationCompiler();
    }

    public function testCompilesScalarMetaWithExplicitCallbacksAndRestPolicy(): void
    {
        $registration = $this->compiler->compile(
            $this->field(['key' => 'headline', 'type' => 'text']),
            'post',
            showInRest: true,
        );

        self::assertSame('post', $registration['post_type']);
        self::assertSame('11111111-1111-4111-8111-111111111111', $registration['field_uuid']);
        self::assertSame('headline', $registration['meta_key']);
        self::assertSame('string', $registration['args']['type']);
        self::assertTrue($registration['args']['single']);
        self::assertSame(['schema' => ['type' => 'string']], $registration['args']['show_in_rest']);
        self::assertFalse($registration['args']['revisions_enabled']);
        self::assertIsCallable($registration['args']['sanitize_callback']);
        self::assertIsCallable($registration['args']['auth_callback']);

        $sanitize = $registration['args']['sanitize_callback'];
        self::assertSame('Hello', $sanitize('  Hello  '));
    }

    public function testCompilesIntegerAndNativeListSchemas(): void
    {
        $integer = $this->compiler->compile(
            $this->field(['key' => 'days', 'type' => 'number', 'settings' => ['integer' => true]]),
            'post',
        );
        self::assertSame('integer', $integer['args']['type']);
        self::assertSame(7, ($integer['args']['sanitize_callback'])('7'));

        $gallery = $this->compiler->compile(
            $this->field(['key' => 'gallery', 'type' => 'gallery']),
            'post',
            showInRest: true,
        );
        self::assertSame('array', $gallery['args']['type']);
        self::assertTrue($gallery['args']['single']);
        self::assertSame(
            ['schema' => ['type' => 'array', 'items' => ['type' => 'integer']]],
            $gallery['args']['show_in_rest'],
        );
        self::assertSame([4, 9], ($gallery['args']['sanitize_callback'])(['4', 9]));
    }

    public function testCompilesRepeatableValuesAsArrayOrMultipleRowsDeliberately(): void
    {
        $arrayRegistration = $this->compiler->compile(
            $this->field([
                'key' => 'aliases',
                'type' => 'text',
                'cloneable' => true,
                'max_clones' => 3,
            ]),
            'post',
            showInRest: true,
        );
        self::assertSame('array', $arrayRegistration['args']['type']);
        self::assertTrue($arrayRegistration['args']['single']);
        self::assertSame(
            ['schema' => ['type' => 'array', 'items' => ['type' => 'string']]],
            $arrayRegistration['args']['show_in_rest'],
        );
        self::assertSame(['One', 'Two'], ($arrayRegistration['args']['sanitize_callback'])([' One ', ' Two ']));

        $rowRegistration = $this->compiler->compile(
            $this->field([
                'key' => 'aliases',
                'type' => 'text',
                'cloneable' => true,
                'clone_as_multiple' => true,
            ]),
            'post',
            showInRest: true,
        );
        self::assertSame('string', $rowRegistration['args']['type']);
        self::assertFalse($rowRegistration['args']['single']);
        self::assertSame(['schema' => ['type' => 'string']], $rowRegistration['args']['show_in_rest']);
        self::assertSame('One', ($rowRegistration['args']['sanitize_callback'])(' One '));
    }

    public function testRequiresStableIdentityAndFailsClosedForUnsupportedTypes(): void
    {
        $withoutUuid = $this->definitions->normalize([
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ]);
        try {
            $this->compiler->compile($withoutUuid, 'post');
            self::fail('Storage should require a persisted stable UUID.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $group = $this->field([
            'key' => 'contact',
            'type' => 'group',
            'subfields' => [['key' => 'name', 'label' => 'Name', 'type' => 'text']],
        ]);
        $this->expectException(InvalidArgumentException::class);
        $this->compiler->compile($group, 'post');
    }

    public function testRejectsInvalidSanitizedValueInsteadOfCoercingIt(): void
    {
        $registration = $this->compiler->compile(
            $this->field(['key' => 'email', 'type' => 'email']),
            'post',
        );

        $this->expectException(InvalidArgumentException::class);
        ($registration['args']['sanitize_callback'])('not-an-email');
    }

    /** @param array<string,mixed> $definition @return array<string,mixed> */
    private function field(array $definition): array
    {
        $definition['uuid'] = $definition['uuid'] ?? '11111111-1111-4111-8111-111111111111';
        $definition['label'] = $definition['label'] ?? ucfirst(str_replace('_', ' ', (string) ($definition['key'] ?? 'field')));
        return $this->definitions->normalize($definition);
    }
}
