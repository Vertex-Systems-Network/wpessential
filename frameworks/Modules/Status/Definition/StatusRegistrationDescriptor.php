<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Definition;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class StatusRegistrationDescriptor
{
    /** @param list<string> $postTypes */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public string $key,
        public string $label,
        public string $countSingular,
        public string $countPlural,
        public bool $public,
        public bool $internal,
        public bool $protected,
        public bool $private,
        public bool $publiclyQueryable,
        public bool $excludeFromSearch,
        public bool $showInAdminAllList,
        public bool $showInAdminStatusList,
        public bool $dateFloating,
        public array $postTypes,
        public string $compatibilityFingerprint,
    ) {
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Status descriptor revision must be positive.');
        }
        if (!preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $this->key)) {
            throw new InvalidArgumentException('Status descriptor key must be canonical and fit the WordPress 20-character storage bound.');
        }
        foreach ([$this->label, $this->countSingular, $this->countPlural] as $label) {
            if (
                $label === ''
                || strlen($label) > 160
                || str_contains($label, '<')
                || str_contains($label, '>')
                || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $label)
            ) {
                throw new InvalidArgumentException('Status descriptor labels must be bounded plain text.');
            }
        }
        if (count(array_filter([$this->public, $this->internal, $this->protected, $this->private])) > 1) {
            throw new InvalidArgumentException('Status visibility classification must be unambiguous.');
        }
        if ($this->internal && $this->publiclyQueryable) {
            throw new InvalidArgumentException('Internal Status descriptors cannot be publicly queryable.');
        }
        if ($this->postTypes === [] || count($this->postTypes) > 64) {
            throw new InvalidArgumentException('Status descriptor post-type applicability must be a bounded non-empty list.');
        }
        $seen = [];
        foreach ($this->postTypes as $postType) {
            if (!is_string($postType) || !preg_match('/^[a-z0-9][a-z0-9_-]{0,19}$/', $postType)) {
                throw new InvalidArgumentException('Status descriptor post types must be bounded WordPress keys.');
            }
            if (isset($seen[$postType])) {
                throw new InvalidArgumentException('Status descriptor post types must be unique.');
            }
            $seen[$postType] = true;
        }
        if (!preg_match('/^[0-9a-f]{64}$/', $this->compatibilityFingerprint)) {
            throw new InvalidArgumentException('Status descriptor compatibility fingerprint must be SHA-256 hex.');
        }
    }
}
