<?php
/**
 * Main plugin class.
 *
 * @package EDD_Stripe
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Stripe class.
 *
 * @since 2.6.0
 */
class EDD_Stripe {

	/**
	 * Singleton instance.
	 *
	 * @since 2.6.0
	 * @var EDD_Stripe
	 */
	private static $instance;

	/**
	 * Rate limiting component.
	 *
	 * @since 2.6.19
	 * @var EDD_Stripe_Rate_Limiting
	 */
	public $rate_limiting;

	/**
	 * Instantiates or returns the singleton instance.
	 *
	 * @since 2.6.0
	 *
	 * @return EDD_Stripe
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Stripe ) ) {
			self::$instance = new EDD_Stripe;
			self::$instance->includes();
			self::$instance->setup_classes();
			self::$instance->actions();
			self::$instance->filters();

			if ( true === edds_is_pro() ) {
				if ( class_exists( '\\EDD\\Extensions\\ExtensionRegistry' ) ) {
					add_action( 'edd_extension_license_init', function( \EDD\Extensions\ExtensionRegistry $registry ) {
						$registry->addExtension(
							EDD_STRIPE_PLUGIN_FILE,
							'Stripe Pro Payment Gateway',
							167,
							EDD_STRIPE_VERSION,
							'stripe_license_key'
						);
					} );
				} elseif ( class_exists( 'EDD_License' ) ) {
					new EDD_License(
						EDD_STRIPE_PLUGIN_FILE,
						'Stripe Pro Payment Gateway',
						EDD_STRIPE_VERSION,
						'Easy Digital Downloads',
						'stripe_license_key',
						null,
						167
					);
				}
			}
		}

		return self::$instance;
	}

	/**
	 * Includes files.
	 *
	 * @since 2.6.0
	 */
	private function includes() {
		if ( ! class_exists( 'Stripe\Stripe' ) ) {
			require_once EDDS_PLUGIN_DIR . '/vendor/autoload.php';
		}

		require_once EDDS_PLUGIN_DIR . '/includes/class-stripe-api.php';

		// We need this one to load early so we can use it in the upcoming includes.
		require_once EDDS_PLUGIN_DIR . '/includes/elements/functions.php';
		$elements_mode = edds_get_elements_mode();

		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-api-unmet-requirements.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-attribute-not-found.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-object-not-found.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-gateway-exception.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/interface-static-registry.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/class-registry.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/modal.php';

		require_once EDDS_PLUGIN_DIR . '/includes/functions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/deprecated.php';
		require_once EDDS_PLUGIN_DIR . '/includes/compat.php';
		require_once EDDS_PLUGIN_DIR . '/includes/i18n.php';
		require_once EDDS_PLUGIN_DIR . '/includes/emails.php';
		require_once EDDS_PLUGIN_DIR . '/includes/payment-receipt.php';
		require_once EDDS_PLUGIN_DIR . '/includes/card-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/gateway-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/gateway-filters.php';

		// Payment Actions, separated by elements type.
		require_once EDDS_PLUGIN_DIR . '/includes/payment-actions/functions.php';
		switch ( $elements_mode ) {
			case 'card-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/payment-actions/card-elements-actions.php';
				break;

			case 'payment-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/payment-actions/payment-elements-actions.php';
				break;
		}

		require_once EDDS_PLUGIN_DIR . '/includes/webhooks.php';
		require_once EDDS_PLUGIN_DIR . '/includes/scripts.php';
		require_once EDDS_PLUGIN_DIR . '/includes/template-functions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/class-edd-stripe-rate-limiting.php';

		// Load Apple Pay functions.
		require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/apple-pay.php';


		// Stripe Elements, separated by elements type.
		switch ( $elements_mode ) {
			case 'card-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/elements/card-elements.php';
				require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/index.php';
				break;

			case 'payment-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/elements/payment-elements.php';
				break;
		}

		// Payment Methods.
		require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/buy-now/index.php';

		if ( is_admin() ) {
			require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/notices.php';

			require_once EDDS_PLUGIN_DIR . '/includes/admin/admin-actions.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/admin-filters.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/settings/stripe-connect.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/settings.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/upgrade-functions.php';
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/wp-cli.php';
		}

		if ( defined( 'EDD_ALL_ACCESS_VER' ) && EDD_ALL_ACCESS_VER ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/edd-all-access.php';
		}

		if ( class_exists( 'EDD_Auto_Register' ) ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/edd-auto-register.php';
		}

		// Pro.
		$pro = EDDS_PLUGIN_DIR . '/includes/pro/index.php';

		if ( file_exists( $pro ) ) {
			require_once $pro;
		}
	}

	/**
	 * Applies various hooks.
	 *
	 * @since 2.6.0
	 */
	private function actions() {
		add_action( 'init', array( self::$instance, 'load_textdomain' ) );
		add_action( 'admin_init', array( self::$instance, 'database_upgrades' ) );
	}

	/**
	 * Applies various filters.
	 *
	 * @since 2.6.0
	 */
	private function filters() {
		add_filter( 'edd_payment_gateways', array( self::$instance, 'register_gateway' ) );
	}

	/**
	 * Configures core components.
	 *
	 * @since 2.6.19
	 */
	private function setup_classes() {
		$this->rate_limiting = new EDD_Stripe_Rate_Limiting();
	}

	/**
	 * Performs database upgrades.
	 *
	 * @since 2.6.0
	 */
	public function database_upgrades() {
		$did_upgrade = false;
		$version     = get_option( 'edds_stripe_version' );

		if ( ! $version || version_compare( $version, EDD_STRIPE_VERSION, '<' ) ) {
			$did_upgrade = true;

			switch ( EDD_STRIPE_VERSION ) {
				case '2.5.8' :
					edd_update_option( 'stripe_checkout_remember', true );
					break;
				case '2.8.0':
					edd_update_option( 'stripe_allow_prepaid', true );
					break;
			}

		}

		if ( $did_upgrade ) {
			update_option( 'edds_stripe_version', EDD_STRIPE_VERSION );
		}
	}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 2.6.0
	 */
	public function load_textdomain() {
		// Set filter for language directory
		$lang_dir = EDDS_PLUGIN_DIR . '/languages/';

		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), 'edds' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'edds', $locale );

		// Setup paths to current locale file
		$mofile_local   = $lang_dir . $mofile;
		$mofile_global  = WP_LANG_DIR . '/edd-stripe/' . $mofile;

		// Look in global /wp-content/languages/edd-stripe/ folder
		if( file_exists( $mofile_global ) ) {
			load_textdomain( 'edds', $mofile_global );

		// Look in local /wp-content/plugins/edd-stripe/languages/ folder
		} elseif( file_exists( $mofile_local ) ) {
			load_textdomain( 'edds', $mofile_local );

		} else {
			// Load the default language files
			load_plugin_textdomain( 'edds', false, $lang_dir );
		}
	}

	/**
	 * Registers the gateway.
	 *
	 * @param array $gateways Payment gateways.
	 * @return array
	 */
	public function register_gateway( $gateways ) {
		// Format: ID => Name.
		$gateways['stripe'] = array(
			'admin_label'    => 'Stripe',
			'checkout_label' => __( 'Credit Card', 'easy-digital-downloads' ),
			'supports'       => array(
				'buy_now',
			),
			'icons'          => array(
				'mastercard',
				'visa',
				'discover',
				'americanexpress',
			),
		);

		return $gateways;
	}

}
