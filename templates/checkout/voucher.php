<div rel="voucher-section">
	<?php if ( ! empty( OWC\Silk\Cart::$selection->discounts->vouchers ) ) : ?>
		<h2><?php _e( 'Voucher', 'owc' ); ?></h2>
		<ul>
			<?php foreach( OWC\Silk\Cart::$selection->discounts->vouchers  as $voucher ) : ?>
				<li>
					<?php echo esc_html( $voucher->voucher ); ?>
					<button rel="voucher-remove" data-voucher="<?php echo esc_attr( $voucher->voucher ); ?>">X</button>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	
	<?php if ( ! OWC\Silk\Cart::$selection->discounts->anyDiscount ) : ?>
		<h2><?php _e( 'Add voucher', 'owc' ); ?></h2>
		<form action="" method="post" rel="voucher-form">
			<input type="text" name="voucher" rel="voucher">
			<input type="submit" value="<?php _e( 'Add voucher', 'owc' ); ?>" rel="voucher-add">
		</form>
	<?php endif; ?>
</div>