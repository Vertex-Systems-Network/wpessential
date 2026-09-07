<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class AdminColumnsFieldValueWriteAbilityHandler implements AbilityHandlerInterface
{
    public const ABILITY = 'wpessential/admin-columns/write-field-value';
    public const AJAX_TYPE = 'admin-columns.write.field-value';

    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    private const COLUMN_KEY_PATTERN = '/^[a-z0-9][a-z0-9_-]{0,63}$/';
    private const INPUT_KEYS = [
        'view_id',
        'column_key',
        'post_id',
        'expected_group_revision',
        'value',
    ];

    public function __construct(private AdminColumnsFieldValueWriteAdapter $writes)
    {
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        foreach (array_keys($input) as $key) {
            if (!in_array($key, self::INPUT_KEYS, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Admin Columns Fields mutation input contains unsupported option "%s".',
                    (string) $key,
                ));
            }
        }

        $viewId = $input['view_id'] ?? null;
        if (!is_string($viewId) || preg_match(self::UUID_PATTERN, $viewId) !== 1) {
            throw new InvalidArgumentException('view_id must be a lowercase RFC 4122 UUID.');
        }

        $columnKey = $input['column_key'] ?? null;
        if (!is_string($columnKey) || preg_match(self::COLUMN_KEY_PATTERN, $columnKey) !== 1) {
            throw new InvalidArgumentException('column_key must be a bounded lowercase machine key.');
        }

        $postId = $input['post_id'] ?? null;
        if (!is_int($postId) || $postId < 1) {
            throw new InvalidArgumentException('post_id must be a positive integer.');
        }

        $expectedGroupRevision = $input['expected_group_revision'] ?? null;
        if (!is_int($expectedGroupRevision) || $expectedGroupRevision < 1) {
            throw new InvalidArgumentException('expected_group_revision must be a positive integer.');
        }

        if (!array_key_exists('value', $input)) {
            throw new InvalidArgumentException('value is required; null is an explicit owner mutation value.');
        }

        return $this->writes->write(
            $viewId,
            $columnKey,
            $postId,
            $expectedGroupRevision,
            $input['value'],
            $context,
        );
    }
}
