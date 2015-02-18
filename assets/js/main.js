var OWC_Shop;

(function($) {

	OWC_Shop = function( options ) {

		/*
		|-----------------------------------------------------------
		| SETUP
		|-----------------------------------------------------------
		*/

		var self = this,
			i;

		self.cartLength = 0;
		self.elements   = {};

		options = $.extend( {
			currency : '',
			elements : {
				cart        : '[rel=shop-cart]',
				cartLength  : '[rel=shop-cart-length]',
				cartForm    : '[rel=shop-cart-form]',
				cartVariant : '[rel=shop-cart-variant]',
				cartQty     : '[rel=shop-cart-qty]',
				cartAdd     : '[rel=shop-cart-add]',
				cartPrice   : '[rel=shop-cart-price]',
				item        : '[rel=shop-item]',
				itemPrice   : '[rel=shop-item-price]',
				itemRemove  : '[rel=shop-item-remove]',
				itemQty     : '[rel=shop-item-qty]',
				itemStep    : '[rel=shop-item-sub], [rel=shop-item-add]',
				total       : '[rel=shop-total]',
				checkoutLink: '[rel=shop-checkout]'
			},
			classes : {
				loading : 'is-loading',
				done    : 'is-done',
				verydone: 'is-very-done'
			}
		}, options );

		for ( element in options.elements ) {
			self.elements['$' + element] = $( options.elements[element] );
		}

		/*
		|-----------------------------------------------------------
		| EVENTS
		|-----------------------------------------------------------
		*/

		self.elements.$cartForm.on( 'change', function() {
			self.updateCartPrice();

			return false;
		} );

		/* Add to cart */
		self.elements.$cartForm.on( 'submit', function(e) {
			e.preventDefault();
			console.log(e.target);

			var $form = $(this),
				id    = $form.find( options.elements.cartVariant ).val();

			self.addToCart( id, $form.find('button') );

			return false;
		} );


		self.elements.$item.on( 'click', options.elements.itemRemove, function(e) {
			e.preventDefault();

			var id = $( e.delegateTarget ).attr( 'data-id' );

			self.removeFromCart( id );

			return false;
		} );
	
		self.elements.$item.on( 'change', options.elements.itemQty, function(e) {
			var id    = $( e.delegateTarget ).attr( 'data-id' ),
				price = $( e.delegateTarget ).attr( 'data-price' ),
				qty   = $( e.target ).val();

			if ( qty < 1 ) {
				$( e.target ).val( 1 );
				qty = 1;
			}

			self.updateQty( id, qty );
			self.updateItemPrice( id, price, qty );

			return false;
		} );

		self.elements.$item.on( 'click', options.elements.itemStep, function(e) {
			e.preventDefault();

			var id    = $( e.delegateTarget ).attr( 'data-id' ),
				price = $( e.delegateTarget ).attr( 'data-price' ),
				inp = $( options.elements.itemQty, e.delegateTarget ),
				qty   = inp.val(),
				step = $(this).attr('rel') === 'shop-item-add' ? 1 : -1;

			qty = parseInt(qty, 10) + step;

			if (qty > 0) {
				inp.val(qty);
				self.updateQty( id, qty );
				self.updateItemPrice( id, price, qty );
			} else {
				inp.val(0);
				self.removeFromCart( id );
			}

			return false;
		} );

		/*
		// update cart when tab is refocused
		document.addEventListener("visibilitychange", function () {
			if (!document.hidden) {
				console.log('welcome back, let\'s reload the cart!')
			}
		}, false);
		*/

		/*
		|-----------------------------------------------------------
		| METHODS
		|-----------------------------------------------------------
		*/

		self.addToCart = function( product_id, el ) {
			if ( product_id < 1 )
				return;
			
			self.elements.$cartForm.addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action		: 'add_to_cart',
					product_id	: product_id
				}
			} ).done( function( response ) {
				if ( response.success ) {
					self.updateCartLength( response.data.totals.totalQuantity );
					self.updateTotalPrice( response.data.totals.grandTotalPrice );
				}
			} ).always( function() {
				self.elements.$cartForm.removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.removeFromCart = function( id, isReplacing ) {
			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action : 'owc_shop_remove_from_cart',
					id     : id
				}
			} ).done( function( response ) {
				if ( typeof response.data != 'undefined' ) {
					self.updateCartLength( response.data[0], isReplacing );
					self.updateTable( id );
					self.updateTotalPrice( response.data[1] );
				}
			} );
		}

		self.updateQty = function( id, qty ) {
			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action : 'owc_shop_update_quantity',
					id     : id,
					qty    : qty
				}
			} ).done( function( response ) {
				if ( typeof response.data != 'undefined' ) {
					self.updateCartLength( response.data[0] );
					self.updateTotalPrice( response.data[1] );
				}
			} );
		}

		self.updateCartPrice = function() {
			/*
			var id    = self.elements.$cartVariant.val(),
				price = self.elements.$cartVariant.find( 'option[value=' + id + ']' ).attr('data-price'),
				compare = self.elements.$cartVariant.find( 'option[value=' + id + ']' ).attr('data-compare'),
				qty   = self.elements.$cartQty.val();

			self.elements.$cartPrice.text( self.formatPrice( price * qty ) );
			if ( compare ) self.elements.$cartPrice.addClass('is-sale').append('<strike>' + self.formatPrice( parseInt(compare, 10) ) + '</strike>');
			*/
		}

		self.updateCartLength = function( length, isReplacing ) {
			self.elements.$cartLength.text( length ).addClass('has-items');

			if ( !length && !isReplacing ) {
				self.elements.$cartLength.removeClass('has-items');
				window.location.reload();
			}
		}

		self.updateItemPrice = function( id, price, qty ) {
			$( '[data-id=' + id + ']' ).find( options.elements.itemPrice ).text( self.formatPrice( price * qty ) );
		}

		self.updateTotalPrice = function( total_sum ) {
			console.log(total_sum);
			self.elements.$total.text( total_sum );
		}

		self.formatPrice = function( price ) {
			var replaceStr = options.currency.indexOf('{{amount_no_decimals}}') != -1 ? '{{amount_no_decimals}}' : '{{amount}}';

			return options.currency.replace( replaceStr, price.toFixed( 2 ) );
		}

		/*
		|-----------------------------------------------------------
		| INIT
		|-----------------------------------------------------------
		*/
		//self.updateCartPrice();
	};

})(jQuery);
