<?php get_header(); ?>
<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">
		<h1><?php _e( 'Redirecting', 'owc' ); ?></h1>
		<?php
			switch ( OWC\Silk\Cart::$instructions->action ) {
				case 'form':
					echo OWC\Silk\Cart::$instructions->formHtml;
					break;
				case 'redirect':
					echo '<script>document.location.href = "' . OWC\Silk\Cart::$instructions->url . '"</script>';
					break;
				case 'success':
					echo '<h1>Yeeesssss!</h1>';
					break;
				case 'failed':
					echo '<h1>Nooooo!</h1>';
					break;
			}
		?>
	</div>
</div>
<?php
get_sidebar( 'content' );
get_sidebar();
get_footer();