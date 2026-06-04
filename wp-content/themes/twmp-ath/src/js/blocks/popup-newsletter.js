import Modal from 'lib/modal'
import { trigger } from 'lib/dom'
import Swiper from 'swiper'
import { Navigation } from 'swiper/modules'

const WELCOME_DELAY = 5000
const WELCOME_EVENT = 'twmp:show-welcome'

export default el => {
	if (!el) return null

	// ensure modal exists
	Modal(el, {
		id: 'modal-popup-newsletter'
	})

	const STORAGE_KEY = 'twmp-ath-popup-newsletter-shown'

	const hasShown = () => {
		try {
			return window.localStorage.getItem(STORAGE_KEY) === '1'
		} catch (err) {
			return false
		}
	}

	const setShown = () => {
		try {
			window.localStorage.setItem(STORAGE_KEY, '1')
		} catch (err) {
		}
	}

	let swiperInstance = null
	let didActivate = false
	let welcomeTimer = null

	const initSlider = () => {
		const swiperEl = el.querySelector('.js-swiper')
		if (!swiperEl) return null

		if (swiperInstance) return swiperInstance

		swiperInstance = new Swiper(swiperEl, {
			modules: [Navigation],
			loop: false,
			slidesPerView: 3,
			spaceBetween: 24,
			navigation: {
				nextEl: el.querySelector('.swiper-button-next'),
				prevEl: el.querySelector('.swiper-button-prev'),
			},
			breakpoints: {
				640: { slidesPerView: 1 },
				768: { slidesPerView: 2 },
				1200: { slidesPerView: 3 }
			}
		})

		return swiperInstance
	}

	el.addEventListener('activate', () => {
		didActivate = true
		window.twmpAthNewsletterActive = true
		window.twmpAthNewsletterShownThisSession = true

		if (welcomeTimer) {
			clearTimeout(welcomeTimer)
			welcomeTimer = null
		}

		// set modal wrapper width if present
		const wrapper = el.querySelector('.modal__wrapper')
		if (wrapper) wrapper.style.maxWidth = '1042px'

		initSlider()

		// mark as shown so it only appears on first load
		setShown()
	})

	el.addEventListener('deactivate', () => {
		window.twmpAthNewsletterActive = false

		if (swiperInstance && typeof swiperInstance.destroy === 'function') {
			swiperInstance.destroy(true, true)
			swiperInstance = null
		}

		if (didActivate) {
			if (welcomeTimer) {
				clearTimeout(welcomeTimer)
			}

			welcomeTimer = setTimeout(() => {
				// dispatch a global event so welcome module can decide to show itself
				try {
					document.dispatchEvent(new CustomEvent(WELCOME_EVENT, {
						detail: {
							source: 'newsletter'
						}
					}))
				} catch (err) {
					// ignore
				}
			}, WELCOME_DELAY)
		}
	})

	// show on first visit only
	if (!hasShown()) {
		trigger('activate', el)
	}

	return null
}
