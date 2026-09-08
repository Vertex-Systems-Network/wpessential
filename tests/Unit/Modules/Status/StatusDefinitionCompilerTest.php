<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class StatusDefinitionCompilerTest extends TestCase
{
    private const ID = '11111111-1111-4111-8111-111111111111';

    public function testCompilesDeterministicInternalCoreDefaults(): void
    {
        $compiler = new StatusDefinitionCompiler();
        $payload = $this->payload();

        $first = $compiler->compile($this->definition($payload));
        $payload['post_types'] = ['page', 'post'];
        $second = $compiler->compile($this->definition($payload));

        self::assertSame('review-ready', $first->key);
        self::assertFalse($first->public);
        self::assertTrue($first->internal);
        self::assertFalse($first->publiclyQueryable);
        self::assertTrue($first->excludeFromSearch);
        self::assertFalse($first->showInAdminAllList);
        self::assertFalse($first->showInAdminStatusList);
        self::assertSame(['page', 'post'], $first->postTypes);
        self::assertSame($first->compatibilityFingerprint, $second->compatibilityFingerprint);
    }

    public function testExplicitPublicClassificationCompilesEffectiveDefaults(): void
    {
        $payload = $this->payload();
        $payload['visibility'] = ['public' => true];

        $compiled = (new StatusDefinitionCompiler())->compile($this->definition($payload));

        self::assertTrue($compiled->public);
        self::assertFalse($compiled->internal);
        self::assertTrue($compiled->publiclyQueryable);
        self::assertFalse($compiled->excludeFromSearch);
        self::assertTrue($compiled->showInAdminAllList);
        self::assertTrue($compiled->showInAdminStatusList);
    }

    public function testSemanticVisibilityChangeChangesFingerprint(): void
    {
        $compiler = new StatusDefinitionCompiler();
        $baseline = $compiler->compile($this->definition($this->payload()));
        $payload = $this->payload();
        $payload['visibility'] = ['protected' => true];

        $changed = $compiler->compile($this->definition($payload));

        self::assertNotSame($baseline->compatibilityFingerprint, $changed->compatibilityFingerprint);
    }

    /** @dataProvider invalidKeys */
    public function testRejectsNonCanonicalReservedOrOverlongKeys(string $key): void
    {
        $payload = $this->payload();
        $payload['key'] = $key;
        $this->expectException(InvalidArgumentException::class);

        (new StatusDefinitionCompiler())->compile($this->definition($payload));
    }

    /** @return iterable<string,array{string}> */
    public static function invalidKeys(): iterable
    {
        yield 'built-in' => ['publish'];
        yield 'uppercase' => ['ReviewReady'];
        yield 'too-long' => ['abcdefghijklmnopqrstu'];
        yield 'whitespace' => ['review ready'];
    }

    public function testRejectsAmbiguousVisibilityClassification(): void
    {
        $payload = $this->payload();
        $payload['visibility'] = ['public' => true, 'private' => true];
        $this->expectException(InvalidArgumentException::class);

        (new StatusDefinitionCompiler())->compile($this->definition($payload));
    }

    public function testRejectsExecutableLabelChannel(): void
    {
        $payload = $this->payload();
        $payload['labels']['label'] = '<script>alert(1)</script>';
        $this->expectException(InvalidArgumentException::class);

        (new StatusDefinitionCompiler())->compile($this->definition($payload));
    }

    public function testRejectsDraftDefinition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new StatusDefinitionCompiler())->compile($this->definition($this->payload(), DefinitionStatus::Draft));
    }

    public function testRejectsDuplicateOrMalformedPostTypeApplicability(): void
    {
        $payload = $this->payload();
        $payload['post_types'] = ['post', 'post'];
        $this->expectException(InvalidArgumentException::class);

        (new StatusDefinitionCompiler())->compile($this->definition($payload));
    }

    /** @param array<string,mixed> $payload */
    private function definition(array $payload, DefinitionStatus $status = DefinitionStatus::Published): Definition
    {
        return new Definition(
            id: self::ID,
            slug: 'review-ready',
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 5,
            status: $status,
            payload: $payload,
            revision: 3,
        );
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        return [
            'key' => 'review-ready',
            'labels' => [
                'label' => 'Review Ready',
                'count_singular' => 'Review Ready',
                'count_plural' => 'Review Ready',
            ],
            'post_types' => ['post', 'page'],
        ];
    }
}
