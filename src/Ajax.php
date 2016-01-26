<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Ajax {

	public static $actions = array(
		'add_to_cart',
		
		'update_selection',
		'update_quantity',
		'update_country',
		'update_payment_method',
		'update_address_fields',

		'add_voucher',
		'remove_voucher',

		'newsletter',

		'fetch_personal_information'
	);

	public function __construct() {
		foreach ( Ajax::$actions as $action ) {
			add_action( 'wp_ajax_' . $action, array( $this, $action ) );
			add_action( 'wp_ajax_nopriv_' . $action, array( $this, $action ) );
		}
	}

	public function add_to_cart() {
		$product_id = esc_attr( $_POST['product_id'] );
		$quantity = (int)$_POST['quantity'];

		Cart::add( $product_id, $quantity );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( Cart::$selection );

		wp_send_json_success( Cart::$selection );
	}

	public function update_quantity() {
		$product_id = esc_attr( $_POST['product_id'] );
		$quantity = (int)$_POST['quantity'];

		Cart::update( $product_id, $quantity );

		if ( ! Cart::$selection )
			wp_send_json_error( 'API Error' );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( Cart::$selection );

		$response = array(
			'totals' 	=> Cart::$selection->totals,
			'summary'	=> Template::get_html( 'checkout/summary' ),
			'items'		=> Template::get_html( 'checkout/items' )
		);

		wp_send_json_success( $response );
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

	public function update_payment_method() {
		$payment_method = $_POST['payment_method'];

		Cart::change_payment_method( $payment_method );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( Cart::$selection->errors );

		$response = array(
			'totals' 	=> Cart::$selection->totals,
			'summary'	=> Template::get_html( 'checkout/summary' ),
			'items'		=> Template::get_html( 'checkout/items' ),
			'shippingMethods'	=> Template::get_html( 'checkout/shipping-methods' ),
			'paymentMethods'	=> Template::get_html( 'checkout/payment-methods' )
		);

		wp_send_json_success( $response );
	}

	public function update_country() {
		$country = esc_attr( $_POST['country'] );

		Cart::change_country( $country );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( Cart::$selection->errors );

		$response = array(
			'totals' 	=> Cart::$selection->totals,
			'summary'	=> Template::get_html( 'checkout/summary' ),
			'items'		=> Template::get_html( 'checkout/items' ),
			'shippingMethods'	=> Template::get_html( 'checkout/shipping-methods' ),
			'paymentMethods'	=> Template::get_html( 'checkout/payment-methods' )
		);

		wp_send_json_success( $response );
	}

	public function update_address_fields() {
		$type = esc_attr( $_POST['type'] );

		$response = array(
			'html'	=> Template::get_html( 'checkout/' . $type . '-information' )
		);

		wp_send_json_success( $response );
	}

	public function add_voucher() {
		$voucher = esc_attr( $_POST['voucher'] );

		Cart::add_voucher( $voucher );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( $selection->errors );

		wp_send_json_success( array(
			'summary'	=> Template::get_html( 'checkout/summary' ),
			'voucher'	=> Template::get_html( 'checkout/voucher' )
		) );
	}

	public function remove_voucher() {
		$voucher = esc_attr( $_POST['voucher'] );

		Cart::remove_voucher( $voucher );

		if ( isset( Cart::$selection->errors ) )
			wp_send_json_error( $selection->errors );

		wp_send_json_success( array(
			'summary'	=> Template::get_html( 'checkout/summary' ),
			'voucher'	=> Template::get_html( 'checkout/voucher' )
		) );
	}

	public function newsletter() {
		$email = sanitize_email( $_POST['email'] );

		$response = Api::post( 'customers/' . $email . '/newsletter-subscription' );

		wp_send_json_success( $response );
	}

	public function fetch_personal_information() {
		$personal_number = $_POST['personal_number'];

		$response = Api::post( 'payment-methods/lookup/address-search', array(
			'paymentMethod'		=> 'lookup',
			'identityNumber'	=> $personal_number,
			'country'			=> 'SE'
		) );

		wp_send_json_success( $response );
	}
}