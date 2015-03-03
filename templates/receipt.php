<?php get_header(); ?>
<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">
		<h1><?php _e( 'Receipt', 'owc' ); ?></h1>
		<pre><?php print_r( OWC\Silk\Cart::$order ); ?></pre>
	</div>
</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();
