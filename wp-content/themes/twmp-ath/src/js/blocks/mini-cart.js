/* global jQuery, location */
import {
	addClass,
	removeClass,
	hasClass,
	selectAll,
	on,
	trigger,
	getData
} from 'lib/dom'
import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock'

const $ = jQuery

const initQuantityCart = callbackFunction => {
	const inputEls = selectAll('.mini-cart-sidebar .qty')

	inputEls.forEach(inputEl => {
		let input = $(inputEl)
		let cartItemKey = getData('cart_item_key', inputEl) || ''

		input.on('input', function () {
			let inputVal = Number($(this).val() || 0)

			// Valid quantity.
			if (inputVal < 1 || isNaN(inputVal)) {
				return ''
			}

			let data = {
				action: 'update_quantity_in_mini_cart',
				nonce: twmpConfig.ajax.nonce,
				key: cartItemKey,
				qty: inputVal
			}
			$.ajax({
				url: twmpConfig.ajax.url,
				data: data,
				type: 'POST',
				// dataType: 'json',
				beforeSend: function (response) {
					loading()
				},
				complete: function (response) {
					$(document).trigger('quantity_updated')
				},
				success: function (result) {
					$('body').trigger('wc_fragment_refresh')
					done()
				}
			})
		})
	})
}

const VISIBLE_BODY_CLASS = 'is-mini-cart-opened'
const UPDATING_BODY_CLASS = 'is-mini-cart-updating'
const body = document.body
const triggers = selectAll('.js-minicart-trigger, .ajax_add_to_cart')

const visible = () => addClass(VISIBLE_BODY_CLASS, body)
const hide = () => removeClass(VISIBLE_BODY_CLASS, body)
const loading = () => addClass(UPDATING_BODY_CLASS, body)
const done = () => removeClass(UPDATING_BODY_CLASS, body)
const isCartPage = () => hasClass('woocommerce-cart', body)

const refresh = () => {
	initQuantityCart()
}

export default el => {
	const closeButtons = selectAll('.js-mini-cart-close', el)

	$(body).on('added_to_cart', () => {
		trigger('minicart.open', body)
	})

	on(
		'minicart.open',
		() => {
			if (isCartPage()) {
				return
			}

			refresh()
			disableBodyScroll(el)
			visible()
			done()
		},
		body
	)

	on(
		'minicart.close',
		() => {
			loading()
			hide()
			enableBodyScroll(el)
		},
		body
	)

	if (closeButtons) {
		on(
			'click',
			() => {
				trigger('minicart.close', body)
			},
			closeButtons
		)
	}

	on(
		'keydown',
		e => {
			if (e.code === 'Escape' && hasClass(VISIBLE_BODY_CLASS, body)) {
				trigger('minicart.close', body)
			}
		},
		window
	)

	on(
		'orientationchange',
		() => {
			trigger('minicart.close', body)
		},
		window
	)

	if (triggers) {
		on(
			'click',
			e => {
				e.preventDefault()

				trigger('minicart.open', body)
			},
			triggers
		)
	}

	initQuantityCart()

	$(document.body)
		.on('wc_fragments_loaded wc_fragments_refreshed', refresh)
		.on('wc_cart_emptied', () => {
			location.reload()
		})
}

export { initQuantityCart }
