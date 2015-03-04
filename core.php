<?php
/*
	Plugin Name: OWC Silk
	Description: Silk WP integration
	Author: Oakwood Creative
	Author URI: http://oakwood.se/
*/

namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

/*
|-----------------------------------------------------------
| AUTOLOAD
|-----------------------------------------------------------
*/

use OWC\Silk\Api,
	OWC\Options\Options;

/*
|-----------------------------------------------------------
| CONSTANTS
|-----------------------------------------------------------
*/

define( 'OWC_SHOP_PATH', dirname( __FILE__ ) );
define( 'OWC_SHOP_PLUGIN_NAME', basename( OWC_SHOP_PATH ) );
define( 'OWC_SHOP_PREFIX', 'owc_silk' );
define( 'CUZTOM_URL', WP_CONTENT_URL . '/vendor/gizburdt/cuztom' );

/*
|-----------------------------------------------------------
| CLASSES
|-----------------------------------------------------------
*/

$admin = new Admin();
$api = new Api( array(
	'url'		=> Admin::$settings['url'],
	'secret'	=> Admin::$settings['secret']
) );
$store = new Store( array(
	'country'	=> 'se',
	'market'	=> Admin::$settings['default_market'],
	'pricelist'	=> Admin::$settings['default_pricelist'],
) );
$options = new Options( 'owc_silk', 'Silk Configuration', array(
	'api' => array(
		'title'  => 'Silk Configuration',
		'fields' => array(
			'url' => array(
				'title' => 'API URL',
				'type'  => 'text',
				'value' => 'http://linumdemo.silkvms.com/api/shop/'
			),
			'secret' => array(
				'title' => 'API Secret',
				'type'  => 'text',
				'value' => ''
			),
			'default_shipping' => array(
				'title' => 'Default Shipping Method',
				'type'  => 'text',
				'value' => 'dummy'
			),
			'default_payment' => array(
				'title' => 'Default Payment Method',
				'type'  => 'select',
				'value' => 'dummy',
				'options'		=> get_option( OWC_SHOP_PREFIX . '_payment_methods' ),
				'option_key'	=> 'paymentMethod',
				'option_value'	=> 'name'
			),
			'default_market' => array(
				'title' => 'Default Market',
				'type'  => 'select',
				'value' => 2,
				'options'		=> get_option( OWC_SHOP_PREFIX . '_markets' ),
				'option_key'	=> 'market',
				'option_value'	=> 'name'
			),
			'default_pricelist' => array(
				'title' => 'Default Pricelist',
				'type'  => 'select',
				'value' => 20,
				'options' => get_option( OWC_SHOP_PREFIX . '_pricelists' ),
				'option_key'	=> 'pricelist',
				'option_value'	=> 'name'
			),
			'attribute_taxonomy' => array(
				'title' => 'Attribute > Taxonomy mapping',
				'type'  => 'text',
				'value' => ''
			),
			'checkout_page' => array(
				'title' => 'Checkout Page',
				'type'  => 'page',
				'value' => 0
			),
			'receipt_page' => array(
				'title' => 'Receipt Page',
				'type'  => 'page',
				'value' => 0
			),
			'push_page' => array(
				'title' => 'Push Page',
				'type'  => 'page',
				'value' => 0
			)
		)
	)
) );
$products = new Products();
$sync = new Sync();
$cart = new Cart();
$debug = new Debug();
$push = new Push();
$ajax = new Ajax();
$template = new Template();

/*
|-----------------------------------------------------------
| ACTIONS
|-----------------------------------------------------------
*/

function scripts() {
	wp_enqueue_script( OWC_SHOP_PLUGIN_NAME . '-js', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'OWC\Silk\scripts' );

function ajaxurl() {
?>
	<script type="text/javascript">
	var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
	</script>
<?php
}
add_action( 'wp_head', 'OWC\Silk\ajaxurl' );

function footer() {
    echo '<script type="text/javascript">var shop = new OWC_Shop();</script>';
}
add_action( 'wp_footer', 'OWC\Silk\footer', 100 );