<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Registrations;

enum RegistrationKind: string
{
    case PostType = 'post_type';
    case Taxonomy = 'taxonomy';
    case Metabox = 'metabox';
    case SettingsPage = 'settings_page';
}
