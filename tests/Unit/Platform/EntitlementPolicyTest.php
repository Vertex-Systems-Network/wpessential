<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Platform;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminMenu\AdminMenuModule;
use WPEssential\Modules\BuilderWidgets\BuilderWidgetsModule;
use WPEssential\Modules\Chat\ChatModule;
use WPEssential\Modules\Cron\CronModule;
use WPEssential\Modules\Dashboard\DashboardModule;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetsModule;
use WPEssential\Modules\Emails\EmailsModule;
use WPEssential\Modules\FormsWorkflows\FormsWorkflowsModule;
use WPEssential\Modules\Membership\MembershipModule;
use WPEssential\Modules\Notifications\NotificationsModule;
use WPEssential\Modules\Profiles\ProfilesModule;
use WPEssential\Modules\Roles\RolesModule;
use WPEssential\Modules\Settings\SettingsModule;
use WPEssential\Platform\Entitlements\EntitlementAwareModuleActivationPolicy;
use WPEssential\Platform\Entitlements\LocalProductEntitlementProvider;
use WPEssential\Platform\Entitlements\PremiumOperationPolicy;
use WPEssential\Platform\Entitlements\ProductEntitlementSnapshot;
use WPEssential\Platform\Entitlements\ProductEntitlementState;
use WPEssential\Platform\Modules\ModuleManifest;

final class EntitlementPolicyTest extends TestCase
{
    public function testFreeModulesAreAlwaysAdmittedRegardlessOfEntitlementState(): void
    {
        $manifest = new ModuleManifest(id: 'free-module', name: 'Free Module', version: '1.0.0', edition: 'free');

        foreach (ProductEntitlementState::cases() as $state) {
            $policy = new EntitlementAwareModuleActivationPolicy($this->provider($state));
            self::assertTrue($policy->allows($manifest), $state->value);
        }
    }

    public function testActiveTrialAndGracePermitPremiumActivationAndMutation(): void
    {
        foreach ([ProductEntitlementState::ProActive, ProductEntitlementState::TrialActive, ProductEntitlementState::Grace] as $state) {
            $provider = $this->provider($state);
            $activation = new EntitlementAwareModuleActivationPolicy($provider);
            $operations = new PremiumOperationPolicy($provider);

            self::assertTrue($activation->allows($this->proManifest()), $state->value);
            self::assertTrue($operations->allowsRead(), $state->value);
            self::assertTrue($operations->allowsMutation(), $state->value);
        }
    }

    public function testExpiredSuspendedAndVerificationFailuresPreserveReadSafeActivationButDenyMutation(): void
    {
        foreach ([
            ProductEntitlementState::Expired,
            ProductEntitlementState::Suspended,
            ProductEntitlementState::VerificationStale,
            ProductEntitlementState::VerificationUnavailable,
        ] as $state) {
            $provider = $this->provider($state);
            $activation = new EntitlementAwareModuleActivationPolicy($provider);
            $operations = new PremiumOperationPolicy($provider);

            self::assertTrue($activation->allows($this->proManifest()), $state->value);
            self::assertTrue($operations->allowsRead(), $state->value);
            self::assertFalse($operations->allowsMutation(), $state->value);
        }
    }

    public function testFreeAndIncompatibleVersionFailClosedForPremiumActivationAndOperations(): void
    {
        foreach ([ProductEntitlementState::Free, ProductEntitlementState::IncompatibleVersion] as $state) {
            $provider = $this->provider($state);
            $activation = new EntitlementAwareModuleActivationPolicy($provider);
            $operations = new PremiumOperationPolicy($provider);

            self::assertFalse($activation->allows($this->proManifest()), $state->value);
            self::assertFalse($operations->allowsRead(), $state->value);
            self::assertFalse($operations->allowsMutation(), $state->value);
        }
    }

    public function testVerificationStatesRemainDistinctFromExpiry(): void
    {
        self::assertNotSame(ProductEntitlementState::Expired, ProductEntitlementState::VerificationStale);
        self::assertNotSame(ProductEntitlementState::Expired, ProductEntitlementState::VerificationUnavailable);
        self::assertSame('verification_stale', ProductEntitlementState::VerificationStale->value);
        self::assertSame('verification_unavailable', ProductEntitlementState::VerificationUnavailable->value);
    }

    public function testImplementedPremiumModuleManifestsAreCanonicalProMetadata(): void
    {
        $modules = [
            new RolesModule(),
            new AdminMenuModule(),
            new SettingsModule(),
            new DashboardModule(),
            new DashboardWidgetsModule(),
            new ProfilesModule(),
            new MembershipModule(),
            new BuilderWidgetsModule(),
            new FormsWorkflowsModule(),
            new CronModule(),
            new NotificationsModule(),
            new EmailsModule(),
            new ChatModule(),
        ];

        foreach ($modules as $module) {
            self::assertSame('pro', $module->manifest()->edition, $module->manifest()->id);
        }
    }

    public function testEntitlementTruthDoesNotDependOnMembershipOrUserAuthentication(): void
    {
        $root = dirname(__DIR__, 3);
        $bootstrap = file_get_contents($root . '/wpessential-pro.php');
        $provider = file_get_contents($root . '/frameworks/Platform/Entitlements/LocalProductEntitlementProvider.php');

        self::assertIsString($bootstrap);
        self::assertIsString($provider);
        self::assertStringNotContainsString('is_user_logged_in', $bootstrap);
        self::assertStringNotContainsString('wp_get_current_user', $bootstrap);
        self::assertStringNotContainsString('MembershipReadService', $bootstrap);
        self::assertStringNotContainsString('Membership', $provider);
        self::assertStringNotContainsString('User', $provider);
    }

    private function provider(ProductEntitlementState $state): LocalProductEntitlementProvider
    {
        return new LocalProductEntitlementProvider(new ProductEntitlementSnapshot($state));
    }

    private function proManifest(): ModuleManifest
    {
        return new ModuleManifest(id: 'synthetic-pro', name: 'Synthetic Pro', version: '1.0.0', edition: 'pro');
    }
}
