<?php

/**
 * Given a Payment ID, extract the transaction ID from Stripe
 *
 * @param  string $payment_id       Payment ID
 * @return string                   Transaction ID
 */
function edds_get_payment_transaction_id( $payment_id ) {

	$txn_id = '';
	$notes  = edd_get_payment_notes( $payment_id );

	foreach ( $notes as $note ) {
		if ( preg_match( '/^Stripe Charge ID: ([^\s]+)/', $note->comment_content, $match ) ) {
			$txn_id = $match[1];
			continue;
		}
	}

	return apply_filters( 'edds_set_payment_transaction_id', $txn_id, $payment_id );
}
add_filter( 'edd_get_payment_transaction_id-stripe', 'edds_get_payment_transaction_id', 10, 1 );

/**
 * Given a transaction ID, generate a link to the Stripe transaction ID details
 *
 * @since  1.9.1
 * @param  string $transaction_id The Transaction ID
 * @param  int    $payment_id     The payment ID for this transaction
 * @return string                 A link to the Stripe transaction details
 */
function edd_stripe_link_transaction_id( $transaction_id, $payment_id ) {

	$test = edd_get_payment_meta( $payment_id, '_edd_payment_mode' ) === 'test' ? 'test/' : '';
	$status = edd_get_payment_status( $payment_id );

	if ( 'preapproval' === $status ) {
		$url = '<a href="https://dashboard.stripe.com/' . esc_attr( $test ) . 'setup_intents/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
	} else {
		$url = '<a href="https://dashboard.stripe.com/' .  esc_attr( $test ) . 'payments/' . esc_attr( $transaction_id ) . '" target="_blank">' . esc_html( $transaction_id ) . '</a>';
	}
	return apply_filters( 'edd_stripe_link_payment_details_transaction_id', $url );

}
add_filter( 'edd_payment_details_transaction_id-stripe', 'edd_stripe_link_transaction_id', 10, 2 );

/**
 * Show the Process / Cancel buttons for preapproved payments
 *
 * @since 1.6
 * @return string
 */
function edds_payments_column_data( $value, $payment_id, $column_name ) {
	if ( 'status' !== $column_name ) {
		return $value;
	}

	$status = edd_get_payment_status( $payment_id );
	if ( ! in_array( $status, array( 'preapproval', 'preapproval_pending' ), true ) ) {
		return $value;
	}

	$customer_id = edd_get_order_meta( $payment_id, '_edds_stripe_customer_id', true );

	if ( empty( $customer_id ) ) {
		return $value;
	}

	$nonce = wp_create_nonce( 'edds-process-preapproval' );

	$base_args        = array(
		'post_type'  => 'download',
		'page'       => 'edd-payment-history',
		'payment_id' => urlencode( $payment_id ),
		'nonce'      => urlencode( $nonce ),
	);
	$preapproval_args = array(
		'edd-action' => 'charge_stripe_preapproval',
	);
	$cancel_args      = array(
		'preapproval_key' => urlencode( $customer_id ),
		'edd-action'      => 'cancel_stripe_preapproval',
	);

	$actions = array(
		sprintf(
			'<a href="%s">%s</a>',
			esc_url(
				add_query_arg(
					array_merge( $base_args, $preapproval_args ),
					admin_url( 'edit.php' )
				)
			),
			esc_html__( 'Process', 'easy-digital-downloads' )
		),
		sprintf(
			'<span class="cancel-preapproval"><a href="%s">%s</a></span>',
			esc_url(
				add_query_arg(
					array_merge( $base_args, $cancel_args ),
					admin_url( 'edit.php' )
				)
			),
			esc_html__( 'Cancel', 'easy-digital-downloads' )
		),
	);

	$value .= '<p class="row-actions">';
	$value .= implode( ' | ', $actions );
	$value .= '</p>';

	return $value;
}
add_filter( 'edd_payments_table_column', 'edds_payments_column_data', 20, 3 );
