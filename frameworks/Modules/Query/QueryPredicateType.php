<?php

declare(strict_types=1);

namespace WPEssential\Modules\Query;

if (!defined('ABSPATH')) {
    exit;
}

enum QueryPredicateType: string
{
    case Group = 'group';
    case Comparison = 'comparison';
    case Existence = 'existence';
    case Range = 'range';
    case SetMembership = 'set_membership';
    case Text = 'text';
    case Taxonomy = 'taxonomy';
    case DateTime = 'datetime';
    case Field = 'field';
    case Relation = 'relation';
    case ProviderExtension = 'provider_extension';
}
