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
				cart        : '[rel=cart]',
				cartLength  : '[rel=cart-length]',
				total       : '[rel=cart-total]',

				productForm : '[rel=product-form]',
				productSize : '[rel=product-size]',
				productQty  : '[rel=product-qty]',

				items       : '[rel=items]',
				item        : '[rel=item]',
				itemRemove  : '[rel=item-remove]',
				itemQty     : '[rel=item-qty]',
				itemStep    : '[rel=item-sub], [rel=item-add]',

				summary         : '[rel=summary]',
				summaryItems    : '[rel=summary-items]',
				summaryTaxes    : '[rel=summary-taxes]',
				summaryShipping : '[rel=summary-shipping]',
				summaryTotal    : '[rel=summary-total]',

				voucherSection  : '[rel=voucher-section]',
				voucher         : '[rel=voucher]',
				voucherForm     : '[rel=voucher-form]',
				voucherAdd      : '[rel=voucher-add]',
				voucherRemove   : '[rel=voucher-remove]',

				paymentMethods  : '[rel=payment-methods]',
				paymentMethod   : '[rel=payment-method]',
				shippingMethods : '[rel=shipping-methods]',
				shippingMethod  : '[rel=shipping-method]',

				sameShipping : '[rel=same-shipping]',
				billingForm  : '[rel=billing-information]',
				country      : '[rel=billing-country]',
				shippingForm : '[rel=shipping-information]',

				submitForm	 : '[rel=checkout-submit]',

				personalNumberForm	 : '[rel=personal-number]'
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
		// Product page: Add to cart
		self.elements.$productForm.on( 'submit', function(e) {
			e.preventDefault();

			var $form = $(this),
				id    = $form.find( options.elements.productSize ).val();
				qty   = $form.find( options.elements.productQty );

			if ( qty.length )
				qty = qty.val()
			else
				qty = 1;

			self.addToCart( id, $form.find('button'), qty );
		} );

		// Checkout: Billing information
		var billingInformationTimeout;
		self.elements.$billingForm.on( 'change', 'input', function() {
			clearTimeout( billingInformationTimeout );

			billingInformationTimeout = setTimeout(function(){
				self.updateSelection( self.elements.$billingForm.serialize(), true );
			}, 50 );
		} );

		// Checkout: Same shipping toggle
		self.elements.$sameShipping.on( 'change', function(){
			if ( $(this).is(':checked') )
				self.elements.$shippingForm.hide();
			else
				self.elements.$shippingForm.show();
		} );

		// Checkout: Shipping information
		var shippingInformationTimeout;
		self.elements.$shippingForm.on( 'change', 'input', function() {
			if ( ! self.elements.$sameShipping.is(':checked') )
				self.elements.$shippingForm.find('input').val('');

			clearTimeout( shippingInformationTimeout );

			shippingInformationTimeout = setTimeout(function(){
				self.updateSelection( self.elements.$shippingForm.serialize(), true );
			}, 50 );
		} );

		// Checkout: Payment method
		self.elements.$paymentMethods.on( 'change', options.elements.paymentMethod, function(){
			var paymentMethod = $(this).val();

			self.updatePaymentMethod( paymentMethod );

			if ( paymentMethod.indexOf('klarna') > -1 ) {
				$('body').addClass('is-klarna');
			} else {
				$('body').removeClass('is-klarna');
			}
		} );

		// Checkout: Country change
		self.elements.$country.on( 'change', function(){
			self.updateCountry( $(this).val() );
		} );

		// Checkout: Product delete
		self.elements.$items.on( 'click', options.elements.itemRemove, function(e) {
			e.preventDefault();

			var id = $( e.target ).closest(options.elements.item).data( 'id' );

			self.updateQty( id, 0 );
		} );

		// Checkout: Product add/remove
		self.elements.$items.on( 'click', options.elements.itemStep, function(e) {
			e.preventDefault();

			var item = $( e.target ).closest(options.elements.item),
				id   = item.attr( 'data-id' ),
				inp  = $( options.elements.itemQty, item ),
				qty  = inp.val(),
				step = $(this).attr('rel') === 'item-add' ? 1 : -1;

			qty = parseInt(qty, 10) + step;

			inp.val(qty);
			self.updateQty( id, qty );
		} );

		// Checkout: Product quantity change
		self.elements.$items.on( 'change', options.elements.itemQty, function(e) {
			e.preventDefault();

			var item = $( e.target ).closest(options.elements.item),
				id   = item.attr( 'data-id' ),
				inp  = $( options.elements.itemQty, item ),
				qty  = inp.val();

			self.updateQty( id, qty );
		} );

		// Checkout: Add voucher
		self.elements.$voucherSection.on( 'submit', options.elements.voucherForm, function(e){
			e.preventDefault();

			var voucher = $(this).find(options.elements.voucher).val();

			self.addVoucher( voucher );
		} );

		// Checkout: Remove voucher
		self.elements.$voucherSection.on( 'click', options.elements.voucherRemove, function(e){
			e.preventDefault();

			var voucher = $(this).data('voucher');

			self.removeVoucher( voucher );
		} );

		// Checkout: Personal number
		self.elements.$personalNumberForm.on( 'submit', function(e) {
			e.preventDefault();

			var personalNumber = $(this).find('input').val();

			self.fetchPersonalInformation( personalNumber );
		} );

		/*
		|-----------------------------------------------------------
		| METHODS
		|-----------------------------------------------------------
		*/

		self.addToCart = function( product_id, el, qty ) {
			if ( product_id < 1 )
				return;

			self.elements.$productForm.addClass( options.classes.loading ).removeClass( options.classes.done );

			if ( typeof qty == 'undefined' )
				qty = 1;

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action		: 'add_to_cart',
					product_id	: product_id,
					quantity    : qty
				}
			} ).done( function( response ) {
				if ( response.success ) {
					$(document).trigger('added_to_cart');
					self.updateCartLength( response.data.totals.totalQuantity );
					self.updateTotalPrice( response.data.totals.grandTotalPrice );
				}
			} ).always( function() {
				self.elements.$productForm.removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.updateQty = function( product_id, quantity ) {
			if ( product_id < 1 )
				return;

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action		: 'update_quantity',
					product_id	: product_id,
					quantity    : quantity
				}
			} ).done( function( response ) {
				if ( response.success ) {
					self.updateCartLength( response.data.totals.totalQuantity );
					self.updateTotalPrice( response.data.totals.grandTotalPrice );

					self.updateItems( response.data.items );
					self.updateSummary( response.data.summary );
				}
			} ).always( function() {
				//
			} );
		}

		self.updateTotalPrice = function( total_sum ) {
			self.elements.$total.text( total_sum );
		}

		self.updateCartLength = function( length ) {
			self.elements.$cartLength.text( length ).addClass('has-items');
		}

		self.updateItems = function( html ) {
			self.elements.$items.html( $(html).html() );
		}

		self.updateSummary = function( html ) {
			self.elements.$summary.html( $(html).html() );
		}

		self.updateSelection = function( data, parseData ) {

			var button = self.elements.$submitForm.find('input[type=submit]');

			button.attr('disabled', true).addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action : 'update_selection',
					data   : data,
					parse_data : parseData
				}
			} ).done( function( response ) {
				$(document).trigger("validate");
				button.attr('disabled', false).removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.updatePaymentMethod = function( paymentMethod ) {

			var button = self.elements.$submitForm.find('input[type=submit]');

			button.attr('disabled', true).addClass( options.classes.loading ).removeClass( options.classes.done );

			self.elements.$productForm.addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action : 'update_payment_method',
					payment_method   : paymentMethod
				}
			} ).done( function( response ) {
				self.elements.$items.html( $(response.data.items).html() );
				self.elements.$summary.html( $(response.data.summary).html() );
				self.elements.$paymentMethods.html( $(response.data.paymentMethods).html() );
				self.elements.$shippingMethods.html( $(response.data.shippingMethods).html() );

				button.attr('disabled', false).removeClass( options.classes.loading ).addClass( options.classes.done );
				self.elements.$productForm.removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.updateCountry = function( country ) {
			var button = self.elements.$submitForm.find('input[type=submit]');

			button.attr('disabled', true).addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action  : 'update_country',
					country : country
				}
			} ).done( function( response ) {
				self.elements.$items.html( $(response.data.items).html() );
				self.elements.$summary.html( $(response.data.summary).html() );
				self.elements.$paymentMethods.html( $(response.data.paymentMethods).html() );
				self.elements.$shippingMethods.html( $(response.data.shippingMethods).html() );
				button.attr('disabled', false).removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.addVoucher = function( voucher ) {
			self.elements.$voucher.removeClass('error');
			self.elements.$voucherForm.removeClass('not-valid');

			if( ! voucher) {
				self.elements.$voucher.addClass('error');

				return;
			}

			self.elements.$voucherAdd.attr('disabled', true).addClass( options.classes.loading ).removeClass( options.classes.done );

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action	: 'add_voucher',
					voucher	: voucher
				}
			} ).done( function( response ) {
				if ( response.success ) {
					self.elements.$summary.html( $(response.data.summary).html() );
					self.elements.$voucherSection.html( $(response.data.voucher).html() );
				} else {
					self.elements.$voucher.addClass('error');
					self.elements.$voucherForm.addClass('not-valid');
				}
			} ).always( function() {
				self.elements.$voucherAdd.attr('disabled', false).removeClass( options.classes.loading ).addClass( options.classes.done );
			} );
		}

		self.removeVoucher = function( voucher ) {
			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action	: 'remove_voucher',
					voucher	: voucher
				}
			} ).done( function( response ) {
				if ( response.success ) {
					self.elements.$summary.html( $(response.data.summary).html() );
					self.elements.$voucherSection.html( $(response.data.voucher).html() );
				}
			} ).always( function() {
				//
			} );
		}

		self.fetchPersonalInformation = function( personalNumber ) {
			self.elements.$billingForm.removeClass('got-address');

			$.ajax( {
				type : 'post',
				url  : ajaxurl,
				data : {
					action	: 'fetch_personal_information',
					personal_number	: personalNumber
				}
			} ).done( function( response ) {
				if ( ! response.data.errors ) {
					$.each(response.data, function(key, val){
						$('[name="address[' + key + ']"]').val(val); //.attr('disabled', true);
					} );
					self.elements.$billingForm.addClass('got-address');
					self.updateSelection( self.elements.$billingForm.serialize(), true );
				} else {
					self.elements.$personalNumberForm.find('input').after('<small class="error-txt">' + self.elements.$personalNumberForm.data('error-text') + '</small>');
				}
			} ).always( function() {
				//
			} );
		}
	};
})(jQuery);
