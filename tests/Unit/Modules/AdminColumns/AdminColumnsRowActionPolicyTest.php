<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsRowActionPolicy;

final class AdminColumnsRowActionPolicyTest extends TestCase
{
    public function testResolvesExplicitEligiblePrimaryAndAdvertisedActions(): void
    {
        $result = (new AdminColumnsRowActionPolicy())->resolve(
            [
                ['key' => 'id', 'enabled' => true, 'primary' => true],
                ['key' => 'title', 'enabled' => true, 'primary' => false],
            ],
            [
                'primary_eligible_columns' => ['id'],
                'row_actions' => ['view', 'edit'],
            ],
            ['view', 'edit', 'delete'],
        );

        self::assertSame(AdminColumnsRowActionPolicy::CONTRACT_VERSION, $result['contract_version']);
        self::assertSame(
            ['column_key' => 'id', 'available' => true, 'reason' => null],
            $result['primary'],
        );
        self::assertSame(
            [
                ['action' => 'view', 'available' => true, 'reason' => null],
                ['action' => 'edit', 'available' => true, 'reason' => null],
                ['action' => 'delete', 'available' => false, 'reason' => 'target_action_unavailable'],
            ],
            $result['actions'],
        );
    }

    public function testDoesNotInventPrimaryWhenNoneIsExplicit(): void
    {
        $result = (new AdminColumnsRowActionPolicy())->resolve(
            [
                ['key' => 'id', 'enabled' => true, 'primary' => false],
                ['key' => 'title', 'enabled' => true, 'primary' => false],
            ],
            [
                'primary_eligible_columns' => ['id', 'title'],
                'row_actions' => ['view'],
            ],
            ['view'],
        );

        self::assertSame(
            ['column_key' => null, 'available' => false, 'reason' => 'no_explicit_primary'],
            $result['primary'],
        );
        self::assertSame('primary_unavailable', $result['actions'][0]['reason']);
    }

    public function testDisabledOrIneligiblePrimaryFailsClosed(): void
    {
        $policy = new AdminColumnsRowActionPolicy();

        $disabled = $policy->resolve(
            [['key' => 'id', 'enabled' => false, 'primary' => true]],
            ['primary_eligible_columns' => ['id'], 'row_actions' => ['view']],
            ['view'],
        );
        self::assertFalse($disabled['primary']['available']);
        self::assertSame('primary_disabled', $disabled['primary']['reason']);

        $ineligible = $policy->resolve(
            [['key' => 'title', 'enabled' => true, 'primary' => true]],
            ['primary_eligible_columns' => ['id'], 'row_actions' => ['view']],
            ['view'],
        );
        self::assertFalse($ineligible['primary']['available']);
        self::assertSame('primary_ineligible', $ineligible['primary']['reason']);
    }

    public function testDuplicatePrimaryDeclarationsFailClosed(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('duplicate primary');

        (new AdminColumnsRowActionPolicy())->resolve(
            [
                ['key' => 'id', 'enabled' => true, 'primary' => true],
                ['key' => 'title', 'enabled' => true, 'primary' => true],
            ],
            [
                'primary_eligible_columns' => ['id', 'title'],
                'row_actions' => ['view'],
            ],
            ['view'],
        );
    }

    public function testDuplicateOrMalformedCapabilityKeysFailClosed(): void
    {
        $policy = new AdminColumnsRowActionPolicy();

        try {
            $policy->resolve(
                [['key' => 'id', 'enabled' => true, 'primary' => true]],
                ['primary_eligible_columns' => ['id', 'id'], 'row_actions' => ['view']],
                ['view'],
            );
            self::fail('Duplicate capability keys must fail closed.');
        } catch (InvalidArgumentException $error) {
            self::assertStringContainsString('unique', $error->getMessage());
        }

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('malformed machine key');
        $policy->resolve(
            [['key' => 'id', 'enabled' => true, 'primary' => true]],
            ['primary_eligible_columns' => ['id'], 'row_actions' => ['edit<script>']],
            ['view'],
        );
    }

    public function testPolicyDoesNotTreatAvailabilityAsAuthorization(): void
    {
        $result = (new AdminColumnsRowActionPolicy())->resolve(
            [['key' => 'id', 'enabled' => true, 'primary' => true]],
            ['primary_eligible_columns' => ['id'], 'row_actions' => ['delete']],
            ['delete'],
        );

        self::assertTrue($result['actions'][0]['available']);
        self::assertArrayNotHasKey('authorized', $result['actions'][0]);
        self::assertArrayNotHasKey('capability_grant', $result['actions'][0]);
    }
}
