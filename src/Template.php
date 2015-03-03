<?php
namespace OWC\Silk;

if ( ! defined( 'ABSPATH' ) ) exit;

class Template {

	public $file = '';

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
	}

	public function template_include( $template ) {
		if ( ! empty( $this->file ) )
			$template = $this->file;

		return $template;
	}

	public function template_redirect() {
		if ( get_post_type() == 'product' && is_single() ) {
			$this->file = Template::get_file( 'single-product' );
		} elseif ( Admin::$settings['checkout_page'] && is_page( Admin::$settings['checkout_page'] ) ) {
			
			if ( Cart::$doing_redirect )
				$this->file = Template::get_file( 'checkout-redirect' );
			else
				$this->file = Template::get_file( 'checkout' );
			
		} elseif ( Admin::$settings['receipt_page'] && is_page( Admin::$settings['receipt_page'] ) ) {
			$this->file = Template::get_file( 'receipt' );
		}
	}

	public static function get_file( $template ) {
		$template = $template . '.php';

		if ( $theme_file = locate_template( 'templates/plugins/' . OWC_SHOP_PLUGIN_NAME . '/' . $template ) ) {
			$file = $theme_file;
		} else {
			$file = OWC_SHOP_PATH . '/templates/' . $template;
		}

		return $file;
	}
}