<?php
/**
 * Bound and validate dashboard action confirmation labels.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;

/** Immutable action confirmation descriptor with strict input bounds. */
final readonly class DashboardWidgetActionConfirmationDescriptor
{
    public const MAX_TITLE_BYTES = 120;
    public const MAX_MESSAGE_BYTES = 500;
    public const MAX_LABEL_BYTES = 80;
    public const MAX_ENCODED_BYTES = 1024;

    public string $title;
    public string $message;
    public string $confirmLabel;
    public string $cancelLabel;

	/**
	 * Validate confirmation title, message and action labels.
	 *
	 * @param string $title Confirmation title.
	 * @param string $message Confirmation message.
	 * @param string $confirmLabel Positive action label.
	 * @param string $cancelLabel Negative action label.
	 * @throws InvalidArgumentException If input or encoded metadata exceeds bounds.
	 */
    public function __construct(
        string $title,
        string $message,
        string $confirmLabel,
        string $cancelLabel,
    ) {
        $this->title = self::normalizeText($title, 'title', self::MAX_TITLE_BYTES);
        $this->message = self::normalizeText($message, 'message', self::MAX_MESSAGE_BYTES);
        $this->confirmLabel = self::normalizeText($confirmLabel, 'confirm_label', self::MAX_LABEL_BYTES);
        $this->cancelLabel = self::normalizeText($cancelLabel, 'cancel_label', self::MAX_LABEL_BYTES);

        $encoded = json_encode([
            'title' => $this->title,
            'message' => $this->message,
            'confirm_label' => $this->confirmLabel,
            'cancel_label' => $this->cancelLabel,
        ], JSON_UNESCAPED_UNICODE);

        if ($encoded === false || strlen($encoded) > self::MAX_ENCODED_BYTES) {
            throw new InvalidArgumentException('Dashboard Widget action confirmation metadata exceeds the bounded encoded size.');
        }
    }

	/**
	 * Normalize a bounded plain-text action label.
	 *
	 * @param string $value Original label text.
	 * @param string $field Label field key.
	 * @param int    $maxBytes Maximum bytes permitted.
	 * @return string
	 * @throws InvalidArgumentException If label text is invalid.
	 */
    private static function normalizeText(string $value, string $field, int $maxBytes): string
    {
        $value = trim($value);

        if (
            $value === ''
            || strlen($value) > $maxBytes
            || preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value) === 1
        ) {
            throw new InvalidArgumentException(
                'Dashboard Widget action confirmation ' . $field . ' must be bounded plain text.',
            );
        }

        return $value;
    }
}
