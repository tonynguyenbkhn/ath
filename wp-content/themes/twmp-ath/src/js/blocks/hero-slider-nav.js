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
import { Navigation, Pagination, Autoplay, Thumbs } from 'swiper/modules'

export default el => {
	let swiperEl = select('.js-swiper', el)
	let thumbsSwiperEl = select('.js-thumbs-swiper', el)

	let settings = null
	let swiper = null
	let thumbsSwiper = null

	const init = () => {
		if (!inViewPort(el)) return

		if (!swiperEl && hasClass('is-not-loaded', el)) {
			loadNoscriptContent(el)

			swiperEl = select('.js-swiper', el)
			thumbsSwiperEl = select('.js-thumbs-swiper', el)

			settings = getData('settings', swiperEl)
				? JSON.parse(getData('settings', swiperEl))
				: {}
		}


		if (swiper && thumbsSwiper) return


		thumbsSwiper = new Swiper(thumbsSwiperEl, {
			modules: [Thumbs],
			slidesPerView: 1.2,
			loop: false,
			spaceBetween: 16,
			breakpoints: {
				576: {
					slidesPerView: 2
				},
				768: {
					slidesPerView: 3
				},
				992: {
					slidesPerView: 4
				},
				1080: {
					slidesPerView: 5
				}
			},
		})

		const swiperSettings = {
			modules: [Navigation, Pagination, Thumbs],
			slidesPerView: 1,
			navigation: {
				nextEl: select('.swiper-button-next', el),
				prevEl: select('.swiper-button-prev', el)
			},
			pagination: {
				el: select('.swiper-pagination', el),
				clickable: true
			},
			thumbs: {
				swiper: thumbsSwiper
			},
			on: {
				init: function () {
					addClass('swiper-loaded', swiperEl)
				}
			}
		}

		// if (settings && settings.autoplay && settings.autoplay > 0) {
		// 	swiperSettings.modules = [...swiperSettings.modules, ...[Autoplay]]
		// 	swiperSettings.autoplay = {
		// 		delay: parseInt(settings.autoplay)
		// 	}
		// }

		swiper = new Swiper(swiperEl, swiperSettings)

		swiper.on('slideChange', function () {
			const activeIndex = swiper.activeIndex
			const activeSlideEl = swiper.slides[activeIndex]

			loadNoscriptContent(activeSlideEl)
		})
	}

	init()

	let currentThumbIndex = 0

	const activateNextThumb = () => {
		if (!thumbsSwiper || !swiper) return

		currentThumbIndex = (currentThumbIndex + 1) % thumbsSwiper.slides.length

		// Điều khiển thumbsSwiper và đồng bộ với swiper chính
		thumbsSwiper.slideTo(currentThumbIndex)
		swiper.slideTo(currentThumbIndex)
	}

	// Tự động active thumbnail sau mỗi 3 giây (bạn có thể chỉnh lại thời gian)
	setInterval(activateNextThumb, 5000)

	on('scroll', throttle(init, 100), window)
}
