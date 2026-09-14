<?php

declare(strict_types=1);

namespace WPEssential\Platform\Entitlements;

if (!defined('ABSPATH')) {
    exit;
}

enum ProductEntitlementState: string
{
    case Free = 'free';
    case TrialActive = 'trial_active';
    case ProActive = 'pro_active';
    case Grace = 'grace';
    case Expired = 'expired';
    case Suspended = 'suspended';
    case VerificationStale = 'verification_stale';
    case VerificationUnavailable = 'verification_unavailable';
    case IncompatibleVersion = 'incompatible_version';
}
