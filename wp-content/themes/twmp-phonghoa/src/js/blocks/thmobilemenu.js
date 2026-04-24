import {
	on,
	hasClass,
	addClass,
	removeClass,
	toggleClass,
	selectAll,
	closest,
	delegate,
	select,
	slideToggle
} from '../lib/dom'

export default el => {
	const subMenus = selectAll('.th-submenu', el),
		menuItemHasChildren = selectAll('.menu-item-has-children', el),
		expandToggler = selectAll('.th-mean-expand', el),
		menuToggleBtn = '.th-menu-toggle',
		menuToggleClass = 'th-menu-visible',
		bodyToggleClass = 'th-body-visible',
		subMenuClass = 'th-submenu',
		subMenuParent = 'th-item-has-children',
		subMenuParentToggle = 'th-active',
		meanExpandClass = 'th-mean-expand',
		appendElement = '<span class="th-mean-expand"></span>',
		subMenuToggleClass = 'th-open',
		body = document.body,
		toggleSpeed = 400

	const toggle = () => {
		toggleClass(menuToggleClass, el)
		toggleClass(bodyToggleClass, body)

		subMenus.forEach(subMenu => {
			if (hasClass('th-open', subMenu)) {
				removeClass('th-open', subMenu)
				addClass('d-block', subMenu)
				const parentSubMenu = closest('.menu-item-has-children', subMenu)
				removeClass('th-active', parentSubMenu)
			}
		})
	}

	delegate('click', toggle, '.th-menu-toggle', body)
	delegate('click', toggle, '[data-th-menu-trigger]', body)

	if (expandToggler) {
		on(
			'click',
			e => {
				e.preventDefault()
				const li = closest('.th-item-has-children', e.target)
				const subMenu = select('.th-submenu', li)
				slideToggle(subMenu)
				if (subMenu) {
					toggleClass(subMenuToggleClass, subMenu)
				}
			},
			expandToggler
		)
	}
}
