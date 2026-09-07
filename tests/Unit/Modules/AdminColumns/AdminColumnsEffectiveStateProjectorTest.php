<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\AdminColumns;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\AdminColumns\AdminColumnsEffectiveStateProjector;

final class AdminColumnsEffectiveStateProjectorTest extends TestCase
{
    public function testProjectsBoundedEffectiveAndDiagnosticEvidence(): void
    {
        $result = (new AdminColumnsEffectiveStateProjector())->project(
            [
                'state' => 'available',
                'reason' => null,
                'capabilities' => ['sort' => true, 'filter' => true, 'edit' => false],
                'batchable' => true,
                'provider_compatible' => true,
                'storage_compatible' => true,
            ],
            [
                [
                    'reference' => 'native.post.id',
                    'state' => 'available',
                    'reason' => null,
                    'capabilities' => ['sort' => true, 'filter' => true, 'edit' => false],
                    'batchable' => true,
                    'provider_compatible' => true,
                    'storage_compatible' => true,
                ],
                [
                    'reference' => 'provider.remote_score',
                    'state' => 'expensive',
                    'reason' => 'remote_latency',
                    'capabilities' => ['sort' => false, 'filter' => false, 'edit' => true],
                    'batchable' => false,
                    'provider_compatible' => true,
                    'storage_compatible' => true,
                ],
                [
                    'reference' => 'provider.degraded_value',
                    'state' => 'degraded',
                    'reason' => 'storage_mode_mismatch',
                    'capabilities' => ['sort' => false, 'filter' => false, 'edit' => false],
                    'batchable' => false,
                    'provider_compatible' => true,
                    'storage_compatible' => false,
                ],
            ],
            [
                'row_count' => 25,
                'page_size' => 50,
                'query_count' => 3,
                'remote_call_count' => 1,
                'cache_state' => 'hit',
            ],
        );

        self::assertSame(AdminColumnsEffectiveStateProjector::CONTRACT_VERSION, $result['contract_version']);
        self::assertSame('available', $result['target']['state']);
        self::assertSame('expensive', $result['sources'][1]['state']);
        self::assertSame('remote_latency', $result['sources'][1]['reason']);
        self::assertSame('degraded', $result['sources'][2]['state']);
        self::assertSame(50, $result['diagnostics']['page_size']);
        self::assertArrayNotHasKey('authorized', $result);
        self::assertArrayNotHasKey('authorization', $result);
    }

    public function testUnavailableStateCannotAdvertiseCapabilityOrBatchability(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unavailable state cannot advertise');

        (new AdminColumnsEffectiveStateProjector())->project(
            [
                'state' => 'unavailable',
                'reason' => 'adapter_missing',
                'capabilities' => ['sort' => true, 'filter' => false, 'edit' => false],
                'batchable' => false,
                'provider_compatible' => true,
                'storage_compatible' => true,
            ],
            [],
            $this->diagnostics(),
        );
    }

    public function testProviderRequiredStateRequiresMissingOrIncompatibleProvider(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('provider_required state requires');

        (new AdminColumnsEffectiveStateProjector())->project(
            [
                'state' => 'provider_required',
                'reason' => 'provider_missing',
                'capabilities' => ['sort' => false, 'filter' => false, 'edit' => false],
                'batchable' => false,
                'provider_compatible' => true,
                'storage_compatible' => true,
            ],
            [],
            $this->diagnostics(),
        );
    }

    public function testAvailableStateRejectsCompatibilityContradiction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('available state contradicts');

        (new AdminColumnsEffectiveStateProjector())->project(
            [
                'state' => 'available',
                'reason' => null,
                'capabilities' => ['sort' => true, 'filter' => true, 'edit' => false],
                'batchable' => true,
                'provider_compatible' => true,
                'storage_compatible' => false,
            ],
            [],
            $this->diagnostics(),
        );
    }

    public function testFullyHealthyEvidenceCannotBeLabelledDegraded(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('degraded state contradicts');

        (new AdminColumnsEffectiveStateProjector())->project(
            [
                'state' => 'degraded',
                'reason' => 'unknown_degradation',
                'capabilities' => ['sort' => true, 'filter' => true, 'edit' => true],
                'batchable' => true,
                'provider_compatible' => true,
                'storage_compatible' => true,
            ],
            [],
            $this->diagnostics(),
        );
    }

    public function testDuplicateSourceReferencesFailClosed(): void
    {
        $source = [
            'reference' => 'native.post.id',
            'state' => 'available',
            'reason' => null,
            'capabilities' => ['sort' => true, 'filter' => true, 'edit' => false],
            'batchable' => true,
            'provider_compatible' => true,
            'storage_compatible' => true,
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('references must be unique');

        (new AdminColumnsEffectiveStateProjector())->project(
            $this->availableTarget(),
            [$source, $source],
            $this->diagnostics(),
        );
    }

    public function testSecretBearingNestedEvidenceFailsClosed(): void
    {
        $target = $this->availableTarget();
        $target['capabilities']['api_token'] = 'must-not-project';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('secret/private diagnostic evidence');

        (new AdminColumnsEffectiveStateProjector())->project(
            $target,
            [],
            $this->diagnostics(),
        );
    }

    public function testDiagnosticBudgetsFailClosed(): void
    {
        $diagnostics = $this->diagnostics();
        $diagnostics['page_size'] = 101;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Diagnostic page_size');

        (new AdminColumnsEffectiveStateProjector())->project(
            $this->availableTarget(),
            [],
            $diagnostics,
        );
    }

    /** @return array<string,mixed> */
    private function availableTarget(): array
    {
        return [
            'state' => 'available',
            'reason' => null,
            'capabilities' => ['sort' => true, 'filter' => true, 'edit' => false],
            'batchable' => true,
            'provider_compatible' => true,
            'storage_compatible' => true,
        ];
    }

    /** @return array<string,mixed> */
    private function diagnostics(): array
    {
        return [
            'row_count' => 10,
            'page_size' => 25,
            'query_count' => 2,
            'remote_call_count' => 0,
            'cache_state' => 'unknown',
        ];
    }
}
