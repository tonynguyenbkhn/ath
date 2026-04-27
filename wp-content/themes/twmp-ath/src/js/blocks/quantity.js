/* global jQuery */
import {
	select,
	selectAll,
	closest,
	getAttribute,
	setAttribute,
	addClass,
	removeClass,
	getData,
	on
} from 'lib/dom'

import { initQuantityCart } from './mini-cart'

const $ = jQuery

export default el => {
	var input = select('input.qty', el)
	if (!input) {
		return
	}
	addClass('is-loading', el)
	addClass('ajax-ready', el)

	// Create Minus button.
	const createMinusButton = () => {
		let el = document.createElement('div')

		addClass('product-qty', el)
		setAttribute('data-qty', 'minus', el)
		el.innerHTML =
			'<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path d="M15 8v1H2V8h13z"/></svg>'

		return el
	}

	// Create Plus button.
	const createPlusButton = () => {
		let el = document.createElement('div')

		addClass('product-qty', el)
		setAttribute('data-qty', 'plus', el)
		el.innerHTML =
			'<svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 17 17"><path d="M16 9H9v7H8V9H1V8h7V1h1v7h7v1z"/></svg>'

		return el
	}

	const initQuantity = () => {
		const quantityEl = select('.quantity', el)
		el.insertBefore(createMinusButton(), quantityEl)
		el.appendChild(createPlusButton())
	}

	initQuantity()

	const cart = closest('form.cart', el)
	const buttons = selectAll('.product-qty', el)
	const maxInput = Number(getAttribute('max', input))

	const eventChange = new Event('change')

	// Get product info.
	const productInfo = cart ? select('.additional-product', cart) : false
	const inStock = productInfo ? getData('in_stock', productInfo) : 'no'
	const outStock = productInfo
		? getData('out_of_stock', productInfo)
		: 'Out of stock'
	const notEnough = productInfo ? getData('not_enough', productInfo) : ''
	const quantityValid = productInfo
		? getData('valid_quantity', productInfo)
		: ''

	on(
		'change',
		e => {
			var inputVal = e.target.value
			var inCartQty = productInfo ? Number(productInfo.value || 0) : 0
			var min = Number(getAttribute('min', input) || 0)
			const ajaxReady = () => removeClass('ajax-ready', input)

			// When quantity updated.
			addClass('ajax-ready', input)

			// Valid quantity.
			if (inputVal < min || isNaN(inputVal)) {
				// eslint-disable-next-line no-undef
				alert(quantityValid)
				ajaxReady()
				return
			}

			// Stock status.
			if (inStock === 'yes') {
				// Out of stock.
				if (maxInput && inCartQty === maxInput) {
					// eslint-disable-next-line no-undef
					alert(outStock)
					ajaxReady()
					return
				}

				// Not enough quantity.
				if (maxInput && +inputVal + +inCartQty > maxInput) {
					// eslint-disable-next-line no-undef
					alert(notEnough)
					ajaxReady()
				}
			}
		},
		input
	)

	// Minus & Plus button click.
	on(
		'click',
		e => {
			const current = Number(input.value || 0)
			const step = Number(getAttribute('step', input) || 1)
			const min = Number(getAttribute('min', input) || 0)
			const max = Number(getAttribute('max', input) || 100)
			const target = closest('.product-qty', e.target)
			let dataType
			if (e.target.matches('.product-qty')) {
				dataType = getAttribute('data-qty', e.target)
			} else {
				dataType = getAttribute('data-qty', target)
			}
			if (dataType === 'minus' && current >= step) {
				// Minus button.
				if (current <= min || current - step < min) {
					return
				}

				input.value = current - step
			}

			if (dataType === 'plus') {
				// Plus button.
				if (max && (current >= max || current + step > max)) {
					return
				}

				input.value = current + step
			}

			// Trigger event.
			input.dispatchEvent(eventChange)
			$(input).trigger('change')
			initQuantityCart()
		},
		buttons
	)

	removeClass('is-loading', el)
}
