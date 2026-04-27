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
			slidesPerView: 1,
			loop: true,
			navigation: {
				nextEl: select('.swiper-button-next', el),
				prevEl: select('.swiper-button-prev', el)
			},
			pagination: {
				el: select('.swiper-pagination', el),
				clickable: true
			},
			on: {
				init: function () {
					addClass('swiper-loaded', swiperEl)
				}
			}
		}


		swiperSettings.modules = [...swiperSettings.modules, ...[Autoplay]]
		swiperSettings.autoplay = {
			delay: 5000
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
