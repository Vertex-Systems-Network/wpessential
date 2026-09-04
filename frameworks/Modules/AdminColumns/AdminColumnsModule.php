<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use LogicException;
use WPEssential\Contracts\DataSourceRegistryInterface;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Contracts\ModuleInterface;
use WPEssential\Contracts\QueryReadConsumerInterface;
use WPEssential\Contracts\ServiceRegistryInterface;
use WPEssential\Platform\Admin\AdminAssetManifest;
use WPEssential\Platform\Modules\ModuleManifest;

final class AdminColumnsModule implements ModuleInterface
{
    public const SERVICE_NORMALIZER = 'module.admin-columns.view-normalizer';
    public const SERVICE_VIEWS = 'module.admin-columns.views';
    public const SERVICE_READ_ADAPTER = 'module.admin-columns.read-adapter';
    public const SERVICE_ADMIN = 'module.admin-columns.admin';

    private const QUERY_READ_SERVICE = 'module.query.read-consumer';
    private const DATA_SOURCE_SERVICE = 'platform.data-sources';
    private const ADMIN_ASSET_SERVICE = 'platform.admin.assets';

    public function manifest(): ModuleManifest
    {
        return new ModuleManifest(
            id: 'admin-columns',
            name: 'Admin Columns',
            version: '0.1.0',
            edition: 'pro',
            dependencies: ['query'],
        );
    }

    public function register(ServiceRegistryInterface $services): void
    {
        $definitions = $services->get('platform.definitions');
        if (!$definitions instanceof DefinitionRepositoryInterface) {
            throw new LogicException('Admin Columns requires the canonical shared Definition repository.');
        }
        $query = $services->get(self::QUERY_READ_SERVICE);
        if (!$query instanceof QueryReadConsumerInterface) {
            throw new LogicException('Admin Columns requires the bounded public Query read consumer.');
        }

        foreach ([self::SERVICE_NORMALIZER, self::SERVICE_VIEWS, self::SERVICE_READ_ADAPTER] as $serviceId) {
            if ($services->has($serviceId)) {
                throw new LogicException(sprintf('Admin Columns service "%s" is already registered.', $serviceId));
            }
        }

        $normalizer = new AdminColumnsViewDefinitionNormalizer();
        $views = new AdminColumnsViewDefinitionService($definitions, $normalizer);
        $readAdapter = new AdminColumnsReadAdapter($views, $query);
        $services->set(self::SERVICE_NORMALIZER, $normalizer);
        $services->set(self::SERVICE_VIEWS, $views);
        $services->set(self::SERVICE_READ_ADAPTER, $readAdapter);
    }

    public function boot(ServiceRegistryInterface $services): void
    {
        $assets = $services->get(self::ADMIN_ASSET_SERVICE);
        $dataSources = $services->get(self::DATA_SOURCE_SERVICE);
        if (!$assets instanceof AdminAssetManifest || !$dataSources instanceof DataSourceRegistryInterface) {
            throw new LogicException('Admin Columns admin requires canonical shared admin assets and Data Source Registry.');
        }
        if ($services->has(self::SERVICE_ADMIN)) {
            throw new LogicException('Admin Columns admin service is already registered.');
        }

        $admin = new AdminColumnsAdminController(
            new AdminColumnsAdminBootstrapProjector($dataSources),
            $assets,
        );
        $services->set(self::SERVICE_ADMIN, $admin);
        $admin->register();
        // The page is read-only scaffolding. No REST/AJAX, persistence, export
        // or source-owner mutation endpoint is exposed in this tranche.
    }
}
