<?php

declare(strict_types=1);

namespace WPEssential\Modules\CustomTables\Migration;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class ProviderCapabilityProfile
{
    public const MYSQL = 'mysql';
    public const MARIADB = 'mariadb';

    public function __construct(
        public string $provider,
        public string $version,
        public bool $instantAddColumnCandidate,
        public bool $instantDefaultChangeCandidate,
        public bool $inplaceVarcharWideningCandidate,
    ) {
        if (!in_array($this->provider, [self::MYSQL, self::MARIADB], true)) {
            throw new InvalidArgumentException('Custom Tables provider capability profile is unsupported.');
        }
        if (preg_match('/^\d+\.\d+\.\d+$/', $this->version) !== 1) {
            throw new InvalidArgumentException('Custom Tables provider version must be canonical semantic numeric form.');
        }
    }

    public static function fromTrustedServerInfo(string $serverInfo): self
    {
        $serverInfo = trim($serverInfo);
        if ($serverInfo === '' || strlen($serverInfo) > 255) {
            throw new InvalidArgumentException('Trusted database server information is invalid.');
        }

        $mariaMarker = stripos($serverInfo, 'mariadb');
        $provider = $mariaMarker !== false ? self::MARIADB : self::MYSQL;
        $versionSource = $provider === self::MARIADB
            ? substr($serverInfo, 0, $mariaMarker)
            : $serverInfo;

        if (!is_string($versionSource)
            || preg_match_all('/(\d+)\.(\d+)\.(\d+)/', $versionSource, $matches, PREG_SET_ORDER) < 1
        ) {
            throw new InvalidArgumentException('Trusted database server version could not be resolved.');
        }

        $versionMatch = $provider === self::MARIADB ? $matches[array_key_last($matches)] : $matches[0];
        $version = sprintf(
            '%d.%d.%d',
            (int) $versionMatch[1],
            (int) $versionMatch[2],
            (int) $versionMatch[3],
        );

        if ($provider === self::MYSQL) {
            if (version_compare($version, '8.0.12', '<')) {
                throw new InvalidArgumentException('MySQL server is below the bounded Custom Tables provider baseline.');
            }

            return new self(
                provider: self::MYSQL,
                version: $version,
                instantAddColumnCandidate: true,
                instantDefaultChangeCandidate: true,
                inplaceVarcharWideningCandidate: true,
            );
        }

        if (version_compare($version, '10.3.2', '<')) {
            throw new InvalidArgumentException('MariaDB server is below the bounded Custom Tables provider baseline.');
        }

        return new self(
            provider: self::MARIADB,
            version: $version,
            instantAddColumnCandidate: true,
            instantDefaultChangeCandidate: false,
            inplaceVarcharWideningCandidate: false,
        );
    }

    /** @return array<string,mixed> */
    public function canonical(): array
    {
        return [
            'provider' => $this->provider,
            'version' => $this->version,
            'instant_add_column_candidate' => $this->instantAddColumnCandidate,
            'instant_default_change_candidate' => $this->instantDefaultChangeCandidate,
            'inplace_varchar_widening_candidate' => $this->inplaceVarcharWideningCandidate,
        ];
    }
}
