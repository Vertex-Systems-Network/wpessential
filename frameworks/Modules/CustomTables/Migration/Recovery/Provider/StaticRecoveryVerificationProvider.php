<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Recovery\Provider;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\BoundRecoveryEvidence;

final class StaticRecoveryVerificationProvider implements RecoveryVerificationProviderInterface
{
    /** @var array<string,BoundRecoveryEvidence> */
    private array $evidenceByArtifact = [];

    /** @param array<string,BoundRecoveryEvidence> $evidenceByArtifact */
    public function __construct(array $evidenceByArtifact = [])
    {
        foreach ($evidenceByArtifact as $artifactId => $evidence) {
            $this->assertArtifactId($artifactId);
            if (!$evidence instanceof BoundRecoveryEvidence) {
                throw new InvalidArgumentException('Custom Tables recovery verification provider requires typed bound evidence.');
            }
            $this->evidenceByArtifact[$artifactId] = $evidence;
        }
    }

    public function verify(string $artifactId, string $planFingerprint): ?BoundRecoveryEvidence
    {
        $this->assertArtifactId($artifactId);
        if (preg_match('/^[a-f0-9]{64}$/', $planFingerprint) !== 1) {
            throw new InvalidArgumentException('Custom Tables recovery verification plan fingerprint is invalid.');
        }

        $evidence = $this->evidenceByArtifact[$artifactId] ?? null;
        if ($evidence === null) {
            return null;
        }
        if (!hash_equals($planFingerprint, $evidence->evidence->planFingerprint)) {
            throw new RuntimeException('Custom Tables recovery verification evidence does not match the requested plan.');
        }

        return $evidence;
    }

    private function assertArtifactId(string $artifactId): void
    {
        if (preg_match('/^[a-z0-9][a-z0-9._:-]{2,127}$/', $artifactId) !== 1) {
            throw new InvalidArgumentException('Custom Tables recovery artifact id is invalid.');
        }
    }
}
