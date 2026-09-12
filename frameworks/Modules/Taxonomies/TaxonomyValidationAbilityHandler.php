<?php

declare(strict_types=1);

namespace WPEssential\Modules\Taxonomies;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class TaxonomyValidationAbilityHandler implements AbilityHandlerInterface
{
    public function __construct(
        private TaxonomyValidationService $validation,
        private ?TaxonomyRoleImpactReadModel $roleImpact = null,
    ) {}

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $report = $this->validation->validate($input);
        $diagnostics = $report['diagnostics'] ?? null;
        $payload = $input['payload'] ?? null;
        if (!is_array($diagnostics) || !is_array($payload) || array_is_list($payload)) {
            return $report;
        }

        $roleImpact = $this->roleImpact ?? new TaxonomyRoleImpactReadModel();
        $diagnostics['role_impact'] = $roleImpact->forCapabilities(
            $roleImpact->effectiveCapabilities($payload),
            $context,
        );
        $report['diagnostics'] = $diagnostics;

        return $report;
    }
}
