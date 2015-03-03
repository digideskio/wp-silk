<?php get_header(); ?>
<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">
		<h1><?php _e( 'Checkout', 'owc' ); ?></h1>
		
		<h2><?php _e( 'Items', 'owc' ); ?></h2>
		<?php include( OWC\Silk\Template::get_file( 'checkout/items' ) ); ?>

		<h2><?php _e( 'Summary', 'owc' ); ?></h2>
		<?php include( OWC\Silk\Template::get_file( 'checkout/summary' ) ); ?>

		<?php include( OWC\Silk\Template::get_file( 'checkout/voucher' ) ); ?>

		<h2><?php _e( 'Payment Method', 'owc' ); ?></h2>
		<ul>
			<?php foreach( OWC\Silk\Cart::$selection->paymentMethodsAvailable as $payment_method ) : ?>
				<li><label><input type="radio" name="payment_method" value="<?php echo esc_attr( $payment_method->paymentMethod ); ?>" rel="payment-method" <?php checked( OWC\Silk\Cart::field_value('name=paymentMethod') == $payment_method->paymentMethod ); ?>> <?php echo esc_html( $payment_method->name ); ?></label></li>
			<?php endforeach; ?>
		</ul>

		<?php if ( ! empty( OWC\Silk\Cart::$selection->shippingMethodsAvailable ) ) : ?>
			<h2><?php _e( 'Shipping Method', 'owc' ); ?></h2>
			<ul>
				<?php foreach( OWC\Silk\Cart::$selection->shippingMethodsAvailable as $shipping_method ) : ?>
					<li><label><input type="radio" name="shipping_method" value="<?php echo esc_attr( $shipping_method->shippingMethod ); ?>" rel="shipping-method" <?php checked( OWC\Silk\Cart::field_value('name=shippingMethod') == $shipping_method->shippingMethod ); ?>> <?php echo esc_html( $shipping_method->name ); ?></label></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<form action="" method="post" rel="billing-information">
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
				'attributes'	=> array( 'rel' => 'billing-country' )
			) ); ?>
		</form>

		<div><label><input type="checkbox" rel="same-shipping" name="same_shipping" <?php checked( ! OWC\Silk\Cart::field_value( 'name=same_shipping&group=other' ) ); ?>> <?php _e( 'Ship to billing address', 'owc' ); ?></label></div>

		<form action="" method="post" rel="shipping-information" style="display: none">
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
				'attributes'	=> array( 'rel' => 'shipping-country' )
			) ); ?>
		</form>

		<form action="" method="post" rel="checkout-submit">
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
