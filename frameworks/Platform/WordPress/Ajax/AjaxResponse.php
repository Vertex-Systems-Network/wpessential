<?php

declare(strict_types=1);

namespace WPEssential\Platform\WordPress\Ajax;


if (!defined('ABSPATH')) {
    exit;
}

final readonly class AjaxResponse
{
    /** @param array<string,mixed> $meta */
    private function __construct(
        public bool $success,
        public mixed $data,
        public ?string $errorCode,
        public string $message,
        public int $status,
        public array $meta = [],
    ) {}

    public static function success(mixed $data = null, int $status = 200): self
    {
        return new self(true, $data, null, '', $status);
    }

    /** @param array<string,mixed> $meta */
    public static function error(string $code, string $message, int $status = 400, array $meta = []): self
    {
        return new self(false, null, $code, $message, $status, $meta);
    }

    /** @return array<string,mixed> */
    public function payload(): array
    {
        return $this->success
            ? ['success' => true, 'data' => $this->data]
            : ['success' => false, 'error' => ['code' => $this->errorCode, 'message' => $this->message], 'meta' => $this->meta];
    }
}
