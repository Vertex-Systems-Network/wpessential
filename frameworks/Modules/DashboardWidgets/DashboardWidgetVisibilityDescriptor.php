<?php

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class DashboardWidgetVisibilityDescriptor
{
    public const MAX_ROLES = 32;
    public const MAX_CAPABILITIES = 32;
    public const MAX_USERS = 100;

    /**
     * @param list<string> $roles
     * @param list<string> $capabilities
     * @param list<int> $users
     */
    public function __construct(
        public string $definitionId,
        public int $revision,
        public array $roles,
        public array $capabilities,
        public array $users,
    ) {
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $this->definitionId)) {
            throw new InvalidArgumentException('Dashboard Widget visibility descriptor definition id must be a lowercase RFC 4122 UUID.');
        }
        if ($this->revision < 1) {
            throw new InvalidArgumentException('Dashboard Widget visibility descriptor revision must be positive.');
        }

        $this->assertMachineKeys($this->roles, self::MAX_ROLES, 'roles');
        $this->assertMachineKeys($this->capabilities, self::MAX_CAPABILITIES, 'capabilities');
        $this->assertUserIds($this->users);
    }

    /**
     * @param list<string> $values
     */
    private function assertMachineKeys(array $values, int $maximum, string $label): void
    {
        if (!array_is_list($values) || count($values) > $maximum) {
            throw new InvalidArgumentException(sprintf('Dashboard Widget visibility %s must be a bounded ordered list.', $label));
        }

        $seen = [];
        foreach ($values as $value) {
            if (!is_string($value) || preg_match('/^[a-z0-9][a-z0-9_-]{0,63}$/', $value) !== 1) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget visibility %s contains a malformed key.', $label));
            }
            if (isset($seen[$value])) {
                throw new InvalidArgumentException(sprintf('Dashboard Widget visibility %s must contain unique keys.', $label));
            }
            $seen[$value] = true;
        }
    }

    /**
     * @param list<int> $values
     */
    private function assertUserIds(array $values): void
    {
        if (!array_is_list($values) || count($values) > self::MAX_USERS) {
            throw new InvalidArgumentException('Dashboard Widget visibility users must be a bounded ordered list.');
        }

        $seen = [];
        foreach ($values as $value) {
            if (!is_int($value) || $value < 1) {
                throw new InvalidArgumentException('Dashboard Widget visibility users must contain positive WordPress user ids.');
            }
            if (isset($seen[$value])) {
                throw new InvalidArgumentException('Dashboard Widget visibility users must contain unique ids.');
            }
            $seen[$value] = true;
        }
    }
}
