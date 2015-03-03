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
				item        : '[rel=shop-item]',
				itemPrice   : '[rel=shop-item-price]',
				itemRemove  : '[rel=shop-item-remove]',
				itemQty     : '[rel=shop-item-qty]',
				itemStep    : '[rel=shop-item-sub], [rel=shop-item-add]',
				total       : '[rel=shop-total]',
				paymentMethod  : '[rel=shop-checkout-payment-method]',
				shippingMethod : '[rel=shop-checkout-shipping-method]',
				billingForm : '[rel=shop-checkout-billing]',
				shippingForm: '[rel=shop-checkout-shipping]',
				sameShipping: '[rel=shop-checkout-same-shipping]',
				submitForm	: '[rel=shop-checkout-submit]'
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

		self.elements.$billingForm.on( 'change', 'input', function() {
			self.updateInformation( self.elements.$billingForm );

			return false;
		} );

		self.elements.$sameShipping.on( 'change', function(){
			if ( $(this).is(':checked') )
				self.elements.$shippingForm.hide();
			else
				self.elements.$shippingForm.show();
		} );

		self.elements.$shippingForm.on( 'change', 'input', function() {
			if ( self.elements.$sameShipping.is(':checked') )
				self.elements.$shippingForm.find('input').val('');
			
			self.updateInformation( self.elements.$shippingForm );

			return false;
		} );

		self.elements.$paymentMethod.on( 'change', function(){
			self.updateSelection({
				paymentMethod : $(this).val()
			});
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

		self.updateTotalPrice = function( total_sum ) {
			self.elements.$total.text( total_sum );
		}

		self.updateCartLength = function( length, isReplacing ) {
			self.elements.$cartLength.text( length ).addClass('has-items');
		}

		self.updateInformation = function( el ) {
			var button = self.elements.$submitForm.find('input[type=submit]'),
				data = el.serialize();

			button.attr('disabled', true).addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action : 'update_selection',
					data   : data,
					parse_data : true
				}
			} ).done( function( response ) {
				button.attr('disabled', false).removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.updateSelection = function( data ) {
			var button = self.elements.$submitForm.find('input[type=submit]');

			button.attr('disabled', true).addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action : 'update_selection',
					data   : data
				}
			} ).done( function( response ) {
				button.attr('disabled', false).removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}
	};
})(jQuery);
