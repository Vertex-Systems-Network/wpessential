<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\FieldDefinitionNormalizer;
use WPEssential\Modules\Fields\PostMetaRegistrationCompiler;
use WPEssential\Modules\Fields\WordPressPostMetaRegistrar;

final class WordPressPostMetaRegistrarRetirementTest extends TestCase
{
    public function testRetiresMatchingOwnedSubtypeRegistration(): void
    {
        $registration = $this->registration();
        $registered = ['book' => ['headline' => $registration['args']]];
        $unregisterCalls = 0;
        $registrar = $this->registrar($registered, $unregisterCalls);

        $registrar->retire($registration);

        self::assertSame(1, $unregisterCalls);
        self::assertArrayNotHasKey('headline', $registered['book']);
    }

    public function testRejectsForeignRegistrationBeforeRetirement(): void
    {
        $registration = $this->registration();
        $foreign = $registration['args'];
        $foreign['description'] = 'Another plugin field.';
        $registered = ['book' => ['headline' => $foreign]];
        $unregisterCalls = 0;
        $registrar = $this->registrar($registered, $unregisterCalls);

        try {
            $registrar->retire($registration);
            self::fail('Foreign registration retirement must fail closed.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString('already owned by another registration', $exception->getMessage());
        }

        self::assertSame(0, $unregisterCalls);
        self::assertArrayHasKey('headline', $registered['book']);
    }

    public function testRejectsMissingRegistrationBeforeRetirement(): void
    {
        $registration = $this->registration();
        $registered = [];
        $unregisterCalls = 0;
        $registrar = $this->registrar($registered, $unregisterCalls);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('not currently registered by WPEssential');
        $registrar->retire($registration);
    }

    public function testVerifiedAbsentStateWinsWhenWordPressReportsUnregisterFailure(): void
    {
        $registration = $this->registration();
        $registered = ['book' => ['headline' => $registration['args']]];
        $registrar = new WordPressPostMetaRegistrar(
            static fn (string $postType, string $metaKey, array $args): bool => true,
            static fn (string $postType, string $feature): bool => true,
            static function (string $objectType, string $objectSubtype) use (&$registered): array {
                if ($objectType !== 'post' || $objectSubtype === '') {
                    return [];
                }
                return $registered[$objectSubtype] ?? [];
            },
            unregisterMetaKey: static function (string $objectType, string $metaKey, string $objectSubtype) use (&$registered): bool {
                unset($registered[$objectSubtype][$metaKey]);
                return false;
            },
        );

        $registrar->retire($registration);

        self::assertArrayNotHasKey('headline', $registered['book']);
    }

    /**
     * @param array<string,array<string,array<string,mixed>>> $registered
     */
    private function registrar(array &$registered, int &$unregisterCalls): WordPressPostMetaRegistrar
    {
        return new WordPressPostMetaRegistrar(
            static function (string $postType, string $metaKey, array $args) use (&$registered): bool {
                $registered[$postType][$metaKey] = $args;
                return true;
            },
            static fn (string $postType, string $feature): bool => true,
            static function (string $objectType, string $objectSubtype) use (&$registered): array {
                if ($objectType !== 'post' || $objectSubtype === '') {
                    return [];
                }
                return $registered[$objectSubtype] ?? [];
            },
            unregisterMetaKey: static function (string $objectType, string $metaKey, string $objectSubtype) use (&$registered, &$unregisterCalls): bool {
                ++$unregisterCalls;
                if (!isset($registered[$objectSubtype][$metaKey])) {
                    return false;
                }
                unset($registered[$objectSubtype][$metaKey]);
                return true;
            },
        );
    }

    /**
     * @return array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>}
     */
    private function registration(): array
    {
        $field = (new FieldDefinitionNormalizer())->normalize([
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'key' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
        ]);

        return (new PostMetaRegistrationCompiler())->compile($field, 'book');
    }
}
