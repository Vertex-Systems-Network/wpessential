<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\DashboardWidgets;

use PHPUnit\Framework\TestCase;
use WPEssential\Modules\DashboardWidgets\DashboardWidgetFormActionExecutionResultAdapter;

final class DashboardWidgetFormActionExecutionResultAdapterTest extends TestCase
{
    private const DEFINITION_ID = '22222222-2222-4222-8222-222222222222';

    public function testAcceptsExactStatusChangedResult(): void
    {
        $result = (new DashboardWidgetFormActionExecutionResultAdapter())->adapt(
            [
                'definition_id' => self::DEFINITION_ID,
                'previous_revision' => 4,
                'revision' => 5,
                'status' => 'disabled',
                'enabled' => false,
                'changed' => true,
                'result_code' => 'status_changed',
            ],
            $this->input(false),
        );

        self::assertNotNull($result);
        self::assertSame('status_changed', $result['result_code']);
        self::assertStringContainsString('completed successfully', $result['notice']);
    }

    public function testAcceptsExactAlreadyTargetStatusResult(): void
    {
        $result = (new DashboardWidgetFormActionExecutionResultAdapter())->adapt(
            [
                'definition_id' => self::DEFINITION_ID,
                'previous_revision' => 4,
                'revision' => 4,
                'status' => 'published',
                'enabled' => true,
                'changed' => false,
                'result_code' => 'already_target_status',
            ],
            $this->input(true),
        );

        self::assertNotNull($result);
        self::assertSame('already_target_status', $result['result_code']);
        self::assertStringContainsString('already in the requested state', $result['notice']);
    }

    public function testRejectsInconsistentOrExpandedOwnerResults(): void
    {
        $adapter = new DashboardWidgetFormActionExecutionResultAdapter();
        $valid = [
            'definition_id' => self::DEFINITION_ID,
            'previous_revision' => 4,
            'revision' => 5,
            'status' => 'disabled',
            'enabled' => false,
            'changed' => true,
            'result_code' => 'status_changed',
        ];

        foreach ([
            [...$valid, 'raw_owner_detail' => 'secret'],
            [...$valid, 'revision' => 4],
            [...$valid, 'definition_id' => '33333333-3333-4333-8333-333333333333'],
            [...$valid, 'status' => 'published'],
            [...$valid, 'enabled' => true],
            [...$valid, 'changed' => false],
            [...$valid, 'result_code' => 'unexpected'],
        ] as $candidate) {
            self::assertNull($adapter->adapt($candidate, $this->input(false)));
        }
    }

    public function testRejectsMalformedBoundInput(): void
    {
        $adapter = new DashboardWidgetFormActionExecutionResultAdapter();
        $result = [
            'definition_id' => self::DEFINITION_ID,
            'previous_revision' => 4,
            'revision' => 5,
            'status' => 'disabled',
            'enabled' => false,
            'changed' => true,
            'result_code' => 'status_changed',
        ];

        self::assertNull($adapter->adapt($result, [
            'definition_id' => self::DEFINITION_ID,
            'expected_revision' => 4,
            'enabled' => false,
            'extra' => true,
        ]));
    }

    /** @return array{definition_id:string,expected_revision:int,enabled:bool} */
    private function input(bool $enabled): array
    {
        return [
            'definition_id' => self::DEFINITION_ID,
            'expected_revision' => 4,
            'enabled' => $enabled,
        ];
    }
}
