<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final readonly class WordPressAjaxGateway
{
    public function __construct(
        private string $action,
        private AjaxDispatcher $dispatcher,
        private WordPressAjaxEnvironmentInterface $environment,
    ) {
        if (trim($this->action) === '') {
            throw new InvalidArgumentException('AJAX action cannot be empty.');
        }
    }

    public function action(): string
    {
        return $this->action;
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
