<?php
/**
 * Payment channels around WooCommerce: bank transfer grace while a transfer is checked, and
 * receipt uploads for bank-transfer orders. Each part has an on/off switch in Settings.
 *
 * Local gateways (JazzCash, EasyPaisa, Bank Alfalah) are ordinary WooCommerce gateways: their
 * paid orders already create licenses through Orders. Lemon Squeezy lives in LemonSqueezy.
 *
 * @package WPEssential\Server
 */

namespace WPEssential\Server;

defined( 'ABSPATH' ) || exit;

/**
 * Bank transfer grace and receipts.
 */
class Payments {

	const RECEIPT_ACTION = 'wpes_receipt';
	const RECEIPT_VIEW   = 'wpes_receipt_view';
	const RECEIPT_TYPES  = array(
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'png'  => 'image/png',
		'pdf'  => 'application/pdf',
	);
	const RECEIPT_MAX    = 5242880;
	const RECEIPT_LIMIT  = 5;

	/** A license gets bank-transfer grace at most once in this period (stops unpaid-order loops). */
	const GRACE_COOLDOWN = 5184000;

	/**
	 * Hooks.
	 */
	public static function hooks() {
		add_action( 'woocommerce_order_status_on-hold', array( __CLASS__, 'on_hold' ) );
		add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'release' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'release' ) );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'receipt_form' ) );
		add_action( 'admin_post_' . self::RECEIPT_ACTION, array( __CLASS__, 'receipt_upload' ) );
		add_action( 'admin_post_' . self::RECEIPT_VIEW, array( __CLASS__, 'receipt_view' ) );
	}

	// ---------------------------------------------------------------- bank transfer grace

	/**
	 * Until when a license is kept working while its bank transfer is checked (0 none).
	 *
	 * @param int $license_id License.
	 * @return int Unix time.
	 */
	public static function pending_until( $license_id ) {
		$orders = get_option( 'wpes_pending_' . (int) $license_id, array() );
		return is_array( $orders ) && $orders ? (int) max( $orders ) : 0;
	}

	/**
	 * Whether a license may get bank-transfer grace now (pure, unit-tested).
	 *
	 * @param int $last_grant When it last got grace (0 never).
	 * @param int $now        Now.
	 * @return bool
	 */
	public static function grace_allowed( $last_grant, $now ) {
		return $last_grant <= 0 || $now - $last_grant >= self::GRACE_COOLDOWN;
	}

	/**
	 * Whether a pending payment keeps the license working at a time (pure, unit-tested).
	 *
	 * @param int $until Pending-until time (0 none).
	 * @param int $now   Now.
	 * @return bool
	 */
	public static function pending_covers( $until, $now ) {
		return $until > 0 && $now <= $until;
	}

	/**
	 * Forget a pending payment (paid, cancelled or failed).
	 *
	 * @param int $license_id License.
	 */
	public static function clear_pending( $license_id ) {
		delete_option( 'wpes_pending_' . (int) $license_id );
	}

	/**
	 * A plan order paid by bank transfer is waiting: keep the license it renews or upgrades
	 * (or the buyer's running trial) working for the grace days.
	 *
	 * @param int $order_id Order.
	 */
	public static function on_hold( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || 'bacs' !== $order->get_payment_method() || ! Plugin::settings( 'bank_grace' ) ) {
			return;
		}
		$until = time() + DAY_IN_SECONDS * max( 1, (int) Plugin::settings( 'bank_grace_days' ) );
		$ids   = array();
		foreach ( $order->get_items() as $item ) {
			$plan = Plans::from_product( $item->get_product_id() );
			if ( ! $plan ) {
				continue;
			}
			$renew               = (int) $item->get_meta( '_wpes_renew' );
			$upgrade             = (int) $item->get_meta( '_wpes_upgrade' );
			list( $action, $id ) = Fulfilment::decide( $renew, Trials::is_trial( $renew ? Licenses::get( $renew ) : null ), $upgrade, (bool) $plan['pro'], Trials::active_trial_id( $order->get_customer_id() ) );
			$license             = 'create' === $action ? null : Licenses::get( $id );
			if ( ! $license || (int) $license['user_id'] !== (int) $order->get_customer_id() ) {
				continue;
			}
			if ( ! self::grace_allowed( (int) get_option( 'wpes_grace_last_' . $id, 0 ), time() ) ) {
				$order->add_order_note( __( 'Bank transfer pending. This license already had a waiting period recently, so it is not extended until the payment is confirmed.', 'wpessential-server' ) );
				continue;
			}
			$orders              = (array) get_option( 'wpes_pending_' . $id, array() );
			$orders[ $order_id ] = $until;
			update_option( 'wpes_pending_' . $id, $orders, false );
			update_option( 'wpes_grace_last_' . $id, time(), false );
			$ids[] = $id;
		}
		if ( $ids ) {
			$order->update_meta_data( '_wpes_pending', $ids );
			$order->save();
			/* translators: %s: date. */
			$order->add_order_note( sprintf( __( 'Bank transfer pending: the customer\'s license keeps working until %s while you check the payment.', 'wpessential-server' ), gmdate( 'Y-m-d', $until ) ) );
			Events::log( 'payment.bank_pending', sprintf( 'Order #%1$d waits for a bank transfer; licenses %2$s kept until %3$s.', $order_id, implode( ',', $ids ), gmdate( 'Y-m-d H:i', $until ) ), array( 'order_id' => (int) $order_id ) );
		}
	}

	/**
	 * Cancelled or failed transfer: stop the grace.
	 *
	 * @param int $order_id Order.
	 */
	public static function release( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		// End only this order's grace; another waiting order for the same license keeps its own.
		foreach ( (array) $order->get_meta( '_wpes_pending' ) as $id ) {
			$orders = (array) get_option( 'wpes_pending_' . (int) $id, array() );
			unset( $orders[ (int) $order_id ] );
			if ( $orders ) {
				update_option( 'wpes_pending_' . (int) $id, $orders, false );
			} else {
				delete_option( 'wpes_pending_' . (int) $id );
			}
		}
	}

	// ---------------------------------------------------------------- receipts

	/**
	 * Whether an upload may be stored as a receipt (pure, unit-tested).
	 *
	 * @param string $name Original file name.
	 * @param int    $size Bytes.
	 * @param string $mime MIME type detected from the file contents.
	 * @return string|null Error code, or null when accepted.
	 */
	public static function receipt_refusal( $name, $size, $mime ) {
		$ext = strtolower( pathinfo( (string) $name, PATHINFO_EXTENSION ) );
		if ( ! isset( self::RECEIPT_TYPES[ $ext ] ) || self::RECEIPT_TYPES[ $ext ] !== $mime ) {
			return 'receipt_type';
		}
		if ( $size <= 0 || $size > self::RECEIPT_MAX ) {
			return 'receipt_size';
		}
		return null;
	}

	/**
	 * Private folder for receipts.
	 *
	 * @return string
	 */
	private static function receipt_dir() {
		// Outside the web root when the host allows it, so no server setup can serve receipts
		// directly; otherwise a denied folder in uploads with long random file names.
		$outside = dirname( untrailingslashit( ABSPATH ) ) . '/wpes-private/receipts';
		if ( is_dir( $outside ) || wp_mkdir_p( $outside ) ) {
			return $outside;
		}
		$uploads = wp_upload_dir();
		$dir     = trailingslashit( $uploads['basedir'] ) . 'wpes-receipts';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		if ( ! file_exists( $dir . '/.htaccess' ) ) {
			file_put_contents( $dir . '/.htaccess', "Require all denied\nDeny from all\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Deny rule.
			file_put_contents( $dir . '/index.php', "<?php\n// Silence.\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Directory index.
		}
		return $dir;
	}

	/**
	 * Upload form on My Account → order, for bank-transfer orders that wait.
	 *
	 * @param \WC_Order $order Order.
	 */
	public static function receipt_form( $order ) {
		if ( ! Plugin::settings( 'receipts' ) || ! $order || 'bacs' !== $order->get_payment_method() || ! $order->has_status( 'on-hold' ) || (int) $order->get_customer_id() !== get_current_user_id() ) {
			return;
		}
		echo '<section class="wpes-receipt"><h2>' . esc_html__( 'Bank transfer receipt', 'wpessential-server' ) . '</h2>';
		if ( $order->get_meta( '_wpes_receipt' ) ) {
			echo '<p>' . esc_html__( 'Thanks, your receipt is with us. We will confirm the payment soon. You can upload a clearer one below.', 'wpessential-server' ) . '</p>';
		} else {
			/* translators: %s: order number. */
			echo '<p>' . esc_html( sprintf( __( 'Upload a photo or PDF of your transfer (use order %s as the reference). JPG, PNG or PDF, up to 5 MB.', 'wpessential-server' ), $order->get_order_number() ) ) . '</p>';
		}
		echo '<form method="post" enctype="multipart/form-data" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( self::RECEIPT_ACTION . '_' . $order->get_id() );
		echo '<input type="hidden" name="action" value="' . esc_attr( self::RECEIPT_ACTION ) . '"><input type="hidden" name="order" value="' . (int) $order->get_id() . '">';
		echo '<p><input type="file" name="receipt" accept=".jpg,.jpeg,.png,.pdf" required> <button type="submit" class="button">' . esc_html__( 'Upload receipt', 'wpessential-server' ) . '</button></p></form></section>';
	}

	/**
	 * Store an uploaded receipt.
	 */
	public static function receipt_upload() {
		$order_id = isset( $_POST['order'] ) ? (int) $_POST['order'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked below.
		check_admin_referer( self::RECEIPT_ACTION . '_' . $order_id );
		$order = wc_get_order( $order_id );
		if ( ! Plugin::settings( 'receipts' ) || ! $order || (int) $order->get_customer_id() !== get_current_user_id() || 'bacs' !== $order->get_payment_method() || ! $order->has_status( 'on-hold' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ), '', array( 'response' => 403 ) );
		}
		$count = (int) $order->get_meta( '_wpes_receipt_count' );
		if ( $count >= self::RECEIPT_LIMIT ) {
			wp_die(
				esc_html__( 'You have uploaded the most receipts allowed for this order. Contact us if you need to send another.', 'wpessential-server' ),
				'',
				array(
					'response'  => 429,
					'back_link' => true,
				)
			);
		}
		$file = $_FILES['receipt'] ?? array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Checked below.
		$tmp  = (string) ( $file['tmp_name'] ?? '' );
		$mime = '' !== $tmp && is_uploaded_file( $tmp ) && function_exists( 'mime_content_type' ) ? (string) mime_content_type( $tmp ) : '';
		$fail = self::receipt_refusal( sanitize_file_name( (string) ( $file['name'] ?? '' ) ), (int) ( $file['size'] ?? 0 ), $mime );
		if ( null !== $fail ) {
			wp_die(
				esc_html__( 'Upload a JPG, PNG or PDF of up to 5 MB.', 'wpessential-server' ),
				'',
				array(
					'response'  => 400,
					'back_link' => true,
				)
			);
		}
		$ext  = strtolower( pathinfo( sanitize_file_name( (string) $file['name'] ), PATHINFO_EXTENSION ) );
		$name = 'order-' . $order_id . '-' . wp_generate_password( 32, false ) . '.' . $ext;
		$dir  = self::receipt_dir();
		if ( ! move_uploaded_file( $tmp, $dir . '/' . $name ) ) { // phpcs:ignore Generic.PHP.ForbiddenFunctions.Found -- Validated upload moved to the private folder.
			wp_die( esc_html__( 'The receipt could not be saved. Try again.', 'wpessential-server' ) );
		}
		$previous = basename( (string) $order->get_meta( '_wpes_receipt' ) );
		if ( '' !== $previous && is_file( $dir . '/' . $previous ) ) {
			wp_delete_file( $dir . '/' . $previous ); // A replacement keeps only the newest receipt.
		}
		$order->update_meta_data( '_wpes_receipt', $name );
		$order->update_meta_data( '_wpes_receipt_count', $count + 1 );
		$order->save();
		// Managers open it by capability (no nonce: the link is made in the customer's session).
		$view = add_query_arg(
			array(
				'action' => self::RECEIPT_VIEW,
				'order'  => $order_id,
			),
			admin_url( 'admin-post.php' )
		);
		/* translators: %s: link. */
		$order->add_order_note( sprintf( __( 'Bank transfer receipt uploaded by the customer: %s', 'wpessential-server' ), $view ) );
		Events::log( 'payment.receipt_uploaded', sprintf( 'Receipt uploaded for order #%d.', $order_id ), array( 'order_id' => $order_id ) );
		wp_safe_redirect( $order->get_view_order_url() );
		exit;
	}

	/**
	 * Show a receipt to shop managers.
	 */
	public static function receipt_view() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'wpessential-server' ), '', array( 'response' => 403 ) );
		}
		$order_id = isset( $_GET['order'] ) ? (int) $_GET['order'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view for shop managers.
		$order    = wc_get_order( $order_id );
		if ( ! $order ) {
			wp_die( esc_html__( 'No receipt.', 'wpessential-server' ), '', array( 'response' => 404 ) );
		}
		$name = basename( (string) $order->get_meta( '_wpes_receipt' ) );
		$path = self::receipt_dir() . '/' . $name;
		$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( '' === $name || ! is_readable( $path ) || ! isset( self::RECEIPT_TYPES[ $ext ] ) ) {
			wp_die( esc_html__( 'No receipt.', 'wpessential-server' ), '', array( 'response' => 404 ) );
		}
		nocache_headers();
		header( 'Content-Type: ' . self::RECEIPT_TYPES[ $ext ] );
		header( 'Content-Disposition: inline; filename="' . $name . '"' );
		header( 'X-Content-Type-Options: nosniff' );
		readfile( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- Private receipt stream.
		exit;
	}
}
