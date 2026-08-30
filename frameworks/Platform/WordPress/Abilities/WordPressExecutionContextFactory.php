<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Abilities;


if (!defined('ABSPATH')) {
    exit;
}

use WPEssential\Platform\Auth\ExecutionChannel;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\Principal;

final class WordPressExecutionContextFactory
{
    public function __construct(private readonly WordPressAbilityEnvironmentInterface $environment) {}

    public function current(): ExecutionContext
    {
        $channel = ExecutionChannel::Internal;
        if ($this->environment->isRestRequest()) {
            $channel = ExecutionChannel::Rest;
        } elseif ($this->environment->isCli()) {
            $channel = ExecutionChannel::Cli;
        }

        return new ExecutionContext(
            principal: new Principal($this->environment->currentUserId()),
            siteId: $this->environment->currentSiteId(),
            channel: $channel,
            networkId: $this->environment->currentNetworkId(),
        );
    }
}
