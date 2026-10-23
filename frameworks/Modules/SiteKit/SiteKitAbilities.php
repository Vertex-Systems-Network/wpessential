<?php

declare(strict_types=1);

namespace WPEssential\Modules\SiteKit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Abilities\AbilityDescriptor;
use WPEssential\Platform\Abilities\AbilityRegistry;
use WPEssential\Platform\Admin\Connect\Mcp\CallbackAbilityHandler;
use WPEssential\Platform\Auth\ExecutionChannel;

/**
 * Site Kit abilities. They are exposed to the admin UI, REST and, through AI Connect, to AI clients:
 * reads always, writes only when "Allow AI to change settings" is on. Snippets are never AI-writable.
 */
final class SiteKitAbilities {

	public const GET    = 'wpessential/site-kit/get';
	public const SCHEMA = 'wpessential/site-kit/schema';
	public const UPDATE = 'wpessential/site-kit/update';
	public const RESET  = 'wpessential/site-kit/reset-group';

	public const CAPABILITY = 'edit_theme_options';

	public function __construct( private readonly SiteKitStore $store ) {}

	public function register( AbilityRegistry $abilities ): void {
		$this->add( $abilities, self::GET, false, 'Read the Site Kit values that differ from the theme defaults.', [], fn (): array => [ 'values' => $this->store->values() ] );
		$this->add( $abilities, self::SCHEMA, false, 'Read the Site Kit groups and fields: type, label, choices and limits.', [], static fn (): array => [ 'groups' => self::schemaForAi() ] );
		$this->add(
			$abilities,
			self::UPDATE,
			true,
			'Change Site Kit settings. Pass {"values": {"field_key": value}}; an empty string resets a field to the theme default. Returns what changed.',
			[
				'values' => [
					'type'                 => 'object',
					'additionalProperties' => true,
				],
			],
			function ( array $input ): array {
				$values = $input['values'] ?? null;
				if ( ! is_array( $values ) || $values === [] ) {
					throw new InvalidArgumentException( 'Pass the fields to change in "values".' );
				}
				$unknown = array_diff( array_map( 'strval', array_keys( $values ) ), array_keys( SiteKitSchema::fields() ) );
				if ( $unknown !== [] ) {
					throw new InvalidArgumentException( 'Unknown Site Kit fields: ' . implode( ', ', $unknown ) . '. Read wpessential/site-kit/schema first.' );
				}

				return [ 'changed' => $this->store->update( $values ) ];
			}
		);
		$this->add(
			$abilities,
			self::RESET,
			true,
			'Reset one Site Kit group to the theme defaults.',
			[
				'group' => [
					'type' => 'string',
					'enum' => array_keys( SiteKitSchema::sections() ),
				],
			],
			function ( array $input ): array {
				$group = (string) ( $input['group'] ?? '' );
				if ( ! isset( SiteKitSchema::sections()[ $group ] ) ) {
					throw new InvalidArgumentException( 'Unknown Site Kit group.' );
				}

				return [ 'reset' => $this->store->resetSection( $group ) ];
			}
		);
	}

	/** @return array<string,array{title:string,fields:array<string,array<string,mixed>>}> */
	public static function schemaForAi(): array {
		$out = [];
		foreach ( SiteKitSchema::sections() as $id => $section ) {
			$fields = [];
			foreach ( $section['fields'] as $key => $field ) {
				if ( $field['type'] !== 'heading' ) {
					$fields[ $key ] = array_intersect_key( $field, array_flip( [ 'type', 'label', 'choices', 'min', 'max', 'step', 'unit', 'responsive' ] ) );
				}
			}
			$out[ $id ] = [
				'title'  => $section['title'],
				'fields' => $fields,
			];
		}

		return $out;
	}

	/**
	 * @param array<string,array<string,mixed>>                $properties
	 * @param \Closure(array<string,mixed>):array<string,mixed> $callback
	 */
	private function add( AbilityRegistry $abilities, string $name, bool $mutates, string $description, array $properties, \Closure $callback ): void {
		$abilities->register(
			new AbilityDescriptor(
				name: $name,
				ownerSurfaceId: SiteKitSchema::OWNER_SURFACE_ID,
				capability: self::CAPABILITY,
				mutates: $mutates,
				channels: [ ExecutionChannel::Internal, ExecutionChannel::Ui, ExecutionChannel::Rest, ExecutionChannel::Ai ],
				inputSchema: [
					'type'                 => 'object',
					'description'          => $description,
					'properties'           => (object) $properties,
					'additionalProperties' => false,
				],
				outputSchema: [ 'type' => 'object' ],
			),
			new CallbackAbilityHandler( $callback )
		);
	}
}
