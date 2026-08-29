<?php

declare(strict_types=1);

namespace WPEssential\Platform\Definitions;

enum DefinitionStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Disabled = 'disabled';
    case Archived = 'archived';
}
