<?php

namespace EDD\Blocks\Admin\Scripts;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\localize' );
/**
 * Adds a custom variable to the JS to allow a user in the block editor
 * to preview sensitive data.
 *
 * @since 2.0
 * @return void
 */
function localize() {

	$user = wp_get_current_user();

	wp_localize_script(
		'wp-block-editor',
		'EDDBlocks',
		array(
			'current_user'  => md5( $user->user_email ),
			'all_access'    => function_exists( 'edd_all_access' ),
			'recurring'     => function_exists( 'EDD_Recurring' ),
			'is_pro'        => edd_is_pro(),
			'no_redownload' => edd_no_redownload(),
		)
	);
}

/**
 * Makes sure the payment icons show on the checkout block in the editor.
 *
 * @since 2.0
 */
add_action( 'admin_print_footer_scripts', '\edd_print_payment_icons_on_checkout' );

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\add_edd_styles_block_editor' );
/**
 * If the EDD styles are registered, load them for the block editor.
 *
 * @since 2.0
 * @return void
 */
function add_edd_styles_block_editor() {
	if ( wp_style_is( 'edd-styles', 'registered' ) ) {
		wp_enqueue_style( 'edd-styles' );
	}
}
