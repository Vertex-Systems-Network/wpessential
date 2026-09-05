<?php

declare(strict_types=1);

namespace WPEssential\Contracts;

if (!defined('ABSPATH')) {
    exit;
}

interface FieldSourceCatalogConsumerInterface
{
    public const CONTRACT_VERSION = 1;
    public const MAX_SOURCES = 100;

    /**
     * Return the bounded owner-certified catalog of published top-level scalar
     * Fields that are readable through the retained native post-meta contract.
     * This is definition/source metadata only; it grants no per-resource read or
     * mutation authorization and exposes no REST/AJAX endpoint.
     *
     * @return list<array{
     *     contract_version:int,
     *     field_ref:string,
     *     group_id:string,
     *     group_revision:int,
     *     field_uuid:string,
     *     label:string,
     *     logical_type:string,
     *     storage_owner:string,
     *     post_types:list<string>
     * }>
     */
    public function listSources(): array;
}
