<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition\Probe;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;
use WPEssential\Modules\CustomTables\Migration\ProviderCapabilityProfile;

interface MetadataPreconditionFactsProviderInterface
{
    public function facts(
        TableSchemaDescriptor $descriptor,
        ProviderCapabilityProfile $capabilities,
    ): MetadataPreconditionFacts;
}
