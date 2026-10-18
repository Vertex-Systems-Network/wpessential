<?php

declare(strict_types=1);

namespace WPEssential\Tests\Unit\Modules\CustomTables\Readiness\Composition\Persistence;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use WPEssential\Contracts\CapabilityCheckerInterface;
use WPEssential\Modules\CustomTables\Migration\Readiness\Authorization\ExecutionActorType;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationIssuer;
use WPEssential\Modules\CustomTables\Migration\Readiness\Composition\Persistence\WpdbMigrationExecutionConfirmationProvider;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyEngine;
use WPEssential\Platform\Auth\Principal;
use WPEssential\Platform\Database\DatabaseAdapterInterface;

final class WpdbMigrationExecutionConfirmationIssuerTest extends TestCase
{
    private const RUN_ID = '0f8fad5b-d9cb-469f-a165-70867728950e';

    public function testAuthorizedUserIssuesConfirmationOnceAndItReadsBack(): void
    {
        $database = new IssuerDatabaseFake();
        $confirmation = self::issuer($database, true)->issue(self::context(5), strtoupper(self::RUN_ID), str_repeat('a', 64), 3);

        self::assertSame(self::RUN_ID, $confirmation->runId);
        self::assertSame(ExecutionActorType::User, $confirmation->actorType);
        self::assertSame(5, $confirmation->actorUserId);
        self::assertCount(1, $database->rows);
        self::assertSame(7, $database->rows[0]['site_id']);
    }

    public function testUnauthorizedActorWritesNothing(): void
    {
        $database = new IssuerDatabaseFake();

        try {
            self::issuer($database, false)->issue(self::context(5), self::RUN_ID, str_repeat('a', 64), 3);
            self::fail('Expected unauthorized issuance to be rejected.');
        } catch (RuntimeException) {
            self::assertSame([], $database->rows);
        }
    }

    public function testSystemOrAnonymousActorIsRejected(): void
    {
        $database = new IssuerDatabaseFake();
        $issuer = self::issuer($database, true);

        foreach ([new Principal(null, 'system'), new Principal(null)] as $principal) {
            try {
                $issuer->issue(new ExecutionContext($principal, 7), self::RUN_ID, str_repeat('a', 64), 3);
                self::fail('Expected non-user actor to be rejected.');
            } catch (RuntimeException) {
                self::assertSame([], $database->rows);
            }
        }
    }

    public function testCrossSiteContextIsRejected(): void
    {
        $database = new IssuerDatabaseFake();

        $this->expectException(RuntimeException::class);
        self::issuer($database, true)->issue(new ExecutionContext(new Principal(5), 8), self::RUN_ID, str_repeat('a', 64), 3);
    }

    public function testSecondConfirmationForSameRunIsRejected(): void
    {
        $database = new IssuerDatabaseFake();
        $issuer = self::issuer($database, true);
        $issuer->issue(self::context(5), self::RUN_ID, str_repeat('a', 64), 3);

        try {
            $issuer->issue(self::context(5), self::RUN_ID, str_repeat('b', 64), 4);
            self::fail('Expected duplicate issuance to be rejected.');
        } catch (RuntimeException) {
            self::assertCount(1, $database->rows);
            self::assertSame(str_repeat('a', 64), $database->rows[0]['plan_fingerprint']);
        }
    }

    public function testReadbackMismatchFailsClosed(): void
    {
        $database = new IssuerDatabaseFake();
        $database->tamperOnInsert = true;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('readback verification failed');
        self::issuer($database, true)->issue(self::context(5), self::RUN_ID, str_repeat('a', 64), 3);
    }

    private static function issuer(IssuerDatabaseFake $database, bool $allowed): WpdbMigrationExecutionConfirmationIssuer
    {
        $capabilities = new class ($allowed) implements CapabilityCheckerInterface {
            public function __construct(private bool $allowed)
            {
            }

            public function can(ExecutionContext $context, string $capability): bool
            {
                return $this->allowed;
            }
        };

        return new WpdbMigrationExecutionConfirmationIssuer(
            $database,
            new WpdbMigrationExecutionConfirmationProvider($database, 7),
            new PolicyEngine($capabilities),
            7,
        );
    }

    private static function context(int $userId): ExecutionContext
    {
        return new ExecutionContext(new Principal($userId), 7);
    }
}

final class IssuerDatabaseFake implements DatabaseAdapterInterface
{
    /** @var list<array<string,mixed>> */
    public array $rows = [];
    public bool $tamperOnInsert = false;

    public function networkTablePrefix(): string { return 'wp_'; }
    public function charsetCollate(): string { return ''; }
    public function prepare(string $query, mixed ...$args): string
    {
        return $query . '|' . implode('|', array_map('strval', $args));
    }
    public function getRow(string $query): ?array
    {
        foreach ($this->rows as $row) {
            if (str_ends_with($query, '|' . $row['site_id'] . '|' . $row['run_id'])) {
                return $row;
            }
        }
        return null;
    }
    public function getResults(string $query): array { return []; }
    public function getVar(string $query): mixed { return null; }
    public function query(string $query): int|bool { return 0; }
    public function insert(string $table, array $data, array $formats = []): bool
    {
        if ($this->tamperOnInsert) {
            $data['readiness_state_revision'] = 99;
        }
        $this->rows[] = $data;
        return true;
    }
    public function lastError(): string { return ''; }
    public function beginTransaction(): void {}
    public function commit(): void {}
    public function rollBack(): void {}
}
