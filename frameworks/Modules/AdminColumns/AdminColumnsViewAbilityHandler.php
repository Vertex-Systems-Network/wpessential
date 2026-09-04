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
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class AdminColumnsViewAbilityHandler implements AbilityHandlerInterface
{
    public const LIST = 'list';
    public const GET = 'get';
    public const SAVE = 'save';
    public const STATUS = 'status';

    public function __construct(
        private AdminColumnsViewDefinitionService $views,
        private string $action,
    ) {
        if (!in_array($this->action, [self::LIST, self::GET, self::SAVE, self::STATUS], true)) {
            throw new InvalidArgumentException('Unsupported Admin Columns View ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        return match ($this->action) {
            self::LIST => $this->list(),
            self::GET => $this->get($input),
            self::SAVE => $this->save($input),
            self::STATUS => $this->changeStatus($input),
            default => throw new RuntimeException('Unsupported Admin Columns View ability action.'),
        };
    }

    /** @return array{definitions:list<array<string,mixed>>} */
    private function list(): array
    {
        return [
            'definitions' => array_map($this->serialize(...), $this->views->all()),
        ];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function get(array $input): array
    {
        return [
            'definition' => $this->serialize($this->views->get($this->requiredUuid($input, 'id'))),
        ];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function save(array $input): array
    {
        $payload = $input['payload'] ?? null;
        if (!is_array($payload) || array_is_list($payload)) {
            throw new InvalidArgumentException('Admin Columns View payload must be an object/map.');
        }

        $id = null;
        if (array_key_exists('id', $input) && $input['id'] !== null) {
            $id = $this->requiredUuid($input, 'id');
        }

        $expectedRevision = $this->expectedRevision($input, $id !== null);
        $status = $this->status($input['status'] ?? DefinitionStatus::Draft->value);
        $definition = $this->views->save($payload, $status, $id, $expectedRevision);

        return ['definition' => $this->serialize($definition)];
    }

    /** @param array<string,mixed> $input @return array{definition:array<string,mixed>} */
    private function changeStatus(array $input): array
    {
        $id = $this->requiredUuid($input, 'id');
        $expectedRevision = $this->expectedRevision($input, true);
        if (!is_int($expectedRevision)) {
            throw new RuntimeException('Admin Columns View expected revision is missing.');
        }
        $status = $this->status($input['status'] ?? null);
        return [
            'definition' => $this->serialize($this->views->changeStatus($id, $status, $expectedRevision)),
        ];
    }

    /** @param array<string,mixed> $input */
    private function expectedRevision(array $input, bool $required): ?int
    {
        $value = $input['expected_revision'] ?? null;
        if ($value === null && !$required) {
            return null;
        }
        if (!is_int($value) || $value < 1) {
            throw new InvalidArgumentException('Admin Columns View expected_revision must be a positive integer.');
        }
        return $value;
    }

    private function status(mixed $value): DefinitionStatus
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Admin Columns View status must be a string.');
        }
        $status = DefinitionStatus::tryFrom($value);
        if (!$status instanceof DefinitionStatus) {
            throw new InvalidArgumentException('Admin Columns View status must be draft, published, disabled, or archived.');
        }
        return $status;
    }

    /** @param array<string,mixed> $input */
    private function requiredUuid(array $input, string $field): string
    {
        $value = $input[$field] ?? null;
        if (!is_string($value)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1
        ) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase RFC 4122 UUID.', $field));
        }
        return $value;
    }

    /** @return array<string,mixed> */
    private function serialize(Definition $definition): array
    {
        return [
            'id' => $definition->id,
            'slug' => $definition->slug,
            'type' => $definition->type,
            'schema_version' => $definition->schemaVersion,
            'owner_surface_id' => $definition->ownerSurfaceId,
            'status' => $definition->status->value,
            'payload' => $definition->payload,
            'revision' => $definition->revision,
            'dependencies' => $definition->dependencies,
            'checksum' => $definition->checksum,
        ];
    }
}
