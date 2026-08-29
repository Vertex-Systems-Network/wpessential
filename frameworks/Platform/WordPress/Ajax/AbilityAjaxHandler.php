<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\WordPress\Abilities\WordPressExecutionContextFactory;

final readonly class AbilityAjaxHandler implements AjaxHandlerInterface
{
    public function __construct(
        private AbilityRegistry $abilities,
        private string $abilityName,
        private WordPressExecutionContextFactory $contexts,
    ) {
        if (!preg_match('#^wpessential/[a-z0-9][a-z0-9-]*/[a-z0-9][a-z0-9-]*$#', $this->abilityName)) {
            throw new InvalidArgumentException('AJAX ability name must use wpessential/<domain>/<action>.');
        }
    }

    public function handle(array $payload): mixed
    {
        $current = $this->contexts->current();
        $context = new ExecutionContext(
            principal: $current->principal,
            siteId: $current->siteId,
            channel: ExecutionChannel::Ui,
            networkId: $current->networkId,
            correlationId: $current->correlationId,
        );

        $decision = $this->abilities->authorize($this->abilityName, $context);
        if (!$decision->allowed) {
            throw new AjaxAuthorizationException($decision->reason);
        }

        return $this->abilities->execute($this->abilityName, $payload, $context);
    }
}
