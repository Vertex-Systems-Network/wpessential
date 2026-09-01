<?php

declare(strict_types=1);

namespace WPEssential\Modules\Fields;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Definitions\Definition;
use WPEssential\Platform\Definitions\DefinitionStatus;

final readonly class FieldGroupPostMetaBinder
{
    public function __construct(
        private FieldGroupDefinitionNormalizer $groups = new FieldGroupDefinitionNormalizer(),
        private FieldGroupRuntimeStorageProjection $storage = new FieldGroupRuntimeStorageProjection(),
        private FieldGroupPostTypeTargetCompiler $targets = new FieldGroupPostTypeTargetCompiler(),
        private PostMetaRegistrationCompiler $compiler = new PostMetaRegistrationCompiler(),
        private WordPressPostMetaRegistrar $registrar = new WordPressPostMetaRegistrar(),
    ) {}

    public function bind(Definition $definition): void
    {
        $this->assertPublishedFieldGroup($definition);

        $group = $this->groups->normalize($definition->payload, true);
        $groupRuntime = $this->storage->projectGroup($group);
        $postTypes = $this->targets->compile($group);

        $registrations = [];
        foreach ($postTypes as $postType) {
            foreach ($group['fields'] as $field) {
                if (!is_array($field)) {
                    throw new InvalidArgumentException('Normalized Field Group fields must contain field maps.');
                }
                if (($field['stores_value'] ?? null) !== true) {
                    continue;
                }

                $fieldRuntime = $this->storage->projectField($field, $groupRuntime['show_in_rest']);
                $registrations[] = $this->compiler->compile(
                    $field,
                    $postType,
                    showInRest: $fieldRuntime['show_in_rest'],
                    revisionsEnabled: $groupRuntime['revisions_enabled'],
                );
            }
        }

        foreach ($registrations as $registration) {
            $this->registrar->preflight($registration);
        }

        foreach ($registrations as $registration) {
            $this->registrar->register($registration);
        }
    }

    private function assertPublishedFieldGroup(Definition $definition): void
    {
        if ($definition->type !== FieldGroupDefinitionNormalizer::DEFINITION_TYPE) {
            throw new InvalidArgumentException('Post-meta binding requires a canonical field_group Definition.');
        }
        if ($definition->ownerSurfaceId !== FieldGroupDefinitionNormalizer::OWNER_SURFACE_ID) {
            throw new InvalidArgumentException('Post-meta binding requires canonical Surface 3 ownership.');
        }
        if ($definition->status !== DefinitionStatus::Published) {
            throw new InvalidArgumentException('Only Published Field Group Definitions may bind runtime post meta.');
        }
    }
}
