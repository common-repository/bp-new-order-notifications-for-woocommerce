<?php
/**
 * Plugin Name: ChaChing - New Order Notifications for WooCommerce
 * Plugin URI: https://brightplugins.com
 * Description: Produces a popup notification for every new order received with a unique ChaChing sound.
 * Version: 0.1
 * Author: Bright Plugins
 * Requires PHP: 7.2.0
 * Requires at least: 4.9
 * Tested up to: 5.9
 * WC tested up to:6.2
 * WC requires at least: 3.9
 * Author URI: https://brightplugins.com
 * Text Domain: bp-new-order-notifications-for-woocommerce
 * Domain Path: /languages
 */


defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

// Define Values.
define( 'BPNON_PLUGIN_DIR', __DIR__ );
define( 'BPNON_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' );
define( 'BPNON_PLUGIN_FILE', __FILE__ );
define( 'BPNON_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'BPNON_PLUGIN_VER', '0.1' );
define( 'BPNON_URL', plugins_url( '', BPNON_PLUGIN_FILE ) );
define( 'BPNON_ASSETS', BPNON_URL . '/assets' );

use Bright_New_Notification\Bootstrap;

// Check if WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) )
	, true )
) {
	add_action( 'woocommerce_loaded', function () {

		$bootstrap = new Bootstrap();
		register_activation_hook( __FILE__, array( $bootstrap, 'on_activation' ) );
		register_deactivation_hook( __FILE__, [$bootstrap, 'onDeactivation'] );

	} );
} else {
	add_action( 'admin_notices', function () {
		$class   = 'notice notice-error';
		$message = __( 'Oops! looks like WooCommerce is disabled. Please, enable it in order to use BP Custom Order Status for WooCommerce.', 'bp-new-order-notifications-for-woocommerce' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	} );
}


