<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Products {
	public function __construct() {
		add_action( 'init', array( $this, 'setup_post_type' ) );
		add_action( 'init', array( $this, 'setup_taxonomies' ) );
		add_action( 'admin_init', array( $this, 'add_post_meta' ) );
	}

	public static function update_product( $product_id, $silk_data ) {
		$post_data = array(
			'post_name'		=> $silk_data->uri,
			'post_title'	=> $silk_data->name,
			'post_content'	=> $silk_data->description,
			'post_status'	=> 'publish',
			'post_type'		=> 'product',
			'post_author'	=> 1	// Default to 1 so this function can run without a user being logged in
		);

		// Post exists?
		$post = get_page_by_path( $silk_data->uri, OBJECT, 'product' );
		
		if ( $post )
			$post_data['ID'] = $post->ID;

		$post_id = wp_insert_post( $post_data );

		Products::update_meta( $post_id, 'product_id', $product_id );
		Products::update_meta( $post_id, 'product_sku', $silk_data->sku );
		Products::update_meta( $post_id, 'json', $silk_data );

		// Update categories
		$category_map = get_option( OWC_SHOP_PREFIX .'_category_map' );
		$term_ids = array();
		
		foreach( array_keys( (array)$silk_data->categories ) as $category_id ) {
			if ( isset( $category_map[$category_id] ) )
				$term_ids[] = (int)$category_map[$category_id];
		}

		wp_set_object_terms( $post_id, $term_ids, 'product_category' );

		// Update additional taxonomies
		$taxonomy_maps = explode( ',', Admin::$settings['attribute_taxonomy'] );
		foreach ( $taxonomy_maps as $taxonomy_map ) {
			$map = explode( '=', $taxonomy_map );
			$attribute = $map[0];
			$taxonomy = $map[1];

			if ( isset( $silk_data->{$attribute} ) ) {
				$term = $silk_data->{$attribute};

				wp_set_object_terms( $post_id, $term, $taxonomy );
			} else {
				wp_delete_object_term_relationships( $post_id, $taxonomy );
			}
		}

		do_action( 'owc_silk_update_product', $post, $silk_data );
	}

	public function setup_post_type() {
		register_post_type( 'product', array(
			'labels'   => array(
				'name'          => _x( 'Products', 'admin', 'owc' ),
				'singular_name' => _x( 'Product', 'admin', 'owc' )
			),
			'public'   => true,
			'supports' => array( 'title' ),
			'rewrite' => array(
				'with_front' => false
			)
		) );
	}

	public function setup_taxonomies() {
		register_taxonomy( 'product_category', 'product', array(
			'label'			=> _x( 'Category', 'admin', 'owc' ),
			'rewrite'		=> array( 'slug' => 'category' ),
			'hierarchical'	=> true
		) );
	}

	public function add_post_meta() {
		add_meta_box( 'silk', _x( 'Silk Data', 'admin', 'owc' ), array( $this, 'meta_box' ), 'product', 'advanced', 'core' );
	}

	public function meta_box( $post ) {
		$silk_data = Products::get_meta( $post->ID, 'json' );
		?>
		<table class="wide-fat spinnup-admin-table">
			<tr>
				<th valign="top" width="100"><p><?php _ex( 'Silk ID', 'admin', 'owc' ); ?></p></th>
				<td><?php echo esc_html( Products::get_meta( $post->ID, 'product_id' ) ); ?></td>
			</tr>
			<tr>
				<th valign="top" width="100"><p><?php _ex( 'Silk Media', 'admin', 'owc' ); ?></p></th>
				<td>
					<?php foreach ( $silk_data->media as $image ) : ?>
						<img src="<?php echo esc_url( $image->sources->mini->url ); ?>">
					<?php endforeach; ?>
				</td>
			</tr>
			<tr>
				<th valign="top"><p><?php _ex( 'Silk Data', 'admin', 'owc' ); ?></p></th>
				<td>
					<pre style="max-width: 700px; height: 300px; padding: 20px; overflow: scroll; background: #eee;"><?php print_r( $silk_data ); ?></pre>
				</td>
			</tr>
		</table>
		<?php
	}

	// Meta functions
	public static function get_meta( $post_id, $key ) {
		return get_post_meta( $post_id, '_' . OWC_SHOP_PREFIX . '_' . $key, true );
	}

	public static function update_meta( $post_id, $key, $value ) {
		return update_post_meta( $post_id, '_' . OWC_SHOP_PREFIX . '_' . $key, $value );
	}

	public static function delete_meta( $post_id, $key ) {
		return delete_post_meta( $post_id, '_' . OWC_SHOP_PREFIX . '_' . $key );
	}

	// Template functions
	public static function get_data( $key, $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		if(isset($data->{$key})) {
			return $data->{$key};
		} else {
			return $data; 
		}
			
	}
	
	public static function get_uri( $post_id = false ) {
		return Products::get_data( 'uri', $post_id );
	}
	
	public static function uri( $post_id = false ) {
		echo esc_attr( Products::get_uri( $post_id ) );
	}


	public static function has_images( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		if (!empty($data->media)) {
			return true; 
		} else {
			return false; 
		}

	}

	public static function get_images( $args = '', $post_id = false ) {
		global $post;
		
		$default = array(
			'size' => '', 
			'single' => false  
		);
		$args = wp_parse_args( $args, $default );

		extract( $args, EXTR_SKIP );

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );
		$urls = array();
		$size = (!empty($size)) ? $size : "full";  

		if (!$single) :
			foreach ($data->media as $key => $value) :
				array_push($urls, $value->sources->$size->url);
			endforeach; 
		else : 
			 array_push($urls, $data->media[0]->sources->$size->url); 	
			 $urls = implode(",", $urls); 	
		endif; 	

		return (!empty($urls)) ? $urls : ""; 
	}


	public static function has_discount( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		return $data->markets->{Store::$market}->pricesByPricelist->{Store::$pricelist}->priceReductionAsNumber > 0;
	}


	public static function get_price( $post_id = false, $args = '' ) {
		global $post;
		
		$defaults = array(
			'before_discount' => false,
			'price_as_number' => false,
		);
		$args = wp_parse_args( $args, $defaults );

		extract( $args, EXTR_SKIP );

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		if ( ! isset( $data->markets->{Store::$market} ) )
			return __( 'ERROR: Market not found', 'owc' );

		if ( ! isset( $data->markets->{Store::$market}->pricesByPricelist->{Store::$pricelist} ) )
			return __( 'ERROR: Pricelist not found', 'owc' );

		if ( $price_as_number )
			$property = ( !$before_discount ) ? 'priceAsNumber' : 'priceBeforeDiscountAsNumber'; 
		else
			$property = ( !$before_discount ) ? 'price' : 'priceBeforeDiscount';

		$price = $data->markets->{Store::$market}->pricesByPricelist->{Store::$pricelist}->$property;
		
		return apply_filters( 'owc_silk_get_price', $price );
	}

	public static function price( $post_id = false, $args = '' ) {
		echo esc_html( Products::get_price( $args, $post_id ) );
	}

	public static function get_product_meta ( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		$obj = new \stdClass(); 
		$obj->metaTitle       = $data->metaTitle; 
		$obj->metaDescription = $data->metaDescription; 
		$obj->metaKeywords    = $data->metaKeywords; 

		return $obj; 
	}

	public static function get_variant ( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		return $data->silkVariantName; 
	}

	public static function has_sizes( $post_id = false ) {
		return count( Products::get_sizes( $post_id ) ) > 1;
	}

	public static function get_sizes( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		return array_values( (array)$data->items );
	}

	public static function has_stock( $product_id, $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );
		
		$sizes = $data->items;
		$size = $sizes->{$product_id};

		if ( ! isset( $size->stockByMarket->{Store::$market} ) )
			return false;

		$stock = $size->stockByMarket->{Store::$market};

		return $stock > 1;
	}


	public static function get_amount_of_stock ( $product_id, $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );
		//print_r($data);

		if ( Products::has_stock( $product_id, $post_id ) ) {
			$sizes = $data->items;
			$size  = $sizes->{$product_id};

			if ( ! isset( $size->stockByMarket->{Store::$market} ) )
				return false;

			return $size->stockByMarket->{Store::$market};

		}
		
		return 0; 	
	}

}