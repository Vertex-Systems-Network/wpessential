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

    /** @var Closure(string,string,string):bool */
    private Closure $unregisterMetaKey;

    private PostMetaRegistrationOwnershipGuard $ownership;

    /**
     * @param null|callable(string,string,array<string,mixed>):bool $registerPostMeta
     * @param null|callable(string,string):bool $postTypeSupports
     * @param null|callable(string,string):array<string,array<string,mixed>> $getRegisteredMetaKeys
     * @param null|callable(string,string,string):bool $unregisterMetaKey
     */
    public function __construct(
        ?callable $registerPostMeta = null,
        ?callable $postTypeSupports = null,
        ?callable $getRegisteredMetaKeys = null,
        ?PostMetaRegistrationOwnershipGuard $ownership = null,
        ?callable $unregisterMetaKey = null,
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

        $this->unregisterMetaKey = $unregisterMetaKey !== null
            ? Closure::fromCallable($unregisterMetaKey)
            : static function (string $objectType, string $metaKey, string $objectSubtype): bool {
                if (!function_exists('unregister_meta_key')) {
                    throw new LogicException('WordPress unregister_meta_key() is unavailable.');
                }
                return unregister_meta_key($objectType, $metaKey, $objectSubtype);
            };

        $this->ownership = $ownership ?? new PostMetaRegistrationOwnershipGuard();
    }

    /**
     * Validate one registration contract and its current WordPress ownership state without mutating registration state.
     *
     * @param array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array<string,mixed>
     * } $registration
     */
    public function preflight(array $registration): bool
    {
        [$postType, $metaKey, $args] = $this->contract($registration);

        $this->assertRequiredSupports($postType, $metaKey, $args);
        [$globalExisting, $subtypeExisting] = $this->ownershipState($postType, $metaKey);

        return $this->ownership->shouldRegister($registration, $globalExisting, $subtypeExisting);
    }

    /**
     * Register a complete plan after one fail-closed preflight phase.
     *
     * Registered-meta maps are snapshots for this synchronous batch: global post scope is read once,
     * each targeted subtype is read once, and required post-type feature checks are cached per pair.
     * Duplicate subtype/meta-key tuples are rejected before snapshots or registration mutation so no
     * intra-batch collision can be hidden by the shared preflight state.
     *
     * @param list<array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array<string,mixed>
     * }> $registrations
     */
    public function registerBatch(array $registrations): void
    {
        if (!array_is_list($registrations)) {
            throw new InvalidArgumentException('Post-meta registration batch must be a list.');
        }
        if ($registrations === []) {
            return;
        }

        $prepared = [];
        $seen = [];
        foreach ($registrations as $registration) {
            if (!is_array($registration) || array_is_list($registration)) {
                throw new InvalidArgumentException('Post-meta registration batch entries must be object/maps.');
            }

            [$postType, $metaKey, $args] = $this->contract($registration);
            $identity = $postType . "\0" . $metaKey;
            if (isset($seen[$identity])) {
                throw new InvalidArgumentException(sprintf(
                    'Post-meta registration batch contains duplicate key "%s" for post type "%s".',
                    $metaKey,
                    $postType,
                ));
            }
            $seen[$identity] = true;
            $prepared[] = [
                'registration' => $registration,
                'post_type' => $postType,
                'meta_key' => $metaKey,
                'args' => $args,
            ];
        }

        $globalKeys = ($this->getRegisteredMetaKeys)('post', '');
        $subtypeKeys = [];
        $supportCache = [];
        $plan = [];

        foreach ($prepared as $entry) {
            $postType = $entry['post_type'];
            $metaKey = $entry['meta_key'];
            $args = $entry['args'];
            $registration = $entry['registration'];

            $this->assertRequiredSupports($postType, $metaKey, $args, $supportCache);

            if (!array_key_exists($postType, $subtypeKeys)) {
                $subtypeKeys[$postType] = ($this->getRegisteredMetaKeys)('post', $postType);
            }

            $globalExisting = $this->existingRegistration($globalKeys, $metaKey, 'global post scope');
            $subtypeExisting = $this->existingRegistration(
                $subtypeKeys[$postType],
                $metaKey,
                sprintf('post type "%s"', $postType),
            );
            $plan[] = [
                'post_type' => $postType,
                'meta_key' => $metaKey,
                'args' => $args,
                'should_register' => $this->ownership->shouldRegister(
                    $registration,
                    $globalExisting,
                    $subtypeExisting,
                ),
            ];
        }

        foreach ($plan as $entry) {
            if ($entry['should_register'] !== true) {
                continue;
            }
            if (!($this->registerPostMeta)($entry['post_type'], $entry['meta_key'], $entry['args'])) {
                throw new RuntimeException(sprintf(
                    'WordPress rejected post-meta registration for "%s" on post type "%s".',
                    $entry['meta_key'],
                    $entry['post_type'],
                ));
            }
        }
    }

    /**
     * @param array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array<string,mixed>
     * } $registration
     */
    public function assertOwned(array $registration): void
    {
        [$postType, $metaKey] = $this->contract($registration);
        [$globalExisting, $subtypeExisting] = $this->ownershipState($postType, $metaKey);

        if ($subtypeExisting === null) {
            if ($globalExisting !== null) {
                $this->ownership->shouldRegister($registration, $globalExisting, null);
            }

            throw new RuntimeException(sprintf(
                'Post-meta key "%s" on post type "%s" is not currently registered by WPEssential; refusing destructive retirement.',
                $metaKey,
                $postType,
            ));
        }

        if ($this->ownership->shouldRegister($registration, $globalExisting, $subtypeExisting) !== false) {
            throw new RuntimeException(sprintf(
                'Post-meta key "%s" on post type "%s" ownership could not be proven.',
                $metaKey,
                $postType,
            ));
        }
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
        $this->registerBatch([$registration]);
    }

    /**
     * Retire only a subtype registration whose immutable Field owner and structural shape are still provably WPE-owned.
     *
     * @param array{
     *     post_type:string,
     *     field_uuid:string,
     *     meta_key:string,
     *     args:array<string,mixed>
     * } $registration
     */
    public function retire(array $registration): void
    {
        $this->assertOwned($registration);
        [$postType, $metaKey] = $this->contract($registration);
        $nativeResult = ($this->unregisterMetaKey)('post', $metaKey, $postType);
        [$globalExisting, $subtypeExisting] = $this->ownershipState($postType, $metaKey);
        if ($subtypeExisting !== null || $globalExisting !== null) {
            throw new RuntimeException(sprintf(
                'WordPress %s unregistering post-meta key "%s" on post type "%s", but registration still exists.',
                $nativeResult ? 'reported success' : 'reported failure',
                $metaKey,
                $postType,
            ));
        }
    }

    /**
     * @param array<string,mixed> $registration
     * @return array{0:string,1:string,2:array<string,mixed>}
     */
    private function contract(array $registration): array
    {
        $postType = $registration['post_type'] ?? null;
        $metaKey = $registration['meta_key'] ?? null;
        $args = $registration['args'] ?? null;
        if (!is_string($postType) || $postType === '' || !is_string($metaKey) || $metaKey === '' || !is_array($args)) {
            throw new InvalidArgumentException('Post-meta registration contract is malformed.');
        }

        return [$postType, $metaKey, $args];
    }

    /**
     * @param array<string,bool>|null $supportCache
     */
    private function assertRequiredSupports(
        string $postType,
        string $metaKey,
        array $args,
        ?array &$supportCache = null,
    ): void {
        if (($args['revisions_enabled'] ?? false) === true
            && !$this->supports($postType, 'revisions', $supportCache)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Post type "%s" does not support revisions required by meta key "%s".',
                $postType,
                $metaKey,
            ));
        }

        if (($args['show_in_rest'] ?? false) !== false
            && !$this->supports($postType, 'custom-fields', $supportCache)
        ) {
            throw new InvalidArgumentException(sprintf(
                'Post type "%s" must support custom-fields before meta key "%s" can be exposed in REST.',
                $postType,
                $metaKey,
            ));
        }
    }

    /** @param array<string,bool>|null $cache */
    private function supports(string $postType, string $feature, ?array &$cache): bool
    {
        if ($cache === null) {
            return ($this->postTypeSupports)($postType, $feature);
        }

        $key = $postType . "\0" . $feature;
        if (!array_key_exists($key, $cache)) {
            $cache[$key] = ($this->postTypeSupports)($postType, $feature);
        }
        return $cache[$key];
    }

    /**
     * @return array{0:null|array<string,mixed>,1:null|array<string,mixed>}
     */
    private function ownershipState(string $postType, string $metaKey): array
    {
        $globalKeys = ($this->getRegisteredMetaKeys)('post', '');
        $subtypeKeys = ($this->getRegisteredMetaKeys)('post', $postType);

        return [
            $this->existingRegistration($globalKeys, $metaKey, 'global post scope'),
            $this->existingRegistration($subtypeKeys, $metaKey, sprintf('post type "%s"', $postType)),
        ];
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
