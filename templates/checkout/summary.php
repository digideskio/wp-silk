<dl rel="summary">
	<dt><?php _e( 'Total', 'owc' ); ?></dt>
	<dd rel="summary-items"><?php OWC\Silk\Cart::totals( 'itemsTotalPrice' ); ?></dd>
	<dt><?php _e( 'Shipping', 'owc' ); ?></dt>
	<dd rel="summary-shipping"><?php OWC\Silk\Cart::totals( 'shippingPrice' ); ?></dd>
	<dt><?php _e( 'Tax', 'owc' ); ?></dt>
	<dd rel="summary-taxes"><?php OWC\Silk\Cart::totals( 'grandTotalPriceTax' ); ?></dd>

	<?php if ( ! empty( OWC\Silk\Cart::get_totals( 'totalDiscountPrice' ) ) ) : ?>
		<dt><?php _e( 'Discount', 'owc' ); ?></dt>
		<dd rel="summary-discount"><?php OWC\Silk\Cart::totals( 'totalDiscountPrice' ); ?></dd>
	<?php endif; ?>

	<dt><?php _e( 'Grand Total', 'owc' ); ?></dt>
	<dd rel="summary-total"><?php OWC\Silk\Cart::totals( 'grandTotalPrice' ); ?></dd>
</dl>