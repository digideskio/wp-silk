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

	public function __construct( $props ) {
		foreach ( $props as $key => $value ) {
			if ( isset( Store::${$key} ) ) {
				Store::${$key} = $value;
			}
		}
	}
}