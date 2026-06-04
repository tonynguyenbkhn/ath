import Modal from 'lib/modal'
import { trigger } from 'lib/dom'
import Swiper from 'swiper'
import { Navigation } from 'swiper/modules'
import gsap from 'gsap'

const WELCOME_DELAY = 5000
const WELCOME_EVENT = 'twmp:show-welcome'
const SHOW_AGAIN_AFTER = 1 * 24 * 60 * 60 * 1000

const getLastVisit = () => {
	try {
		const value = window.localStorage.getItem(STORAGE_KEY)
		const timestamp = Number(value)

		return Number.isFinite(timestamp) && timestamp > 0 ? timestamp : 0
	} catch (error) {
		return 0
	}
}

const setLastVisit = timestamp => {
	try {
		window.localStorage.setItem(STORAGE_KEY, String(timestamp))
	} catch (error) {
	}
}

export default el => {
	if (!el) return null

	// ensure modal exists
	Modal(el, {
		id: 'modal-popup-newsletter'
	})

	const STORAGE_KEY = 'twmp-ath-popup-newsletter-shown'

	const getLastShown = () => {
		try {
			const value = window.localStorage.getItem(STORAGE_KEY)
			const ts = Number(value)

			return Number.isFinite(ts) && ts > 0 ? ts : 0
		} catch (err) {
			return 0
		}
	}

	const setShown = (timestamp = Date.now()) => {
		try {
			window.localStorage.setItem(STORAGE_KEY, String(timestamp))
		} catch (err) {}
	}

	const shouldShowNewsletter = () => {
		const last = getLastShown()
		const now = Date.now()

		return !last || now - last >= SHOW_AGAIN_AFTER
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

		// entry animation
		const wrapper = el.querySelector('.modal__wrapper')
		if (!wrapper) return
		// stronger 3D look: set perspective on overlay and animate rotation/translateZ
		el.style.perspective = '1200px'
		gsap.killTweensOf(wrapper)
		gsap.set(wrapper, { transformStyle: 'preserve-3d', backfaceVisibility: 'hidden', willChange: 'transform,opacity' })
		gsap.fromTo(
			wrapper,
			{ rotationX: -60, rotationY: 12, z: -220, scale: 0.96, opacity: 0, boxShadow: '0px 8px 20px rgba(0,0,0,0.12)', transformOrigin: 'top center' },
			{ rotationX: 0, rotationY: 0, z: 0, scale: 1, opacity: 1, duration: 0.72, ease: 'power4.out', boxShadow: '0px 26px 80px rgba(0,0,0,0.28)' }
		)

		if (welcomeTimer) {
			clearTimeout(welcomeTimer)
			welcomeTimer = null
		}

		// set modal wrapper width if present
		if (wrapper) wrapper.style.maxWidth = '1042px'

		initSlider()

		// mark as shown (timestamp) so it respects daily frequency
		setShown()
	})

	let isClosing = false

	const animatedClose = () => {
		if (isClosing) return
		isClosing = true
		const wrapper = el.querySelector('.modal__wrapper')
		if (!wrapper) {
			trigger('deactivate', el)
			return
		}

		gsap.killTweensOf(wrapper)
		gsap.to(wrapper, {
			rotationX: 70,
			rotationY: -12,
			z: -220,
			scale: 0.94,
			opacity: 0,
			duration: 0.5,
			ease: 'power3.in',
			onComplete: () => {
				el.style.perspective = ''
				trigger('deactivate', el)
				isClosing = false
			}
		})
	}

	// intercept overlay clicks (capture) to animate before modal.js deactivates
	el.addEventListener('click', e => {
		if (e.target === el) {
			e.stopPropagation()
			e.preventDefault()
			animatedClose()
		}
	}, true)

	// intercept internal close buttons
	const closeButtons = el.querySelectorAll('[data-close-modal], .js-close-button')
	closeButtons.forEach(btn => {
		btn.addEventListener('click', e => {
			e.preventDefault()
			e.stopPropagation()
			animatedClose()
		})
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

	// show if not shown in the last day
	if (shouldShowNewsletter()) {
		trigger('activate', el)
	}

	return null
}
