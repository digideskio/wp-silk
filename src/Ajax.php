<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {

	public static $actions = array(
		'add_to_cart'
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
}