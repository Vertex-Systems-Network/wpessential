<?php
/**
 * Dashboard widget visibility eligibility and denial reasons.
 *
 * @package WPEssential\Modules\DashboardWidgets
 */

declare(strict_types=1);

namespace WPEssential\Modules\DashboardWidgets;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;

/** Immutable visibility decision for the Dashboard widget runtime. */
final readonly class DashboardWidgetVisibilityDecision {

	public const REASON_ALLOWED             = 'allowed';
	public const REASON_UNAUTHENTICATED     = 'unauthenticated';
	public const REASON_NON_USER_ACTOR      = 'non_user_actor';
	public const REASON_CONTEXT_MISMATCH    = 'context_mismatch';
	public const REASON_ROLE_MISMATCH       = 'role_mismatch';
	public const REASON_CAPABILITY_MISMATCH = 'capability_mismatch';
	public const REASON_USER_MISMATCH       = 'user_mismatch';
	public const REASON_BEFORE_SCHEDULE     = 'before_schedule';
	public const REASON_EXPIRED             = 'expired';

	/**
	 * Canonical disallowed visibility reasons.
	 *
	 * @var list<string>
	 */
	private const DENY_REASONS = array(
		self::REASON_UNAUTHENTICATED,
		self::REASON_NON_USER_ACTOR,
		self::REASON_CONTEXT_MISMATCH,
		self::REASON_ROLE_MISMATCH,
		self::REASON_CAPABILITY_MISMATCH,
		self::REASON_USER_MISMATCH,
		self::REASON_BEFORE_SCHEDULE,
		self::REASON_EXPIRED,
	);

	/**
	 * Construct a visibility decision.
	 *
	 * @param bool   $allowed Whether the widget is allowed.
	 * @param string $reason Canonical decision reason.
	 */
	private function __construct(
		public bool $allowed,
		public string $reason,
	) {}

	/**
	 * Create an allowed visibility decision.
	 *
	 * @return self
	 */
	public static function allow(): self {
		return new self( true, self::REASON_ALLOWED );
	}

	/**
	 * Create a validated denial decision.
	 *
	 * @param string $reason A supported denial reason.
	 * @return self
	 * @throws InvalidArgumentException When the reason is unknown.
	 */
	public static function deny( string $reason ): self {
		if ( ! in_array( $reason, self::DENY_REASONS, true ) ) {
			throw new InvalidArgumentException( 'Dashboard Widget visibility denial reason is unsupported.' );
		}

		return new self( false, $reason );
	}
}
