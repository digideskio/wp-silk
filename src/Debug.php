<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Debug {

	public function __construct() {
		add_action( 'wp', array( $this, 'wp' ) );
	}

	public function wp() {
		#Cart::add( '1-1' );
		/*Cart::set_payment_details( array(
			'paymentReturnPage' => 'http://wp.dev/wp/?success=1',
			'paymentFailedPage' => 'http://wp.dev/wp/?failed=1',
			'termsAndConditions' => true,
			'address' => array(
				'email'			=> 'test@test.com',
				'phoneNumber'	=> '123456798',
				'firstName'		=> 'Test',
				'lastName'		=> 'Testing',
				'address1'		=> 'asdfasd',
				'address2'		=> '',
				'zipCode'		=> '12345',
				'city'			=> 'Stockholm',
				'state'			=> 'ca',
				'country'		=> 'us'
			)
		) );*/

		#var_dump(Cart::$payment_data);
		#var_dump(Cart::$selection);
		/*if ( isset( $_GET['success'] ) ) {
			$result = Cart::handle_payment_result();
			var_dump($result);
		} else {
			$instructions = Cart::get_payment_instructions();
			echo $instructions->formHtml;
		}*/

		#Sync::run();
	}
}