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

		if ( ! is_admin() ) {
			add_action( 'pre_get_posts', array( $this, 'category_sorting_filtering' ) );
			add_action( 'pre_get_posts', array( $this, 'search_filtering' ) );
		}
	}

	function category_sorting_filtering( $query ) {
		if ( ! $query->is_tax( 'product_category' ) )
			return;

		$category = $query->get( 'product_category' );
		$products = get_option( OWC_SHOP_PREFIX .'_sorting_' . $category );

		if ( empty( $products ) )
			return;

		// Filter products not active in market
		$products = array_filter( $products, array( $this, 'market_products_filter' ) );

		$query->set( 'post__in', $products );
		$query->set( 'orderby', 'post__in' );
	}

	function search_filtering( $query ) {
		if ( ! $query->is_search() )
			return;

		// Filter products not active in market
		$query->set( 'post__in', Store::market_products() );
	}

	function market_products_filter( $post_id ) {
		return in_array( $post_id, Store::market_products() );
	}

	static function market_products() {
		$market_products = get_option( OWC_SHOP_PREFIX . '_market_products' );
		$products = $market_products[ Store::$market ];

		return $products;
	}
}