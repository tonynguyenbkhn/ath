import {
	on,
	inViewPort,
	trigger,
	selectAll,
	select,
	addClass,
	hasClass,
	removeClass,
	toggleClass,
	getAttribute,
	appendHtml,
	closest,
	delegate
} from 'lib/dom'
import { throttle, debounce, map } from 'lib/utils'
require('whatwg-fetch')
const body = document.body
const parseJSON = response => response.json()
export default el => {
	let loaded = false

	const $uls = selectAll('.wp-block-categories-list', el)
	let inputTriggerEls
	let paginations

	const CLASS_TOGGLE_FILTER = 'filter-theme__mobile-open'
	const filterThemeWrapper = select('.filter-theme', el)

	const init = () => {
		$uls.forEach(ul => {
			if (!hasClass('d-none', ul)) {
				addClass('d-none', ul)
			}
			let checkboxHtml = ''
			let nameInput
			if (hasClass('wp-block-categories', ul)) {
				nameInput = 'name="category[]"'
			} else if (hasClass('wp-block-nen-tang', ul)) {
				nameInput = 'name="nen-tang[]"'
			} else {
				nameInput = ''
			}
			const $lis = selectAll('.cat-item', ul)
			const $widget = closest('.widget_block', ul)
			$lis.forEach(li => {
				const classList = getAttribute('class', li).split(/\s+/)
				const catIdClass = classList.find(
					cls => cls.startsWith('cat-item-') && !cls.includes('current-cat')
				)
				const catId = catIdClass ? catIdClass.replace('cat-item-', '') : ''
				const isChecked = hasClass('current-cat', li) ? 'checked' : ''
				const labelText = select('a', li).textContent.trim()

				checkboxHtml +=
					'<div class="cat-item"><input ' +
					nameInput +
					' class="cat-item__input" type="checkbox" value="' +
					catId +
					'" ' +
					isChecked +
					'><label class="d-block">' +
					labelText +
					'</label></div>'
			})

			appendHtml(
				$widget,
				`<div class="wp-block-categories-list">${checkboxHtml}</div>`
			)
		})
	}

	const toggle = () => {
		toggleClass(CLASS_TOGGLE_FILTER, body)
	}

	delegate('click', toggle, '.js-filter-theme-icon-toggle', el)

	delegate('click', toggle, '[data-filter-theme-trigger]', body)

	const fetchPosts = ($currentPage = 1) => {
		if (loaded) {
			return true
		}

		trigger(
			{
				event: 'update',
				data: {
					currentPage: $currentPage
				}
			},
			el
		)

		loaded = true
	}

	const getSelectedValues = selector => {
		return Array.from(document.querySelectorAll(selector + ':checked')).map(
			input => input.value
		)
	}

	const getRestUrl = () => {
		return `${filterApi.ajax.restUrl}/${filterApi.endpoint}`
	}

	let isFetching = false

	on(
		'update',
		e => {
			if (isFetching) return

			const paged = e.detail.currentPage

			addClass('is-loading', el)
			isFetching = true

			const categoryIds = getSelectedValues('input[name="category[]"]')
			const platformIds = getSelectedValues('input[name="nen-tang[]"]')
			const sortBy = select('select[name="sortBy"]', el).value
			const sortOrder = select('select[name="sortOrder"]', el).value

			let $category_id = ''
			let $platform_id = ''
			if (categoryIds.length) $category_id = categoryIds.join(',')
			if (platformIds.length) $platform_id = platformIds.join(',')

			const contentEl = select('.js-main-content', el)
			const contentShowCountResults = select('.js-show-count-results', el)
			const headerFilterTheme = select('.js-header-filter-theme', el)

			if (contentEl) {
				addClass('is-loading', el)
				if (hasClass('d-none', headerFilterTheme)) {
					addClass('d-none', headerFilterTheme)
				}
				isFetching = true
				contentEl.innerHTML = ''
				window
					.fetch(getRestUrl(), {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({
							category_id: $category_id,
							platform_id: $platform_id,
							page: paged,
							sortBy: sortBy,
							sortOrder: sortOrder
						})
					})
					.then(parseJSON)
					.then(data => {
						if (data.total) {
							const countCurrent =
								Number(paged) < Number(data.pages)
									? Number(paged) * Number(data.per_page)
									: Number(data.total)
							contentShowCountResults.innerText =
								countCurrent + '/' + data.total
						} else {
							contentShowCountResults.innerText = 0
						}
						if (data.html) {
							contentEl.innerHTML = data.html
							// Load Rating
							twmp_rating('.rating')
							// Load More
							initPaginationEvents()
						}
						removeClass('is-loading', el)
						removeClass('d-none', headerFilterTheme)
					})
					.finally(() => {
						isFetching = false
					})
			}
		},
		el
	)

	const filterSidebar = () => {
		inputTriggerEls = selectAll(
			'input[name="category[]"], input[name="nen-tang[]"], select[name="sortBy"], select[name="sortOrder"]',
			el
		)

		if (inputTriggerEls) {
			on(
				'change',
				e => {
					loaded = false
					isFetching = false
					fetchPosts()
				},
				inputTriggerEls
			)
		}
	}

	const initPaginationEvents = () => {
		paginations = selectAll('.page-numbers', el)

		if (paginations && paginations.length) {
			paginations.forEach(page => {
				page.onclick = e => {
					e.preventDefault()

					if (isFetching) return

					let currentPage = 1
					const navPagination = closest('.nav-pagination', e.target)
					const maxPage = getAttribute('data-pages', navPagination)

					if (hasClass('prev', e.target)) {
						currentPage = Number(getAttribute('data-paged', e.target)) || 1
					} else if (hasClass('next', e.target)) {
						currentPage =
							Number(getAttribute('data-paged', e.target)) || maxPage
					} else {
						currentPage = parseInt(e.target.innerText, 10)
					}

					if (Number(currentPage) > Number(maxPage)) {
						currentPage = maxPage
					}

					paginations.forEach(page => {
						removeClass('current', page)
					})

					loaded = false
					isFetching = false
					fetchPosts(currentPage)

					addClass('current', e.target)
				}
			})
		}
	}

	on(
		'load',
		throttle(() => {
			if (inViewPort(el) && !loaded) {
				// Init
				init()
				fetchPosts()
				// Filter
				filterSidebar()
			}
		}, 100),
		window
	)
}
