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
	slideToggle,
	toggleClass,
	inViewPort
} from 'lib/dom'
import { throttle } from 'lib/utils'
require('whatwg-fetch')
import Tabs from 'lib/tabs'

const parseJSON = response => response.json()
export default el => {
	if (typeof apiviews === 'undefined') {
		return
	}

	let loaded = false

	const btnShowToggleContent = select('.js-btn-toggle-content', el)
	const wrapperContent = select('.js-content-toggle', el)

	if (btnShowToggleContent) {
		on(
			'click',
			e => {
				e.preventDefault()
				if (wrapperContent) {
					toggleClass('is-expanded', wrapperContent)
				}
			},
			btnShowToggleContent
		)
	}

	const getRestUrl = () => {
		return `${apiviews.ajax.restUrl}/${apiviews.endpoint}`
	}

	const init = () => {
		trigger(
			{
				event: 'update'
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

			isFetching = true

			window
				.fetch(getRestUrl(), {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify({ post_id: apiviews.post_id })
				})
				.then(parseJSON)
				.then(data => {})
				.finally(() => {
					isFetching = false
				})
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
}
