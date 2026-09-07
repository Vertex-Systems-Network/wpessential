<?php

declare(strict_types=1);

namespace WPEssential\Modules\AdminColumns;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Definitions\Definition;

final readonly class AdminColumnsPersonalPreferenceAbilityHandler implements AbilityHandlerInterface
{
    public const LOAD = 'load';
    public const SAVE = 'save';
    public const RESET = 'reset';

    public const ABILITY_LOAD = 'wpessential/admin-columns/personal-preference/load';
    public const ABILITY_SAVE = 'wpessential/admin-columns/personal-preference/save';
    public const ABILITY_RESET = 'wpessential/admin-columns/personal-preference/reset';

    public const AJAX_LOAD = 'admin-columns.personal.preference.load';
    public const AJAX_SAVE = 'admin-columns.personal.preference.save';
    public const AJAX_RESET = 'admin-columns.personal.preference.reset';

    private const ACTIONS = [self::LOAD, self::SAVE, self::RESET];

    public function __construct(
        private AdminColumnsViewDefinitionService $views,
        private AdminColumnsPersonalPreferenceStore $preferences,
        private string $action,
    ) {
        if (!in_array($this->action, self::ACTIONS, true)) {
            throw new InvalidArgumentException('Personal preference handler action is unsupported.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $this->assertKnownKeys($input);
        $userId = $this->authenticatedUserId($context);

        $viewId = $input['view_id'] ?? null;
        if (!is_string($viewId)) {
            throw new InvalidArgumentException('Personal preference view_id must be a UUID string.');
        }
        $expectedRevision = $input['expected_view_revision'] ?? null;
        if (!is_int($expectedRevision) || $expectedRevision < 1) {
            throw new InvalidArgumentException('Personal preference expected_view_revision must be positive.');
        }

        $view = $this->views->get($viewId);
        $this->assertRevision($view, $expectedRevision);
        $columns = $this->columns($view);

        if ($this->action === self::LOAD) {
            return $this->preferences->load($userId, $view->id, $view->revision, $columns);
        }

        if ($this->action === self::RESET) {
            $this->preferences->reset($userId, $view->id);
            return $this->preferences->load($userId, $view->id, $view->revision, $columns);
        }

        $preference = $input['preference'] ?? null;
        if (!is_array($preference) || array_is_list($preference)) {
            throw new InvalidArgumentException('Personal preference save requires an object/map preference payload.');
        }

        return $this->preferences->save($userId, $view->id, $view->revision, $columns, $preference);
    }

    private function authenticatedUserId(ExecutionContext $context): int
    {
        if ($context->principal->actorType !== 'user' || !$context->principal->isAuthenticated()) {
            throw new RuntimeException('Personal preference access requires an authenticated user principal.');
        }

        $userId = $context->principal->userId;
        if (!is_int($userId) || $userId < 1) {
            throw new RuntimeException('Personal preference principal user id is unavailable.');
        }
        return $userId;
    }

    private function assertRevision(Definition $view, int $expectedRevision): void
    {
        if ($view->revision !== $expectedRevision) {
            throw new RuntimeException(sprintf(
                'Admin Columns personal preference View revision changed: expected %d, current revision is %d.',
                $expectedRevision,
                $view->revision,
            ));
        }
    }

    /** @return list<array{key:string,enabled?:bool}> */
    private function columns(Definition $view): array
    {
        $columns = $view->payload['columns'] ?? null;
        if (!is_array($columns) || !array_is_list($columns) || $columns === []) {
            throw new RuntimeException('Admin Columns personal preference View columns are unavailable.');
        }

        $result = [];
        foreach ($columns as $index => $column) {
            if (!is_array($column) || array_is_list($column)) {
                throw new RuntimeException(sprintf('Admin Columns personal preference Column %d is malformed.', $index));
            }
            $key = $column['key'] ?? null;
            $enabled = $column['enabled'] ?? true;
            if (!is_string($key) || !is_bool($enabled)) {
                throw new RuntimeException(sprintf('Admin Columns personal preference Column %d is invalid.', $index));
            }
            $result[] = ['key' => $key, 'enabled' => $enabled];
        }

        return $result;
    }

    /** @param array<string,mixed> $input */
    private function assertKnownKeys(array $input): void
    {
        if (array_is_list($input)) {
            throw new InvalidArgumentException('Personal preference input must be an object/map.');
        }

        $allowed = ['view_id', 'expected_view_revision'];
        if ($this->action === self::SAVE) {
            $allowed[] = 'preference';
        }

        foreach (array_keys($input) as $key) {
            if (!in_array($key, $allowed, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Personal preference %s input contains unsupported key "%s".',
                    $this->action,
                    (string) $key,
                ));
            }
        }
    }
}
