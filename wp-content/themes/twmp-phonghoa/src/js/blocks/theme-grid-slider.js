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
			slidesPerView: 1.3,
			loop: true,
			spaceBetween: 15,
			navigation: {
				nextEl: select('.swiper-button-next', el),
				prevEl: select('.swiper-button-prev', el)
			},
			pagination: {
				el: select('.swiper-pagination', el),
				clickable: true
			},
			breakpoints: {
				782: {
					slidesPerView: 2
				},
				1080: {
					slidesPerView: 4
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

		twmp_rating('.rating')
	}

	init()

	on('scroll', throttle(init, 100), window)
}
