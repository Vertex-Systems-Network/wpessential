<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\RecoveryVerificationProviderInterface;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\WpdbMigrationRunRepository;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CustomTablesRuntimeCompositionFactory
{
    public function __construct(
        private DatabaseAdapterInterface $database,
        private PolicyEngine $policy,
        private int $networkId,
        private int $siteId,
    ) {
        if ($this->networkId < 1 || $this->siteId < 1) {
            throw new InvalidArgumentException('Custom Tables runtime composition factory scope ids must be positive.');
        }
    }

    public function create(
        RecoveryVerificationProviderInterface $recoveryVerification,
        MigrationExecutionConfirmationProviderInterface $confirmations,
    ): RuntimeCompositionReadinessService {
        return new RuntimeCompositionReadinessService(
            runs: new WpdbMigrationRunRepository(
                $this->database,
                new MigrationRunRecordCodec(),
                $this->siteId,
            ),
            recoveryVerification: $recoveryVerification,
            confirmations: $confirmations,
            authorizationPolicy: new MigrationExecutionAuthorizationPolicyAdapter($this->policy),
            networkId: $this->networkId,
            siteId: $this->siteId,
        );
    }
}
