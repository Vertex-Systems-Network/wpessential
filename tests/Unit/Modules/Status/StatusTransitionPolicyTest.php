<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Status;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Status\Transition\StatusTransitionPolicy;
use WPEssential\Modules\Status\Transition\StatusTransitionRule;

final class StatusTransitionPolicyTest extends TestCase
{
    public function testExplicitEdgesAllowAndUndeclaredEdgesFailClosed(): void
    {
        $policy = new StatusTransitionPolicy([
            new StatusTransitionRule('draft', 'review-ready', capability: 'edit_posts'),
        ]);

        self::assertTrue($policy->allows('draft', 'review-ready'));
        self::assertFalse($policy->allows('review-ready', 'draft'));
        self::assertFalse($policy->allows('draft', 'publish'));
        self::assertSame('edit_posts', $policy->ruleFor('draft', 'review-ready')?->capability);
    }

    public function testSameStatusUpdateIsNotAnActualTransition(): void
    {
        $policy = new StatusTransitionPolicy([
            new StatusTransitionRule('draft', 'review-ready'),
        ]);

        self::assertFalse($policy->allows('draft', 'draft'));
        self::assertNull($policy->ruleFor('draft', 'draft'));
    }

    public function testRejectsSameStatusAndReservedCoreLifecycleEdges(): void
    {
        try {
            new StatusTransitionRule('draft', 'draft');
            self::fail('same-status rule must be rejected');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        new StatusTransitionRule('draft', 'trash');
    }

    public function testDuplicateEdgeRejectedButReverseEdgeIsDistinct(): void
    {
        $reverse = new StatusTransitionPolicy([
            new StatusTransitionRule('draft', 'review-ready'),
            new StatusTransitionRule('review-ready', 'draft'),
        ]);
        self::assertCount(2, $reverse->rules());

        $this->expectException(InvalidArgumentException::class);
        new StatusTransitionPolicy([
            new StatusTransitionRule('draft', 'review-ready'),
            new StatusTransitionRule('draft', 'review-ready'),
        ]);
    }

    public function testBulkAndProgrammaticFlagsAreExplicit(): void
    {
        $policy = new StatusTransitionPolicy([
            new StatusTransitionRule(
                'draft',
                'review-ready',
                bulkAllowed: true,
                programmaticAllowed: false,
            ),
        ]);

        self::assertTrue($policy->allows('draft', 'review-ready', bulk: true));
        self::assertFalse($policy->allows('draft', 'review-ready', programmatic: true));
    }

    public function testReasonContractIsBoundedAndFailClosed(): void
    {
        $rule = new StatusTransitionRule('draft', 'review-ready', reasonRequired: true);
        $policy = new StatusTransitionPolicy([$rule]);

        self::assertFalse($policy->acceptsReason($rule, null));
        self::assertFalse($policy->acceptsReason($rule, '   '));
        self::assertTrue($policy->acceptsReason($rule, 'Ready for editorial review.'));
        self::assertFalse($policy->acceptsReason($rule, str_repeat('x', 501)));
        self::assertFalse($policy->acceptsReason($rule, "bad\x00reason"));
    }

    public function testFingerprintAndOrderAreDeterministic(): void
    {
        $a = new StatusTransitionRule('draft', 'review-ready');
        $b = new StatusTransitionRule('review-ready', 'publish', capability: 'publish_posts');

        $first = new StatusTransitionPolicy([$b, $a]);
        $second = new StatusTransitionPolicy([$a, $b]);

        self::assertSame($first->compatibilityFingerprint, $second->compatibilityFingerprint);
        self::assertSame(['draft->review-ready', 'review-ready->publish'], array_map(
            static fn (StatusTransitionRule $rule): string => $rule->edgeKey(),
            $first->rules(),
        ));
    }

    public function testRejectsInvalidCapabilityOrStatusKey(): void
    {
        try {
            new StatusTransitionRule('Draft', 'review-ready');
            self::fail('non-canonical status key must be rejected');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        new StatusTransitionRule('draft', 'review-ready', capability: 'Edit Posts');
    }
}
