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

		if ( is_array( Store::$countries ) ) {
			$countries = array();
			foreach ( Store::$countries as $country ) {
				if ( ! isset( $country->shipTo ) || ! $country->shipTo )
					continue;

				$countries[ strtolower( $country->country ) ] = $country;
			}
			Store::$countries = $countries;
		}

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}

	function pre_get_posts( $query ) {
		if ( ! $query->is_tax( 'product_category' ) )
			return;

		$category = $query->get( 'product_category' );
		$products = get_option( OWC_SHOP_PREFIX .'_sorting_' . $category );

		if ( empty( $products ) )
			return;

		// Filter products not active in market
		$products = array_filter( $products, array( $this, 'market_products' ) );

		$query->set( 'post__in', $products );
		$query->set( 'orderby', 'post__in' );
	}

	function market_products( $post_id ) {
		$market_products = get_option( OWC_SHOP_PREFIX . '_market_products' );
		$products = $market_products[ Store::$market ];

		return in_array( $post_id, $products );
	}
}