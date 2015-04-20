<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Sync {
	public function __construct() {
		if ( ! wp_next_scheduled( 'owc_silk_sync' ) ) {
			wp_schedule_event( time(), 'hourly', 'owc_silk_sync' );
		}
		add_action( 'owc_silk_sync', array( $this, 'run' ) );
	}

	public static function run() {
		global $wpdb;

		// Update countries
		$countries = (array)Api::get( 'countries' );
		update_option( OWC_SHOP_PREFIX . '_countries', $countries );

		// Update markets
		$markets = (array)Api::get( 'markets' );
		update_option( OWC_SHOP_PREFIX . '_markets', $markets );

		// Update pricelists
		$pricelists = (array)Api::get( 'pricelists' );
		update_option( OWC_SHOP_PREFIX . '_pricelists', $pricelists );

		// Update payment methods
		$payment_methods = (array)Api::get( 'payment-methods' );
		update_option( OWC_SHOP_PREFIX . '_payment_methods', $payment_methods );

		$categories = (array)Api::get( 'categories' );
		update_option( OWC_SHOP_PREFIX . '_categories', $categories );

		// Update categories
		foreach ( $categories as $category_id => $category ) {
			Sync::insert_category( $category );
		}

		// Update category map
		$terms = $wpdb->get_results( "SELECT t.term_id, tt.description AS silk_id FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id WHERE tt.taxonomy = 'product_category'" );
		$category_map = array();

		foreach ( $terms as $term ) {
			$category_map[ $term->silk_id ] = $term->term_id;
		}
		update_option( OWC_SHOP_PREFIX .'_category_map', $category_map );

		// Update products
		$current_products_arr = $wpdb->get_results( "SELECT ID, post_name FROM {$wpdb->posts} WHERE post_type = 'product'" );

		$current_products = array();

		foreach ( $current_products_arr as $row ) {
			$current_products[ $row->post_name ] = $row->ID;
		}

		foreach ( (array)Api::get('products') as $product_id => $silk_data ) {
			Products::update_product( $product_id, $silk_data );

			if ( isset( $current_products[ $silk_data->uri ] ) )
				unset( $current_products[ $silk_data->uri ] );
		}

		// Trash inactive products
		if ( ! empty( $current_products ) ) {
			foreach ( $current_products as $post_id ) {
				wp_trash_post( $post_id );
			}
		}

		return true;
	}

	public static function insert_category( $category, $parent_id = false ) {
		if ( ! isset( $category->name ) )
			return;
		
		$term = term_exists( $category->name, 'product_category' );
		$term_id = false;

		if ( ! $term ) {
			$term_id = wp_insert_term( $category->name, 'product_category', array(
				'description'	=> $category->category,
				'parent'		=> $parent_id
			) );
		} else {
			$term_id = $term['term_id'];
			
			wp_update_term( $term_id, 'product_category', array(
				'description'	=> $category->category,
				'parent' 		=> $parent_id
			) );
		}
		$term = get_term( $term_id, 'product_category' );

		global $wpdb;

		$products_ids = implode( ',', $category->products );
		$post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_value IN ($products_ids) ORDER BY FIELD(meta_value, $products_ids)" );

		update_option( OWC_SHOP_PREFIX .'_sorting_' . $term->slug, $post_ids );

		do_action( 'owc_silk_update_category', $term_id, $category );

		if ( ! empty( $category->categories ) ) {
			foreach ( $category->categories  as $child_category ) {
				Sync::insert_category( $child_category, $term_id );
			}
		}
	}
}