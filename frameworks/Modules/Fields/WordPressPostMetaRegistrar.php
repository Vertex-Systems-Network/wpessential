<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

final class WordPressPostMetaRegistrar
{
    /** @var Closure(string,string,array<string,mixed>):bool */
    private Closure $registerPostMeta;

    /** @var Closure(string,string):bool */
    private Closure $postTypeSupports;

    /** @var Closure(string,string):array<string,array<string,mixed>> */
    private Closure $getRegisteredMetaKeys;

    private PostMetaRegistrationOwnershipGuard $ownership;

    /**
     * @param null|callable(string,string,array<string,mixed>):bool $registerPostMeta
     * @param null|callable(string,string):bool $postTypeSupports
     * @param null|callable(string,string):array<string,array<string,mixed>> $getRegisteredMetaKeys
     */
    public function __construct(
        ?callable $registerPostMeta = null,
        ?callable $postTypeSupports = null,
        ?callable $getRegisteredMetaKeys = null,
        ?PostMetaRegistrationOwnershipGuard $ownership = null,
    ) {
        $this->registerPostMeta = $registerPostMeta !== null
            ? Closure::fromCallable($registerPostMeta)
            : static function (string $postType, string $metaKey, array $args): bool {
                if (!function_exists('register_post_meta')) {
                    throw new LogicException('WordPress register_post_meta() is unavailable.');
                }
                return register_post_meta($postType, $metaKey, $args);
            };

        $this->postTypeSupports = $postTypeSupports !== null
            ? Closure::fromCallable($postTypeSupports)
            : static function (string $postType, string $feature): bool {
                if (!function_exists('post_type_supports')) {
                    throw new LogicException('WordPress post_type_supports() is unavailable.');
                }
                return post_type_supports($postType, $feature);
            };

        $this->getRegisteredMetaKeys = $getRegisteredMetaKeys !== null
            ? Closure::fromCallable($getRegisteredMetaKeys)
            : static function (string $objectType, string $objectSubtype): array {
                if (!function_exists('get_registered_meta_keys')) {
                    throw new LogicException('WordPress get_registered_meta_keys() is unavailable.');
                }
                return get_registered_meta_keys($objectType, $objectSubtype);
            };

        $this->ownership = $ownership ?? new PostMetaRegistrationOwnershipGuard();
    }

    /**
     * @param array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array<string,mixed>
     * } $registration
     */
    public function register(array $registration): void
    {
        $postType = $registration['post_type'] ?? null;
        $metaKey = $registration['meta_key'] ?? null;
        $args = $registration['args'] ?? null;
        if (!is_string($postType) || $postType === '' || !is_string($metaKey) || $metaKey === '' || !is_array($args)) {
            throw new InvalidArgumentException('Post-meta registration contract is malformed.');
        }

        if (($args['revisions_enabled'] ?? false) === true && !($this->postTypeSupports)($postType, 'revisions')) {
            throw new InvalidArgumentException(sprintf(
                'Post type "%s" does not support revisions required by meta key "%s".',
                $postType,
                $metaKey,
            ));
        }

        if (($args['show_in_rest'] ?? false) !== false && !($this->postTypeSupports)($postType, 'custom-fields')) {
            throw new InvalidArgumentException(sprintf(
                'Post type "%s" must support custom-fields before meta key "%s" can be exposed in REST.',
                $postType,
                $metaKey,
            ));
        }

        $globalKeys = ($this->getRegisteredMetaKeys)('post', '');
        $subtypeKeys = ($this->getRegisteredMetaKeys)('post', $postType);
        $globalExisting = $this->existingRegistration($globalKeys, $metaKey, 'global post scope');
        $subtypeExisting = $this->existingRegistration($subtypeKeys, $metaKey, sprintf('post type "%s"', $postType));

        if (!$this->ownership->shouldRegister($registration, $globalExisting, $subtypeExisting)) {
            return;
        }

        if (!($this->registerPostMeta)($postType, $metaKey, $args)) {
            throw new RuntimeException(sprintf(
                'WordPress rejected post-meta registration for "%s" on post type "%s".',
                $metaKey,
                $postType,
            ));
        }
    }

    /**
     * @param array<string,array<string,mixed>> $registered
     * @return null|array<string,mixed>
     */
    private function existingRegistration(array $registered, string $metaKey, string $scope): ?array
    {
        if (!array_key_exists($metaKey, $registered)) {
            return null;
        }

        $existing = $registered[$metaKey];
        if (!is_array($existing)) {
            throw new RuntimeException(sprintf(
                'Existing registration for post-meta key "%s" at %s is malformed; refusing to overwrite it.',
                $metaKey,
                $scope,
            ));
        }

        return $existing;
    }
}
