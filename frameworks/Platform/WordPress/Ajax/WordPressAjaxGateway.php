<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class WordPressAjaxGateway
{
    public function __construct(
        private readonly string $action,
        private readonly AjaxDispatcher $dispatcher,
        private readonly WordPressAjaxEnvironmentInterface $environment,
    ) {
        if (trim($this->action) === '') {
            throw new InvalidArgumentException('AJAX action cannot be empty.');
        }
    }

    public function register(): void
    {
        $this->environment->registerAction('wp_ajax_' . $this->action, [$this, 'handle']);
        $this->environment->registerAction('wp_ajax_nopriv_' . $this->action, [$this, 'handle']);
    }

    public function handle(): void
    {
        $response = $this->dispatcher->dispatch($this->environment->request(), $this->environment->isAuthenticated());
        $this->environment->respond($response);
    }
}
