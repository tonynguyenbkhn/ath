import {
	select,
	selectAll,
	on,
	trigger,
	getData,
	addClass,
	hasClass,
	removeClass,
	loadNoscriptContent,
	inViewPort
} from 'lib/dom'
import { throttle } from 'lib/utils'
import Tabs from 'lib/tabs'
require('whatwg-fetch')
import Swiper from 'swiper'
import { Navigation, Pagination } from 'swiper/modules'

const parseJSON = response => response.json()

export default el => {
	const contentEl = select('.js-main-content', el)
	let tabPanels = selectAll('[role="tabpanel"]', el)
	let desktopTriggerEls = selectAll('[role="tab"]', el)
	let loaded = false
	let isFetching = false

	const settings = getData('settings', el)
		? JSON.parse(getData('settings', el))
		: {}

	const getRestUrl = categoryId => {
		return `${twmpConfig.ajax.restUrl}/${settings.endpoint}/?category_id=${categoryId}&posts_per_page=${settings.postsPerPage}&query_type=${settings.queryType}`
	}

	const swiperInit = ell => {
		const swiperEl = select('.js-swiper', ell)
		if (!swiperEl || hasClass('swiper-initialized', swiperEl)) return

		const swiperSettings = getData('settings', swiperEl)
			? JSON.parse(getData('settings', swiperEl))
			: {}

		new Swiper(swiperEl, {
			modules: [Navigation, Pagination],
			slidesPerView: 5,
			loop: true,
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
		})
	}

	const tabState = Tabs(el, {
		lazyload: true,
		lazyloadCallback: (navItem, panelItem) => {}
	})

	const init = () => {
		if (loaded) return

		if (contentEl && hasClass('is-not-loaded', contentEl)) {
			loadNoscriptContent(contentEl)
			removeClass('is-loading', el)
		}
		swiperInit(tabPanels[0])
		// Gán sự kiện cho tab
		on(
			'click',
			e => {
				const index = desktopTriggerEls.indexOf(e.target)
				trigger({ event: 'update', data: { currentIndex: index } }, el)
			},
			desktopTriggerEls
		)

		// Lần đầu cập nhật tab
		trigger({ event: 'update', data: { currentIndex: 0 } }, el)

		loaded = true
		// Xóa event scroll sau khi init xong
		window.removeEventListener('scroll', checkInit)
		window.removeEventListener('load', checkInit)
	}

	const checkInit = throttle(() => {
		if (inViewPort(el) && !loaded) {
			init()
		}
	}, 300)

	on(
		'update',
		e => {
			if (isFetching) return
			const index = e.detail.currentIndex
			const currentTabEl = tabPanels[index]
			const categoryId = currentTabEl
				? getData('category-id', currentTabEl)
				: null
			const listEl = currentTabEl ? select('.js-grid', currentTabEl) : null

			if (listEl && hasClass('is-not-loaded', listEl) && categoryId) {
				addClass('is-loading', el)
				isFetching = true

				fetch(getRestUrl(categoryId))
					.then(parseJSON)
					.then(data => {
						if (data.html) {
							listEl.innerHTML = data.html
							removeClass('is-not-loaded', listEl)
						}
						removeClass('is-loading', el)
						swiperInit(currentTabEl)
					})
					.finally(() => {
						isFetching = false
					})
			}
		},
		el
	)

	// Thêm event listener cho load và scroll
	window.addEventListener('load', checkInit)
	window.addEventListener('scroll', checkInit)
}
