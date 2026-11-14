<?php

declare(strict_types=1);

// phpcs:disable WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception messages are internal and never echoed raw: they reach admins as JSON validation issues (shown with textContent) or through escaped notices.

namespace WPEssential\Platform\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use RuntimeException;
use WPEssential\Contracts\AbilityHandlerInterface;
use WPEssential\Contracts\InputAuthorizingAbilityHandlerInterface;
use WPEssential\Contracts\ModuleEditionScopeInterface;
use WPEssential\Platform\Auth\AuthorizationRequest;
use WPEssential\Platform\Auth\ExecutionContext;
use WPEssential\Platform\Auth\PolicyDecision;
use WPEssential\Platform\Auth\PolicyEngine;

final class AbilityRegistry implements ModuleEditionScopeInterface {

	/** @var array<string, array{descriptor: AbilityDescriptor, handler: AbilityHandlerInterface, edition: string}> */
	private array $abilities = [];

	/** @var string Edition of the module currently registering abilities. */
	private string $edition = 'free';

	/**
	 * Answers whether premium (pro) abilities may change data.
	 *
	 * @var (callable(): bool)|null
	 */
	private $premiumMutationGate = null;

	public function __construct( private readonly PolicyEngine $policy ) {}

	public function register( AbilityDescriptor $descriptor, AbilityHandlerInterface $handler ): void {
		if ( isset( $this->abilities[ $descriptor->name ] ) ) {
			throw new RuntimeException( sprintf( 'Ability "%s" is already registered.', $descriptor->name ) );
		}
		$this->abilities[ $descriptor->name ] = [
			'descriptor' => $descriptor,
			'handler'    => $handler,
			'edition'    => $this->edition,
		];
	}

	public function enterEdition( string $edition ): void {
		$this->edition = $edition === 'free' ? 'free' : 'pro';
	}

	public function leaveEdition(): void {
		$this->edition = 'free';
	}

	/** Edition ("free" or "pro") of the module that registered the ability. */
	public function edition( string $name ): ?string {
		return $this->abilities[ $name ]['edition'] ?? null;
	}

	/**
	 * Set by the Pro add-on: when the gate answers false, mutating pro abilities are denied
	 * (read-only). Free abilities are never gated.
	 *
	 * @param callable(): bool $gate
	 */
	public function setPremiumMutationGate( callable $gate ): void {
		$this->premiumMutationGate = $gate;
	}

	public function descriptor( string $name ): ?AbilityDescriptor {
		return $this->abilities[ $name ]['descriptor'] ?? null;
	}

	/** @return list<AbilityDescriptor> */
	public function descriptors(): array {
		return array_values(
			array_map(
				static fn ( array $entry ): AbilityDescriptor => $entry['descriptor'],
				$this->abilities,
			)
		);
	}

	/** @param array<string,mixed> $input */
	public function authorize( string $name, ExecutionContext $context, array $input = [] ): PolicyDecision {
		$entry = $this->abilities[ $name ] ?? null;
		if ( $entry === null ) {
			return PolicyDecision::deny( 'ability_not_registered' );
		}

		$descriptor = $entry['descriptor'];
		if ( ! $descriptor->allows( $context->channel ) ) {
			return PolicyDecision::deny( 'channel_not_exposed' );
		}

		$decision = $this->policy->authorize(
			new AuthorizationRequest(
				context: $context,
				ability: $descriptor->name,
				capability: $descriptor->capability,
			)
		);
		if ( ! $decision->allowed ) {
			return $decision;
		}

		// Checked after the caller's own permission, so a denial never hides a missing capability.
		if ( $descriptor->mutates
			&& $entry['edition'] === 'pro'
			&& $this->premiumMutationGate !== null
			&& ( $this->premiumMutationGate )() !== true
		) {
			return PolicyDecision::deny( 'premium_read_only' );
		}

		$handler = $entry['handler'];
		if ( $handler instanceof InputAuthorizingAbilityHandlerInterface ) {
			return $handler->authorizeInput( $input, $context );
		}

		return $decision;
	}

	/** @param array<string, mixed> $input */
	public function execute( string $name, array $input, ExecutionContext $context ): mixed {
		$entry = $this->abilities[ $name ] ?? null;
		if ( $entry === null ) {
			throw new RuntimeException( sprintf( 'Ability "%s" is not registered.', $name ) );
		}

		$decision = $this->authorize( $name, $context, $input );
		if ( ! $decision->allowed ) {
			throw new RuntimeException( sprintf( 'Ability "%s" denied: %s.', $name, $decision->reason ) );
		}

		return $entry['handler']->handle( $input, $context );
	}
}
