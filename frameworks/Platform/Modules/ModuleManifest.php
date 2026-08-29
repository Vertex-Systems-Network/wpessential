<?php

declare(strict_types=1);

namespace WPEssential\Platform\Modules;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ModuleManifest
{
    /** @param list<string> $dependencies */
    public function __construct(
        public string $id,
        public string $name,
        public string $version,
        public string $edition = 'free',
        public array $dependencies = [],
        public string $minimumPlatformVersion = '0.1.0',
        public string $minimumWordPressVersion = '6.9',
        public string $minimumPhpVersion = '8.2',
    ) {
        if (!preg_match('/^[a-z0-9][a-z0-9-]*$/', $this->id)) {
            throw new InvalidArgumentException('Module id must be a lowercase slug.');
        }
        if ($this->name === '') {
            throw new InvalidArgumentException('Module name cannot be empty.');
        }
        if ($this->version === '') {
            throw new InvalidArgumentException('Module version cannot be empty.');
        }
        if (!in_array($this->edition, ['free', 'pro'], true)) {
            throw new InvalidArgumentException('Module edition must be free or pro.');
        }

        $seen = [];
        foreach ($this->dependencies as $dependency) {
            if (!is_string($dependency) || !preg_match('/^[a-z0-9][a-z0-9-]*$/', $dependency)) {
                throw new InvalidArgumentException('Module dependencies must be lowercase module slugs.');
            }
            if ($dependency === $this->id) {
                throw new InvalidArgumentException('A module cannot depend on itself.');
            }
            if (isset($seen[$dependency])) {
                throw new InvalidArgumentException('Module dependencies must be unique.');
            }
            $seen[$dependency] = true;
        }
    }
}
