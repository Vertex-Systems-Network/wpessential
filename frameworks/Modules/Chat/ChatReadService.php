<?php

declare(strict_types=1);

namespace WPEssential\Modules\Chat;

if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Contracts\DefinitionRepositoryInterface;
use WPEssential\Platform\Definitions\Definition;

final readonly class ChatReadService
{
    public function __construct(private DefinitionRepositoryInterface $definitions) {}

    /** @return array<string,mixed>|null */
    public function get(string $id): ?array
    {
        $definition = $this->definitions->get($id);
        return $definition instanceof Definition ? ConversationDefinition::view($definition) : null;
    }

    /** @return list<array<string,mixed>> */
    public function catalog(): array
    {
        $definitions = $this->definitions->byType(ConversationDefinition::TYPE);
        usort($definitions, static fn (Definition $left, Definition $right): int => ($left->slug <=> $right->slug) ?: ($left->id <=> $right->id));
        $catalog = [];
        foreach ($definitions as $definition) {
            $catalog[] = ConversationDefinition::view($definition);
        }
        return $catalog;
    }
}
