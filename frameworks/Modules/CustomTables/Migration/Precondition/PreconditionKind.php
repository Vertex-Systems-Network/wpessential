<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration\Precondition;

if (!defined('ABSPATH')) {
    exit;
}

enum PreconditionKind: string
{
    case TableExists = 'table_exists';
    case TableMissing = 'table_missing';
    case ColumnMatchesFingerprint = 'column_matches_fingerprint';
    case RowCountRange = 'row_count_range';
    case NoNullValues = 'no_null_values';
    case NoDuplicateValues = 'no_duplicate_values';
    case MaxValueFits = 'max_value_fits';
    case MaxStringLengthFits = 'max_string_length_fits';
    case NoOrphanReferences = 'no_orphan_references';
    case DatabaseFeatureAvailable = 'db_feature_available';
    case CapacityAvailable = 'capacity_available';
    case NoActiveMigration = 'no_active_migration';
    case BackupTierAvailable = 'backup_tier_available';
}
