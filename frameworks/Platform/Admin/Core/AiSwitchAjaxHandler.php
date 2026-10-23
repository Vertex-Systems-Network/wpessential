<?php

declare(strict_types=1);

namespace WPEssential\Platform\Admin\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use InvalidArgumentException;
use WPEssential\Platform\Admin\Connect\AiConnect;
use WPEssential\Platform\WordPress\Ajax\AjaxHandlerInterface;

/** AI Connect switches. Not an ability: the AI channel must never be able to change its own access. */
final class AiSwitchAjaxHandler implements AjaxHandlerInterface {

	public function __construct( private readonly AiConnect $connect ) {}

	/** @param array<string,mixed> $payload */
	public function handle( array $payload ): mixed {
		$op      = (string) ( $payload['op'] ?? '' );
		$enabled = ( $payload['enabled'] ?? false ) === true || ( $payload['enabled'] ?? '' ) === '1';

		if ( $op === 'power' ) {
			$this->connect->set( 'enabled', $enabled );
			if ( ! $enabled && function_exists( 'get_current_user_id' ) ) {
				$this->connect->revokeAll( (int) get_current_user_id() );
			}
		} elseif ( $op === 'writes' ) {
			$this->connect->set( 'allow_writes', $enabled && $this->connect->enabled() );
		} else {
			throw new InvalidArgumentException( 'Unknown AI Connect switch.' );
		}

		return [ 'settings' => $this->connect->settings() ] + ( $op === 'power' ? [ 'reload' => true ] : [] );
	}
}
