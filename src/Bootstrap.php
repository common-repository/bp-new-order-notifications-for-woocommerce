<?php

namespace Bright_New_Notification;

/**
 *
 * @author Niloy <niloy@brightvessel.com>
 *
 * @since 1.0.0
 */
class Bootstrap {

	public function __construct() {
		new Settings();
		//	add_filter( 'woocommerce_coupon_data_tabs', [$this, 'data_tabs'], 10, 1 );
		add_action( 'init', [$this, 'int_event'] );

		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_script'] );
		add_action( 'admin_bar_menu', [$this, 'admin_bar_item'], 100 );
		add_action( 'admin_menu', array( $this, 'add_notification_menu' ) );
		self::add_ajax( 'bp_new_order_notification', [$this, 'ajax_callback'] );
		self::add_ajax( 'bp_new_order_offline_notification', [$this, 'ajax_offline_callback'] );
	}

	public function add_notification_menu() {
		$this->page_id = add_submenu_page(
			'woocommerce',
			__( 'Order Notification', 'bp-new-order-notifications-for-woocommerce' ),
			__( 'Order Notification', 'bp-new-order-notifications-for-woocommerce' ),
			'manage_woocommerce',
			'bp_notification_order',
			array( $this, 'notification_menu_callback' )
		);
	}
	public function notification_menu_callback() {

		?>
		<h2><?php echo __( 'Recent Order List', 'bp-new-order-notifications-for-woocommerce' ); ?></h2>
		<div class="ui toggle checkbox">
			<input id="alert-btn-page-notification" type="checkbox" name="public">
			<label><?php echo __( 'Activate new order alert', 'bp-new-order-notifications-for-woocommerce' ) ?>; </label>
		</div>
		<table id="new-order-table" class="ui celled table" >
			 <thead>
			<tr>
				<th><?php echo __( 'Order ID', 'bp-new-order-notifications-for-woocommerce' ); ?></th>
				<th><?php echo __( 'Order Date', 'bp-new-order-notifications-for-woocommerce' ); ?></th>
				<th><?php echo __( 'Order Status', 'bp-new-order-notifications-for-woocommerce' ); ?></th>
				<th><?php echo __( 'Action', 'bp-new-order-notifications-for-woocommerce' ); ?></th>

			</tr>
			</thead>
		</table>
		<?php

	}
	/**
	 * Add camp to cart by using Ajax
	 *
	 * @return void
	 */
	public function ajax_callback() {
		if ( !DOING_AJAX ) {
			wp_die();
		} // Not Ajax

		// Check for nonce security
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( !wp_verify_nonce( $nonce, 'bp_new_order_notification_nonce' ) ) {
			wp_die( 'oops!' );
		}
		$query = new \WC_Order_Query( array(
			'limit'   => 5,
			'orderby' => 'date',
			'order'   => 'DESC',
			'type'    => 'shop_order',
			'status'  => array( 'wc-processing', 'wc-completed' ),
			'return'  => 'ids',

		) );

		$orders           = $query->get_orders();
		$latest_order_ids = [];

		foreach ( $orders as $key => $order_id ) {
			$order = wc_get_order( $order_id ); // Get an instance of the WC_Order Object

			$date_created_dt = $order->get_date_created(); // Get order date paid WC_DateTime Object

			$timezone        = $date_created_dt->getTimezone(); // Get the timezone
			$date_created_ts = $date_created_dt->getTimestamp(); // Get the timestamp in seconds
			$now_dt          = new \WC_DateTime(); // Get current WC_DateTime object instance

			$now_dt->setTimezone( $timezone ); // Set the same time zone
			$now_ts = $now_dt->getTimestamp(); // Get the current timestamp in seconds

			$refresh_timer   = self::get_option( 'resfresh_timer', 30 ); // time in seconds
			$diff_in_seconds = $now_ts - $date_created_ts; // Get the difference (in seconds)

			if ( $diff_in_seconds < $refresh_timer ) {
				$latest_order_ids[0] = $order_id;
			}
		}

		$title = $this->replace_title_text( self::get_option( 'notifictaion_title', '{greetings} - New Order: #{order_id}' ), $latest_order_ids[0] );

		$data = array(

			'order_edit_link' => get_edit_post_link( $latest_order_ids[0] ),
			'title'           => $title,
			'additional_txt'  => self::get_option( 'notifictaion_textarea', 'Congratulations on the sale.' ),
			'has_order'       => ( !empty( $latest_order_ids ) ) ? 1 : 0,
			'list'            => $this->get_recent_notifictaion_orders(),

		);
		wp_send_json_success( $data );

		wp_die(); // this is required to terminate immediately and return a proper response

	}
	/**
	 * @param $string
	 * @param $price
	 */
	function replace_title_text( $string, $order_id = '' ) {
		$patterns     = array( '{greetings}', '{order_id}' );
		$replacements = array( $this->random_greetings(), $order_id );
		return str_replace( $patterns, $replacements, $string );
	}
	/**
	 * @param $string
	 * @param $price
	 */
	function replace_order_id_text( $string, $hi = '' ) {
		$patterns     = array( '{order_id}' );
		$replacements = array( $this->random_greetings() );
		return str_replace( $patterns, $replacements, $string );
	}

	/**
	 * Get recent order list for table
	 */
	public function get_recent_notifictaion_orders() {
		$order_list = [];

		$query = new \WC_Order_Query( array(
			'limit'   => self::get_option( 'recent_orders', '10' ),
			'orderby' => 'date',
			'type'    => 'shop_order',
			'order'   => 'DESC',
			'status'  => array( 'wc-processing', 'wc-completed' ),
			'return'  => 'ids',

		) );
		foreach ( $query->get_orders() as $key => $id ) {
			$order        = wc_get_order( $id );
			$o_date       = $order->get_date_created();
			$order_list[] = [
				'order_id'     => $id,
				'order_date'   => $o_date->date( 'M d Y - h:i A' ),
				'order_status' => '<span class="recent-noorder">' . $order->get_status() . '<span>',
				'action'       => '<a target="_blank" href="' . get_edit_post_link( $id ) . '" class="ui primary button">Edit Order</a>',
			];
		}
		return array_reverse( $order_list );
	}
	/**
	 * Ajax offline callback for notifications
	 *
	 * @return void
	 */
	public function ajax_offline_callback() {
		if ( !DOING_AJAX ) {
			wp_die();
		} // Not Ajax

		// Check for nonce security
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( !wp_verify_nonce( $nonce, 'bp_new_order_notification_nonce' ) ) {
			wp_die( 'oops!' );
		}

		$data = array(
			'offline_sales' => self::sum_sales_for_date()['orders'],
			'list'          => $this->get_recent_notifictaion_orders(),
		);

		wp_send_json_success( $data );

		wp_die(); // this is required to terminate immediately and return a proper response

	}

	/**
	 * Returns the total of yesterday's sales.
	 *
	 * @param  string     $date Date for sales to sum (i.e. YYYY-MM-DD).
	 * @return floatval
	 */
	public static function sum_sales_for_date() {
		$now_dt = new \WC_DateTime(); // Get current WC_DateTime object instance

		$date        = $now_dt->date( 'Y-m-d' );
		$order_query = new \WC_Order_Query( array( 'date_created' => $date ) );
		$orders      = $order_query->get_orders();
		$total       = 0;

		foreach ( (array) $orders as $order ) {
			$total += $order->get_total();
		}
		$data = [
			'orders' => count( $orders ),
			'total'  => wc_price( $total ),
		];
		return $data;
	}
	/**
	 * Add WP ajax action with ease.
	 *
	 * @param string   $action    Ajax action name
	 * @param callable $callback  Ajax callback function
	 * @param bool     $is_nopriv Whether privileged request or not
	 */
	public static function add_ajax( $action, $callback, $is_nopriv = true, $priority = 10 ) {
		if ( empty( $action ) || !is_callable( $callback ) ) {
			return;
		}

		// Use prefix in case we want to namespace all the ajax requests.
		$prefix = '_';

		add_action(
			'wp_ajax' . $prefix . $action,
			$callback,
			$priority
		);

		if ( $is_nopriv ) {
			add_action(
				'wp_ajax_nopriv' . $prefix . $action,
				$callback,
				$priority
			);
		}
	}

	/**
	 * @return mixed
	 */
	public function random_greetings() {
		# code...
		$gretings = ['Woohoo', 'Howdy', 'Bravo', 'Wow', 'Ahoy', 'Yay', 'Yikes', 'Hooray', 'Whoa', 'Woot', 'Oh joy', 'Yo!', 'Viola!'];
		return $gretings[array_rand( $gretings )];
	}
	/**
	 * @param $option
	 * @param $default
	 */
	public static function get_option( $option = '', $default = null ) {
		$options = get_option( 'bpnon_settings' ); // Attention: Set your unique id of the framework
		return ( isset( $options[$option] ) ) ? $options[$option] : $default;
	}
	/**
	 * @param  $hook
	 * @return null
	 */
	public function enqueue_admin_script( $hook ) {
		$currentScreen = get_current_screen();
		$params        = array(
			'ajax_url'                 => admin_url( 'admin-ajax.php', 'relative' ),
			'ajax_nonce'               => wp_create_nonce( 'bp_new_order_notification_nonce' ),
			'popup_mode'               => self::get_option( 'popup_mode', 'false' ),
			'offline_notfication'      => self::get_option( 'offline_notfication', '1' ),
			'refresh_timer'            => self::get_option( 'resfresh_timer', 30 ) . '000',
			'notifictaion_sound'       => self::get_option( 'notifictaion_sound', 'sound_cha-ching' ),
			'notifictaion_daily_sound' => plugin_dir_url( __DIR__ ) . 'assets/sounds/sound_notification.mp3',

		);
        
		wp_enqueue_style( 'new-order-admin', BPNON_ASSETS . '/css/admin.css', [], BPNON_PLUGIN_VER );

		if ( $currentScreen->id == 'woocommerce_page_bp_notification_order' ) {
			wp_enqueue_style( 'semantic', BPNON_ASSETS . '/css/semantic.min.css', );
			wp_enqueue_style( 'semanticui', BPNON_ASSETS . '/css/dataTables.semanticui.min.css', );

			wp_enqueue_script( 'datatable', BPNON_ASSETS . '/js/jquery.dataTables.min.js', ['jquery'], null, true );
			wp_enqueue_script( 'datatable-ui', BPNON_ASSETS . '/js/dataTables.semanticui.min.js', ['jquery'], null, true );
		}
        
        wp_enqueue_script( 'sweetalert2', BPNON_ASSETS . '/js/sweetalert2.all.min.js', array( 'jquery' ) );
		wp_enqueue_script( 'bp_order_notification', BPNON_ASSETS . '/js/admin.js', array( 'jquery', 'sweetalert2' ), BPNON_PLUGIN_VER );
		wp_localize_script( 'bp_order_notification', 'js_args', $params );

	}

	public function int_event() {
		if ( !wp_next_scheduled( 'bpnon_refresh_timer' ) ) {
			wp_schedule_event( time(), 'every_minute', 'bpnon_refresh_timer' );
		}

		add_action( 'bpnon_refresh_timer', [$this, 'check_for_new_order'] );
	}

	public function check_for_new_order() {

	}

	public function on_activation() {
		if ( !get_option( 'bp_new_order_notification_installed' ) ) {
			update_option( 'bp_new_order_notification_installed', date( "Y/m/d" ) );
		}
		wp_schedule_event( time(), 'every_minute', 'bpnon_refresh_timer' );

		do_action( 'bp_new_order_notification_on_activation' );
	}
	public function onDeactivation() {
		wp_clear_scheduled_hook( 'bpnon_refresh_timer' );
		do_action( 'bp_new_order_notification_on_deactivation' );
	}
	/**
	 * Add menu in admin bar
	 * @return null
	 */
	function admin_bar_item( \WP_Admin_Bar $wp_admin_bar ) {

		if ( !is_admin() ) {
			return;
		}
		$menu_id = 'new-order-notify';
		$wp_admin_bar->add_menu(
			array(
				'id'     => $menu_id,
				'parent' => 'top-secondary',

				'title'  => __( 'New Order Notification', 'bp-custom-order-status' ), //you can use img tag with image link. it will show the image icon Instead of the title.
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => __( 'Enable', 'bp-custom-order-status' ),
				'id'     => 'new-order-notification-enable',
				'href'   => '#',

			)
		);
		$wp_admin_bar->add_menu(
			array(
				'parent' => $menu_id,
				'title'  => __( 'Disable', 'bp-custom-order-status' ),
				'id'     => 'new-order-notification-disable',
				'href'   => '#',

			)
		);

	}

}
