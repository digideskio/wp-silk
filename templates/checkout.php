<?php get_header(); ?>
<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">
		<h1><?php _e( 'Checkout', 'owc' ); ?></h1>
		<h2><?php _e( 'Items', 'owc' ); ?></h2>
		<ul>
			<?php foreach( OWC\Silk\Cart::$selection->items as $item ) : ?>
				<li><?php printf( '%dx %s (%s) - %s', $item->quantity, esc_html( $item->productName ), esc_html( $item->size ), esc_html( $item->totalPrice ) ); ?></li>
			<?php endforeach; ?>
		</ul>

		<h2><?php _e( 'Add voucher', 'owc' ); ?></h2>
		<input type="text" name="voucher"><input type="submit" value="<?php _e( 'Add voucher', 'owc' ); ?>" rel="shop-checkout-voucher">

		<h2><?php _e( 'Payment Method', 'owc' ); ?></h2>
		<ul>
			<?php foreach( OWC\Silk\Cart::$selection->paymentMethodsAvailable as $payment_method ) : ?>
				<li><label><input type="radio" name="payment_method" value="<?php echo esc_attr( $payment_method->paymentMethod ); ?>" rel="shop-checkout-payment"> <?php echo esc_html( $payment_method->name ); ?></label></li>
			<?php endforeach; ?>
		</ul>

		<h2><?php _e( 'Shipping Method', 'owc' ); ?></h2>
		<ul>
			<?php foreach( OWC\Silk\Cart::$selection->shippingMethodsAvailable as $shipping_method ) : ?>
				<li><label><input type="radio" name="shipping_method" value="<?php echo esc_attr( $shipping_method->paymentMethod ); ?>" rel="shop-checkout-shipping"> <?php echo esc_html( $shipping_method->name ); ?></label></li>
			<?php endforeach; ?>
		</ul>

		<h2><?php _e( 'Billing Information', 'owc' ); ?></h2>
		<?php OWC\Silk\Cart::field( array(
			'label' => __( 'First Name', 'owc' ), 
			'name' => 'firstName', 
			'group' => 'address' 
		) ); ?>
		<?php OWC\Silk\Cart::field( array(
			'label' => __( 'Last Name', 'owc' ), 
			'name' => 'lastName', 
			'group' => 'address' 
		) ); ?>
		<?php OWC\Silk\Cart::field( array(
			'label' => __( 'Address', 'owc' ), 
			'name' => 'address1', 
			'group' => 'address' 
		) ); ?>
		<?php OWC\Silk\Cart::field( array(
			'label' => __( 'Zip Code', 'owc' ), 
			'name' => 'zipCode', 
			'group' => 'address' 
		) ); ?>
		<?php OWC\Silk\Cart::field( array(
			'label' => __( 'City', 'owc' ), 
			'name' => 'city', 
			'group' => 'address' 
		) ); ?>
		<?php OWC\Silk\Cart::field( array(
			'label'			=> __( 'Country', 'owc' ),
			'name'			=> 'country', 
			'group'			=> 'address', 
			'type'			=> 'select', 
			'options'		=> array( 'sdsadf' ),
			'attributes'	=> array( 'rel' => 'shop-checkout-country' 
		) ); ?>

		<h2><?php _e( 'Shipping Information', 'owc' ); ?></h2>
		<?php print_r(OWC\Silk\Cart::$selection); ?>
	</div>
</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
