import { select, on } from 'lib/dom'

export default el => {
	const checkoutForm = select('form.checkout', el)

	if (!checkoutForm) {
		return
	}

	let refreshTimer = null
	let loadingOverlay = null
	const getCartForm = () => select('.woocommerce-cart-form')

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

	on(
		'change',
		e => {
			const target = e.target

			if (
				!target ||
				![
					'twmp_ticket_price_option',
					'twmp_ticket_performance',
				].includes(target.name)
			) {
				return
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
