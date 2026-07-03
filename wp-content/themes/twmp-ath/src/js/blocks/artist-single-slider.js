import Swiper from 'swiper'
import { Navigation, Pagination } from 'swiper/modules'

const parseSettings = el => {
	const rawSettings = el.getAttribute('data-settings')

	if (!rawSettings) {
		return {}
	}

	try {
		return JSON.parse(rawSettings)
	} catch (error) {
		console.warn('artist single slider: invalid swiper settings', error)
		return {}
	}
}

export default el => {
	if (!el) {
		return null
	}

	const swiperEl = el.classList.contains('js-swiper') ? el : el.querySelector('.js-swiper')

	if (!swiperEl) {
		return null
	}

	const section = swiperEl.closest('.artist-single-slider')
	const settings = parseSettings(swiperEl)

	return new Swiper(swiperEl, {
		modules: [Navigation, Pagination],
		loop: false,
		slidesPerView: 1.15,
		spaceBetween: 24,
		...settings,
		navigation: {
			nextEl: section?.querySelector('.swiper-button-next'),
			prevEl: section?.querySelector('.swiper-button-prev'),
			...(settings.navigation && typeof settings.navigation === 'object' ? settings.navigation : {}),
		},
		pagination: {
			el: section?.querySelector('.swiper-pagination'),
			type: 'progressbar',
			clickable: false,
			...(settings.pagination && typeof settings.pagination === 'object' ? settings.pagination : {}),
		},
	})
}
