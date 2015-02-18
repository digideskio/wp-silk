<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Admin {

	/*
	|-----------------------------------------------------------
	| PROPERTIES
	|-----------------------------------------------------------
	*/

	// Settings
	public static $settings = array();

	/*
	|-----------------------------------------------------------
	| CONSTRUCTOR
	|-----------------------------------------------------------
	*/

	public function __construct() {
		// actions
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );

		// filters
		add_filter( 'manage_product_posts_columns', array( $this, 'change_columns' ) );

		Admin::$settings = get_option( OWC_SHOP_PREFIX );
	}

	/*
	|-----------------------------------------------------------
	| ACTIONS
	|-----------------------------------------------------------
	*/

	// init
	public function init() {

	}

	// manage_product_posts_columns
	public function change_columns( $cols ) {
		$cols = array(
			'cb'      => '<input type="checkbox" />',
			'image'   => _x( 'Image', 'admin', 'owc' ),
			'title'   => _x( 'Title', 'admin', 'owc' ),
			'product' => _x( 'Product', 'admin', 'owc' ),
			'price'   => _x( 'Price', 'admin', 'owc' ),
			'date'    => _x( 'Date added', 'admin', 'owc' )
		);

		return $cols;
	}

	// manage_posts_custom_column
	public function custom_columns( $column, $post_id ) {
		$output = "";

		switch ( $column ) {
			case 'image':
				$image = ''; //Product::get_image( $post_id );

				if ( $image ) {
					$output .= "<img src='{$image}' width='80' style='vertical-align: middle; margin: 0 10px 0 0'>";
				}

				echo $output;
				break;

			case 'product':
				/*$product_id = $this->get_meta( $post_id, 'product_id' );

				if ( $product_id ) {
					$product = Product::get_product( $product_id );

					if ( $product ) {
						$url  = 'https://' . Admin::$settings['name'] . '.' . Admin::$settings['url'] . '/products/' . $product_id;
						$title = $product->title;

						$output .= "<a href='{$url}' target='_blank'>";
						if ( ! empty( $product->image ) ) {
							$output .= "<img src='{$product->image->src}' width='50' style='vertical-align: middle; margin: 0 10px 0 0'>";
						}
						$output .= "{$product->title}</a>";
					}
				}

				echo $output;*/
				break;

			case 'price':
				$output     = "";
				/*$product_id = $this->get_meta( $post_id, 'product_id' );

				if ( $product_id ) {
					$output = Product::get_price( $product_id );
				}

				echo $output['price'] . ' <strike>' . $output['compare'] . '</strike>';*/
				break;
		}
	}

	/*
	|-----------------------------------------------------------
	| METHODS
	|-----------------------------------------------------------
	*/

	// meta methods
	public function get_meta($post_id, $key ) {
		return get_post_meta( $post_id, '_' . OWC_SHOP_PREFIX . '_' . $key, true );
	}
	public function update_meta( $post_id, $key, $value ) {
		return update_post_meta( $post_id, '_' . OWC_SHOP_PREFIX . '_' . $key, $value );
	}
	public function delete_meta( $post_id, $key ) {
		return delete_post_meta( $post_id, '_' . OWC_SHOP_PREFIX . '_' . $key );
	}

}
