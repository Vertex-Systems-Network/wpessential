<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Modules\CustomTables\Migration\Precondition\Probe\WordPressMetadataPreconditionFactsProvider;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\Policy\MigrationExecutionAuthorizationPolicyAdapter;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\TrustedRecoveryVerificationProviderInterface;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\MigrationRunRecordCodec;
use WPEssential\Modules\CustomTables\Migration\Run\Persistence\WpdbMigrationRunRepository;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final readonly class CustomTablesRuntimeCompositionFactory
{
    private function __construct(
        private DatabaseAdapterInterface $database,
        private PolicyEngine $policy,
        private WordPressMetadataPreconditionFactsProvider $metadataFacts,
        private TrustedRecoveryVerificationProviderInterface $recoveryVerification,
        private TrustedMigrationExecutionConfirmationProviderInterface $confirmations,
        private int $networkId,
        private int $siteId,
    ) {
        if ($this->networkId < 1 || $this->siteId < 1) {
            throw new InvalidArgumentException('Custom Tables runtime composition factory scope ids must be positive.');
        }
    }

    public static function production(
        DatabaseAdapterInterface $database,
        PolicyEngine $policy,
        WordPressMetadataPreconditionFactsProvider $metadataFacts,
        TrustedRecoveryVerificationProviderInterface $recoveryVerification,
        TrustedMigrationExecutionConfirmationProviderInterface $confirmations,
        int $networkId,
        int $siteId,
    ): self {
        return new self(
            database: $database,
            policy: $policy,
            metadataFacts: $metadataFacts,
            recoveryVerification: $recoveryVerification,
            confirmations: $confirmations,
            networkId: $networkId,
            siteId: $siteId,
        );
    }

    public function create(): RuntimeCompositionReadinessService
    {
        return new RuntimeCompositionReadinessService(
            runs: new WpdbMigrationRunRepository(
                $this->database,
                new MigrationRunRecordCodec(),
                $this->siteId,
            ),
            metadataFacts: $this->metadataFacts,
            recoveryVerification: $this->recoveryVerification,
            confirmations: $this->confirmations,
            authorizationPolicy: new MigrationExecutionAuthorizationPolicyAdapter($this->policy),
            networkId: $this->networkId,
            siteId: $this->siteId,
        );
    }
}
