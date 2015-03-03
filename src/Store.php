<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Store {

	/*
	|-----------------------------------------------------------
	| PROPERTIES
	|-----------------------------------------------------------
	*/

	public static $market = 1;
	public static $country = 1;
	public static $pricelist = 1;
	public static $countries = array();

	public function __construct( $props ) {
		foreach ( $props as $key => $value ) {
			if ( isset( Store::${$key} ) ) {
				Store::${$key} = $value;
			}
		}

		Store::$countries = get_option( OWC_SHOP_PREFIX . '_countries' );

		if ( ! Store::$countries ) {
			Store::$countries = (array)Api::get( 'countries' );

			update_option( OWC_SHOP_PREFIX . '_countries', Store::$countries );
		}
	}
}