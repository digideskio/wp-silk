<?php get_header(); ?>
<div id="primary" class="content-area">
	

	<div id="content" class="site-content" role="main">
		<a class="cart<?php if ( OWC\Silk\Cart::get_quantity() ) echo ' has-items'; ?>" rel="cart" href="<?php echo esc_url( OWC\Silk\Cart::get_checkout_url() ); ?>">
			<?php _e( 'Your cart:', 'owc' ); ?>
			<span class="cart-length" rel="cart-length"><?php OWC\Silk\Cart::quantity(); ?></span>
			<?php echo _n( 'item', 'items', OWC\Silk\Cart::get_quantity(), 'owc' ); ?>,
			<span class="cart-total" rel="cart-total"><?php OWC\Silk\Cart::total(); ?></span>
		</a>
		<?php while ( have_posts() ) : the_post(); ?>
			<h1><?php the_title(); ?></h1>

			<?php the_content(); ?>

			<?php if ( OWC\Silk\Products::has_discount() ) : ?>
				<strike><?php OWC\Silk\Products::price( 'before_discount=1' ); ?></strike>
			<?php endif; ?>

			<?php OWC\Silk\Products::price(); ?>

			<form action="" method="post" data-product-form="<?php OWC\Silk\Products::uri(); ?>" rel="product-form">
				<?php if ( OWC\Silk\Products::has_sizes() ) : ?>
					<select name="size" rel="product-size">
						<option value="-1"><?php _e( 'Select size', 'owc' ); ?></option>
						<?php foreach ( OWC\Silk\Products::get_sizes() as $size ) : ?>
							<option value="<?php echo esc_attr( $size->item ); ?>"><?php echo esc_html( $size->name ); ?></option>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input name="size" rel="product-size" type="hidden" value="<?php echo esc_attr( OWC\Silk\Products::get_sizes()[0]->item ); ?>">
				<?php endif; ?>
				<button type="submit"><?php _e( 'Add to cart', 'owc' ); ?></button>
			</form>

			<?php $meta = OWC\Silk\Products::get_meta( $post->ID, 'json' ); ?>
			<pre><?php print_r($meta); ?></pre>
			<?php endwhile; ?>
	</div>
</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
