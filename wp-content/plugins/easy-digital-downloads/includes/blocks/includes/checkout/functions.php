<?php
/**
 * General functions for EDD checkout blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Checkout\Functions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Whether the checkout page is using blocks.
 *
 * @since 2.0
 * @return bool
 */
function checkout_has_blocks() {
	if ( edd_doing_ajax() && ! empty( $_POST['current_page'] ) ) {
		return has_block( 'edd/checkout', absint( $_POST['current_page'] ) );
	}

	return has_block( 'edd/checkout' ) || has_block( 'edd/checkout', absint( edd_get_option( 'purchase_page' ) ) );
}

/**
 * Gets a string to represent the cart quantity.
 *
 * @since 2.0
 * @return string
 */
function get_quantity_string() {
	$quantity = absint( edd_get_cart_quantity() );

	return sprintf(
		'%1$s %2$s',
		$quantity,
		_n( 'item', 'items', $quantity, 'easy-digital-downloads' )
	);
}
