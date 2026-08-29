<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Security;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

final class NonceManager
{
    public function __construct(
        private readonly NonceEnvironmentInterface $environment,
        private readonly string $baseAction,
    ) {
        if (trim($this->baseAction) === '') {
            throw new InvalidArgumentException('Nonce base action cannot be empty.');
        }
    }

    public function create(NonceOperation $operation, string $scope): string
    {
        return $this->environment->create($this->action($operation, $scope));
    }

    public function verify(string $nonce, NonceOperation $operation, string $scope): bool
    {
        return $nonce !== '' && $this->environment->verify($nonce, $this->action($operation, $scope));
    }

    public function action(NonceOperation $operation, string $scope): string
    {
        $scope = trim($scope);
        if ($scope === '' || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $scope) !== 1) {
            throw new InvalidArgumentException('Nonce scope is invalid.');
        }

        return sprintf('%s:%s:%s', $this->baseAction, $operation->value, $scope);
    }
}
