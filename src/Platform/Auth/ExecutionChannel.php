<?php

declare(strict_types=1);

namespace WPEssential\Platform\Auth;

enum ExecutionChannel: string
{
    case Internal = 'internal';
    case Ui = 'ui';
    case Rest = 'rest';
    case Cli = 'cli';
    case Workflow = 'workflow';
    case Ai = 'ai';
}
