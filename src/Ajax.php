<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {

	public static $actions = array(
		'add_to_cart',
		'update_selection'
	);

	public function __construct() {
		foreach ( Ajax::$actions as $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, $action ) );
			add_action( 'wp_ajax_' . $action, array( $this, $action ) );
		}
	}

	public function add_to_cart() {
		$product_id = esc_attr( $_POST['product_id'] );

		Cart::add( $product_id );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( Cart::$selection );

		wp_send_json_success( Cart::$selection );
	}

	public function update_selection() {
		if ( isset( $_POST['parse_data'] ) )
			parse_str( $_POST['data'], $data );
		else
			$data = $_POST['data'];

		$payment_data = Cart::$payment_data;

		foreach( $data as $key => $val ) {
			$payment_data[ $key ] = $val;
		}

		Cart::$payment_data = $payment_data;
		Cart::set_session( 'payment_data', Cart::$payment_data );

		wp_send_json_success( Cart::$payment_data );
	}
}