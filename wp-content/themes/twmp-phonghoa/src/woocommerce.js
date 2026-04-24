document.addEventListener('DOMContentLoaded', function () {
	;(function ($) {
		'use strict'

		$('.single_add_to_cart_button').addClass('js-add-to-cart')

		$('body').delegate('.js-add-to-cart', 'click', function (e) {
			e.preventDefault()
			var $buttonEl = $(e.target)
			var $buttonElValue = e.target.value

			var productId =
				Number($buttonEl.data('product-id')) > 0
					? $buttonEl.data('product-id')
					: Number($buttonElValue) > 0
					? $buttonElValue
					: 0	
			if (productId > 0) {
				e.preventDefault()

				var data = {
					action: 'woocommerce_ajax_add_to_cart',
					product_id: productId,
					product_sku: '',
					quantity: 1
				}

				$('body').trigger('adding_to_cart', [$buttonEl, data])

				$.ajax({
					type: 'POST',
					url: woocommerce_params.wc_ajax_url
						.toString()
						.replace('%%endpoint%%', 'add_to_cart'),
					data: data,
					beforeSend: function (response) {
						$buttonEl.removeClass('added').addClass('loading')
					},
					complete: function (response) {
						$buttonEl.addClass('added').removeClass('loading')

						setTimeout(function () {
							$buttonEl.removeClass('added')
						}, 4000)
					},
					success: function (response) {
						if (response.error && response.product_url) {
							window.location = response.product_url
							return
						} else {
							$('body').trigger('added_to_cart', [
								response.fragments,
								response.cart_hash,
								$buttonEl
							])
							window.location.href = '/checkout/?pay=kredivo'
						}
					}
				})
			}
		})
	})(jQuery)
})