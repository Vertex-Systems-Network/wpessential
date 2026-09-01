<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Platform\Auth\ExecutionContext;

final readonly class FieldValueAbilityHandler implements AbilityHandlerInterface
{
    public const READ = 'read';
    public const WRITE = 'write';

    public function __construct(
        private FieldValueTargetResolver $targets,
        private PostMetaValueStore $values,
        private WordPressPostResourceAuthorizer $authorization,
        private string $action,
    ) {
        if (!in_array($this->action, [self::READ, self::WRITE], true)) {
            throw new InvalidArgumentException('Unsupported Field value Ability action.');
        }
    }

    public function handle(array $input, ExecutionContext $context): mixed
    {
        $groupId = $this->uuid($input, 'group_id');
        $fieldUuid = $this->uuid($input, 'field_uuid');
        $postId = $this->positiveInt($input, 'post_id');

        if ($this->action === self::READ) {
            $this->authorization->assertCanRead($context, $postId);
        } else {
            $this->authorization->assertCanWrite($context, $postId);
        }

        $target = $this->targets->resolve($groupId, $fieldUuid, $postId);

        if ($this->action === self::READ) {
            return $this->response($target, 'read', false, $this->values->read(
                $target->field,
                $target->postType,
                $target->postId,
            ));
        }

        $expectedRevision = $this->positiveInt($input, 'expected_group_revision');
        if ($expectedRevision !== $target->groupRevision) {
            throw new RuntimeException(sprintf(
                'Field value schema revision conflict: expected %d, current revision is %d.',
                $expectedRevision,
                $target->groupRevision,
            ));
        }
        if (!array_key_exists('value', $input)) {
            throw new InvalidArgumentException('Field value write requires a value property; null is allowed for optional deletion.');
        }
        $result = $this->values->write($target->field, $target->postType, $target->postId, $input['value']);

        return $this->response($target, $result->status, $result->changed(), $result->value);
    }

    /** @return array<string,mixed> */
    private function response(ResolvedFieldValueTarget $target, string $status, bool $changed, mixed $value): array
    {
        return [
            'group_id' => $target->groupId,
            'group_revision' => $target->groupRevision,
            'field_uuid' => $target->fieldUuid,
            'field_key' => $target->fieldKey,
            'post_id' => $target->postId,
            'post_type' => $target->postType,
            'status' => $status,
            'changed' => $changed,
            'value' => $value,
        ];
    }

    /** @param array<string,mixed> $input */
    private function uuid(array $input, string $key): string
    {
        $value = $input[$key] ?? null;
        if (!is_string($value)
            || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1
        ) {
            throw new InvalidArgumentException(sprintf('%s must be a lowercase RFC 4122 UUID.', $key));
        }
        return $value;
    }

    /** @param array<string,mixed> $input */
    private function positiveInt(array $input, string $key): int
    {
        $value = $input[$key] ?? null;
        if (!is_int($value) || $value < 1) {
            throw new InvalidArgumentException(sprintf('%s must be a positive integer.', $key));
        }
        return $value;
    }
}
