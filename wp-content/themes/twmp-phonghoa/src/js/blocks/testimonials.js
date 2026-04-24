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
			slidesPerView: 'auto',
			roundLengths: true,
			navigation: {
				nextEl: select('.swiper-button-next', el),
				prevEl: select('.swiper-button-prev', el)
			},
			pagination: {
				el: select('.swiper-pagination', el),
				clickable: true
			},
			loop: true,
			breakpoints: {
				320: {
					slidesPerView: 1
				},
				768: {
					slidesPerView: 2,
					spaceBetween: 30
				},
				1024: {
					slidesPerView: 3,
					spaceBetween: 30
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

	init()

	on('scroll', throttle(init, 100), window)
}
