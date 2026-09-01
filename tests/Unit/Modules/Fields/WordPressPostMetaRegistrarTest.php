<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;

final class WordPressPostMetaRegistrarTest extends TestCase
{
    public function testRegistersCompiledContractWhenRequiredSupportsExist(): void
    {
        $calls = [];
        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                $calls[] = [$postType, $metaKey, $args];
                return true;
            },
            static fn (string $postType, string $feature): bool => $postType === 'book'
                && in_array($feature, ['revisions', 'custom-fields'], true),
            static fn (string $objectType, string $objectSubtype): array => [],
        );

        $registrar->register($this->registration(showInRest: true, revisionsEnabled: true));

        self::assertCount(1, $calls);
        self::assertSame('book', $calls[0][0]);
        self::assertSame('headline', $calls[0][1]);
        self::assertTrue($calls[0][2]['revisions_enabled']);
    }

    public function testRejectsRevisionRegistrationForUnsupportedSubtype(): void
    {
        $registrar = new WordPressPostMetaRegistrar(
            static fn (string $postType, string $metaKey, array $args): bool => true,
            static fn (string $postType, string $feature): bool => false,
            static fn (string $objectType, string $objectSubtype): array => [],
        );

        $this->expectException(InvalidArgumentException::class);
        $registrar->register($this->registration(revisionsEnabled: true));
    }

    public function testRejectsRestExposureWithoutCustomFieldsSupport(): void
    {
        $registrar = new WordPressPostMetaRegistrar(
            static fn (string $postType, string $metaKey, array $args): bool => true,
            static fn (string $postType, string $feature): bool => $feature === 'revisions',
            static fn (string $objectType, string $objectSubtype): array => [],
        );

        $this->expectException(InvalidArgumentException::class);
        $registrar->register($this->registration(showInRest: true));
    }

    public function testSurfacesWordPressRegistrationFailure(): void
    {
        $registrar = new WordPressPostMetaRegistrar(
            static fn (string $postType, string $metaKey, array $args): bool => false,
            static fn (string $postType, string $feature): bool => true,
            static fn (string $objectType, string $objectSubtype): array => [],
        );

        $this->expectException(RuntimeException::class);
        $registrar->register($this->registration());
    }

    public function testRejectsForeignSubtypeCollisionBeforeCallingWordPress(): void
    {
        $calls = 0;
        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                ++$calls;
                return true;
            },
            static fn (string $postType, string $feature): bool => true,
            static fn (string $objectType, string $objectSubtype): array => $objectSubtype === 'book'
                ? ['headline' => self::existingArgs('Another plugin field.')]
                : [],
        );

        try {
            $registrar->register($this->registration());
            self::fail('Expected foreign post-meta ownership collision.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already owned by another registration', $exception->getMessage());
        }

        self::assertSame(0, $calls);
    }

    public function testRejectsGlobalPostScopeCollisionBeforeCallingWordPress(): void
    {
        $calls = 0;
        $registration = $this->registration();
        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                ++$calls;
                return true;
            },
            static fn (string $postType, string $feature): bool => true,
            static fn (string $objectType, string $objectSubtype): array => $objectSubtype === ''
                ? ['headline' => $registration['args']]
                : [],
        );

        try {
            $registrar->register($registration);
            self::fail('Expected global post-meta ownership collision.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('global post scope', $exception->getMessage());
        }

        self::assertSame(0, $calls);
    }

    public function testMatchingWpeSubtypeRegistrationIsIdempotent(): void
    {
        $calls = 0;
        $registration = $this->registration(showInRest: true, revisionsEnabled: true);
        $existing = $registration['args'];
        $existing['label'] = 'Older mutable label';

        $registrar = new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$calls): bool {
                ++$calls;
                return true;
            },
            static fn (string $postType, string $feature): bool => true,
            static fn (string $objectType, string $objectSubtype): array => $objectSubtype === 'book'
                ? ['headline' => $existing]
                : [],
        );

        $registrar->register($registration);

        self::assertSame(0, $calls);
    }

    /**
     * @return array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>}
     */
    private function registration(bool $showInRest = false, bool $revisionsEnabled = false): array
    {
        $field = (new FieldDefinitionNormalizer())->normalize([
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ]);

        return (new PostMetaRegistrationCompiler())->compile(
            $field,
            'book',
            showInRest: $showInRest,
            revisionsEnabled: $revisionsEnabled,
        );
    }

    /** @return array<string,mixed> */
    private static function existingArgs(string $description): array
    {
        return [
            'type' => 'string',
            'label' => 'Existing',
            'description' => $description,
            'single' => true,
            'sanitize_callback' => static fn (mixed $value): mixed => $value,
            'auth_callback' => static fn (): bool => true,
            'show_in_rest' => false,
            'revisions_enabled' => false,
        ];
    }
}
