<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Products {
	public function __construct() {
		add_action( 'init', array( $this, 'setup_post_type' ) );
		add_action( 'init', array( $this, 'setup_taxonomies' ) );
		add_action( 'admin_init', array( $this, 'add_post_meta' ) );
	}

	public function update_product( $product_id, $silk_data ) {
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
		Products::update_meta( $post_id, 'json', $silk_data );

		// Update categories
		$category_map = get_option( OWC_SHOP_PREFIX .'_category_map' );
		$term_ids = array();
		
		foreach( array_keys( (array)$silk_data->categories ) as $category_id ) {
			if ( isset( $category_map[$category_id] ) )
				$term_ids[] = (int)$category_map[$category_id];
		}

		wp_set_object_terms( $post_id, $term_ids, 'product_category' );
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

		return $data->{$key};
	}
	
	public static function get_uri( $post_id = false ) {
		return Products::get_data( 'uri', $post_id );
	}
	
	public static function uri( $post_id = false ) {
		echo esc_attr( Products::get_uri( $post_id ) );
	}

	public static function get_price( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		if ( ! isset( $data->markets->{Store::$market} ) )
			return __( 'ERROR: Market not found', 'owc' );

		if ( ! isset( $data->markets->{Store::$market}->pricesByPricelist->{Store::$pricelist} ) )
			return __( 'ERROR: Pricelist not found', 'owc' );

		return $data->markets->{Store::$market}->pricesByPricelist->{Store::$pricelist}->price;
	}

	public static function price( $post_id = false ) {
		echo esc_html( Products::get_price( $post_id ) );
	}

	public static function has_variants( $post_id = false ) {
		return count( Products::get_variants( $post_id ) ) > 1;
	}

	public static function get_variants( $post_id = false ) {
		global $post;

		if ( ! $post_id )
			$post_id = $post->ID;

		$data = Products::get_meta( $post_id, 'json' );

		return array_values( (array)$data->items );
	}
}