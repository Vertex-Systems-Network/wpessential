<?php

declare(strict_types=1);

namespace WPEssential\Platform\Secrets;

use RuntimeException;
use WPEssential\Contracts\SecretVaultInterface;

final class InMemorySecretVault implements SecretVaultInterface
{
    /**
     * Reference/test implementation only.
     * It intentionally does not claim at-rest encryption.
     *
     * @var array<string, array{reference:SecretReference, plaintext:string, revoked:bool}>
     */
    private array $records = [];

    public function __construct(private readonly SecretMetadataValidator $metadataValidator = new SecretMetadataValidator())
    {
    }

    public function store(string $name, int $ownerSurfaceId, string $plaintext, array $metadata = []): SecretReference
    {
        if ($plaintext === '') {
            throw new RuntimeException('Secret plaintext cannot be empty.');
        }

        $this->metadataValidator->validate($metadata);

        $reference = new SecretReference(
            id: bin2hex(random_bytes(16)),
            name: $name,
            ownerSurfaceId: $ownerSurfaceId,
            version: 1,
            metadata: $metadata,
        );

        $this->records[$reference->id] = [
            'reference' => $reference,
            'plaintext' => $plaintext,
            'revoked' => false,
        ];

        return $reference;
    }

    public function resolve(SecretReference $reference): SensitiveValue
    {
        $record = $this->records[$reference->id] ?? null;
        if ($record === null || $record['revoked']) {
            throw new RuntimeException('Secret reference is unavailable or revoked.');
        }
        if ($record['reference']->version !== $reference->version) {
            throw new RuntimeException('Secret reference version is stale.');
        }

        return new SensitiveValue($record['plaintext']);
    }

    public function rotate(SecretReference $reference, string $plaintext): SecretReference
    {
        if ($plaintext === '') {
            throw new RuntimeException('Secret plaintext cannot be empty.');
        }

        $record = $this->records[$reference->id] ?? null;
        if ($record === null || $record['revoked']) {
            throw new RuntimeException('Secret reference is unavailable or revoked.');
        }
        if ($record['reference']->version !== $reference->version) {
            throw new RuntimeException('Secret reference version is stale.');
        }

        $rotated = new SecretReference(
            id: $reference->id,
            name: $reference->name,
            ownerSurfaceId: $reference->ownerSurfaceId,
            version: $reference->version + 1,
            metadata: $reference->metadata,
        );

        $this->records[$reference->id] = [
            'reference' => $rotated,
            'plaintext' => $plaintext,
            'revoked' => false,
        ];

        return $rotated;
    }

    public function revoke(SecretReference $reference): void
    {
        $record = $this->records[$reference->id] ?? null;
        if ($record === null) {
            return;
        }
        if ($record['reference']->version !== $reference->version) {
            throw new RuntimeException('Secret reference version is stale.');
        }

        $record['plaintext'] = '';
        $record['revoked'] = true;
        $this->records[$reference->id] = $record;
    }
}
