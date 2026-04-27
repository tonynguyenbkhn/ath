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
import { map, throttle, matchHeightMobile } from 'lib/utils'
import Tabs from 'lib/tabs'
require('whatwg-fetch')

const parseJSON = response => response.json()

export default el => {
	const contentEl = select('.js-main-content', el)
	let tabState = null
	let tabPanels = []
	let desktopTriggerEls = []
	let loaded = false

	const settings = getData('settings', el)
		? JSON.parse(getData('settings', el))
		: {}

	const getRestUrl = categoryId => {
		return `${twmpConfig.ajax.restUrl}/${settings.endpoint}/?category_id=${categoryId}&posts_per_page=${settings.postsPerPage}&query_type=${settings.queryType}`
	}

	const init = () => {
		if (loaded) {
			return true
		}

		if (contentEl && hasClass('is-not-loaded', contentEl)) {
			loadNoscriptContent(contentEl)
			removeClass('is-loading', el)
		}

		tabState = Tabs(el)
		desktopTriggerEls = selectAll('[role="tab"]', el)
		tabPanels = selectAll('[role="tabpanel"]', el)

		trigger(
			{
				event: 'update',
				data: {
					currentIndex: 0
				}
			},
			el
		)

		loaded = true
	}

	let isFetching = false

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
			const panels = selectAll('[role="tabpanel"]', el)

			if (listEl && hasClass('is-not-loaded', listEl) && categoryId) {
				addClass('is-loading', el)
				isFetching = true
				window
					.fetch(getRestUrl(categoryId))
					.then(parseJSON)
					.then(data => {
						if (data.html) {
							listEl.innerHTML = data.html
							matchHeightMobile('.product-tabs-category .product-tabs-category__tab-content.is-active .product-scroll-wrapper .products', ':scope > li');
							removeClass('is-not-loaded', listEl)
						}
						panels.forEach(item => removeClass('prev-active', item))
						removeClass('is-loading', el)
					})
					.finally(() => {
						isFetching = false
					})
			}
		},
		el
	)

	on(
		'load',
		throttle(() => {
			if (inViewPort(el) && !loaded) {
				init()
			}
		}, 100),
		window
	)

	on(
		'scroll',
		throttle(() => {
			if (inViewPort(el) && !loaded) {
				init()
			}
		}, 300),
		window
	)
}
