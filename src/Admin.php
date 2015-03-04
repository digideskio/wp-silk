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
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 100 );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );
		add_action( 'manage_posts_custom_column', array( $this, 'custom_columns' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'handle_admin_screen' ), 100 );

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

	public function admin_bar_menu( $wp_admin_bar ) {
		$wp_admin_bar->add_menu( array(
			'id'	=> 'wp-silk',
			'title'	=> __( 'Sync from Silk', 'owc' ),
			'href'	=> admin_url( 'options-general.php?page=owc-silk-tools&owc_silk_sync=1' )
		) );
	}

	public function admin_menu( $wp_admin_bar ) {
		add_submenu_page( 'options-general.php', __( 'Silk Tools', 'owc' ), __( 'Silk Tools', 'owc' ), 'manage_options', 'owc-silk-tools', array( $this, 'screen_tools' ) );
	}

	// Screens
	public function handle_admin_screen() {
		if ( isset( $_REQUEST['owc_silk_sync'] ) ) {
			$synced = Sync::run();

			wp_redirect( admin_url( 'options-general.php?page=owc-silk-tools&synced=' . $synced ) );
			exit;
		}
	}

	public function screen_tools() {
		?>
<div class="wrap">
	<h2>
		<?php _ex( 'Silk Tools', 'admin', 'owc' ); ?>
	</h2>

	<?php if ( isset( $_GET['synced'] ) ): ?>
		<?php if ( $_GET['synced'] == 1 ): ?>
			<div class="updated">
				<p><strong><?php _e( 'Sync performed', 'owc' ); ?></strong></p>
			</div>
		<?php else : ?>
			<div class="error">
				<p><strong><?php _e( 'Something went wrong, please try again', 'owc' ); ?></strong></p>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	
	<form action="" method="post">
		<?php wp_nonce_field( 'owc-general-settings' ); ?>

		<h3><?php _ex( 'General', 'admin', 'owc' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th><?php _ex( 'Do manual sync', 'admin', 'owc' ); ?></th>
				<td>
					<input type="submit" name="owc_silk_sync" value="<?php _e( 'Sync', 'owc' ); ?>" class="button button-primary">
				</td>
			</tr>
		</table>
	</form>
</div>
		<?php
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
