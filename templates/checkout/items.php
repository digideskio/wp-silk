<ul rel="items">
	<?php foreach( OWC\Silk\Cart::$selection->items as $item ) : ?>
		<li rel="item" data-id="<?php echo esc_attr( $item->item ); ?>">
			<?php printf( '%s (%s) - %s', esc_html( $item->productName ), esc_html( $item->size ), esc_html( $item->totalPrice ) ); ?>
			<button rel="item-sub">-</button>
			<input rel="item-qty" type="text" value="<?php echo esc_html( $item->quantity ); ?>" size="1">
			<button rel="item-add">+</button>
			<button rel="item-remove">Remove</button>
		</li>
	<?php endforeach; ?>
</ul>