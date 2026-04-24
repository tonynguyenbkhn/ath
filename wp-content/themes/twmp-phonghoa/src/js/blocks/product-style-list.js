import {
	on,
	select,
	getData,
	removeClass,
	addClass,
	hasClass,
	closest,
	selectAll
} from 'lib/dom'

export default el => {
	const shopWrapper = closest('.woocommerce-archive-wrapper', el)
	const ulProducts = select('ul.products', shopWrapper)
	const typeList = ['columns-3', 'columns-4']

	typeList.forEach(t => hasClass(t, ulProducts) && removeClass(t, ulProducts))

	addClass('columns-4', ulProducts)

	const listTypeEl = selectAll('.product-style-list__item', shopWrapper)

	listTypeEl.forEach(t => hasClass('active', t) && removeClass('active', t))

	const itemCurrent = select('.product-style-list__item.columns-4')

	addClass('active', itemCurrent)

	on(
		'click',
		e => {
			const type = getData('type', e.target)
			if (!type || !typeList.includes(type)) return

			typeList.forEach(
				t => hasClass(t, ulProducts) && removeClass(t, ulProducts)
			)

			listTypeEl.forEach(t => hasClass('active', t) && removeClass('active', t))

			const itemCurrent = closest('.product-style-list__item', e.target)

			addClass('active', itemCurrent)

			addClass(type, ulProducts)
		},
		el
	)
}
