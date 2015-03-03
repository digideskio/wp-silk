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
			$countries = array();
			foreach ( (array)Api::get( 'countries' ) as $country ) {
				if ( ! $country->shipTo )
					continue;

				$countries[ $country->country ] = $country;
			}
			Store::$countries = $countries;

			update_option( OWC_SHOP_PREFIX . '_countries', Store::$countries );
		}
	}
}