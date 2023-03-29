<?php

/**
 * Register payment statuses for preapproval
 *
 * @since 1.6
 * @return void
 */
function edds_register_post_statuses() {
	register_post_status( 'preapproval_pending', array(
		'label'                     => _x( 'Preapproval Pending', 'Pending preapproved payment', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s Payment status count */
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'easy-digital-downloads' )
	) );
	register_post_status( 'preapproval', array(
		'label'                     => _x( 'Preapproved', 'Preapproved payment', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s Payment status count */
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'easy-digital-downloads' )
	) );
	register_post_status( 'cancelled', array(
		'label'                     => _x( 'Cancelled', 'Cancelled payment', 'easy-digital-downloads' ),
		'public'                    => true,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		/* translators: %s Payment status count */
		'label_count'               => _n_noop( 'Active <span class="count">(%s)</span>', 'Active <span class="count">(%s)</span>', 'easy-digital-downloads' )
	) );
}
add_action( 'init',  'edds_register_post_statuses', 110 );

/**
 * Register the statement_descriptor email tag.
 *
 * @since 2.6
 * @return void
 */
function edd_stripe_register_email_tags() {
	$statement_descriptor = edds_get_statement_descriptor();
	if ( ! empty( $statement_descriptor ) ) {
		edd_add_email_tag(
			'stripe_statement_descriptor',
			__( 'Outputs a line stating what charges will appear as on customer\'s credit card statements.', 'easy-digital-downloads' ),
			'edd_stripe_statement_descriptor_template_tag',
			__( 'Statement Descriptor', 'easy-digital-downloads' )
		);
	}
}
add_action( 'edd_add_email_tags', 'edd_stripe_register_email_tags' );

/**
 * Swap the {statement_descriptor} email tag with the string from the option
 *
 * @since 2.6
 * @param $payment_id
 *
 * @return mixed
 */
function edd_stripe_statement_descriptor_template_tag( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	if ( 'stripe' !== $payment->gateway ) {
		return '';
	}

	$statement_descriptor = edds_get_statement_descriptor();
	if ( empty( $statement_descriptor ) ) {
		return '';
	}

	// If you want to filter this, use the %s to define where you want the actual statement descriptor to show in your message.
	$email_tag_output = __( apply_filters( 'edd_stripe_statement_descriptor_email_tag', 'Charges will appear on your card statement as %s' ), 'easy-digital-downloads' );

	return sprintf( $email_tag_output, $statement_descriptor );
}
