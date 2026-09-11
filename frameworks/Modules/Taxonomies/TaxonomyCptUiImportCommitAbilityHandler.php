<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class TaxonomyCptUiImportCommitAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(
        private TaxonomyCptUiImportPreviewService $preview,
        private TaxonomyAbilityHandler $save,
    ) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $source = $input['source'] ?? null;
        if (!is_array($source) || array_is_list($source)) {
            throw new InvalidArgumentException('CPT UI import source must be an object/map.');
        }

        $id = $input['id'] ?? null;
        if ($id !== null && !is_string($id)) {
            throw new InvalidArgumentException('CPT UI import id must be a string when provided.');
        }

        $report = $this->preview->preview($source, $id);
        if (!$report['valid']) {
            foreach ($report['issues'] as $issue) {
                if ($issue['severity'] === 'blocked') {
                    throw new InvalidArgumentException($issue['message']);
                }
            }
            throw new InvalidArgumentException('CPT UI import is blocked by canonical Taxonomy validation.');
        }

        $request = ['payload' => $report['payload']];
        $expectedRevision = $input['expected_revision'] ?? null;

        if ($id === null) {
            if ($expectedRevision !== null) {
                throw new InvalidArgumentException('Create-only CPT UI import must not provide expected_revision without an id.');
            }
        } else {
            $request['id'] = $id;
            $request['expected_revision'] = $expectedRevision;
        }

        $result = $this->save->handle($request, $context);
        if (!is_array($result)) {
            throw new InvalidArgumentException('Canonical Taxonomy save returned an invalid import result.');
        }

        $result['import'] = [
            'unsupported_fields' => $report['unsupported_fields'],
            'issues' => $report['issues'],
        ];
        return $result;
    }
}
