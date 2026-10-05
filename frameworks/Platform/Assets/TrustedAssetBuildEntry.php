<?php

declare(strict_types=1);

namespace WPEssential\Platform\Assets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class TrustedAssetBuildEntry
{
    public function __construct(
        public string $assetHandle,
        public string $scriptEntry,
        public ?string $styleEntry = null,
    ) {
        if (preg_match('/^wpe-[a-z0-9][a-z0-9-]{1,127}$/', $this->assetHandle) !== 1) {
            throw new InvalidArgumentException('Trusted asset build mapping handle is invalid.');
        }

        $this->assertEntry($this->scriptEntry, 'script');

        if ($this->styleEntry !== null) {
            $this->assertEntry($this->styleEntry, 'style');
        }
    }

    private function assertEntry(string $entry, string $kind): void
    {
        if (
            preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,127}$/', $entry) !== 1
            || str_contains($entry, '..')
        ) {
            throw new InvalidArgumentException(
                sprintf('Trusted asset %s build entry is invalid.', $kind),
            );
        }
    }
}
