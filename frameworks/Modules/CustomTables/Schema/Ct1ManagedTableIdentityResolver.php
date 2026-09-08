<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Schema;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Modules\CustomTables\Definition\TableSchemaDescriptor;

final readonly class Ct1ManagedTableIdentityResolver
{
    private const NAMESPACE = 'wpe_';

    public function __construct(private object $wpdb)
    {
        if (!property_exists($this->wpdb, 'prefix')) {
            throw new InvalidArgumentException('CT1 identity resolver requires the trusted current-site wpdb prefix.');
        }
    }

    public function resolve(TableSchemaDescriptor $descriptor): Ct1ManagedTableIdentity
    {
        if ($descriptor->storageMode !== 'managed' || $descriptor->scope !== 'site') {
            throw new InvalidArgumentException('CT1 identity resolver accepts managed site-scoped tables only.');
        }

        $sitePrefix = (string) $this->wpdb->prefix;
        if ($sitePrefix === ''
            || strlen($sitePrefix) > 48
            || preg_match('/^[A-Za-z0-9_]+$/', $sitePrefix) !== 1
        ) {
            throw new RuntimeException('Trusted current-site WordPress table prefix is invalid.');
        }

        $physicalName = $sitePrefix . self::NAMESPACE . $descriptor->tableKey;
        if (strlen($physicalName) > 64) {
            throw new RuntimeException('Managed CT1 physical table identity exceeds the database identifier limit.');
        }

        return new Ct1ManagedTableIdentity(
            tableKey: $descriptor->tableKey,
            sitePrefix: $sitePrefix,
            physicalName: $physicalName,
        );
    }
}
