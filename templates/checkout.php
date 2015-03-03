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
				<li><label><input type="radio" name="payment_method" value="<?php echo esc_attr( $payment_method->paymentMethod ); ?>" rel="shop-checkout-payment-method" <?php checked( OWC\Silk\Cart::field_value('name=paymentMethod') == $payment_method->paymentMethod ); ?>> <?php echo esc_html( $payment_method->name ); ?></label></li>
			<?php endforeach; ?>
		</ul>

		<h2><?php _e( 'Shipping Method', 'owc' ); ?></h2>
		<ul>
			<?php foreach( OWC\Silk\Cart::$selection->shippingMethodsAvailable as $shipping_method ) : ?>
				<li><label><input type="radio" name="shipping_method" value="<?php echo esc_attr( $shipping_method->shippingMethod ); ?>" rel="shop-checkout-shipping-method" <?php checked( OWC\Silk\Cart::field_value('name=shippingMethod') == $shipping_method->shippingMethod ); ?>> <?php echo esc_html( $shipping_method->name ); ?></label></li>
			<?php endforeach; ?>
		</ul>

		<form action="" method="post" rel="shop-checkout-billing">
			<h2><?php _e( 'Billing Information', 'owc' ); ?></h2>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'First Name', 'owc' ),
				'name' => 'firstName'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'Last Name', 'owc' ),
				'name' => 'lastName'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'E-mail', 'owc' ),
				'name' => 'email'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'Address', 'owc' ),
				'name' => 'address1'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'Zip Code', 'owc' ),
				'name' => 'zipCode'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'City', 'owc' ),
				'name' => 'city'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label'			=> __( 'Country', 'owc' ),
				'name'			=> 'country',
				'type'			=> 'select',
				'options'		=> OWC\Silk\Store::$countries,
				'attributes'	=> array( 'rel' => 'shop-checkout-country' )
			) ); ?>
		</form>

		<div><label><input type="checkbox" rel="shop-checkout-same-shipping" name="same_shipping" <?php checked( ! OWC\Silk\Cart::field_value( 'name=same_shipping&group=other' ) ); ?>> <?php _e( 'Ship to billing address', 'owc' ); ?></label></div>

		<form action="" method="post" rel="shop-checkout-shipping" style="display: none">
			<h2><?php _e( 'Shipping Information', 'owc' ); ?></h2>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'First Name', 'owc' ),
				'name' => 'firstName',
				'group' => 'shippingAddress' 
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'Last Name', 'owc' ),
				'name' => 'lastName',
				'group' => 'shippingAddress'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'Address', 'owc' ),
				'name' => 'address1',
				'group' => 'shippingAddress'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'Zip Code', 'owc' ),
				'name' => 'zipCode',
				'group' => 'shippingAddress'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label' => __( 'City', 'owc' ),
				'name' => 'city',
				'group' => 'shippingAddress'
			) ); ?>
			<?php OWC\Silk\Cart::field( array(
				'label'			=> __( 'Country', 'owc' ),
				'name'			=> 'country',
				'group'			=> 'shippingAddress',
				'type'			=> 'select',
				'options'		=> OWC\Silk\Store::$countries,
				'attributes'	=> array( 'rel' => 'shop-checkout-country' )
			) ); ?>
		</form>

		<form action="" method="post" rel="shop-checkout-submit">
			<input type="hidden" name="paymentMethod" value="<?php echo esc_attr( OWC\Silk\Cart::$selection->paymentMethod ); ?>">
			<input type="hidden" name="shippingMethod" value="<?php echo esc_attr( OWC\Silk\Cart::$selection->shippingMethod ); ?>">

			<label><input type="checkbox" name="terms"><?php _e( 'I agree to the terms and conditions', 'owc' ); ?></label>
			<input type="submit" name="<?php echo esc_attr( OWC_SHOP_PREFIX . '_submit' ); ?>" value="<?php _e( 'Proceed to payment', 'owc' ); ?>">
		</form>

		<pre><?php print_r(OWC\Silk\Cart::$payment_data); ?><?php print_r(OWC\Silk\Cart::$selection); ?></pre>
	</div>
</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
