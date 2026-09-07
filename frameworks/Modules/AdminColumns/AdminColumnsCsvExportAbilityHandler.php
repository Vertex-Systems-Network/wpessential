<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class AdminColumnsCsvExportAbilityHandler implements AbilityHandlerInterface
{
    public const ABILITY = 'wpessential/admin-columns/export-csv';
    public const AJAX_TYPE = 'admin-columns.export.csv';
    public const CONTRACT_VERSION = 1;
    public const CONTENT_TYPE = 'text/csv; charset=UTF-8';
    public const FILENAME = 'admin-columns-export.csv';

    private const MAX_OUTPUT_BYTES = 8 * 1024 * 1024;
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    private const INPUT_KEYS = [
        'view_id',
        'expected_view_revision',
        'scope_request',
        'controls',
        'runtime',
    ];

    public function __construct(private AdminColumnsCsvExportService $exports)
    {
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        foreach (array_keys($input) as $key) {
            if (!is_string($key) || !in_array($key, self::INPUT_KEYS, true)) {
                throw new InvalidArgumentException('Admin Columns CSV export input contains unsupported top-level input.');
            }
        }
        foreach (self::INPUT_KEYS as $key) {
            if (!array_key_exists($key, $input)) {
                throw new InvalidArgumentException(sprintf('Admin Columns CSV export input requires "%s".', $key));
            }
        }

        $viewId = $input['view_id'];
        if (!is_string($viewId) || preg_match(self::UUID_PATTERN, $viewId) !== 1) {
            throw new InvalidArgumentException('view_id must be a lowercase RFC 4122 UUID.');
        }

        $expectedViewRevision = $input['expected_view_revision'];
        if (!is_int($expectedViewRevision) || $expectedViewRevision < 1) {
            throw new InvalidArgumentException('expected_view_revision must be a positive integer.');
        }

        $scopeRequest = $this->objectMap($input['scope_request'], 'scope_request');
        $controls = $this->objectMap($input['controls'], 'controls');
        $runtime = $this->objectMap($input['runtime'], 'runtime');

        $csv = $this->exports->exportScoped(
            $viewId,
            $expectedViewRevision,
            $scopeRequest,
            $controls,
            $runtime,
            $context,
        );
        $bytes = strlen($csv);
        if ($bytes > self::MAX_OUTPUT_BYTES) {
            throw new InvalidArgumentException('Admin Columns CSV export output exceeds the bounded transport envelope.');
        }

        return [
            'contract_version' => self::CONTRACT_VERSION,
            'content_type' => self::CONTENT_TYPE,
            'filename' => self::FILENAME,
            'bytes' => $bytes,
            'csv' => $csv,
        ];
    }

    /** @return array<string,mixed> */
    private function objectMap(mixed $value, string $label): array
    {
        if (!is_array($value) || array_is_list($value)) {
            throw new InvalidArgumentException(sprintf('%s must be an object/map.', $label));
        }

        return $value;
    }
}
