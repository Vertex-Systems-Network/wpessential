<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Security;


if (!defined('ABSPATH')) {
    exit;
}

enum NonceOperation: string
{
    case Apply = 'apply';
    case Create = 'create';
    case Update = 'update';
    case Reset = 'reset';
    case Delete = 'delete';
}
