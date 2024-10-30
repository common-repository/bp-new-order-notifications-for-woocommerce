<?php
namespace Bright_New_Notification;

class Settings {

	public function __construct() {

		add_action( 'admin_menu', array( $this, 'bp_admin_menu' ) );
		add_filter( "plugin_row_meta", [$this, 'pluginMetaLinks'], 20, 2 );
		$this->pluginOptions();
	}

	public function pluginOptions() {

		// Set a unique slug-like ID
		$prefix = 'bpnon_settings';

		// Create options
		\CSF::createOptions( $prefix, array(
			'menu_title'      => 'Order Notification Settings',
			'menu_slug'       => 'bpnon-setting',
			'framework_title' => 'New Order Notification Settings ',
			'menu_type'       => 'submenu',
			'menu_parent'     => 'brightplugins',
			'nav'             => 'inline',
			'theme'           => 'light',
			'show_footer'     => false,
			'show_bar_menu'   => false,
		) );

		// Create a section
		\CSF::createSection( $prefix, array(
			'title'  => 'General Settings',
			'fields' => array(
				// A text field
				array(
					'id'      => 'resfresh_timer',
					'type'    => 'number',
					'unit'    => 'second',
					'default' => 30,
					'title'   => __( 'Refresh Timer', 'bp-new-order-notifications-for-woocommerce' ),
					'desc'    => __( 'Check new orders after second.', 'bp-new-order-notifications-for-woocommerce' ),
				),
				array(
					'id'      => 'notifictaion_sound',
					'type'    => 'radio',
					'title'   => 'Radio',
					'options' => array(
						'cash_reg'           => 'Cash Reg',
						'sound_cha-ching'    => 'Cha Ching',
						'sound_notification' => 'Notification',
						'custom'             => 'Custom',
					),
					'default' => 'sound_cha-ching',
					'inline'  => true,
				),
				array(
					'id'         => 'notifictaion_sound_custom',
					'type'       => 'media',
					'title'      => 'Media',
					'library'    => 'audio',
					'dependency' => array( 'notifictaion_sound', '==', 'custom' ),
				),
				array(
					'id'      => 'offline_notfication',
					'type'    => 'switcher',
					'title'   => 'Switcher',
					'desc'    => __( 'Display Daily total sales when you enable notification', 'bp-new-order-notifications-for-woocommerce' ),
					'default' => true,
				),
				array(
					'id'      => 'popup_mode',
					'type'    => 'select',
					'title'   => 'Preview notification',
					'options' => array(
						'true'  => 'Toast',
						'false' => 'Popup',

					),
					'desc'    => __( 'Toast: Display Notification at bottom right corner <br>
					Popup: Show Notification as Center Popup' ),
					'default' => true,
				),
				array(
					'id'      => 'recent_orders',
					'type'    => 'number',
					'default' => 10,
					'title'   => __( 'Recent Orders', 'bp-new-order-notifications-for-woocommerce' ),
					'desc'    => __( 'Set how many orders will show into the order notification table', 'bp-new-order-notifications-for-woocommerce' ),
				),

			),
		) );
		// Create a section
		\CSF::createSection( $prefix, array(
			'title'  => 'Notification Level',
			'fields' => array(

				array(
					'id'          => 'notifictaion_title',
					'type'        => 'text',
					'placeholder' => '{greetings} - New Order: #{order_id}',
					'default'     => '{greetings} - New Order: #{order_id}',
					'title'       => __( 'Notification Title', 'bp-new-order-notifications-for-woocommerce' ),
					'desc'        => __( 'Set Title for Notifications', 'bp-new-order-notifications-for-woocommerce' ),
				),
				array(
					'id'      => 'notifictaion_textarea',
					'type'    => 'textarea',
					'title'   => __( 'Notification Additional Text', 'bp-new-order-notifications-for-woocommerce' ),
					'default' => 'Congratulations on the sale.',
				),

			),
		) );

	}

	/**
	 * Add links to plugin's description in plugins table
	 *
	 * @param  array   $links Initial list of links.
	 * @param  string  $file  Basename of current plugin.
	 * @return array
	 */
	public function pluginMetaLinks( $links, $file ) {
		if ( $file !== BPNON_PLUGIN_BASE ) {
			return $links;
		}
		$support_link = '<a style="color:red;" target="_blank" href="https://brightplugins.com/support/">' . __( 'Support', 'bp-custom-order-status' ) . '</a>';

		$links[] = $support_link;

		return $links;
	}

	public function bp_admin_menu() {

		add_menu_page( 'Bright Plugins', 'Bright Plugins', '#manage_options', 'brightplugins', null, plugin_dir_url( __DIR__ ) . 'assets/img/bp-logo-icon.png', 60 );

		//do_action( 'bp_sub_menu' );
	}

}
