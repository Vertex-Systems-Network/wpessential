<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use RuntimeException;

final class FieldValueTargetMismatchException extends RuntimeException {}
