import { appendHtml, select, selectAll } from 'lib/dom'
import initMegaMenu from 'lib/mega-menu'
import { map } from 'lib/utils'

export default () => {
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
