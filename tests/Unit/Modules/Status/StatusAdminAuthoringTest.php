<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Modules\Status\Admin\StatusAdminAuthoringHandler;
use WPEssential\Modules\Status\Admin\StatusAdminFallbackRenderer;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Modules\Status\Transition\Definition\StatusTransitionPolicyDefinitionCompiler;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;
use WPEssential\Platform\Definitions\InMemoryDefinitionRepository;

final class StatusAdminAuthoringTest extends TestCase
{
    private InMemoryDefinitionRepository $definitions;
    private ExecutionContext $context;

    protected function setUp(): void
    {
        $this->definitions = new InMemoryDefinitionRepository();
        $this->context = new ExecutionContext(new Principal(1), 1);
    }

    public function testCreatesCanonicalDraftWithEffectivePreviewThenPublishes(): void
    {
        $save = $this->handler(StatusAdminAuthoringHandler::SAVE);
        $created = $save->handle([
            'definition_type' => 'status',
            'status' => 'draft',
            'payload' => $this->statusPayload(),
        ], $this->context);

        self::assertSame('draft', $created['definition']['status']);
        self::assertSame(1, $created['definition']['revision']);
        self::assertSame('status-review-ready', $created['definition']['slug']);
        self::assertTrue($created['preview']['visibility']['internal']);
        self::assertFalse($created['preview']['admin']['show_in_all_list']);
        self::assertSame(['page', 'post'], $created['preview']['post_types']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', (string) $created['definition']['checksum']);

        $published = $this->handler(StatusAdminAuthoringHandler::STATUS)->handle([
            'id' => $created['definition']['id'],
            'expected_revision' => 1,
            'status' => 'published',
        ], $this->context);

        self::assertSame('published', $published['definition']['status']);
        self::assertSame(2, $published['definition']['revision']);
    }

    public function testUpdateRequiresExactRevisionAndStatusKeyIsImmutable(): void
    {
        $save = $this->handler(StatusAdminAuthoringHandler::SAVE);
        $created = $save->handle([
            'definition_type' => 'status',
            'payload' => $this->statusPayload(),
        ], $this->context);

        try {
            $save->handle([
                'definition_type' => 'status',
                'id' => $created['definition']['id'],
                'expected_revision' => 9,
                'payload' => $this->statusPayload(),
            ], $this->context);
            self::fail('stale revision must fail');
        } catch (RuntimeException) {
            self::assertTrue(true);
        }

        $renamed = $this->statusPayload();
        $renamed['key'] = 'renamed-status';
        $this->expectException(InvalidArgumentException::class);
        $save->handle([
            'definition_type' => 'status',
            'id' => $created['definition']['id'],
            'expected_revision' => 1,
            'payload' => $renamed,
        ], $this->context);
    }

    public function testDraftStillUsesCanonicalCompilerValidation(): void
    {
        $payload = $this->statusPayload();
        $payload['key'] = 'publish';
        $this->expectException(InvalidArgumentException::class);

        $this->handler(StatusAdminAuthoringHandler::SAVE)->handle([
            'definition_type' => 'status',
            'status' => 'draft',
            'payload' => $payload,
        ], $this->context);
    }

    public function testTransitionPolicyRejectsReservedCoreLifecycleEdge(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->handler(StatusAdminAuthoringHandler::SAVE)->handle([
            'definition_type' => 'status-transition-policy',
            'slug' => 'editorial',
            'payload' => [
                'edges' => [[
                    'from' => 'draft',
                    'to' => 'trash',
                    'capability' => 'edit_posts',
                    'reason_required' => false,
                    'bulk_allowed' => false,
                    'programmatic_allowed' => false,
                ]],
            ],
        ], $this->context);
    }

    public function testPolicyPreviewUsesCanonicalRuleOrdering(): void
    {
        $result = $this->handler(StatusAdminAuthoringHandler::SAVE)->handle([
            'definition_type' => 'status-transition-policy',
            'slug' => 'editorial',
            'payload' => [
                'edges' => [
                    ['from' => 'review-ready', 'to' => 'publish'],
                    ['from' => 'draft', 'to' => 'review-ready', 'reason_required' => true],
                ],
            ],
        ], $this->context);

        self::assertSame('status-transition-policy', $result['preview']['kind']);
        self::assertSame('draft', $result['preview']['edges'][0]['from']);
        self::assertSame('review-ready', $result['preview']['edges'][0]['to']);
        self::assertTrue($result['preview']['edges'][0]['reason_required']);
    }

    public function testListAndGetNeverLeakNonSurfaceFiveDefinitions(): void
    {
        $foreign = new Definition(
            id: '61111111-1111-4111-8111-111111111111',
            slug: 'foreign-status',
            type: 'status',
            schemaVersion: 1,
            ownerSurfaceId: 6,
            status: DefinitionStatus::Draft,
            payload: $this->statusPayload(),
        );
        $this->definitions->save($foreign);

        $created = $this->handler(StatusAdminAuthoringHandler::SAVE)->handle([
            'definition_type' => 'status',
            'payload' => $this->statusPayload(),
        ], $this->context);
        $listed = $this->handler(StatusAdminAuthoringHandler::LIST)->handle([], $this->context);

        self::assertCount(1, $listed['definitions']);
        self::assertSame($created['definition']['id'], $listed['definitions'][0]['id']);

        $this->expectException(RuntimeException::class);
        $this->handler(StatusAdminAuthoringHandler::GET)->handle(['id' => $foreign->id], $this->context);
    }

    public function testFallbackRendererIsSemanticEscapedAndMarksCoreLifecycleReserved(): void
    {
        $renderer = new StatusAdminFallbackRenderer();
        $html = $renderer->render(['Bad <script>alert(1)</script>']);

        self::assertStringContainsString('<main id="wpe-status-admin" aria-labelledby="wpe-status-title">', $html);
        self::assertStringContainsString('<form method="post"', $html);
        self::assertStringContainsString('<fieldset><legend>Status definition</legend>', $html);
        self::assertStringContainsString('role="alert"', $html);
        self::assertStringContainsString('Bad &lt;script&gt;alert(1)&lt;/script&gt;', $html);
        self::assertStringNotContainsString('<script>', $html);
        self::assertContains('future', $renderer->reservedCoreLifecycleStatuses());
        self::assertContains('trash', $renderer->reservedCoreLifecycleStatuses());
        self::assertContains('inherit', $renderer->reservedCoreLifecycleStatuses());
    }

    public function testStatusModuleAdminSchemasDoNotExposeScopeOrImplementationSelectors(): void
    {
        $source = file_get_contents(dirname(__DIR__, 4) . '/frameworks/Modules/Status/StatusModule.php');
        self::assertIsString($source);
        self::assertStringContainsString("'wpessential/status/admin/'", $source);
        self::assertStringContainsString("capability: self::ADMIN_CAPABILITY", $source);
        self::assertStringContainsString("NonceOperation::Update", $source);
        self::assertStringNotContainsString("'site_id'", $source);
        self::assertStringNotContainsString("'network_id'", $source);
        self::assertStringNotContainsString("'provider' =>", $source);
        self::assertStringNotContainsString("'implementation' =>", $source);
    }

    private function handler(string $action): StatusAdminAuthoringHandler
    {
        return new StatusAdminAuthoringHandler(
            $this->definitions,
            new StatusDefinitionCompiler(),
            new StatusTransitionPolicyDefinitionCompiler(),
            $action,
        );
    }

    /** @return array<string,mixed> */
    private function statusPayload(): array
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
