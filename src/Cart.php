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
	public static $doing_redirect = false;
	public static $instructions;
	public static $order;

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
				'shippingMethod'		=> Admin::$settings['default_shipping'],
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

		add_action( 'wp', array( $this, 'handle_submit' ) );
		add_action( 'wp', array( $this, 'handle_payment_redirect' ) );
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
		if ( ! Admin::$settings['checkout_page'] )
			return '';
		
		return get_permalink( Admin::$settings['checkout_page'] );
	}

	public static function get_quantity() {
		if ( ! isset ( Cart::$selection->totals ) )
			return 0;

		return Cart::$selection->totals->totalQuantity;
	}

	public static function quantity() {
		echo esc_html( Cart::get_quantity() );
	}

	public static function get_total() {
		if ( ! isset ( Cart::$selection->totals ) )
			return 0;

		return Cart::$selection->totals->grandTotalPrice;
	}

	public static function total() {
		echo esc_html( Cart::get_total() );
	}

	public static function field( $args = '' ) {
		$defaults = array(
			'label'			=> '',
			'name'			=> '',
			'group'			=> 'address',
			'type'			=> 'text',
			'options'		=> false,
			'attributes'	=> false,
			'required'		=> true,
			'prefix'		=> 'owc_checkout_', 
			'class'         => '', 
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		if ( $required )
			$attributes['required'] = 'required';

		$attributes_html = '';
		if ( $attributes ) {
			$attribute_arr = array();
			foreach( $attributes as $key => $val ) {
				$attribute_arr[] = $key . '="' . esc_attr( $val ) . '"';
			}

			$attributes_html = implode( ' ', $attribute_arr );
		}

		$field_name = $group . '[' . $name . ']';
		$id = $prefix . $group . '_' . $name;

		switch ( $type ) {
			case 'select':
				$options_html = '';
				foreach ( $options as $key => $val ) {
					if ( ! isset( $val->name ) )
						continue;
					
					$selected = Cart::field_value( array( 'group' => $group, 'name' => $name ) ) == $key;
					$options_html .= '<option value="' . $key . '"' . ( $selected ? ' selected' : '' ) . '>' . esc_html( $val->name ) . '</option>';
				}
				printf( '<div class="%s"><label for="%s">%s</label><select id="%s" type="%s" name="%s" %s>%s</select></div>', $class, $id, $label, $id, $type, $field_name, $attributes_html, $options_html );
				break;
			default:
				printf( '<div class="%s"><label for="%s">%s</label><input id="%s" type="%s" name="%s" value="%s" %s></div>', $class, $id, $label, $id, $type, $field_name, Cart::field_value( array( 'group' => $group, 'name' => $name ) ), $attributes_html );
				break;
		}
	}

	public static function field_value( $args = '' ) {
		$defaults = array(
			'name'	=> '',
			'group'	=> ''
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		if ( empty( $group ) )
			return Cart::$payment_data[ $name ];

		if ( ! isset( Cart::$payment_data[ $group ] ) || ! isset( Cart::$payment_data[ $group ][ $name ] ) )
			return '';

		return Cart::$payment_data[ $group ][ $name ];
	}

	// Actions

	public function handle_submit() {
		if ( ! isset( $_POST[ OWC_SHOP_PREFIX . '_submit'] ) )
			return;

		if ( ! isset( $_POST[ 'terms'] ) )
			wp_die( __( 'You must agree to the terms and conditions', 'owc' ) );

		$checkout_page = get_permalink( Admin::$settings['checkout_page'] );

		Cart::$payment_data['paymentReturnPage'] = get_permalink( Admin::$settings['receipt_page'] );
		Cart::$payment_data['paymentFailedPage'] = add_query_arg( array( 'silk_failed' => 1 ), $checkout_page );
		Cart::$payment_data['termsAndConditions'] = 1;

		$instructions = Cart::get_payment_instructions();

		if ( isset( $instructions->errors ) ) {
			wp_redirect( add_query_arg( array( 'errors' => array_keys( $instructions ) ), $checkout_page ) );
			exit;
		}

		Cart::$doing_redirect = true;
		Cart::$instructions = $instructions;
	}

	public function handle_payment_redirect() {
		if ( isset( $_GET['silk_failed'] ) ) {
			wp_redirect( add_query_arg( array( 'fatal_error' => 'payment_result_fail' ), $checkout_page ) );
			exit;
		}

		if ( ! is_page( Admin::$settings['receipt_page'] ) )
			return;

		$response = Cart::handle_payment_result();
		Cart::$order = $response;

	}
}