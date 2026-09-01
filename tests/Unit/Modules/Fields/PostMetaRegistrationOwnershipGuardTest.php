<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Fields;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Fields\PostMetaRegistrationOwnershipGuard;

final class PostMetaRegistrationOwnershipGuardTest extends TestCase
{
    public function testAllowsUnclaimedSubtypeRegistration(): void
    {
        self::assertTrue((new PostMetaRegistrationOwnershipGuard())->shouldRegister(
            $this->registration(),
            null,
            null,
        ));
    }

    public function testTreatsSameOwnerAndStructuralShapeAsIdempotent(): void
    {
        $registration = $this->registration();
        $existing = $registration['args'];
        $existing['label'] = 'Mutable label changed';
        $existing['show_in_rest'] = [
            'schema' => [
                'items' => ['type' => 'integer'],
                'type' => 'array',
            ],
        ];

        self::assertFalse((new PostMetaRegistrationOwnershipGuard())->shouldRegister(
            $registration,
            null,
            $existing,
        ));
    }

    public function testRejectsSameKeyOwnedByDifferentFieldUuid(): void
    {
        $existing = $this->registration()['args'];
        $existing['description'] = 'WPEssential Field value (33333333-3333-4333-8333-333333333333).';

        $this->expectException(RuntimeException::class);
        (new PostMetaRegistrationOwnershipGuard())->shouldRegister(
            $this->registration(),
            null,
            $existing,
        );
    }

    public function testRejectsIncompatibleShapeForSameOwner(): void
    {
        $existing = $this->registration()['args'];
        $existing['single'] = false;

        $this->expectException(RuntimeException::class);
        (new PostMetaRegistrationOwnershipGuard())->shouldRegister(
            $this->registration(),
            null,
            $existing,
        );
    }

    public function testRejectsAnyGlobalPostScopeClaim(): void
    {
        $this->expectException(RuntimeException::class);
        (new PostMetaRegistrationOwnershipGuard())->shouldRegister(
            $this->registration(),
            $this->registration()['args'],
            null,
        );
    }

    public function testRejectsCandidateWithoutCanonicalOwnershipFingerprint(): void
    {
        $registration = $this->registration();
        $registration['args']['description'] = 'Generic field';

        $this->expectException(InvalidArgumentException::class);
        (new PostMetaRegistrationOwnershipGuard())->shouldRegister($registration, null, null);
    }

    /**
     * @return array{post_type:string,field_uuid:string,meta_key:string,args:array<string,mixed>}
     */
    private function registration(): array
    {
        return [
            'post_type' => 'book',
            'field_uuid' => '22222222-2222-4222-8222-222222222222',
            'meta_key' => 'gallery_ids',
            'args' => [
                'type' => 'array',
                'label' => 'Gallery',
                'description' => 'WPEssential Field value (22222222-2222-4222-8222-222222222222).',
                'single' => true,
                'sanitize_callback' => static fn (mixed $value): mixed => $value,
                'auth_callback' => static fn (): bool => true,
                'show_in_rest' => [
                    'schema' => [
                        'type' => 'array',
                        'items' => ['type' => 'integer'],
                    ],
                ],
                'revisions_enabled' => false,
            ],
        ];
    }
}
