<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\Backup;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use WPEssential\Modules\Backup\Verification\LocalBackupManifestVerifier;
use WPEssential\Modules\CustomTables\Migration\Recovery\Binding\RecoveryEvidenceBindingDecision;
use WPEssential\Modules\CustomTables\Migration\Recovery\MigrationGenerationStamp;
use WPEssential\Modules\CustomTables\Migration\Recovery\Provider\LocalBackupRecoveryVerificationProvider;

final class LocalBackupManifestVerifierTest extends TestCase
{
    private const BACKUP_ID = '0f8fad5b-d9cb-469f-a165-70867728950e';
    private const ARTIFACT = 'database/dump.sql';
    private const CONTENT = "CREATE TABLE wp_example (id INT);\n";

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/wpe-backup-test-' . bin2hex(random_bytes(6));
        mkdir($this->root . '/' . self::BACKUP_ID . '/database', 0777, true);
        file_put_contents($this->root . '/' . self::BACKUP_ID . '/' . self::ARTIFACT, self::CONTENT);
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($iterator as $file) {
            $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->root);
    }

    public function testVerifiedBackupReportsAgeAndManifest(): void
    {
        $this->writeManifest([]);

        $verified = $this->verifier()->verify(self::BACKUP_ID);

        self::assertNotNull($verified);
        self::assertSame(5, $verified->ageHours);
        self::assertSame('mysql', $verified->manifest->databaseProvider);
    }

    /** @return iterable<string,array{array<string,mixed>}> */
    public static function failClosedManifests(): iterable
    {
        yield 'partial state' => [['state' => 'completed_partial']];
        yield 'failed items' => [['failed_items' => ['table wp_options']]];
        yield 'requested scope not captured' => [['scope_requested' => ['database', 'files']]];
        yield 'checksum mismatch' => [['artifacts' => [['path' => self::ARTIFACT, 'sha256' => str_repeat('0', 64), 'size' => strlen(self::CONTENT)]]]];
        yield 'size mismatch' => [['artifacts' => [['path' => self::ARTIFACT, 'sha256' => hash('sha256', self::CONTENT), 'size' => 1]]]];
        yield 'missing artifact' => [['artifacts' => [['path' => 'database/missing.sql', 'sha256' => hash('sha256', self::CONTENT), 'size' => 3]]]];
        yield 'traversal path' => [['artifacts' => [['path' => '../outside.sql', 'sha256' => hash('sha256', self::CONTENT), 'size' => 3]]]];
        yield 'other site' => [['site_id' => 8]];
        yield 'other backup id' => [['backup_id' => '1f8fad5b-d9cb-469f-a165-70867728950e']];
        yield 'future timestamp' => [['created_at' => '2026-10-19T00:00:00Z']];
        yield 'unknown format' => [['manifest_version' => 2]];
        yield 'unsupported provider' => [['database' => ['provider' => 'sqlite', 'version' => '3.45.1']]];
    }

    /**
     * @param array<string,mixed> $override
     * @dataProvider failClosedManifests
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('failClosedManifests')]
    public function testInvalidOrUnverifiableBackupFailsClosed(array $override): void
    {
        $this->writeManifest($override);

        self::assertNull($this->verifier()->verify(self::BACKUP_ID));
    }

    public function testMissingRootOrBackupFailsClosed(): void
    {
        self::assertNull((new LocalBackupManifestVerifier($this->root . '/absent', 7))->verify(self::BACKUP_ID));
        self::assertNull($this->verifier()->verify('1f8fad5b-d9cb-469f-a165-70867728950e'));
    }

    public function testSymlinkedArtifactOutsideBackupFailsClosed(): void
    {
        $outside = $this->root . '/outside.sql';
        file_put_contents($outside, self::CONTENT);
        symlink($outside, $this->root . '/' . self::BACKUP_ID . '/database/link.sql');
        $this->writeManifest(['artifacts' => [['path' => 'database/link.sql', 'sha256' => hash('sha256', self::CONTENT), 'size' => strlen(self::CONTENT)]]]);

        self::assertNull($this->verifier()->verify(self::BACKUP_ID));
    }

    public function testProviderBindsEvidenceOnlyToTheBoundPlan(): void
    {
        $plan = str_repeat('c', 64);
        $this->writeManifest(['bound_plan_fingerprints' => [$plan]]);
        $provider = new LocalBackupRecoveryVerificationProvider($this->verifier());

        $evidence = $provider->verify(self::BACKUP_ID, $plan);
        self::assertNotNull($evidence);
        self::assertTrue($evidence->evidence->verifiedBackupAvailable);
        self::assertSame(5, $evidence->observedAgeHours);

        $decision = RecoveryEvidenceBindingDecision::evaluate(
            new MigrationGenerationStamp(
                planFingerprint: $plan,
                sourceObservedFingerprint: str_repeat('e', 64),
                targetDefinitionId: '22222222-2222-4222-8222-222222222222',
                targetRevision: 1,
                targetSchemaVersion: 1,
                provider: 'mysql',
                providerVersion: '8.0.36',
            ),
            $evidence,
            24,
        );
        self::assertTrue($decision->valid);

        self::assertNull($provider->verify(self::BACKUP_ID, str_repeat('d', 64)));
        self::assertNull($provider->verify('not-a-backup-uuid', $plan));
    }

    private function verifier(): LocalBackupManifestVerifier
    {
        return new LocalBackupManifestVerifier(
            $this->root,
            7,
            new DateTimeImmutable('2026-10-18T17:30:00Z', new DateTimeZone('UTC')),
        );
    }

    /** @param array<string,mixed> $override */
    private function writeManifest(array $override): void
    {
        $manifest = array_replace([
            'manifest_version' => 1,
            'backup_id' => self::BACKUP_ID,
            'site_id' => 7,
            'created_at' => '2026-10-18T12:00:00Z',
            'state' => 'completed_verified',
            'scope_requested' => ['database'],
            'scope_captured' => ['database'],
            'table_prefix' => 'wp_',
            'database' => ['provider' => 'mysql', 'version' => '8.0.36'],
            'artifacts' => [['path' => self::ARTIFACT, 'sha256' => hash('sha256', self::CONTENT), 'size' => strlen(self::CONTENT)]],
            'failed_items' => [],
            'bound_plan_fingerprints' => [],
        ], $override);

        file_put_contents($this->root . '/' . self::BACKUP_ID . '/manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR));
    }
}
