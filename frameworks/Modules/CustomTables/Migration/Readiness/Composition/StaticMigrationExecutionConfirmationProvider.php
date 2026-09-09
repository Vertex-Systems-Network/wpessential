<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Readiness\Composition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class StaticMigrationExecutionConfirmationProvider implements MigrationExecutionConfirmationProviderInterface
{
    /** @var array<string,BoundMigrationExecutionConfirmation> */
    private array $confirmations = [];

    /** @param list<BoundMigrationExecutionConfirmation> $confirmations */
    public function __construct(array $confirmations = [])
    {
        foreach ($confirmations as $confirmation) {
            if (!$confirmation instanceof BoundMigrationExecutionConfirmation) {
                throw new InvalidArgumentException('Custom Tables execution confirmation provider requires typed confirmations.');
            }

            $key = strtolower($confirmation->runId);
            if (isset($this->confirmations[$key])) {
                throw new InvalidArgumentException('Custom Tables execution confirmation provider contains a duplicate run id.');
            }

            $this->confirmations[$key] = $confirmation;
        }
    }

    public function confirmationFor(string $runId): ?BoundMigrationExecutionConfirmation
    {
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $runId) !== 1) {
            throw new InvalidArgumentException('Custom Tables execution confirmation lookup run id must be a canonical UUID.');
        }

        return $this->confirmations[strtolower($runId)] ?? null;
    }
}
