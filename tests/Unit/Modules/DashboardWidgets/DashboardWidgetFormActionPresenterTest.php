<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionConfirmationDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetActionInputDescriptor;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionPresenter;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetRegistrationDescriptor;

final class DashboardWidgetFormActionPresenterTest extends TestCase
{
    public function testRendersOnlyEscapedBoundedPresentationAndTransportTruth(): void
    {
        $presenter = new DashboardWidgetFormActionPresenter(
            'wpessential_dispatch',
            static fn (): string => 'nonce-"<&',
        );

        $html = $presenter->render(
            $this->descriptor(
                new DashboardWidgetActionConfirmationDescriptor(
                    'Disable <workflow>?',
                    'Confirm "change" & continue.',
                    'Disable <now>',
                    'Cancel & close',
                ),
            ),
            'https://example.test/wp-admin/admin-ajax.php?x=1&y=2',
        );

        self::assertStringContainsString('data-wpessential-dashboard-form-action="1"', $html);
        self::assertStringContainsString(
            'data-route-type="dashboard-widgets.form-action.confirm"',
            $html,
        );
        self::assertStringContainsString(
            'data-definition-id="11111111-1111-4111-8111-111111111111"',
            $html,
        );
        self::assertStringContainsString('data-definition-revision="7"', $html);
        self::assertStringContainsString('Disable &lt;now&gt;', $html);
        self::assertStringContainsString('Disable &lt;workflow&gt;?', $html);
        self::assertStringContainsString('Confirm &quot;change&quot; &amp; continue.', $html);
        self::assertStringNotContainsString('<workflow>', $html);
        self::assertStringNotContainsString('wpessential/forms-workflows/set-enabled', $html);
        self::assertStringNotContainsString('expected_revision', $html);
        self::assertStringNotContainsString('22222222-2222-4222-8222-222222222222', $html);
    }

    public function testFailsClosedWithoutCompleteActionDescriptors(): void
    {
        $presenter = new DashboardWidgetFormActionPresenter(
            'wpessential_dispatch',
            static fn (): string => 'nonce',
        );

        foreach ([
            $this->descriptor(null),
            $this->descriptor(
                new DashboardWidgetActionConfirmationDescriptor('Title', 'Message', 'Confirm', 'Cancel'),
                includeInput: false,
            ),
        ] as $descriptor) {
            try {
                $presenter->render($descriptor, 'https://example.test/wp-admin/admin-ajax.php');
                self::fail('Incomplete form-action descriptor must fail closed.');
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testFailsClosedForInvalidEndpointOrNonce(): void
    {
        $confirmation = new DashboardWidgetActionConfirmationDescriptor(
            'Title',
            'Message',
            'Confirm',
            'Cancel',
        );
        $descriptor = $this->descriptor($confirmation);

        $presenter = new DashboardWidgetFormActionPresenter(
            'wpessential_dispatch',
            static fn (): string => '',
        );
        $this->expectException(InvalidArgumentException::class);
        $presenter->render($descriptor, 'https://example.test/wp-admin/admin-ajax.php');
    }

    private function descriptor(
        ?DashboardWidgetActionConfirmationDescriptor $confirmation,
        bool $includeInput = true,
    ): DashboardWidgetRegistrationDescriptor {
        return new DashboardWidgetRegistrationDescriptor(
            definitionId: '11111111-1111-4111-8111-111111111111',
            revision: 7,
            key: 'workflow-control',
            title: 'Workflow control',
            context: 'normal',
            priority: 'default',
            networkDashboard: false,
            actionAbilityId: $confirmation !== null
                ? 'wpessential/forms-workflows/set-enabled'
                : null,
            actionConfirmation: $confirmation,
            actionInput: $includeInput && $confirmation !== null
                ? new DashboardWidgetActionInputDescriptor(
                    definitionId: '11111111-1111-4111-8111-111111111111',
                    definitionRevision: 7,
                    abilityId: 'wpessential/forms-workflows/set-enabled',
                    literalBindings: [
                        'definition_id' => '22222222-2222-4222-8222-222222222222',
                        'expected_revision' => 4,
                        'enabled' => false,
                    ],
                    dynamicBindings: [],
                )
                : null,
        );
    }
}
