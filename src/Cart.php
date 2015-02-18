<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Cart {

	/*
	|-----------------------------------------------------------
	| PROPERTIES
	|-----------------------------------------------------------
	*/

	public static $selection_id;
	public static $selection;
	public static $payment_data;

	public function __construct( $selection_id = false ) {
		// Get or create a selection
		if ( ! $selection_id ) {
			$selection_id = Cart::get_session( 'selection_id' );
		}

		if ( $selection_id ) {
			$selection = Api::get( 'selections/' . $selection_id );
		} else {
			$selection = Api::post( 'selections', array(
				'country'	=> Store::$country
			) );

			if ( $selection ) {
				$selection_id = $selection->selection;
				
				Cart::set_session( 'selection_id', $selection_id );
			}
		}

		Cart::$selection_id = $selection_id;
		Cart::$selection = $selection;

		// Get payment data from session
		Cart::$payment_data = Cart::get_session( 'payment_data' );

		if ( ! Cart::$payment_data ) {
			Cart::$payment_data = array(
				'paymentMethod'			=> Admin::$settings['default_payment'],
				'paymentReturnPage'		=> Admin::$settings['return_page'],
				'paymentFailedPage'		=> Admin::$settings['failed_page'],
				'termsAndConditions'	=> false,
				'ipAddress'				=> $_SERVER['REMOTE_ADDR'],
				'address' => array(
					'email'			=> '',
					'phoneNumber'	=> '',
					'firstName'		=> '',
					'lastName'		=> '',
					'address1'		=> '',
					'address2'		=> '',
					'zipCode'		=> '',
					'city'			=> '',
					'state'			=> '',
					'country'		=> ''
				)
			);

			Cart::set_session( 'payment_data', Cart::$payment_data );
		}
	}

	public static function add( $product_id ) {
		Cart::$selection = Api::post( 'selections/' . Cart::$selection_id . '/items/' . $product_id );
	}

	public static function remove( $product_id ) {
		Cart::$selection = Api::delete( 'selections/' . Cart::$selection_id . '/items/' . $product_id );
	}

	public static function update( $product_id, $quantity ) {
		Cart::$selection = Api::put( 'selections/' . Cart::$selection_id . '/items/' . $product_id . '/quantity/' . $quantity );
	}

	public static function set_payment_details( $data = array() ) {
		Cart::$payment_data = array_merge( Cart::$payment_data, $data );

		Cart::set_session( 'payment_data', Cart::$payment_data );
	}

	public static function get_payment_instructions() {
		return Api::post( 'selections/' . Cart::$selection_id . '/payment', Cart::$payment_data );
	}

	public static function handle_payment_result() {
		$response = Api::post( 'selections/' . Cart::$selection_id . '/payment-result', array( 'paymentMethodFields' => $_REQUEST ) );

		if ( ! isset( $response->errors ) )
			Cart::clear_session();

		return $response;
	}

	public static function set_session( $key, $data ) {
		if ( ! session_id() )
			session_start();

		$_SESSION[ $key ] = $data;
	}

	public static function get_session( $key ) {
		if ( ! session_id() )
			session_start();

		return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : false;
	}

	public static function clear_session() {
		if ( ! session_id() )
			session_start();

		unset( $_SESSION['selection_id'] );
		unset( $_SESSION['payment_data'] );
	}

	// Template functions

	public static function get_checkout_url() {
		return get_permalink( Admin::$settings['checkout_page'] );
	}

	public static function get_quantity() {
		return Cart::$selection->totals->totalQuantity;
	}

	public static function quantity() {
		echo esc_html( Cart::get_quantity() );
	}

	public static function get_total() {
		return Cart::$selection->totals->grandTotalPrice;
	}

	public static function total() {
		echo esc_html( Cart::get_total() );
	}

	public static function field( $args = '' ) {
		$defaults = array(
			'label'			=> '',
			'group'			=> 'address',
			'type'			=> 'text',
			'options'		=> false,
			'attributes'	=> false,
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		$attribute_string = '';
		if ( $attributes ) {
			$attribute_string = implode( ' ', array_)
		}
	}

}