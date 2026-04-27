import {
	select,
	hasClass,
	inViewPort,
	on,
	addClass,
	loadNoscriptContent,
	getData
} from 'lib/dom'
import { throttle } from 'lib/utils'
import Swiper from 'swiper'
import { Navigation, Pagination, Autoplay } from 'swiper/modules'

export default el => {
	let swiperEl = select('.js-swiper', el)
	let settings = null
	let swiper = null

	const init = () => {
		if (!inViewPort(el)) return

		if (!swiperEl && hasClass('is-not-loaded', el)) {
			loadNoscriptContent(el)

			swiperEl = select('.js-swiper', el)

			settings = getData('settings', swiperEl)
				? JSON.parse(getData('settings', swiperEl))
				: {}
		}

		if (swiper) return

		const swiperSettings = {
			modules: [Navigation, Pagination],
			slidesPerView: 2,
			slidesPerColumn: 2,
			loop: true,
			spaceBetween: 10,
			navigation: {
				nextEl: select('.swiper-button-next', el),
				prevEl: select('.swiper-button-prev', el)
			},
			pagination: {
				el: select('.swiper-pagination', el),
				clickable: true
			},
			breakpoints: {
				768: {
					slidesPerColumn: 1,
					slidesPerView: 3
				},
				992: {
					slidesPerColumn: 1,
					slidesPerView: 4
				},
				1080: {
					slidesPerColumn: 1,
					slidesPerView: 5
				}
			},
			on: {
				init: function () {
					addClass('swiper-loaded', swiperEl)
				}
			}
		}

		if (settings && settings.autoplay && settings.autoplay > 0) {
			swiperSettings.modules = [...swiperSettings.modules, ...[Autoplay]]
			swiperSettings.autoplay = {
				delay: parseInt(settings.autoplay)
			}
		}

		swiper = new Swiper(swiperEl, swiperSettings)

		swiper.on('slideChange', function () {
			const activeIndex = swiper.activeIndex
			const activeSlideEl = swiper.slides[activeIndex]

			loadNoscriptContent(activeSlideEl)
		})
	}

	const initCountdown = () => {
		const countdownWrapper = select('.countdown-wrapper', el)
		if (!countdownWrapper) return

		const endDateStr = countdownWrapper.dataset.endDate
		const endDate = new Date(endDateStr).getTime()

		const getDigitEls = (prefix) => ({
			_1: select(`.${prefix}_1`, countdownWrapper),
			_2: select(`.${prefix}_2`, countdownWrapper)
		})

		const daysEl = getDigitEls('day')
		const hoursEl = getDigitEls('hour')
		const minutesEl = getDigitEls('minute')
		const secondsEl = getDigitEls('second')

		const updateCountdown = () => {
			const now = new Date().getTime()
			const distance = endDate - now

			if (distance < 0) {
				['day', 'hour', 'minute', 'second'].forEach(prefix => {
					const els = getDigitEls(prefix)
					els._1.textContent = '0'
					els._2.textContent = '0'
				})
				clearInterval(timer)
				return
			}

			const days = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0')
			const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0')
			const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0')
			const seconds = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0')

			daysEl._1.textContent = days[0]
			daysEl._2.textContent = days[1]

			hoursEl._1.textContent = hours[0]
			hoursEl._2.textContent = hours[1]

			minutesEl._1.textContent = minutes[0]
			minutesEl._2.textContent = minutes[1]

			secondsEl._1.textContent = seconds[0]
			secondsEl._2.textContent = seconds[1]
		}

		updateCountdown()
		const timer = setInterval(updateCountdown, 1000)
	}

	if (!el || !hasClass('is-not-loaded', el)) {
		initCountdown()
	} else {
		const observer = new MutationObserver((mutations, obs) => {
			if (select('.countdown-wrapper', el)) {
				initCountdown()
				obs.disconnect()
			}
		})
		observer.observe(el || document.body, {
			childList: true,
			subtree: true
		})
	}

	init()

	on('scroll', throttle(init, 100), window)
}
