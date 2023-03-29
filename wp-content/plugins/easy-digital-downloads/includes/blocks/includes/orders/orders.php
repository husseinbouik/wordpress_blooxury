<?php
/**
 * Orders blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Orders;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Blocks\Functions as Helpers;

require_once EDD_BLOCKS_DIR . 'includes/orders/functions.php';

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers all of the EDD core blocks.
 *
 * @since 2.0
 * @return void
 */
function register() {
	$blocks = array(
		'order-history'  => array(
			'render_callback' => __NAMESPACE__ . '\orders',
		),
		'confirmation'   => array(
			'render_callback' => __NAMESPACE__ . '\confirmation',
		),
		'receipt'        => array(
			'render_callback' => __NAMESPACE__ . '\receipt',
		),
		'user-downloads' => array(
			'render_callback' => __NAMESPACE__ . '\downloads',
		),
	);

	foreach ( $blocks as $block => $args ) {
		register_block_type( EDD_BLOCKS_DIR . 'build/' . $block, $args );
	}
}

/**
 * Renders the order history block.
 *
 * @since 2.0
 * @param array  $block_attributes The block attributes.
 * @return string
 */
function orders( $block_attributes = array() ) {
	if ( ! is_user_logged_in() ) {
		return '';
	}

	if ( edd_user_pending_verification() ) {
		ob_start();
		include EDD_BLOCKS_DIR . 'views/orders/pending.php';

		return ob_get_clean();
	}

	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'number'  => 20,
			'columns' => 2,
		)
	);

	$number = (int) $block_attributes['number'];
	$args   = Functions\get_order_history_args( $block_attributes );

	// Set up classes.
	$classes = array(
		'wp-block-edd-orders',
		'edd-blocks__orders',
	);
	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<?php
		$classes = Helpers\get_block_classes( $block_attributes, array( 'edd-blocks__orders-grid' ) );
		$orders  = edd_get_orders( $args );
		include EDD_BLOCKS_DIR . 'views/orders/orders.php';

		unset( $args['number'], $args['offset'] );
		$count = edd_count_orders( $args );
		include EDD_BLOCKS_DIR . 'views/orders/pagination.php';
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Renders the order confirmation block.
 *
 * @since 2.0
 * @return string
 */
function confirmation( $block_attributes = array() ) {
	$session = Functions\get_purchase_session();
	if ( empty( $session['purchase_key'] ) ) {
		if ( Helpers\is_block_editor() ) {
			return '<p class="edd-alert edd-alert-info">' . esc_html( __( 'To view a sample confirmation screen, you need to have at least one order in your store.', 'easy-digital-downloads' ) ) . '</p>';
		}

		return '<p class="edd-alert edd-alert-error">' . esc_html( __( 'Your purchase session could not be retrieved.', 'easy-digital-downloads' ) ) . '</p>';
	}

	global $edd_receipt_args;

	$edd_receipt_args = wp_parse_args(
		$block_attributes,
		array(
			'payment_key'    => false,
			'payment_method' => true,
		)
	);

	// Set up classes.
	$classes = array(
		'wp-block-edd-confirmation',
		'edd-blocks__confirmation',
	);
	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<?php
		$order                  = edd_get_order_by( 'payment_key', $session['purchase_key'] );
		$edd_receipt_args['id'] = $order->id;
		include EDD_BLOCKS_DIR . 'views/orders/receipt-items.php';
		include EDD_BLOCKS_DIR . 'views/orders/totals.php';
		?>
		<div class="edd-blocks__confirmation-details">
			<a href="<?php echo esc_url( edd_get_receipt_page_uri( $order->id ) ); ?>">
				<?php esc_html_e( 'View Details and Downloads', 'easy-digital-downloads' ); ?>
			</a>
		</div>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Renders the full order receipt.
 *
 * @since 2.0
 * @param array $block_attributes
 * @return string
 */
function receipt( $block_attributes = array() ) {
	global $edd_receipt_args;

	$edd_receipt_args = wp_parse_args(
		$block_attributes,
		array(
			'error'          => __( 'Sorry, trouble retrieving order receipt.', 'easy-digital-downloads' ),
			'payment_key'    => false,
			'payment_method' => true,
		)
	);
	$payment_key      = Functions\get_payment_key();

	// No key found.
	if ( ! $payment_key ) {
		if ( Helpers\is_block_editor() ) {
			return '<p class="edd-alert edd-alert-info">' . esc_html( __( 'To view a sample receipt, you need to have at least one order in your store.', 'easy-digital-downloads' ) ) . '</p>';
		}

		return '<p class="edd-alert edd-alert-error">' . esc_html( $edd_receipt_args['error'] ) . '</p>';
	}

	ob_start();
	edd_print_errors();

	$order         = edd_get_order_by( 'payment_key', $payment_key );
	$user_can_view = edd_can_view_receipt( $order );
	if ( ! $user_can_view ) {
		show_no_access_message( $order );

		return ob_get_clean();
	}

	$classes = array(
		'wp-block-edd-receipt',
		'edd-blocks__receipt',
	);
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<?php
		include EDD_BLOCKS_DIR . 'views/orders/totals.php';
		maybe_show_receipt( $order );
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Shows the message if the current viewer doesn't have access to any order information.
 *
 * @since 2.0
 * @param EDD\Orders\Order $order
 * @return void
 */
function show_no_access_message( $order ) {
	// User is logged in, but does not have access.
	if ( is_user_logged_in() ) {
		printf(
			'<p class="edd-alert edd-alert-error">%s</p>',
			esc_html__( 'Sorry, you do not have permission to view this receipt.', 'easy-digital-downloads' )
		);
		return;
	}

	// User is not logged in and can view a guest order.
	if ( empty( $order->user_id ) ) {
		printf(
			'<p>%s</p>',
			esc_html__( 'Please confirm your email address to access your downloads.', 'easy-digital-downloads' )
		);
		include EDD_BLOCKS_DIR . 'views/orders/guest.php';

		return;
	}

	// Otherwise, the order was made by a customer with a user account.
	printf(
		'<p>%s</p>',
		esc_html__( 'Please log in to view your order.', 'easy-digital-downloads' )
	);
	echo \EDD\Blocks\Forms\login( array( 'redirect' => edd_get_receipt_page_uri( $order->id ) ) );
}

/**
 * Shows the full receipt details if criteria are met; otherwise show a verification or login form.
 *
 * @since 2.0
 * @param EDD\Orders\Order $order
 * @return void
 */
function maybe_show_receipt( $order ) {
	$session = edd_get_purchase_session();
	if ( is_user_logged_in() || ( ! empty( $session['purchase_key'] ) && $session['purchase_key'] === $order->payment_key ) ) {
		global $edd_receipt_args;
		include EDD_BLOCKS_DIR . 'views/orders/receipt-items.php';

		/**
		 * Fires after the order receipt table.
		 *
		 * @since 3.0
		 * @param \EDD\Orders\Order $order          Current order.
		 * @param array             $edd_receipt_args [edd_receipt] shortcode arguments.
		 */
		do_action( 'edd_order_receipt_after_table', $order, $edd_receipt_args );
		return;
	}

	// The order belongs to a registered WordPress user.
	?>
	<p>
		<?php esc_html_e( 'Please log in to access your downloads.', 'easy-digital-downloads' ); ?>
	</p>
	<?php
	echo \EDD\Blocks\Forms\login( array( 'current' => true ) );
}

add_action( 'edd_view_receipt_guest', __NAMESPACE__ . '\verify_guest_email' );
/**
 * Verfies the email address to view the details for a guest order.
 *
 * @since 2.0
 * @param array $data
 * @return void
 */
function verify_guest_email( $data ) {
	if ( empty( $data['edd_guest_email'] ) || empty( $data['edd_guest_nonce'] ) || ! wp_verify_nonce( $data['edd_guest_nonce'], 'edd-guest-nonce' ) ) {
		edd_set_error( 'edd-guest-error', __( 'Your email address could not be verified.', 'easy-digital-downloads' ) );
		return;
	}
	$order = edd_get_order( $data['order_id'] );
	if ( $order instanceof \EDD\Orders\Order && $data['edd_guest_email'] === $order->email ) {
		edd_set_purchase_session(
			array(
				'purchase_key' => $order->payment_key,
			)
		);
		return;
	}
	edd_set_error( 'edd-guest-error', __( 'Your email address could not be verified.', 'easy-digital-downloads' ) );
}

/**
 * Renders the download history block.
 *
 * @since 2.0.5
 * @param array $block_attributes
 * @return string
 */
function downloads( $block_attributes = array() ) {
	if ( ! is_user_logged_in() ) {
		return '';
	}

	if ( edd_user_pending_verification() ) {
		ob_start();
		include EDD_BLOCKS_DIR . 'views/orders/pending.php';

		return ob_get_clean();
	}

	$block_attributes = wp_parse_args(
		$block_attributes,
		array(
			'search'     => false,
			'variations' => true,
			'nofiles'    => __( 'No downloadable files found.', 'easy-digital-downloads' ),
			'hide_empty' => true,
		)
	);

	$downloads = get_purchased_products( $block_attributes );
	if ( ! $downloads ) {
		return '';
	}

	// Set up classes.
	$classes = array(
		'wp-block-edd-user-downloads',
		'edd-blocks__user-downloads',
	);
	ob_start();
	?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<?php
		if ( $block_attributes['search'] && function_exists( '\\EDD\\Blocks\\Pro\\Search\\do_search' ) ) {
			\EDD\Blocks\Pro\Search\do_search();
		}
		?>
		<div class="edd-blocks__row edd-blocks__row-header edd-order-items__header">
			<div class="edd-blocks__row-label"><?php esc_html_e( 'Products', 'easy-digital-downloads' ); ?></div>
			<?php if ( ! edd_no_redownload() ) : ?>
				<div class="edd-blocks__row-label"><?php esc_html_e( 'Files', 'easy-digital-downloads' ); ?></div>
			<?php endif; ?>
		</div>
		<?php
		ksort( $downloads );
		foreach ( $downloads as $name => $item ) {
			include EDD_BLOCKS_DIR . 'views/orders/downloads.php';
		}
		?>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * Gets an array of products the user has purchased.
 *
 * @since 2.0.5
 * @param  array The block attributes
 * @return false|array
 */
function get_purchased_products( $block_attributes ) {
	$args = array(
		'user_id'    => get_current_user_id(),
		'fields'     => 'ids',
		'status__in' => \edd_get_complete_order_statuses(),
		'number'     => 9999,
	);
	if ( Helpers\is_block_editor() ) {
		$args['number'] = 50;
		unset( $args['user_id'] );
	}
	$order_ids = edd_get_orders( $args );
	if ( empty( $order_ids ) ) {
		return false;
	}

	$items = edd_get_order_items(
		array(
			'order_id__in' => $order_ids,
			'number'       => 99999,
			'status__in'   => \edd_get_deliverable_order_item_statuses(),
		)
	);
	if ( empty( $items ) ) {
		return false;
	}

	$downloads = array();
	foreach ( $items as $item ) {
		if ( edd_is_bundled_product( $item->product_id ) ) {
			$key = ! empty( $block_attributes['variations'] ) ? $item->product_name : edd_get_download_name( $item->product_id );
			if ( array_key_exists( $key, $downloads ) ) {
				continue;
			}
			$bundled_products = edd_get_bundled_products( $item->product_id, $item->price_id );
			foreach ( $bundled_products as $bundle_item ) {
				$product_id      = edd_get_bundle_item_id( $bundle_item );
				$price_id        = edd_get_bundle_item_price_id( $bundle_item );
				$key             = edd_get_download_name( $product_id );
				$order_item_args = array(
					'order_id'     => $item->order_id,
					'status'       => $item->status,
					'product_id'   => $product_id,
					'product_name' => $key,
				);
				if ( is_numeric( $price_id ) && edd_has_variable_prices( $product_id ) ) {
					if ( ! empty( $block_attributes['variations'] ) ) {
						$key                             = edd_get_download_name( $product_id, $price_id );
						$order_item_args['product_name'] = $key;
					} else {
						$download_files = edd_get_download_files( $product_id, $price_id );
						$conditions     = wp_list_pluck( $download_files, 'condition' );
						if ( ! empty( $conditions ) && ! in_array( 'all', $conditions, true ) ) {
							$key                             = edd_get_download_name( $product_id, $price_id );
							$order_item_args['product_name'] = $key;
						}
					}
					$order_item_args['price_id'] = $price_id;
				}
				if ( array_key_exists( $key, $downloads ) ) {
					continue;
				}
				$downloads[ $key ] = new \EDD\Orders\Order_Item( $order_item_args );
			}
			continue;
		}
		$key = $item->product_name;
		if ( is_numeric( $item->price_id ) && edd_has_variable_prices( $item->product_id ) ) {
			if ( empty( $block_attributes['variations'] ) ) {
				$download_files = edd_get_download_files( $item->product_id, $item->price_id );
				$conditions     = wp_list_pluck( $download_files, 'condition' );
				if ( empty( $conditions ) || in_array( 'all', $conditions, true ) ) {
					$key = edd_get_download_name( $item->product_id );
				}
			}
		}
		if ( array_key_exists( $key, $downloads ) ) {
			continue;
		}
		$downloads[ $key ] = $item;
	}

	return ! empty( $downloads ) ? $downloads : false;
}
