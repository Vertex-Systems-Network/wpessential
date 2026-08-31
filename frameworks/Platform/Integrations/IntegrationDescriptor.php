<?php

declare(strict_types=1);

namespace WPEssential\Platform\Integrations;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class IntegrationDescriptor
{
    /** @param list<string> $capabilities */
    public function __construct(
        public string $key,
        public string $provider,
        public int $ownerSurfaceId,
        public array $capabilities,
        public ?string $credentialReferenceId = null,
        public int $transportSurfaceId = 23,
        public bool $externalAuthority = true,
        public bool $unknownOutcomeFirstClass = true,
    ) {
        if (!preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $this->key)) {
            throw new InvalidArgumentException('Integration key must be stable lowercase identifier.');
        }
        if (!preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $this->provider)) {
            throw new InvalidArgumentException('Integration provider must be a stable non-secret identifier.');
        }
        if ($this->ownerSurfaceId < 1 || $this->ownerSurfaceId > 56) {
            throw new InvalidArgumentException('Integration owner must be a canonical surface id 1..56.');
        }
        if ($this->transportSurfaceId !== 23) {
            throw new InvalidArgumentException('External HTTP/OAuth/webhook transport must resolve through canonical Surface 23.');
        }
        if (!$this->externalAuthority || !$this->unknownOutcomeFirstClass) {
            throw new InvalidArgumentException('External integrations must preserve provider authority and unknown-outcome semantics.');
        }
        if ($this->capabilities === [] || count($this->capabilities) !== count(array_unique($this->capabilities))) {
            throw new InvalidArgumentException('Integration capabilities must be non-empty and unique.');
        }
        foreach ($this->capabilities as $capability) {
            if (!preg_match('/^[a-z][a-z0-9._-]{1,127}$/', $capability)) {
                throw new InvalidArgumentException('Integration capability keys must be stable lowercase identifiers.');
            }
        }
        if ($this->credentialReferenceId !== null && !preg_match('/^[a-f0-9]{32}$/', $this->credentialReferenceId)) {
            throw new InvalidArgumentException('Integration credentials must use an opaque Vault reference id, never plaintext.');
        }
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, $this->capabilities, true);
    }
}
