<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;

/**
 * Fail-closed ownership policy for native post-meta registration.
 *
 * WordPress allows a later registration to replace an existing registry slot. This guard keeps
 * WPEssential from silently taking over a global or subtype-specific key owned by another field.
 */
final readonly class PostMetaRegistrationOwnershipGuard
{
    /**
     * @param array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array<string,mixed>
     * } $registration
     * @param null|array<string,mixed> $globalExisting
     * @param null|array<string,mixed> $subtypeExisting
     */
    public function shouldRegister(
        array $registration,
        ?array $globalExisting,
        ?array $subtypeExisting,
    ): bool {
        $postType = $registration['post_type'] ?? null;
        $fieldUuid = $registration['field_uuid'] ?? null;
        $metaKey = $registration['meta_key'] ?? null;
        $args = $registration['args'] ?? null;

        if (
            !is_string($postType) || $postType === ''
            || !is_string($fieldUuid) || $fieldUuid === ''
            || !is_string($metaKey) || $metaKey === ''
            || !is_array($args) || array_is_list($args)
        ) {
            throw new InvalidArgumentException('Post-meta ownership guard received a malformed registration contract.');
        }

        $expectedDescription = sprintf('WPEssential Field value (%s).', $fieldUuid);
        if (($args['description'] ?? null) !== $expectedDescription) {
            throw new InvalidArgumentException(sprintf(
                'Post-meta registration for "%s" is missing the canonical WPEssential Field ownership fingerprint.',
                $metaKey,
            ));
        }

        if ($globalExisting !== null) {
            $this->assertNamedExistingRegistration($globalExisting, $metaKey, 'global post scope');

            throw new RuntimeException(sprintf(
                'Post-meta key "%s" is already registered at global post scope; subtype "%s" cannot safely claim it.',
                $metaKey,
                $postType,
            ));
        }

        if ($subtypeExisting === null) {
            return true;
        }

        $this->assertNamedExistingRegistration($subtypeExisting, $metaKey, sprintf('post type "%s"', $postType));

        if (($subtypeExisting['description'] ?? null) !== $expectedDescription) {
            throw new RuntimeException(sprintf(
                'Post-meta key "%s" on post type "%s" is already owned by another registration.',
                $metaKey,
                $postType,
            ));
        }

        $expectedShape = $this->structuralShape($args, $metaKey, 'candidate');
        $existingShape = $this->structuralShape($subtypeExisting, $metaKey, 'existing');
        if ($expectedShape !== $existingShape) {
            throw new RuntimeException(sprintf(
                'Post-meta key "%s" on post type "%s" has an incompatible existing WPEssential registration shape.',
                $metaKey,
                $postType,
            ));
        }

        foreach (['sanitize_callback', 'auth_callback'] as $callbackKey) {
            if (!is_callable($args[$callbackKey] ?? null) || !is_callable($subtypeExisting[$callbackKey] ?? null)) {
                throw new RuntimeException(sprintf(
                    'Post-meta key "%s" on post type "%s" has an incomplete existing WPEssential callback contract.',
                    $metaKey,
                    $postType,
                ));
            }
        }

        // Same immutable Field owner and same structural contract: registration is already satisfied.
        return false;
    }

    /** @param array<string,mixed> $existing */
    private function assertNamedExistingRegistration(array $existing, string $metaKey, string $scope): void
    {
        if ($existing === [] || array_is_list($existing)) {
            throw new RuntimeException(sprintf(
                'Existing registration for post-meta key "%s" at %s is malformed; refusing to overwrite it.',
                $metaKey,
                $scope,
            ));
        }
    }

    /**
     * @param array<string,mixed> $args
     * @return array<string,mixed>
     */
    private function structuralShape(array $args, string $metaKey, string $source): array
    {
        $type = $args['type'] ?? null;
        $single = $args['single'] ?? null;
        $showInRest = $args['show_in_rest'] ?? null;
        $revisionsEnabled = $args['revisions_enabled'] ?? null;

        if (
            !is_string($type) || $type === ''
            || !is_bool($single)
            || (!is_bool($showInRest) && !is_array($showInRest))
            || !is_bool($revisionsEnabled)
        ) {
            throw new RuntimeException(sprintf(
                'The %s registration shape for post-meta key "%s" is incomplete; refusing ownership resolution.',
                $source,
                $metaKey,
            ));
        }

        $shape = [
            'type' => $type,
            'single' => $single,
            'show_in_rest' => $this->canonicalize($showInRest),
            'revisions_enabled' => $revisionsEnabled,
        ];

        if (array_key_exists('default', $args)) {
            $shape['default'] = $this->canonicalize($args['default']);
        }

        return $shape;
    }

    private function canonicalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }

        ksort($value);
        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }
}
