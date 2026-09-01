<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class PostMetaValueWriteResult
{
    public const WRITTEN = 'written';
    public const UNCHANGED = 'unchanged';
    public const DELETED = 'deleted';
    public const ABSENT = 'absent';

    /** @var list<string> */
    private const STATUSES = [self::WRITTEN, self::UNCHANGED, self::DELETED, self::ABSENT];

    public function __construct(
        public string $status,
        public string $fieldUuid,
        public string $metaKey,
        public mixed $value,
    ) {
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException(sprintf('Unknown post-meta write status "%s".', $status));
        }
        if ($fieldUuid === '' || $metaKey === '') {
            throw new InvalidArgumentException('Post-meta write result requires Field UUID and meta key provenance.');
        }
    }

    public function changed(): bool
    {
        return in_array($this->status, [self::WRITTEN, self::DELETED], true);
    }
}
