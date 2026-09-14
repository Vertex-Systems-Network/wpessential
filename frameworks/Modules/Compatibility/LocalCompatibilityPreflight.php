<?php

declare(strict_types=1);

namespace WPEssential\Modules\Compatibility;

if (!defined('ABSPATH')) {
    exit;
}

final class LocalCompatibilityPreflight
{
    public const COMPATIBLE = 'compatible';
    public const FREE_MISSING = 'free_missing';
    public const FREE_BOOTSTRAP_INCOMPLETE = 'free_bootstrap_incomplete';
    public const FREE_METADATA_MISSING = 'free_metadata_missing';
    public const FREE_VERSION_INVALID = 'free_version_invalid';
    public const FREE_VERSION_TOO_OLD = 'free_version_too_old';
    public const FREE_VERSION_TOO_NEW = 'free_version_too_new';
    public const PLATFORM_API_MISSING = 'platform_api_missing';
    public const PLATFORM_API_INVALID = 'platform_api_invalid';
    public const PLATFORM_API_TOO_OLD = 'platform_api_too_old';
    public const PLATFORM_API_TOO_NEW = 'platform_api_too_new';
    public const PLATFORM_SCHEMA_MISSING = 'platform_schema_missing';
    public const PLATFORM_SCHEMA_INVALID = 'platform_schema_invalid';
    public const PLATFORM_SCHEMA_TOO_OLD = 'platform_schema_too_old';
    public const PLATFORM_SCHEMA_TOO_NEW = 'platform_schema_too_new';
    public const PRO_METADATA_INVALID = 'pro_metadata_invalid';
    public const PRO_PACKAGE_INCOMPLETE = 'pro_package_incomplete';

    private const STRICT_VERSION_PATTERN = '/^[0-9]+\.[0-9]+\.[0-9]+$/';
    private const MARKETING_VERSION_PATTERN = '/^[0-9]+\.[0-9]+\.[0-9]+(?:-[0-9A-Za-z.-]+)?$/';

    /**
     * Evaluate only local package metadata. No WordPress service, network, entitlement or
     * Membership state participates in this decision.
     *
     * @param array<string,mixed> $free
     * @param array<string,mixed> $pro
     * @return array{
     *   state:string,
     *   dimension:string,
     *   reason:string,
     *   remediation:string,
     *   free_version:string,
     *   pro_version:string,
     *   platform_api:string,
     *   platform_schema:int|string,
     *   pro_schema:int|string,
     *   premium_boot_allowed:bool,
     *   premium_migrations_allowed:bool
     * }
     */
    public static function evaluate(array $free, array $pro): array
    {
        if (($free['present'] ?? false) !== true) {
            return self::result(self::FREE_MISSING, 'package', 'free_not_loaded', 'activate_free', $free, $pro);
        }

        if (($free['bootstrap_complete'] ?? false) !== true) {
            return self::result(
                self::FREE_BOOTSTRAP_INCOMPLETE,
                'package',
                'free_bootstrap_not_ready',
                'repair_free',
                $free,
                $pro,
            );
        }

        if (($pro['package_complete'] ?? true) !== true) {
            return self::result(
                self::PRO_PACKAGE_INCOMPLETE,
                'package',
                'required_pro_file_missing',
                'reinstall_pro',
                $free,
                $pro,
            );
        }

        $proVersion = self::stringValue($pro['version'] ?? null);
        $minFree = self::stringValue($pro['min_free_version'] ?? null);
        $maxFree = self::stringValue($pro['max_free_version'] ?? null);
        $minApi = self::stringValue($pro['min_platform_api'] ?? null);
        $maxApi = self::stringValue($pro['max_platform_api'] ?? null);
        $minSchema = $pro['min_platform_schema'] ?? null;
        $maxSchema = $pro['max_platform_schema'] ?? null;
        $proSchema = $pro['schema'] ?? null;

        if (
            $proVersion === null
            || $minFree === null
            || $maxFree === null
            || $minApi === null
            || $maxApi === null
            || !self::isMarketingVersion($proVersion)
            || !self::isMarketingVersion($minFree)
            || !self::isMarketingVersion($maxFree)
            || !self::isStrictVersion($minApi)
            || !self::isStrictVersion($maxApi)
            || !is_int($minSchema)
            || $minSchema < 0
            || !is_int($maxSchema)
            || $maxSchema < 0
            || !is_int($proSchema)
            || $proSchema < 0
        ) {
            return self::result(
                self::PRO_METADATA_INVALID,
                'pro_metadata',
                'pro_metadata_missing_malformed_or_contradictory',
                'reinstall_pro',
                $free,
                $pro,
            );
        }

        if (
            version_compare($minFree, $maxFree, '>')
            || version_compare($minApi, $maxApi, '>')
            || $minSchema > $maxSchema
        ) {
            return self::result(
                self::PRO_METADATA_INVALID,
                'pro_metadata',
                'pro_metadata_missing_malformed_or_contradictory',
                'reinstall_pro',
                $free,
                $pro,
            );
        }

        $freeVersion = self::stringValue($free['version'] ?? null);
        if ($freeVersion === null) {
            return self::result(
                self::FREE_METADATA_MISSING,
                'free_version',
                'free_version_missing',
                'repair_free',
                $free,
                $pro,
            );
        }
        if (!self::isMarketingVersion($freeVersion)) {
            return self::result(
                self::FREE_VERSION_INVALID,
                'free_version',
                'free_version_malformed',
                'repair_free',
                $free,
                $pro,
            );
        }
        if (version_compare($freeVersion, $minFree, '<')) {
            return self::result(
                self::FREE_VERSION_TOO_OLD,
                'free_version',
                'free_version_below_supported_minimum',
                'update_free',
                $free,
                $pro,
            );
        }
        if (version_compare($freeVersion, $maxFree, '>')) {
            return self::result(
                self::FREE_VERSION_TOO_NEW,
                'free_version',
                'free_version_above_supported_maximum',
                'update_pro',
                $free,
                $pro,
            );
        }

        $platformApi = self::stringValue($free['platform_api'] ?? null);
        if ($platformApi === null) {
            return self::result(
                self::PLATFORM_API_MISSING,
                'platform_api',
                'platform_api_missing',
                'update_free',
                $free,
                $pro,
            );
        }
        if (!self::isStrictVersion($platformApi)) {
            return self::result(
                self::PLATFORM_API_INVALID,
                'platform_api',
                'platform_api_malformed',
                'repair_free',
                $free,
                $pro,
            );
        }
        if (version_compare($platformApi, $minApi, '<')) {
            return self::result(
                self::PLATFORM_API_TOO_OLD,
                'platform_api',
                'platform_api_below_supported_minimum',
                'update_free',
                $free,
                $pro,
            );
        }
        if (version_compare($platformApi, $maxApi, '>')) {
            return self::result(
                self::PLATFORM_API_TOO_NEW,
                'platform_api',
                'platform_api_above_supported_maximum',
                'update_pro',
                $free,
                $pro,
            );
        }

        $platformSchema = $free['platform_schema'] ?? null;
        if ($platformSchema === null) {
            return self::result(
                self::PLATFORM_SCHEMA_MISSING,
                'platform_schema',
                'platform_schema_missing',
                'update_free',
                $free,
                $pro,
            );
        }
        if (!is_int($platformSchema) || $platformSchema < 0) {
            return self::result(
                self::PLATFORM_SCHEMA_INVALID,
                'platform_schema',
                'platform_schema_malformed',
                'repair_free',
                $free,
                $pro,
            );
        }
        if ($platformSchema < $minSchema) {
            return self::result(
                self::PLATFORM_SCHEMA_TOO_OLD,
                'platform_schema',
                'platform_schema_below_supported_minimum',
                'update_free',
                $free,
                $pro,
            );
        }
        if ($platformSchema > $maxSchema) {
            return self::result(
                self::PLATFORM_SCHEMA_TOO_NEW,
                'platform_schema',
                'platform_schema_above_supported_maximum',
                'update_pro',
                $free,
                $pro,
            );
        }

        return self::result(self::COMPATIBLE, 'pair', 'compatible_local_pair', 'none', $free, $pro, true);
    }

    /**
     * @return array{
     *   state:string,
     *   dimension:string,
     *   reason:string,
     *   remediation:string,
     *   free_version:string,
     *   pro_version:string,
     *   platform_api:string,
     *   platform_schema:int|string,
     *   pro_schema:int|string,
     *   premium_boot_allowed:bool,
     *   premium_migrations_allowed:bool
     * }
     */
    public static function evaluateRuntime(bool $proPackageComplete = true): array
    {
        $freePresent = defined('WPE_VERSION')
            || defined('WPE_FREE_BOOTSTRAP_READY')
            || defined('WPE_PLATFORM_API_VERSION')
            || defined('WPE_PLATFORM_SCHEMA_GENERATION');

        return self::evaluate(
            [
                'present' => $freePresent,
                'bootstrap_complete' => defined('WPE_FREE_BOOTSTRAP_READY') && WPE_FREE_BOOTSTRAP_READY === true,
                'version' => defined('WPE_VERSION') ? WPE_VERSION : null,
                'platform_api' => defined('WPE_PLATFORM_API_VERSION') ? WPE_PLATFORM_API_VERSION : null,
                'platform_schema' => defined('WPE_PLATFORM_SCHEMA_GENERATION') ? WPE_PLATFORM_SCHEMA_GENERATION : null,
            ],
            [
                'package_complete' => $proPackageComplete,
                'version' => defined('WPE_PRO_VERSION') ? WPE_PRO_VERSION : null,
                'min_free_version' => defined('WPE_PRO_MIN_FREE_VERSION') ? WPE_PRO_MIN_FREE_VERSION : null,
                'max_free_version' => defined('WPE_PRO_MAX_FREE_VERSION') ? WPE_PRO_MAX_FREE_VERSION : null,
                'min_platform_api' => defined('WPE_PRO_MIN_PLATFORM_API_VERSION')
                    ? WPE_PRO_MIN_PLATFORM_API_VERSION
                    : null,
                'max_platform_api' => defined('WPE_PRO_MAX_PLATFORM_API_VERSION')
                    ? WPE_PRO_MAX_PLATFORM_API_VERSION
                    : null,
                'min_platform_schema' => defined('WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION')
                    ? WPE_PRO_MIN_PLATFORM_SCHEMA_GENERATION
                    : null,
                'max_platform_schema' => defined('WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION')
                    ? WPE_PRO_MAX_PLATFORM_SCHEMA_GENERATION
                    : null,
                'schema' => defined('WPE_PRO_SCHEMA_GENERATION') ? WPE_PRO_SCHEMA_GENERATION : null,
            ],
        );
    }

    private static function isMarketingVersion(string $value): bool
    {
        return preg_match(self::MARKETING_VERSION_PATTERN, $value) === 1;
    }

    private static function isStrictVersion(string $value): bool
    {
        return preg_match(self::STRICT_VERSION_PATTERN, $value) === 1;
    }

    private static function stringValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }

    /**
     * @param array<string,mixed> $free
     * @param array<string,mixed> $pro
     * @return array{
     *   state:string,
     *   dimension:string,
     *   reason:string,
     *   remediation:string,
     *   free_version:string,
     *   pro_version:string,
     *   platform_api:string,
     *   platform_schema:int|string,
     *   pro_schema:int|string,
     *   premium_boot_allowed:bool,
     *   premium_migrations_allowed:bool
     * }
     */
    private static function result(
        string $state,
        string $dimension,
        string $reason,
        string $remediation,
        array $free,
        array $pro,
        bool $allowed = false,
    ): array {
        $platformSchema = $free['platform_schema'] ?? 'unknown';
        $proSchema = $pro['schema'] ?? 'unknown';

        return [
            'state' => $state,
            'dimension' => $dimension,
            'reason' => $reason,
            'remediation' => $remediation,
            'free_version' => self::stringValue($free['version'] ?? null) ?? 'unknown',
            'pro_version' => self::stringValue($pro['version'] ?? null) ?? 'unknown',
            'platform_api' => self::stringValue($free['platform_api'] ?? null) ?? 'unknown',
            'platform_schema' => is_int($platformSchema) ? $platformSchema : 'unknown',
            'pro_schema' => is_int($proSchema) ? $proSchema : 'unknown',
            'premium_boot_allowed' => $allowed,
            // Compatibility-layer admission only; migration-specific safety checks remain downstream.
            'premium_migrations_allowed' => $allowed,
        ];
    }
}
