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
	public static $errors = false;

	public function __construct( $selection_id = false ) {
		// Get or create a selection
		if ( ! $selection_id ) {
			$selection_id = Cart::get_session( 'selection_id' );
		}

		if ( Cart::get_session( 'pricelist' ) )
			Store::$pricelist = Cart::get_session( 'pricelist' );

		if ( $selection_id ) {
			$selection = Api::get( 'selections/' . $selection_id );
		} else {
			$selection = Api::post( 'selections', array(
				'country'	=> Store::$country,
				'pricelist'	=> Store::$pricelist
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

		// Get order receipt from session
		Cart::$order = Cart::get_session( 'order' );

		add_action( 'wp', array( $this, 'handle_submit' ) );
		add_action( 'wp', array( $this, 'handle_payment_redirect' ) );
	}

	// Items
	public static function add( $product_id, $quantity = 1 ) {
		Cart::$selection = Api::post( 'selections/' . Cart::$selection_id . '/items/' . $product_id . '/quantity/' . $quantity );
	}

	public static function remove( $product_id, $quantity = 0 ) {
		Cart::$selection = Api::delete( 'selections/' . Cart::$selection_id . '/items/' . $product_id . ( $quantity > 0 ? '/quantity/' . $quantity : '' ) );
	}

	public static function update( $product_id, $quantity ) {
		Cart::$selection = Api::put( 'selections/' . Cart::$selection_id . '/items/' . $product_id . '/quantity/' . $quantity );
	}

	// Vouchers
	public static function add_voucher( $voucher ) {
		Cart::$selection =  Api::post( 'selections/' . Cart::$selection_id . '/vouchers/' . $voucher );
	}

	public static function remove_voucher( $voucher ) {
		Cart::$selection =  Api::delete( 'selections/' . Cart::$selection_id . '/vouchers/' . $voucher );
	}

	// Country
	public static function change_country( $country, $group = 'address' ) {
		if ( Cart::$payment_data[ $group ]['country'] == $country )
			return;

		Cart::$payment_data[ $group ]['country'] = $country;
		Cart::set_payment_details( Cart::$payment_data );

		Cart::$selection = Api::put( 'selections/' . Cart::$selection_id . '/countries/' . $country );
	
		$pricelist = get_option( OWC_SHOP_PREFIX . '_pricelists' );

		$country_upper = strtoupper( $country );

		foreach ( $pricelist as $value ) {
			if ( isset( $value->countries ) && in_array( $country_upper, $value->countries ) ) {
				Store::$pricelist = $value->pricelist;
			}
		}

		Cart::set_session( 'country', $country );
		Cart::set_session( 'pricelist', Store::$pricelist );
	}

	// Payment
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

	// Session
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

	public static function get_totals( $key ) {
		if ( ! isset ( Cart::$selection->totals ) )
			return 0;

		return Cart::$selection->totals->{$key};
	}

	public static function totals( $key ) {
		echo esc_html( Cart::get_totals( $key ) );
	}

	public static function get_total() {
		return Cart::get_totals( 'grandTotalPrice' );
	}

	public static function total() {
		echo esc_html( Cart::get_total() );
	}

	public static function field( $args = '' ) {
		$defaults = array(
			'label'			=> '',
			'name'			=> '',
			'default_value'	=> '',
			'group'			=> 'address',
			'type'			=> 'text',
			'options'		=> false,
			'attributes'	=> false,
			'required'		=> true,
			'prefix'		=> 'owc_checkout_',
			'class'         => '',
			'error_class'	=> 'error',
			'error_before'	=> '<small class="error-txt">',
			'error_after'	=> '</small>',
			'error_text'	=> __( 'Required field', 'owc' )
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		if ( $required )
			$attributes['required'] = 'required';

		$errors = Cart::get_errors();
		$has_error = false;
		if ( isset( $errors[$group] ) && isset( $errors[$group][$name] ) )
			$has_error = true;

		$error_html = '';
		if ( $has_error ) {
			$class .= " $error_class";
			$error_html = $error_before . $error_text . $error_after;
		}


		$attributes_html = '';
		if ( $attributes ) {
			$attribute_arr = array();
			foreach( $attributes as $key => $val ) {
				$attribute_arr[] = $key . '="' . esc_attr( $val ) . '"';
			}

			$attributes_html = implode( ' ', $attribute_arr );
		}

		$field_value = Cart::field_value( array( 'group' => $group, 'name' => $name ) );
		
		if ( empty( $field_value ) && ! empty( $default_value ) )
			$field_value = $default_value;

		$field_name = $group . '[' . $name . ']';
		$id = $prefix . $group . '_' . $name;

		switch ( $type ) {
			case 'select':
				$options_html = '';
				foreach ( $options as $key => $val ) {
					if ( ! isset( $val->name ) )
						continue;
					
					$selected = ( $field_value == $key );
					$options_html .= '<option value="' . $key . '"' . ( $selected ? ' selected' : '' ) . '>' . esc_html( $val->name ) . '</option>';
				}
				printf( '<div class="%s"><label for="%s">%s</label><select id="%s" type="%s" name="%s" %s>%s</select>%s</div>', $class, $id, $label, $id, $type, $field_name, $attributes_html, $options_html, $error_html );
				break;
			default:
				printf( '<div class="%s"><label for="%s">%s</label><input id="%s" type="%s" name="%s" value="%s" %s>%s</div>', $class, $id, $label, $id, $type, $field_name, Cart::field_value( array( 'group' => $group, 'name' => $name ) ), $attributes_html, $error_html );
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

	public static function get_errors() {
		if ( ! Cart::$errors )
			Cart::group_errors();

		return Cart::$errors;
	}

	private static function group_errors() {
		if ( ! isset( $_GET['errors'] ) )
			return array();
		
		$errors = $_GET['errors'];
		$errors_map = array();
		
		foreach( $errors as $error ) {
			$map = explode( ':', $error );

			if ( isset( $map[1] ) ) {
				$group = $map[0];
				$field = $map[1];
			} else {
				$group = 'no_group';
				$field = $error;
			}

			if ( ! isset( $errors_map[ $group ] ) )
				$errors_map[ $group ] = array();

			$errors_map[ $group ][ $field ] = $field;
		}

		Cart::$errors = $errors_map;
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
			wp_redirect( add_query_arg( array( 'errors' => array_keys( (array)$instructions->errors ) ), $checkout_page ) );
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

		do_action( 'owc_silk_before_handle_payment_redirect' );

		//if ( isset( $_REQUEST[ 'trans' ] ) ) {
			$response = Cart::handle_payment_result();
			Cart::$order = $response;
			Cart::set_session( 'order', Cart::$order );
		//}
	}
}