<?php

declare(strict_types=1);

namespace WPEssential\Platform\Jobs;


if (!defined('ABSPATH')) {
    exit;
}

enum JobFailureClass: string
{
    case TransientLocal = 'transient_local';
    case ProviderRateLimited = 'provider_rate_limited';
    case ProviderTransient = 'provider_transient';
    case DependencyUnavailable = 'dependency_unavailable';
    case ConcurrencyConflict = 'concurrency_conflict';
    case ValidationPermanent = 'validation_permanent';
    case AuthorizationRevoked = 'authorization_revoked';
    case UnknownExternalOutcome = 'unknown_external_outcome';
    case Poison = 'poison';

    public function isRetryable(): bool
    {
        return in_array($this, [
            self::TransientLocal,
            self::ProviderRateLimited,
            self::ProviderTransient,
            self::DependencyUnavailable,
            self::ConcurrencyConflict,
        ], true);
    }

    public function requiresReconciliation(): bool
    {
        return $this === self::UnknownExternalOutcome;
    }
}
