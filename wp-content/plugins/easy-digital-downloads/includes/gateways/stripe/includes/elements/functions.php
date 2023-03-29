<?php

/**
 * Determines if the Stripe API keys can be managed manually.
 *
 * @since 2.8.0
 *
 * @return bool
 */
function edds_stripe_connect_can_manage_keys() {

	$stripe_connect_account_id = edd_get_option( 'stripe_connect_account_id', false );
	$secret                    = edd_is_test_mode() ? edd_get_option( 'test_secret_key' ) : edd_get_option( 'live_secret_key' );

	return empty( $stripe_connect_account_id ) && $secret;
}

/**
 * Get the current elements mode.
 *
 * If the user is gated into the legacy mode, set the default to card-elements.
 *
 * @since 2.9.0
 *
 * @return string The elements mode string.
 */
function edds_get_elements_mode() {
	$default = _edds_legacy_elements_enabled() ? 'card-elements' : 'payment-elements';

	/**
	 * Because we use the deferred payment intents beta, only connected accounts can use Payment Elements
	 * for now, so we'll force them to be in `card-elements`.
	 */
	if ( edds_stripe_connect_can_manage_keys() ) {
		return 'card-elements';
	}

	/**
	 * Recurring Subscription payment method updates need to still run card elements for now.
	 */
	if (
		function_exists( 'edd_recurring' ) &&
		( isset( $_GET['action'] ) && 'update' === $_GET['action'] ) &&
		( isset( $_GET['subscription_id'] ) && is_numeric( $_GET['subscription_id'] ) )
	) {
		return 'card-elements';
	}

	return edd_get_option( 'stripe_elements_mode', $default );
}

/**
 * INTERNAL ONLY: Determines if the user is gated into using the legacy card-elements.
 *
 * This is a transitionary function, intentded to allow us to later remove it. Do not
 * use this function in any extending of EDD or Stripe.
 *
 * @since 2.9.0
 *
 * @return bool If the user is gated into using the legacy card-elements.
 */
function _edds_legacy_elements_enabled() {
	return get_option( '_edds_legacy_elements_enabled', false );
}
