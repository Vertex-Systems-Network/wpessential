<?php

declare(strict_types=1);

namespace WPEssential\Modules\Status\Registration;

if (!defined('ABSPATH')) {
    exit;
}

use Closure;
use LogicException;
use Throwable;
use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Modules\Status\Definition\StatusDefinitionCompiler;
use WPEssential\Modules\Status\Definition\StatusRegistrationDescriptor;
use WPEssential\Platform\Definitions\DefinitionStatus;

final class StatusNativeRegistrar
{
    private bool $processed = false;

    /** @var list<string> */
    private array $registered = [];

    /** @var list<string> */
    private array $conflicts = [];

    /** @var array<string,string> */
    private array $errors = [];

    /** @var Closure(string):bool */
    private Closure $statusExists;

    /** @var Closure(string,array<string,mixed>):mixed */
    private Closure $registerStatus;

    /** @var Closure(string,string):mixed */
    private Closure $labelCountFactory;

    /** @var Closure(string,callable,int):void */
    private Closure $addAction;

    /**
     * @param null|callable(string):bool $statusExists
     * @param null|callable(string,array<string,mixed>):mixed $registerStatus
     * @param null|callable(string,string):mixed $labelCountFactory
     * @param null|callable(string,callable,int):void $addAction
     */
    public function __construct(
        private readonly DefinitionRepositoryInterface $definitions,
        private readonly StatusDefinitionCompiler $compiler = new StatusDefinitionCompiler(),
        ?callable $statusExists = null,
        ?callable $registerStatus = null,
        ?callable $labelCountFactory = null,
        ?callable $addAction = null,
    ) {
        $this->statusExists = $statusExists !== null
            ? Closure::fromCallable($statusExists)
            : static function (string $key): bool {
                if (!function_exists('get_post_status_object')) {
                    throw new LogicException('WordPress status lookup API is unavailable.');
                }

                return get_post_status_object($key) !== null;
            };

        $this->registerStatus = $registerStatus !== null
            ? Closure::fromCallable($registerStatus)
            : static function (string $key, array $arguments): mixed {
                if (!function_exists('register_post_status')) {
                    throw new LogicException('WordPress status registration API is unavailable.');
                }

                return register_post_status($key, $arguments);
            };

        $this->labelCountFactory = $labelCountFactory !== null
            ? Closure::fromCallable($labelCountFactory)
            : static function (string $singular, string $plural): mixed {
                if (!function_exists('_n_noop')) {
                    throw new LogicException('WordPress translation API is unavailable.');
                }

                return _n_noop($singular, $plural);
            };

        $this->addAction = $addAction !== null
            ? Closure::fromCallable($addAction)
            : static function (string $hook, callable $callback, int $priority): void {
                if (!function_exists('add_action')) {
                    throw new LogicException('WordPress hook API is unavailable.');
                }

                add_action($hook, $callback, $priority);
            };
    }

    public function register(): void
    {
        try {
            ($this->addAction)('init', [$this, 'registerActive'], 20);
        } catch (Throwable) {
            $this->errors['runtime'] = 'WordPress hook API is unavailable.';
        }
    }

    public function registerActive(): void
    {
        if ($this->processed) {
            return;
        }
        $this->processed = true;

        $descriptors = [];
        foreach ($this->definitions->byType(StatusDefinitionCompiler::TYPE) as $definition) {
            if ($definition->status !== DefinitionStatus::Published) {
                continue;
            }

            try {
                $descriptor = $this->compiler->compile($definition);
            } catch (Throwable) {
                $this->errors[$definition->id] = 'Published Status definition failed compilation.';
                return;
            }

            if (isset($descriptors[$descriptor->key])) {
                $this->errors[$descriptor->key] = 'Duplicate Published Status registration key.';
                return;
            }
            $descriptors[$descriptor->key] = $descriptor;
        }
        ksort($descriptors, SORT_STRING);

        $plans = [];
        try {
            foreach ($descriptors as $key => $descriptor) {
                if (($this->statusExists)($key)) {
                    $this->conflicts[] = $key;
                    continue;
                }
                $plans[$key] = $this->arguments($descriptor);
            }
        } catch (Throwable) {
            $this->errors['runtime'] = 'WordPress Status runtime APIs are unavailable.';
            return;
        }

        if ($this->conflicts !== []) {
            sort($this->conflicts, SORT_STRING);
            return;
        }

        foreach ($plans as $key => $arguments) {
            try {
                $result = ($this->registerStatus)($key, $arguments);
            } catch (Throwable) {
                $this->errors[$key] = 'WordPress Status registration failed.';
                return;
            }
            if (!is_object($result)) {
                $this->errors[$key] = 'WordPress Status registration returned an invalid result.';
                return;
            }
            $this->registered[] = $key;
        }
    }

    /** @return list<string> */
    public function registered(): array
    {
        return $this->registered;
    }

    /** @return list<string> */
    public function conflicts(): array
    {
        return $this->conflicts;
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** @return array<string,mixed> */
    private function arguments(StatusRegistrationDescriptor $descriptor): array
    {
        return [
            'label' => $descriptor->label,
            'label_count' => ($this->labelCountFactory)($descriptor->countSingular, $descriptor->countPlural),
            'public' => $descriptor->public,
            'internal' => $descriptor->internal,
            'protected' => $descriptor->protected,
            'private' => $descriptor->private,
            'publicly_queryable' => $descriptor->publiclyQueryable,
            'exclude_from_search' => $descriptor->excludeFromSearch,
            'show_in_admin_all_list' => $descriptor->showInAdminAllList,
            'show_in_admin_status_list' => $descriptor->showInAdminStatusList,
            'date_floating' => $descriptor->dateFloating,
        ];
    }
}
