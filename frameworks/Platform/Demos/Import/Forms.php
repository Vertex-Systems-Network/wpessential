<?php
/**
 * Forms with WPForms (Lite or Pro): created from a simple field list by demos and the MCP server.
 *
 * @package WPEssential\Core
 */

namespace WPEssential\Platform\Demos\Import;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPForms helper.
 */
class Forms {

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'init', array( __CLASS__, 'fallback' ), 99 );
	}

	/**
	 * Without WPForms active, show an email link where a demo placed a form instead of the raw shortcode.
	 */
	public static function fallback() {
		if ( shortcode_exists( 'wpforms' ) ) {
			return;
		}
		add_shortcode(
			'wpforms',
			static function () {
				$mail = get_option( 'admin_email' );
				/* translators: %s: email link. */
				return '<p class="wpe-form-fallback">' . sprintf( esc_html__( 'Write to us at %s.', 'wpessential' ), '<a href="mailto:' . esc_attr( $mail ) . '">' . esc_html( $mail ) . '</a>' ) . '</p>';
			}
		);
	}

	/**
	 * Make sure WPForms is active (installs WPForms Lite from WordPress.org when missing).
	 *
	 * @return true|\WP_Error
	 */
	public static function ready() {
		if ( function_exists( 'wpforms' ) ) {
			return true;
		}
		$res = Plugins::apply(
			array(
				'slug'   => 'wpforms-lite',
				'name'   => 'WPForms Lite',
				'source' => 'wporg',
				'state'  => 'required',
			)
		);
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		$main = WP_PLUGIN_DIR . '/wpforms-lite/wpforms.php';
		if ( ! function_exists( 'wpforms' ) && is_readable( $main ) ) {
			include_once $main;
		}
		return function_exists( 'wpforms' ) ? true : new \WP_Error( 'wpe_wpforms', __( 'WPForms could not be loaded.', 'wpessential' ) );
	}

	/**
	 * Create a form.
	 *
	 * @param string $title   Title.
	 * @param array  $fields  [{type,label,required,choices,placeholder,description,size}].
	 * @param string $submit  Button text.
	 * @param string $message Confirmation message.
	 * @param array  $extra   Extra settings: email (notification address), subject.
	 * @return int|\WP_Error Form ID.
	 */
	public static function create( $title, array $fields, $submit = '', $message = '', array $extra = array() ) {
		$ok = self::ready();
		if ( is_wp_error( $ok ) ) {
			return $ok;
		}
		$id = wpforms()->obj( 'form' )->add( $title );
		if ( ! $id ) {
			return new \WP_Error( 'wpe_wpforms_add', __( 'WPForms did not create the form.', 'wpessential' ) );
		}
		$data = array(
			'id'       => $id,
			'field_id' => count( $fields ) + 1,
			'fields'   => array(),
			'settings' => array(
				'form_title'             => $title,
				'submit_text'            => $submit ? $submit : __( 'Send', 'wpessential' ),
				'submit_text_processing' => __( 'Sending…', 'wpessential' ),
				'antispam_v3'            => '1',
				'ajax_submit'            => '1',
				'notification_enable'    => '1',
				'notifications'          => array(
					1 => array(
						'notification_name' => __( 'Default notification', 'wpessential' ),
						'email'             => $extra['email'] ?? '{admin_email}',
						/* translators: %s: form title. */
						'subject'           => $extra['subject'] ?? sprintf( __( 'New entry: %s', 'wpessential' ), $title ),
						'sender_name'       => get_bloginfo( 'name' ),
						'sender_address'    => '{admin_email}',
						'message'           => '{all_fields}',
					),
				),
				'confirmations'          => array(
					1 => array(
						'type'           => 'message',
						'message'        => $message ? $message : __( 'Thanks — we received your message and will reply soon.', 'wpessential' ),
						'message_scroll' => '1',
					),
				),
			),
			'meta'     => array( 'template' => 'blank' ),
		);
		$n    = 1;
		foreach ( $fields as $f ) {
			$type  = sanitize_key( $f['type'] ?? 'text' );
			$map   = array(
				'phone' => 'phone',
				'tel'   => 'phone',
				'url'   => 'url',
			);
			$field = array(
				'id'          => $n,
				'type'        => $map[ $type ] ?? $type,
				'label'       => sanitize_text_field( $f['label'] ?? ucfirst( $type ) ),
				'description' => sanitize_text_field( $f['description'] ?? '' ),
				'required'    => empty( $f['required'] ) ? '' : '1',
				'size'        => sanitize_key( $f['size'] ?? 'large' ),
				'placeholder' => sanitize_text_field( $f['placeholder'] ?? '' ),
			);
			if ( 'name' === $field['type'] ) {
				$field['format'] = 'simple';
			}
			if ( 'phone' === $field['type'] ) {
				$field['format'] = 'international';
			}
			if ( in_array( $field['type'], array( 'select', 'radio', 'checkbox' ), true ) ) {
				$field['choices'] = array();
				foreach ( array_values( (array) ( $f['choices'] ?? array() ) ) as $i => $label ) {
					$field['choices'][ $i + 1 ] = array(
						'label' => sanitize_text_field( $label ),
						'value' => '',
					);
				}
			}
			$data['fields'][ $n ] = $field;
			++$n;
		}
		wpforms()->obj( 'form' )->update( $id, $data );
		return (int) $id;
	}
}
