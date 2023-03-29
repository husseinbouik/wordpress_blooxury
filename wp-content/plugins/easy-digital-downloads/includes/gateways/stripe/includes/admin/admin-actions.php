<?php

/**
 * Trigger preapproved payment charge
 *
 * @since 1.6
 * @return void
 */
function edds_process_preapproved_charge() {

	if( empty( $_GET['nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_GET['nonce'], 'edds-process-preapproval' ) )
		return;

	$payment_id  = absint( $_GET['payment_id'] );
	$charge      = edds_charge_preapproved( $payment_id );

	if ( $charge ) {
		wp_redirect( esc_url_raw( add_query_arg( array( 'edd-message' => 'preapproval-charged' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) ); exit;
	} else {
		wp_redirect( esc_url_raw( add_query_arg( array( 'edd-message' => 'preapproval-failed' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) ); exit;
	}

}
add_action( 'edd_charge_stripe_preapproval', 'edds_process_preapproved_charge' );


/**
 * Cancel a preapproved payment
 *
 * @since 1.6
 * @return void
 */
function edds_process_preapproved_cancel() {
	global $edd_options;

	if( empty( $_GET['nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_GET['nonce'], 'edds-process-preapproval' ) )
		return;

	$payment_id = absint( $_GET['payment_id'] );

	if ( empty( $payment_id ) ) {
		return;
	}

	$payment     = edd_get_payment( $payment_id );
	$customer_id = $payment->get_meta( '_edds_stripe_customer_id', true );
	$status      = $payment->status;

	if ( empty( $customer_id ) ) {
		return;
	}

	if ( 'preapproval' !== $status ) {
		return;
	}

	edd_insert_payment_note( $payment_id, __( 'Preapproval cancelled', 'easy-digital-downloads' ) );
	edd_update_payment_status( $payment_id, 'cancelled' );
	$payment->delete_meta( '_edds_stripe_customer_id' );

	wp_redirect( esc_url_raw( add_query_arg( array( 'edd-message' => 'preapproval-cancelled' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ) ) ); exit;
}
add_action( 'edd_cancel_stripe_preapproval', 'edds_process_preapproved_cancel' );

/**
 * Adds a JS confirmation to check whether a preapproved payment should really be cancelled.
 *
 * @since 2.8.10
 * @return void
 */
add_action( 'admin_print_footer_scripts-download_page_edd-payment-history', function () {
	?>
	<script>
		document.addEventListener( 'DOMContentLoaded', function() {
			var cancelLinks = document.querySelectorAll( '.row-actions .cancel-preapproval a' );
			cancelLinks.forEach( function( link ) {
				link.addEventListener( 'click', function( e ) {
					if ( ! confirm( '<?php esc_attr_e( 'Are you sure you want to cancel this order?', 'easy-digital-downloads' ); ?>' ) ) {
						e.preventDefault();
					}
				} );
			} );
		} );
	</script>
	<?php
} );

/**
 * Admin Messages
 *
 * @since 1.6
 * @return void
 */
function edds_admin_messages() {

	if ( isset( $_GET['edd-message'] ) && 'preapproval-charged' == $_GET['edd-message'] ) {
		 add_settings_error( 'edds-notices', 'edds-preapproval-charged', __( 'The preapproved payment was successfully charged.', 'easy-digital-downloads' ), 'updated' );
	}
	if ( isset( $_GET['edd-message'] ) && 'preapproval-failed' == $_GET['edd-message'] ) {
		 add_settings_error( 'edds-notices', 'edds-preapproval-charged', __( 'The preapproved payment failed to be charged. View order details for further details.', 'easy-digital-downloads' ), 'error' );
	}
	if ( isset( $_GET['edd-message'] ) && 'preapproval-cancelled' == $_GET['edd-message'] ) {
		 add_settings_error( 'edds-notices', 'edds-preapproval-cancelled', __( 'The preapproved payment was successfully cancelled.', 'easy-digital-downloads' ), 'updated' );
	}

	if( isset( $_GET['edd_gateway_connect_error'], $_GET['edd-message'] ) ) {
		/* translators: %1$s Stripe Connect error message. %2$s Retry URL. */
		echo '<div class="notice notice-error"><p>' . sprintf( __( 'There was an error connecting your Stripe account. Message: %1$s. Please <a href="%2$s">try again</a>.', 'easy-digital-downloads' ), esc_html( urldecode( $_GET['edd-message'] ) ), esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) ) ) . '</p></div>';
		add_filter( 'wp_parse_str', function( $ar ) {
			if( isset( $ar['edd_gateway_connect_error'] ) ) {
				unset( $ar['edd_gateway_connect_error'] );
			}

			if( isset( $ar['edd-message'] ) ) {
				unset( $ar['edd-message'] );
			}
			return $ar;
		});
	}

	settings_errors( 'edds-notices' );
}
add_action( 'admin_notices', 'edds_admin_messages' );

/**
 * Add payment meta item to payments that used an existing card
 *
 * @since 2.6
 * @param $payment_id
 * @return void
 */
function edds_show_existing_card_meta( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	$existing_card = $payment->get_meta( '_edds_used_existing_card' );
	if ( ! empty( $existing_card ) ) {
		?>
		<div class="edd-order-stripe-existing-card edd-admin-box-inside">
			<p>
				<span class="label"><?php _e( 'Used Existing Card:', 'easy-digital-downloads' ); ?></span>&nbsp;
				<span><?php _e( 'Yes', 'easy-digital-downloads' ); ?></span>
			</p>
		</div>
		<?php
	}
}
add_action( 'edd_view_order_details_payment_meta_after', 'edds_show_existing_card_meta', 10, 1 );

/**
 * Handles redirects to the Stripe settings page under certain conditions.
 *
 * @since 2.6.14
 */
function edds_stripe_connect_test_mode_toggle_redirect() {

	// Check for our marker
	if( ! isset( $_POST['edd-test-mode-toggled'] ) ) {
		return;
	}

	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	if ( false === edds_is_gateway_active() ) {
		return;
	}

	/**
	 * Filter the redirect that happens when options are saved and
	 * add query args to redirect to the Stripe settings page
	 * and to show a notice about connecting with Stripe.
	 */
	add_filter( 'wp_redirect', function( $location ) {
		if( false !== strpos( $location, 'page=edd-settings' ) && false !== strpos( $location, 'settings-updated=true' ) ) {
			$location = add_query_arg(
				array(
					'edd-message' => 'connect-to-stripe',
				),
				$location
			);
		}
		return $location;
	} );

}
add_action( 'admin_init', 'edds_stripe_connect_test_mode_toggle_redirect' );

/**
 * Adds a "Refund Charge in Stripe" checkbox to the refund UI.
 *
 * @param \EDD\Orders\Order $order
 *
 * @since 2.8.7
 */
function edds_show_refund_checkbox( \EDD\Orders\Order $order ) {
	if ( 'stripe' !== $order->gateway ) {
		return;
	}
	?>
	<div class="edd-form-group edd-stripe-refund-transaction">
		<div class="edd-form-group__control">
			<input type="checkbox" id="edd-stripe-refund" name="edd-stripe-refund" class="edd-form-group__input" value="1">
			<label for="edd-stripe-refund" class="edd-form-group__label">
				<?php esc_html_e( 'Refund Charge in Stripe', 'easy-digital-downloads' ); ?>
			</label>
		</div>
	</div>
	<?php
}
add_action( 'edd_after_submit_refund_table', 'edds_show_refund_checkbox' );
