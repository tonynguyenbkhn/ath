import { on, select, addClass, removeClass, appendHtml, selectAll } from 'lib/dom'
import { throttle, map } from 'lib/utils'
import initMegaMenu from 'lib/mega-menu'

const ACTIVE_EL_CLASS = 'header--visible'

export default el => {
	on(
		'scroll',
		throttle(function () {
			const scrollTop = window.pageYOffset || document.body.scrollTop
			if (scrollTop > 200) {
				addClass(ACTIVE_EL_CLASS, el)
			} else {
				removeClass(ACTIVE_EL_CLASS, el)
			}
		}, 100),
		window
	)

	const buildMegaMenu = () => {
		const menuItems = selectAll('.has-mega-menu')
		const megaMenuEl = select('.js-mega-menu')

		if (!megaMenuEl) return
		if (!menuItems) return

		map(menuItem => {
			const menuClasses = menuItem.className.split(' ')

			if (menuClasses) {
				map(menuClass => {
					const matches = menuClass.match(/sub-menu-(.*)/)

					if (matches && matches[1]) {
						const targetEl = select('#' + matches[1], megaMenuEl)

						if (!targetEl) return

						appendHtml(
							menuItem,
							'<div class="sub-menu is-mega-menu">' +
							targetEl.innerHTML +
							'</div>'
						)
					}
				}, menuClasses)
			}
		}, menuItems)
	}

	buildMegaMenu()
	initMegaMenu()
}
