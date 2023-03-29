<?php
/**
 * Payment receipt.
 *
 * @package EDD_Stripe
 * @since   2.7.0
 */

/**
 * Output a Payment authorization form in the Payment Receipt.
 *
 * @param WP_Post $payment Payment.
 */
function edds_payment_receipt_authorize_payment_form( $payment ) {
	// EDD 3.0 compat.
	if ( is_a( $payment, 'WP_Post' ) ) {
		$payment = edd_get_payment( $payment->ID );
	}

	$customer_id = $payment->get_meta( '_edds_stripe_customer_id' );
	$payment_intent_id = $payment->get_meta( '_edds_stripe_payment_intent_id' );

	if ( empty( $customer_id ) || empty( $payment_intent_id ) ) {
		return false;
	}

	if ( 'preapproval_pending' !== $payment->status ) {
		return false;
	}

	$payment_intent = edds_api_request( 'PaymentIntent', 'retrieve', $payment_intent_id );

	// Enqueue core scripts.
	add_filter( 'edd_is_checkout', '__return_true' );

	edd_load_scripts();

	remove_filter( 'edd_is_checkout', '__return_true' );

	edd_stripe_js( true );
	edd_stripe_css( true );
?>

<form
	id="edds-update-payment-method"
	data-payment-intent="<?php echo esc_attr( $payment_intent->id ); ?>"
	<?php if ( isset( $payment_intent->last_payment_error ) && isset( $payment_intent->last_payment_error->payment_method ) ) : ?>
	data-payment-method="<?php echo esc_attr( $payment_intent->last_payment_error->payment_method->id ); ?>"
	<?php endif; ?>
>
	<h3>Authorize Payment</h3>
	<p><?php esc_html_e( 'To finalize your preapproved purchase, please confirm your payment method.', 'easy-digital-downloads' ); ?></p>

	<div id="edd_checkout_form_wrap">
		<?php
		/** This filter is documented in easydigitaldownloads/includes/checkout/template.php */
		do_action( 'edd_stripe_cc_form' );
		?>

		<p>
			<input
				id="edds-update-payment-method-submit"
				type="submit"
				data-loading="<?php echo esc_attr( 'Please Wait…', 'edds' ); ?>"
				data-submit="<?php echo esc_attr( 'Authorize Payment', 'edds' ); ?>"
				value="<?php echo esc_attr( 'Authorize Payment', 'edds' ); ?>"
				class="button edd-button"
			/>
		</p>

		<div id="edds-update-payment-method-errors"></div>

		<?php
		wp_nonce_field(
			'edds-complete-payment-authorization',
			'edds-complete-payment-authorization'
		);
		?>

	</div>
</form>

<?php
}
add_action( 'edd_payment_receipt_after_table', 'edds_payment_receipt_authorize_payment_form' );
