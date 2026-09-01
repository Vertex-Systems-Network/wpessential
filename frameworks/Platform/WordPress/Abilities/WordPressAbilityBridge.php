<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use RuntimeException;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;

final class WordPressAbilityBridge
{
    /** @var array<string, WordPressAbilityExposure> */
    private array $exposures = [];

    public function __construct(
        private readonly AbilityRegistry $registry,
        private readonly WordPressAbilityEnvironmentInterface $environment,
        private readonly WordPressExecutionContextFactory $contexts,
        private readonly WordPressAbilityNameMapper $names = new WordPressAbilityNameMapper(),
    ) {
    }

    public function expose(WordPressAbilityExposure $exposure): void
    {
        $descriptor = $this->registry->descriptor($exposure->internalName);
        if (!$descriptor instanceof AbilityDescriptor) {
            throw new RuntimeException(sprintf('Cannot expose unregistered internal ability "%s".', $exposure->internalName));
        }
        if ($exposure->showInRest && !$descriptor->allows(ExecutionChannel::Rest)) {
            throw new RuntimeException('REST exposure requires explicit internal REST channel allowlisting.');
        }
        if ($descriptor->inputSchema !== [] && (($descriptor->inputSchema['type'] ?? null) !== 'object')) {
            throw new RuntimeException('WordPress bridge currently exposes only object-shaped or empty input schemas.');
        }
        if (isset($this->exposures[$exposure->internalName])) {
            throw new RuntimeException(sprintf('Ability exposure "%s" already exists.', $exposure->internalName));
        }

        $this->assertCoreNameAvailable($exposure);
        $this->exposures[$exposure->internalName] = $exposure;
    }

    public function registerCategory(): bool
    {
        if (!$this->environment->abilitiesApiAvailable()) return false;
        if (!$this->environment->doingAction('wp_abilities_api_categories_init')) {
            throw new RuntimeException('WPE ability category must register on wp_abilities_api_categories_init.');
        }

        return $this->environment->registerCategory('wpessential', [
            'label' => 'WPEssential',
            'description' => 'Approved WPEssential platform abilities.',
        ]);
    }

    /** @return list<string> core ability names registered */
    public function registerAbilities(): array
    {
        if (!$this->environment->abilitiesApiAvailable()) return [];
        if (!$this->environment->doingAction('wp_abilities_api_init')) {
            throw new RuntimeException('WPE abilities must register on wp_abilities_api_init.');
        }

        $registered = [];
        foreach ($this->exposures as $exposure) {
            $descriptor = $this->registry->descriptor($exposure->internalName);
            if (!$descriptor instanceof AbilityDescriptor) {
                throw new RuntimeException('Internal ability disappeared before WordPress registration.');
            }

            $coreName = $this->names->map($exposure->internalName);
            $args = [
                'label' => $exposure->label,
                'description' => $exposure->description,
                'category' => 'wpessential',
                'execute_callback' => function (mixed $input = null) use ($exposure): mixed {
                    if ($input !== null && !is_array($input)) {
                        throw new InvalidArgumentException('WPE WordPress ability input must be an object/array.');
                    }
                    return $this->registry->execute(
                        $exposure->internalName,
                        $input ?? [],
                        $this->contexts->current(),
                    );
                },
                'permission_callback' => function (mixed $input = null) use ($exposure): bool {
                    if ($input !== null && !is_array($input)) {
                        return false;
                    }
                    return $this->registry->authorize(
                        $exposure->internalName,
                        $this->contexts->current(),
                        $input ?? [],
                    )->allowed;
                },
                'meta' => [
                    'annotations' => [
                        'readonly' => !$descriptor->mutates,
                    ],
                    'show_in_rest' => $exposure->showInRest,
                ],
            ];
            if ($descriptor->inputSchema !== []) {
                $args['input_schema'] = $descriptor->inputSchema;
            }
            if ($descriptor->outputSchema !== []) {
                $args['output_schema'] = $descriptor->outputSchema;
            }

            if (!$this->environment->registerAbility($coreName, $args)) {
                throw new RuntimeException(sprintf('WordPress rejected ability registration "%s".', $coreName));
            }
            $registered[] = $coreName;
        }

        return $registered;
    }

    private function assertCoreNameAvailable(WordPressAbilityExposure $candidate): void
    {
        $candidateCoreName = $this->names->map($candidate->internalName);
        foreach ($this->exposures as $existing) {
            if ($this->names->map($existing->internalName) === $candidateCoreName) {
                throw new RuntimeException(sprintf(
                    'Internal abilities "%s" and "%s" collide at WordPress core name "%s".',
                    $existing->internalName,
                    $candidate->internalName,
                    $candidateCoreName,
                ));
            }
        }
    }
}
