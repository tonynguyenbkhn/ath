import { select, on } from 'lib/dom'

export default el => {
	const checkoutForm = select('form.checkout', el)

	if (!checkoutForm) {
		return
	}

	let refreshTimer = null
	let loadingOverlay = null
	const getCartForm = () => select('.woocommerce-cart-form')
	const getQuantityInput = () => select('.twmp-ticket-quantity__input', el)

	const setLoadingState = isLoading => {
		if (isLoading) {
			if (!loadingOverlay) {
				loadingOverlay = document.createElement('div')
				loadingOverlay.className = 'twmp-checkout-ticket-detail__loading'
				loadingOverlay.setAttribute('aria-hidden', 'true')
				loadingOverlay.style.cssText = [
					'position:absolute',
					'inset:0',
					'z-index:999',
					'cursor:wait',
					'background:rgba(255,255,255,.45)',
				].join(';')

				el.style.position = el.style.position || 'relative'
				el.appendChild(loadingOverlay)
			}

			el.setAttribute('aria-busy', 'true')
			el.classList.add('is-loading')
			return
		}

		el.removeAttribute('aria-busy')
		el.classList.remove('is-loading')

		if (loadingOverlay && loadingOverlay.parentNode) {
			loadingOverlay.parentNode.removeChild(loadingOverlay)
		}

		loadingOverlay = null
	}

	const serializeForm = form => {
		const formData = new FormData(form)
		return new URLSearchParams(formData).toString()
	}

	const updateCheckoutSession = () => {
		if (
			typeof window.wc_checkout_params === 'undefined' ||
			!window.wc_checkout_params ||
			!window.wc_checkout_params.wc_ajax_url
		) {
			return Promise.resolve()
		}

		const body = new URLSearchParams()
		body.append('security', window.wc_checkout_params.update_order_review_nonce || '')
		body.append('post_data', serializeForm(checkoutForm))

		const url = window.wc_checkout_params.wc_ajax_url.replace('%%endpoint%%', 'update_order_review')

		return fetch(url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
			},
			body: body.toString(),
		}).then(() => undefined)
	}

	const submitCartUpdate = () => {
		const cartForm = getCartForm()

		if (!cartForm) {
			window.location.reload()
			return Promise.resolve()
		}

		const body = new URLSearchParams(serializeForm(cartForm))
		body.set('update_cart', '1')

		return fetch(cartForm.action, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
			},
			body: body.toString(),
		}).then(() => {
			window.location.reload()
			})
	}

	const clampQuantity = value => {
		const parsed = Number.parseInt(value, 10)
		if (Number.isNaN(parsed) || parsed < 1) {
			return 1
		}

		return parsed
	}

	const syncQuantityInput = nextValue => {
		const quantityInput = getQuantityInput()
		if (!quantityInput) {
			return
		}

		quantityInput.value = String(clampQuantity(nextValue))
		quantityInput.dispatchEvent(new Event('change', { bubbles: true }))
	}

	const updateQuantityByStep = step => {
		const quantityInput = getQuantityInput()
		if (!quantityInput) {
			return
		}

		const currentValue = clampQuantity(quantityInput.value)
		const nextValue = step === 'plus' ? currentValue + 1 : currentValue - 1
		syncQuantityInput(nextValue)
	}

	on(
		'click',
		e => {
			const target = e.target
			const stepButton = target && target.closest ? target.closest('[data-ticket-quantity-step]') : null

			if (!stepButton) {
				return
			}

			e.preventDefault()
			updateQuantityByStep(stepButton.getAttribute('data-ticket-quantity-step'))
		},
		el
	)

	on(
		'change',
		e => {
			const target = e.target

			if (
				!target ||
				![
					'twmp_ticket_price_option',
					'twmp_ticket_performance',
					'twmp_ticket_quantity',
				].includes(target.name)
			) {
				return
			}

			if (target.name === 'twmp_ticket_quantity') {
				target.value = String(clampQuantity(target.value))
			}

			window.clearTimeout(refreshTimer)
			setLoadingState(true)

			refreshTimer = window.setTimeout(() => {
				updateCheckoutSession()
					.then(() => submitCartUpdate())
					.catch(() => submitCartUpdate())
					.finally(() => {
						setLoadingState(false)
					})
			}, 100)
		},
		el
	)
}
